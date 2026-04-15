<?php
/**
 * InventoryBatch model — FIFO batch inventory management.
 */
class InventoryBatch {
    private PDO $conn;
    private string $table = 'inventory_batches';

    /**
     * @param PDO|null $conn Reuse an existing PDO connection (for transaction safety).
     *                       When null, creates a standalone connection.
     */
    public function __construct(?PDO $conn = null) {
        if ($conn !== null) {
            $this->conn = $conn;
        } else {
            $db = new Database();
            $this->conn = $db->getConnection();
        }
    }

    /**
     * Check whether the inventory_batches table exists.
     */
    public function tableExists(): bool {
        try {
            $this->conn->query("SELECT 1 FROM {$this->table} LIMIT 1");
            return true;
        } catch (\PDOException) {
            return false;
        }
    }

    /**
     * Get all batches for a product ordered by received_at ASC (oldest first / FIFO).
     */
    public function getByProduct(int $productId): array {
        $stmt = $this->conn->prepare(
            "SELECT * FROM {$this->table}
             WHERE product_id = :product_id
             ORDER BY received_at ASC, id ASC"
        );
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get batches with remaining stock for a product (FIFO order).
     */
    public function getAvailableByProduct(int $productId): array {
        $stmt = $this->conn->prepare(
            "SELECT * FROM {$this->table}
             WHERE product_id = :product_id AND quantity_remaining > 0
             ORDER BY received_at ASC, id ASC"
        );
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get total remaining quantity across all batches for a product.
     */
    public function getTotalRemaining(int $productId): int {
        $stmt = $this->conn->prepare(
            "SELECT COALESCE(SUM(quantity_remaining), 0) AS total
             FROM {$this->table}
             WHERE product_id = :product_id"
        );
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Receive a new batch of stock for a product.
     * Also updates the product's stock field to stay in sync.
     *
     * @return int The new batch ID.
     */
    public function receiveBatch(int $productId, array $data): int {
        $quantity = max(1, (int)($data['quantity'] ?? 0));
        $batchCode = trim((string)($data['batch_code'] ?? ''));
        if ($batchCode === '') {
            $batchCode = $this->generateBatchCode($productId);
        }

        $this->conn->beginTransaction();

        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO {$this->table}
                    (product_id, batch_code, quantity_received, quantity_remaining, cost_price, supplier, note, received_at)
                 VALUES
                    (:product_id, :batch_code, :quantity_received, :quantity_remaining, :cost_price, :supplier, :note, :received_at)"
            );
            $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
            $stmt->bindValue(':batch_code', $batchCode, PDO::PARAM_STR);
            $stmt->bindValue(':quantity_received', $quantity, PDO::PARAM_INT);
            $stmt->bindValue(':quantity_remaining', $quantity, PDO::PARAM_INT);

            $costPrice = isset($data['cost_price']) && $data['cost_price'] !== '' ? (float)$data['cost_price'] : null;
            $stmt->bindValue(':cost_price', $costPrice, $costPrice !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

            $supplier = trim((string)($data['supplier'] ?? ''));
            $stmt->bindValue(':supplier', $supplier !== '' ? $supplier : null, $supplier !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);

            $note = trim((string)($data['note'] ?? ''));
            $stmt->bindValue(':note', $note !== '' ? $note : null, $note !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);

            $receivedAt = trim((string)($data['received_at'] ?? ''));
            $stmt->bindValue(':received_at', $receivedAt !== '' ? $receivedAt : date('Y-m-d H:i:s'), PDO::PARAM_STR);

            $stmt->execute();
            $batchId = (int)$this->conn->lastInsertId();

            $this->syncProductStock($productId);

            $logStmt = $this->conn->prepare(
                "INSERT INTO inventory_logs (product_id, order_id, action, quantity, note)
                 VALUES (:product_id, NULL, 'import', :quantity, :note)"
            );
            $logStmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
            $logStmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
            $logStmt->bindValue(':note', 'Nhập lô ' . $batchCode . ($note !== '' ? ' — ' . $note : ''), PDO::PARAM_STR);
            $logStmt->execute();

            $this->conn->commit();
            return $batchId;
        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Synchronize a product's stock column with the sum of batch remaining quantities.
     */
    public function syncProductStock(int $productId): void {
        $totalRemaining = $this->getTotalRemaining($productId);
        $stmt = $this->conn->prepare(
            "UPDATE products SET stock = :stock, updated_at = CURRENT_TIMESTAMP WHERE id = :id"
        );
        $stmt->bindValue(':stock', $totalRemaining, PDO::PARAM_INT);
        $stmt->bindValue(':id', $productId, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Check whether the inventory_batch_allocations table exists.
     */
    public function allocationsTableExists(): bool {
        try {
            $this->conn->query("SELECT 1 FROM inventory_batch_allocations LIMIT 1");
            return true;
        } catch (\PDOException) {
            return false;
        }
    }

    /**
     * Deduct quantity from batches using FIFO (oldest first).
     * Records allocations per order so restore can reverse exactly.
     *
     * @param int $productId
     * @param int $quantity
     * @param int $orderId If > 0, record allocations for this order.
     * @return array<int, array{batch_id: int, deducted: int}>
     */
    public function deductFifo(int $productId, int $quantity, int $orderId = 0): array {
        $batches = $this->getAvailableByProduct($productId);
        $remaining = $quantity;
        $consumed = [];
        $hasAllocations = $orderId > 0 && $this->allocationsTableExists();

        $deductStmt = $this->conn->prepare(
            "UPDATE {$this->table}
             SET quantity_remaining = quantity_remaining - :deduct,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id AND quantity_remaining >= :deduct_check"
        );

        $allocStmt = $hasAllocations ? $this->conn->prepare(
            "INSERT INTO inventory_batch_allocations (order_id, product_id, batch_id, quantity)
             VALUES (:order_id, :product_id, :batch_id, :quantity)"
        ) : null;

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $available = (int)$batch['quantity_remaining'];
            $deduct = min($remaining, $available);

            $deductStmt->bindValue(':deduct', $deduct, PDO::PARAM_INT);
            $deductStmt->bindValue(':id', (int)$batch['id'], PDO::PARAM_INT);
            $deductStmt->bindValue(':deduct_check', $deduct, PDO::PARAM_INT);
            $deductStmt->execute();

            if ($deductStmt->rowCount() > 0) {
                $consumed[] = ['batch_id' => (int)$batch['id'], 'deducted' => $deduct];
                $remaining -= $deduct;

                if ($allocStmt) {
                    $allocStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
                    $allocStmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
                    $allocStmt->bindValue(':batch_id', (int)$batch['id'], PDO::PARAM_INT);
                    $allocStmt->bindValue(':quantity', $deduct, PDO::PARAM_INT);
                    $allocStmt->execute();
                }
            }
        }

        return $consumed;
    }

    /**
     * Restore previously deducted batch quantities for an order+product.
     * Reads allocations to return stock to the exact batches that were consumed.
     * Deletes the allocation records after restoring.
     *
     * @return int Total units restored to batches.
     */
    public function restoreFifo(int $orderId, int $productId): int {
        if (!$this->allocationsTableExists()) {
            return 0;
        }

        $allocStmt = $this->conn->prepare(
            "SELECT id, batch_id, quantity
             FROM inventory_batch_allocations
             WHERE order_id = :order_id AND product_id = :product_id
             ORDER BY id ASC"
        );
        $allocStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $allocStmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $allocStmt->execute();

        $allocations = $allocStmt->fetchAll();
        if ($allocations === [] || $allocations === false) {
            return 0;
        }

        $restoreStmt = $this->conn->prepare(
            "UPDATE {$this->table}
             SET quantity_remaining = quantity_remaining + :restore,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :batch_id"
        );

        $deleteStmt = $this->conn->prepare(
            "DELETE FROM inventory_batch_allocations WHERE id = :id"
        );

        $totalRestored = 0;

        foreach ($allocations as $alloc) {
            $qty = (int)$alloc['quantity'];
            if ($qty <= 0) {
                continue;
            }

            $restoreStmt->bindValue(':restore', $qty, PDO::PARAM_INT);
            $restoreStmt->bindValue(':batch_id', (int)$alloc['batch_id'], PDO::PARAM_INT);
            $restoreStmt->execute();

            $deleteStmt->bindValue(':id', (int)$alloc['id'], PDO::PARAM_INT);
            $deleteStmt->execute();

            $totalRestored += $qty;
        }

        return $totalRestored;
    }

    /**
     * Check whether an order has any batch allocations recorded for a product.
     */
    public function hasAllocations(int $orderId, int $productId): bool {
        if (!$this->allocationsTableExists()) {
            return false;
        }

        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM inventory_batch_allocations
             WHERE order_id = :order_id AND product_id = :product_id"
        );
        $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();

        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Get a single batch by ID.
     */
    public function getById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }

    /**
     * Get batches for admin listing with product info.
     */
    public function getAdminList(int $productId = 0, int $limit = 50, int $offset = 0): array {
        $where = '1 = 1';
        $params = [];

        if ($productId > 0) {
            $where .= ' AND ib.product_id = :product_id';
            $params[':product_id'] = $productId;
        }

        $stmt = $this->conn->prepare(
            "SELECT ib.*, p.name AS product_name, p.stock AS product_stock
             FROM {$this->table} ib
             LEFT JOIN products p ON ib.product_id = p.id
             WHERE {$where}
             ORDER BY ib.received_at DESC, ib.id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    /**
     * Count batches for pagination.
     */
    public function getAdminTotal(int $productId = 0): int {
        $where = '1 = 1';
        $params = [];

        if ($productId > 0) {
            $where .= ' AND product_id = :product_id';
            $params[':product_id'] = $productId;
        }

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE {$where}");
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Generate a unique batch code.
     */
    private function generateBatchCode(int $productId): string {
        return 'BATCH-' . $productId . '-' . date('YmdHis') . '-' . random_int(100, 999);
    }

    /**
     * Expose the PDO connection for transactional callers.
     */
    public function getConnection(): PDO {
        return $this->conn;
    }
}
