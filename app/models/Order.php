<?php
/**
 * Order model
 */

class Order {
    private PDO $conn;
    private string $table = 'orders';
    private ?string $lastErrorMessage = null;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Get the raw PDO connection for transactions.
     */
    public function getConnection(): PDO {
        return $this->conn;
    }

    /**
     * Get the last model-level error message, if any.
     */
    public function getLastErrorMessage(): ?string {
        return $this->lastErrorMessage;
    }

    /**
     * Create an order row.
     */
    public function create(array $data): int {
        $query = "INSERT INTO {$this->table}
                  (user_id, order_number, full_name, email, phone, address, note, subtotal, discount_amount, shipping_fee, total_amount, coupon_code, payment_method, payment_status, order_status)
                  VALUES
                  (:user_id, :order_number, :full_name, :email, :phone, :address, :note, :subtotal, :discount_amount, :shipping_fee, :total_amount, :coupon_code, :payment_method, :payment_status, :order_status)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':user_id', $data['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':order_number', $data['order_number'], PDO::PARAM_STR);
        $stmt->bindValue(':full_name', $data['full_name'], PDO::PARAM_STR);
        $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindValue(':phone', $data['phone'], PDO::PARAM_STR);
        $stmt->bindValue(':address', $data['address'], PDO::PARAM_STR);
        $stmt->bindValue(':note', $data['note'] ?: null, $data['note'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':subtotal', $data['subtotal']);
        $stmt->bindValue(':discount_amount', $data['discount_amount']);
        $stmt->bindValue(':shipping_fee', $data['shipping_fee']);
        $stmt->bindValue(':total_amount', $data['total_amount']);
        $stmt->bindValue(':coupon_code', $data['coupon_code'] ?: null, $data['coupon_code'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':payment_method', $data['payment_method'], PDO::PARAM_STR);
        $stmt->bindValue(':payment_status', $data['payment_status'], PDO::PARAM_STR);
        $stmt->bindValue(':order_status', $data['order_status'], PDO::PARAM_STR);
        $stmt->execute();

        return (int)$this->conn->lastInsertId();
    }

    /**
     * Add a line item to an order.
     */
    public function addItem(int $orderId, array $item): void {
        $query = "INSERT INTO order_details
                  (order_id, product_id, variant_id, product_name, product_image, price, quantity, subtotal)
                  VALUES
                  (:order_id, :product_id, NULL, :product_name, :product_image, :price, :quantity, :subtotal)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->bindValue(':product_id', $item['product_id'], PDO::PARAM_INT);
        $stmt->bindValue(':product_name', $item['product_name'], PDO::PARAM_STR);
        $stmt->bindValue(':product_image', $item['product_image'] ?: null, $item['product_image'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':price', $item['price']);
        $stmt->bindValue(':quantity', $item['quantity'], PDO::PARAM_INT);
        $stmt->bindValue(':subtotal', $item['subtotal']);
        $stmt->execute();
    }

    /**
     * Add a payment row for an order.
     */
    public function addPayment(int $orderId, array $payment): void {
        $query = "INSERT INTO payments (order_id, provider, transaction_code, status, amount, paid_at, note)
                  VALUES (:order_id, :provider, :transaction_code, :status, :amount, :paid_at, :note)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->bindValue(':provider', $payment['provider'], PDO::PARAM_STR);
        $stmt->bindValue(':transaction_code', $payment['transaction_code'] ?: null, $payment['transaction_code'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':status', $payment['status'], PDO::PARAM_STR);
        $stmt->bindValue(':amount', $payment['amount']);
        $stmt->bindValue(':paid_at', $payment['paid_at'] ?: null, $payment['paid_at'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':note', $payment['note'] ?: null, $payment['note'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->execute();
    }

    /**
     * Get recent orders with item counts for a user.
     */
    public function getByUserId(int $userId, int $limit = 10): array {
        $query = "SELECT o.*,
                         COUNT(od.id) AS item_count,
                         COALESCE(SUM(od.quantity), 0) AS total_quantity
                  FROM {$this->table} o
                  LEFT JOIN order_details od ON od.order_id = o.id
                  WHERE o.user_id = :user_id
                  GROUP BY o.id
                  ORDER BY o.created_at DESC, o.id DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get paginated orders for one user with optional order status filter.
     */
    public function getPaginatedByUserId(int $userId, int $limit = 10, int $offset = 0, string $orderStatus = 'all'): array {
        $filterSql = '';
        if ($orderStatus !== 'all') {
            $filterSql = ' AND o.order_status = :order_status';
        }

        $query = "SELECT o.*,
                         COUNT(od.id) AS item_count,
                         COALESCE(SUM(od.quantity), 0) AS total_quantity
                  FROM {$this->table} o
                  LEFT JOIN order_details od ON od.order_id = o.id
                  WHERE o.user_id = :user_id{$filterSql}
                  GROUP BY o.id
                  ORDER BY o.created_at DESC, o.id DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        if ($orderStatus !== 'all') {
            $stmt->bindValue(':order_status', $orderStatus, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Count orders for one user with optional order status filter.
     */
    public function countByUserId(int $userId, string $orderStatus = 'all'): int {
        $query = "SELECT COUNT(*)
                  FROM {$this->table}
                  WHERE user_id = :user_id";

        if ($orderStatus !== 'all') {
            $query .= " AND order_status = :order_status";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        if ($orderStatus !== 'all') {
            $stmt->bindValue(':order_status', $orderStatus, PDO::PARAM_STR);
        }
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    /**
     * Get dashboard-style order stats for a user.
     */
    public function getUserOrderStats(int $userId): array {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total_orders,
                    COALESCE(SUM(order_status IN ('pending', 'confirmed', 'processing')), 0) AS active_orders,
                    COALESCE(SUM(order_status = 'shipping'), 0) AS shipping_orders,
                    COALESCE(SUM(payment_status IN ('unpaid', 'pending_review', 'failed')), 0) AS payment_attention_orders
             FROM {$this->table}
             WHERE user_id = :user_id"
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $stats = $stmt->fetch() ?: [];

        return [
            'total_orders' => (int)($stats['total_orders'] ?? 0),
            'active_orders' => (int)($stats['active_orders'] ?? 0),
            'shipping_orders' => (int)($stats['shipping_orders'] ?? 0),
            'payment_attention_orders' => (int)($stats['payment_attention_orders'] ?? 0),
        ];
    }

    /**
     * Get a full order detail with items and latest payment for one user.
     */
    public function getDetailByUserId(int $userId, int $orderId): ?array {
        $query = "SELECT o.*,
                         COUNT(od.id) AS item_count,
                         COALESCE(SUM(od.quantity), 0) AS total_quantity
                  FROM {$this->table} o
                  LEFT JOIN order_details od ON od.order_id = o.id
                  WHERE o.user_id = :user_id AND o.id = :order_id
                  GROUP BY o.id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->execute();

        $order = $stmt->fetch();
        if (!$order) {
            return null;
        }

        $itemStmt = $this->conn->prepare(
            "SELECT od.*,
                    p.slug AS product_slug
             FROM order_details od
             INNER JOIN {$this->table} o ON o.id = od.order_id
             LEFT JOIN products p ON p.id = od.product_id
             WHERE o.user_id = :user_id AND od.order_id = :order_id
             ORDER BY od.id ASC"
        );
        $itemStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $itemStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $itemStmt->execute();
        $order['items'] = $itemStmt->fetchAll();

        $paymentStmt = $this->conn->prepare(
            "SELECT p.*
             FROM payments p
             INNER JOIN {$this->table} o ON o.id = p.order_id
             WHERE o.user_id = :user_id AND p.order_id = :order_id
             ORDER BY p.id DESC
             LIMIT 1"
        );
        $paymentStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $paymentStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $paymentStmt->execute();
        $order['payment'] = $paymentStmt->fetch() ?: null;

        return $order;
    }

    /**
     * Get online_mock orders for admin review by payment status.
     */
    public function getAdminOnlineMockOrdersByPaymentStatus(string $paymentStatus, int $limit = 30): array {
        $query = "SELECT o.id,
                         o.order_number,
                         o.full_name,
                         o.email,
                         o.phone,
                         o.total_amount,
                         o.payment_status,
                         o.order_status,
                         o.created_at,
                         o.updated_at,
                         p.transaction_code,
                         p.status AS payment_record_status,
                         p.paid_at,
                         p.note AS payment_note
                  FROM {$this->table} o
                  LEFT JOIN payments p ON p.id = (
                      SELECT p2.id
                      FROM payments p2
                      WHERE p2.order_id = o.id
                      ORDER BY p2.id DESC
                      LIMIT 1
                  )
                  WHERE o.payment_method = 'online_mock' AND o.payment_status = :payment_status
                  ORDER BY o.created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':payment_status', $paymentStatus, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Count online_mock orders by payment status for admin realtime checks.
     */
    public function countAdminOnlineMockOrdersByPaymentStatus(string $paymentStatus): int {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*)
             FROM {$this->table}
             WHERE payment_method = 'online_mock' AND payment_status = :payment_status"
        );
        $stmt->bindValue(':payment_status', $paymentStatus, PDO::PARAM_STR);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    /**
     * Get minimal online_mock order info for external QR payment portal.
     */
    public function getOnlineMockOrderForPortal(int $orderId): ?array {
        $stmt = $this->conn->prepare(
            "SELECT id, user_id, order_number, total_amount, payment_method, payment_status, created_at
             FROM {$this->table}
             WHERE id = :order_id
             LIMIT 1"
        );
        $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->execute();

        $order = $stmt->fetch();
        if (!$order || (string)$order['payment_method'] !== 'online_mock') {
            return null;
        }

        return $order;
    }

    /**
     * Confirm online_mock payment through external QR portal.
     */
    public function confirmOnlineMockPaymentByPortal(int $orderId): bool {
        $order = $this->getOnlineMockOrderForPortal($orderId);
        if (!$order) {
            return false;
        }

        return $this->confirmOnlineMockPaymentByUser((int)$order['user_id'], $orderId);
    }

    /**
     * Mark an online_mock order as paid from user confirmation.
     */
    public function confirmOnlineMockPaymentByUser(int $userId, int $orderId): bool {
        $this->clearLastError();

        $this->conn->beginTransaction();

        try {
            $orderStmt = $this->conn->prepare(
                "SELECT id, order_number, total_amount, payment_method, payment_status
                 FROM {$this->table}
                 WHERE id = :order_id AND user_id = :user_id
                 LIMIT 1
                 FOR UPDATE"
            );
            $orderStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
            $orderStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $orderStmt->execute();

            $order = $orderStmt->fetch();
            if (!$order || (string)$order['payment_method'] !== 'online_mock') {
                $this->conn->rollBack();
                $this->lastErrorMessage = 'Không tìm thấy đơn chuyển khoản giả lập hợp lệ để xác nhận.';
                return false;
            }

            if ((string)$order['payment_status'] !== 'unpaid') {
                $this->conn->rollBack();
                $this->lastErrorMessage = 'Đơn hàng hiện không còn ở trạng thái chờ xác nhận thanh toán.';
                return false;
            }

            $generatedCode = 'MOCK' . date('YmdHis') . random_int(100, 999);
            $paymentNote = 'Khách đã xác nhận chuyển khoản giả lập. Chờ admin duyệt.';

            $paymentUpdateStmt = $this->conn->prepare(
                "UPDATE payments
                 SET paid_at = NULL,
                     transaction_code = IFNULL(NULLIF(transaction_code, ''), :transaction_code),
                     note = :note
                 WHERE order_id = :order_id AND provider = 'online_mock' AND status = 'unpaid'
                 ORDER BY id DESC
                 LIMIT 1"
            );
            $paymentUpdateStmt->bindValue(':transaction_code', $generatedCode, PDO::PARAM_STR);
            $paymentUpdateStmt->bindValue(':note', $paymentNote, PDO::PARAM_STR);
            $paymentUpdateStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
            $paymentUpdateStmt->execute();

            if ($paymentUpdateStmt->rowCount() < 1) {
                $this->addPayment($orderId, [
                    'provider' => 'online_mock',
                    'transaction_code' => $generatedCode,
                    'status' => 'unpaid',
                    'amount' => (float)$order['total_amount'],
                    'paid_at' => null,
                    'note' => $paymentNote,
                ]);
            }

            $orderUpdateStmt = $this->conn->prepare(
                "UPDATE {$this->table}
                 SET payment_status = 'pending_review',
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :order_id AND user_id = :user_id AND payment_status = 'unpaid'"
            );
            $orderUpdateStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
            $orderUpdateStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $orderUpdateStmt->execute();

            if ($orderUpdateStmt->rowCount() < 1) {
                $this->conn->rollBack();
                $this->lastErrorMessage = 'Không thể cập nhật trạng thái thanh toán của đơn hàng.';
                return false;
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            $this->lastErrorMessage = $this->mapOnlineMockPaymentError($e);
            error_log('Order confirmOnlineMockPaymentByUser Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Approve a pending online_mock payment from admin area.
     */
    public function approveOnlineMockPaymentByAdmin(int $orderId): bool {
        $this->clearLastError();

        $this->conn->beginTransaction();

        try {
            $orderStmt = $this->conn->prepare(
                "SELECT id
                 FROM {$this->table}
                 WHERE id = :order_id
                   AND payment_method = 'online_mock'
                   AND payment_status = 'pending_review'
                 LIMIT 1
                 FOR UPDATE"
            );
            $orderStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
            $orderStmt->execute();

            if (!$orderStmt->fetch()) {
                $this->conn->rollBack();
                $this->lastErrorMessage = 'Không tìm thấy đơn hàng đang chờ duyệt thanh toán.';
                return false;
            }

            $paidAt = date('Y-m-d H:i:s');
            $paymentUpdateStmt = $this->conn->prepare(
                "UPDATE payments
                 SET status = 'paid',
                     paid_at = :paid_at,
                     note = CONCAT(IFNULL(note, ''), IF(note IS NULL OR note = '', '', ' | '), 'Admin đã duyệt chuyển khoản giả lập.')
                 WHERE order_id = :order_id AND provider = 'online_mock' AND status IN ('pending_review', 'unpaid')
                 ORDER BY id DESC
                 LIMIT 1"
            );
            $paymentUpdateStmt->bindValue(':paid_at', $paidAt, PDO::PARAM_STR);
            $paymentUpdateStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
            $paymentUpdateStmt->execute();

            $orderUpdateStmt = $this->conn->prepare(
                "UPDATE {$this->table}
                 SET payment_status = 'paid',
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :order_id AND payment_status = 'pending_review'"
            );
            $orderUpdateStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
            $orderUpdateStmt->execute();

            if ($orderUpdateStmt->rowCount() < 1) {
                $this->conn->rollBack();
                $this->lastErrorMessage = 'Không thể cập nhật trạng thái thanh toán sang đã duyệt.';
                return false;
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            $this->lastErrorMessage = $this->mapOnlineMockPaymentError($e);
            error_log('Order approveOnlineMockPaymentByAdmin Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reject a pending online_mock payment from admin area.
     */
    public function rejectOnlineMockPaymentByAdmin(int $orderId, string $reason): bool {
        $this->clearLastError();

        $this->conn->beginTransaction();

        try {
            $orderStmt = $this->conn->prepare(
                "SELECT id
                 FROM {$this->table}
                 WHERE id = :order_id
                   AND payment_method = 'online_mock'
                   AND payment_status = 'pending_review'
                 LIMIT 1
                 FOR UPDATE"
            );
            $orderStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
            $orderStmt->execute();

            if (!$orderStmt->fetch()) {
                $this->conn->rollBack();
                $this->lastErrorMessage = 'Không tìm thấy đơn hàng đang chờ duyệt để từ chối.';
                return false;
            }

            $rejectNote = 'Admin từ chối duyệt chuyển khoản giả lập';
            if ($reason !== '') {
                $rejectNote .= ': ' . $reason;
            }

            $paymentUpdateStmt = $this->conn->prepare(
                "UPDATE payments
                 SET status = 'failed',
                     paid_at = NULL,
                     note = CONCAT(IFNULL(note, ''), IF(note IS NULL OR note = '', '', ' | '), :reject_note)
                 WHERE order_id = :order_id AND provider = 'online_mock' AND status IN ('pending_review', 'unpaid')
                 ORDER BY id DESC
                 LIMIT 1"
            );
            $paymentUpdateStmt->bindValue(':reject_note', $rejectNote, PDO::PARAM_STR);
            $paymentUpdateStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
            $paymentUpdateStmt->execute();

            $orderUpdateStmt = $this->conn->prepare(
                "UPDATE {$this->table}
                 SET payment_status = 'failed',
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :order_id AND payment_status = 'pending_review'"
            );
            $orderUpdateStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
            $orderUpdateStmt->execute();

            if ($orderUpdateStmt->rowCount() < 1) {
                $this->conn->rollBack();
                $this->lastErrorMessage = 'Không thể cập nhật trạng thái thanh toán sang thất bại.';
                return false;
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            $this->lastErrorMessage = $this->mapOnlineMockPaymentError($e);
            error_log('Order rejectOnlineMockPaymentByAdmin Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Re-submit a rejected online_mock payment for admin review.
     */
    public function resubmitOnlineMockPaymentByUser(int $userId, int $orderId): bool {
        $this->clearLastError();

        $this->conn->beginTransaction();

        try {
            $orderStmt = $this->conn->prepare(
                "SELECT id
                 FROM {$this->table}
                 WHERE id = :order_id
                   AND user_id = :user_id
                   AND payment_method = 'online_mock'
                   AND payment_status = 'failed'
                 LIMIT 1
                 FOR UPDATE"
            );
            $orderStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
            $orderStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $orderStmt->execute();

            if (!$orderStmt->fetch()) {
                $this->conn->rollBack();
                $this->lastErrorMessage = 'Không tìm thấy đơn hàng chuyển khoản giả lập cần gửi lại yêu cầu.';
                return false;
            }

            $paymentUpdateStmt = $this->conn->prepare(
                "UPDATE payments
                 SET status = 'unpaid',
                     paid_at = NULL,
                     note = CONCAT(IFNULL(note, ''), IF(note IS NULL OR note = '', '', ' | '), 'Khách đã gửi lại yêu cầu duyệt chuyển khoản giả lập.')
                 WHERE order_id = :order_id AND provider = 'online_mock' AND status = 'failed'
                 ORDER BY id DESC
                 LIMIT 1"
            );
            $paymentUpdateStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
            $paymentUpdateStmt->execute();

            $orderUpdateStmt = $this->conn->prepare(
                "UPDATE {$this->table}
                 SET payment_status = 'pending_review',
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :order_id AND user_id = :user_id AND payment_status = 'failed'"
            );
            $orderUpdateStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
            $orderUpdateStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $orderUpdateStmt->execute();

            if ($orderUpdateStmt->rowCount() < 1) {
                $this->conn->rollBack();
                $this->lastErrorMessage = 'Không thể cập nhật trạng thái thanh toán để gửi lại yêu cầu.';
                return false;
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            $this->lastErrorMessage = $this->mapOnlineMockPaymentError($e);
            error_log('Order resubmitOnlineMockPaymentByUser Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get admin order overview stats.
     */
    public function getAdminStats(): array {
        $stmt = $this->conn->query(
            "SELECT COUNT(*) AS total_orders,
                    COALESCE(SUM(order_status = 'pending'), 0) AS pending_orders,
                    COALESCE(SUM(order_status = 'confirmed'), 0) AS confirmed_orders,
                    COALESCE(SUM(order_status = 'processing'), 0) AS processing_orders,
                    COALESCE(SUM(order_status = 'shipping'), 0) AS shipping_orders,
                    COALESCE(SUM(order_status = 'delivered'), 0) AS delivered_orders,
                    COALESCE(SUM(order_status = 'cancelled'), 0) AS cancelled_orders,
                    COALESCE(SUM(payment_status = 'pending_review'), 0) AS pending_payment_reviews
             FROM {$this->table}"
        );

        $stats = $stmt->fetch() ?: [];

        return [
            'total_orders' => (int)($stats['total_orders'] ?? 0),
            'pending_orders' => (int)($stats['pending_orders'] ?? 0),
            'confirmed_orders' => (int)($stats['confirmed_orders'] ?? 0),
            'processing_orders' => (int)($stats['processing_orders'] ?? 0),
            'shipping_orders' => (int)($stats['shipping_orders'] ?? 0),
            'delivered_orders' => (int)($stats['delivered_orders'] ?? 0),
            'cancelled_orders' => (int)($stats['cancelled_orders'] ?? 0),
            'pending_payment_reviews' => (int)($stats['pending_payment_reviews'] ?? 0),
        ];
    }

    /**
     * Get paginated admin orders with filters.
     */
    public function getAdminList(string $search = '', string $orderStatus = 'all', string $paymentStatus = 'all', int $limit = 20, int $offset = 0): array {
        $bindings = [];
        $whereSql = $this->buildAdminOrderFilterSql($search, $orderStatus, $paymentStatus, $bindings);

        $query = "SELECT o.*,
                         COALESCE(od_summary.item_count, 0) AS item_count,
                         COALESCE(od_summary.total_quantity, 0) AS total_quantity,
                         p.transaction_code,
                         p.status AS payment_record_status,
                         p.paid_at,
                         p.note AS payment_note
                  FROM {$this->table} o
                  LEFT JOIN (
                      SELECT order_id,
                             COUNT(*) AS item_count,
                             COALESCE(SUM(quantity), 0) AS total_quantity
                      FROM order_details
                      GROUP BY order_id
                  ) od_summary ON od_summary.order_id = o.id
                  LEFT JOIN payments p ON p.id = (
                      SELECT p2.id
                      FROM payments p2
                      WHERE p2.order_id = o.id
                      ORDER BY p2.id DESC
                      LIMIT 1
                  )
                  WHERE {$whereSql}
                  ORDER BY o.created_at DESC, o.id DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $this->bindAdminOrderFilters($stmt, $bindings);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Count admin orders with filters.
     */
    public function getAdminTotal(string $search = '', string $orderStatus = 'all', string $paymentStatus = 'all'): int {
        $bindings = [];
        $whereSql = $this->buildAdminOrderFilterSql($search, $orderStatus, $paymentStatus, $bindings);

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} o WHERE {$whereSql}");
        $this->bindAdminOrderFilters($stmt, $bindings);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    /**
     * Get a full order detail for admin view.
     */
    public function getAdminDetailById(int $orderId): ?array {
        $stmt = $this->conn->prepare(
            "SELECT o.*,
                    u.username,
                    u.full_name AS account_full_name,
                    u.email AS account_email,
                    u.phone AS account_phone
             FROM {$this->table} o
             INNER JOIN users u ON u.id = o.user_id
             WHERE o.id = :order_id
             LIMIT 1"
        );
        $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->execute();

        $order = $stmt->fetch();
        if (!$order) {
            return null;
        }

        $itemStmt = $this->conn->prepare(
            "SELECT od.*,
                    p.slug AS product_slug
             FROM order_details od
             LEFT JOIN products p ON p.id = od.product_id
             WHERE od.order_id = :order_id
             ORDER BY od.id ASC"
        );
        $itemStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $itemStmt->execute();
        $order['items'] = $itemStmt->fetchAll();

        $paymentStmt = $this->conn->prepare(
            "SELECT *
             FROM payments
             WHERE order_id = :order_id
             ORDER BY id DESC"
        );
        $paymentStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $paymentStmt->execute();
        $order['payments'] = $paymentStmt->fetchAll();
        $order['payment'] = $order['payments'][0] ?? null;

        return $order;
    }

    /**
     * Update the fulfillment status of an order from admin area.
     */
    public function updateAdminOrderStatus(int $orderId, string $nextStatus): bool {
        $this->clearLastError();

        $allowedStatuses = ['pending', 'confirmed', 'processing', 'shipping', 'delivered', 'cancelled'];
        if (!in_array($nextStatus, $allowedStatuses, true)) {
            $this->lastErrorMessage = 'Trạng thái đơn hàng không hợp lệ.';
            return false;
        }

        $this->conn->beginTransaction();

        try {
            $currentStmt = $this->conn->prepare(
                "SELECT order_status
                 FROM {$this->table}
                 WHERE id = :order_id
                 LIMIT 1
                 FOR UPDATE"
            );
            $currentStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
            $currentStmt->execute();

            $currentStatus = $currentStmt->fetchColumn();
            if ($currentStatus === false) {
                $this->conn->rollBack();
                $this->lastErrorMessage = 'Không tìm thấy đơn hàng cần cập nhật.';
                return false;
            }

            if ((string)$currentStatus === $nextStatus) {
                $this->conn->commit();
                return true;
            }

            $updateStmt = $this->conn->prepare(
                "UPDATE {$this->table}
                 SET order_status = :order_status,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :order_id"
            );
            $updateStmt->bindValue(':order_status', $nextStatus, PDO::PARAM_STR);
            $updateStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
            $updateStmt->execute();

            if ($updateStmt->rowCount() < 1) {
                $this->conn->rollBack();
                $this->lastErrorMessage = 'Không thể cập nhật trạng thái đơn hàng lúc này.';
                return false;
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            $this->lastErrorMessage = 'Có lỗi khi cập nhật trạng thái đơn hàng. Vui lòng thử lại sau.';
            error_log('Order updateAdminOrderStatus Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Build SQL filters for admin order queries.
     */
    private function buildAdminOrderFilterSql(string $search, string $orderStatus, string $paymentStatus, array &$bindings): string {
        $conditions = ['1 = 1'];

        $search = trim($search);
        if ($search !== '') {
            $searchValue = '%' . $search . '%';
            $conditions[] = '(o.order_number LIKE :search_order_number OR o.full_name LIKE :search_full_name OR o.email LIKE :search_email OR o.phone LIKE :search_phone)';
            $bindings[':search_order_number'] = $searchValue;
            $bindings[':search_full_name'] = $searchValue;
            $bindings[':search_email'] = $searchValue;
            $bindings[':search_phone'] = $searchValue;
        }

        if ($orderStatus !== 'all') {
            $conditions[] = 'o.order_status = :order_status';
            $bindings[':order_status'] = $orderStatus;
        }

        if ($paymentStatus !== 'all') {
            $conditions[] = 'o.payment_status = :payment_status';
            $bindings[':payment_status'] = $paymentStatus;
        }

        return implode(' AND ', $conditions);
    }

    /**
     * Bind admin order filter values.
     */
    private function bindAdminOrderFilters(PDOStatement $stmt, array $bindings): void {
        foreach ($bindings as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
    }

    /**
     * Reset last error state before a mutating payment action.
     */
    private function clearLastError(): void {
        $this->lastErrorMessage = null;
    }

    /**
     * Map low-level SQL exceptions to actionable payment errors.
     */
    private function mapOnlineMockPaymentError(Throwable $e): string {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'pending_review')) {
            return $this->missingPendingReviewSchemaMessage();
        }

        if (str_contains($message, 'data truncated') && (str_contains($message, 'payment_status') || str_contains($message, 'status'))) {
            return $this->missingPendingReviewSchemaMessage();
        }

        return 'Có lỗi khi cập nhật xác nhận thanh toán mô phỏng. Vui lòng thử lại sau.';
    }

    /**
     * Shared guidance when the database schema is missing pending_review.
     */
    private function missingPendingReviewSchemaMessage(): string {
        return 'CSDL chưa hỗ trợ trạng thái pending_review. Hãy chạy file database/alter_payment_status_pending_review.sql rồi thử lại.';
    }
}
