<?php
/**
 * Address model
 */

class Address {
class Address {
    private PDO $conn;
    private string $table = 'addresses';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Get all saved addresses for a user.
     */
    public function getAllByUserId(int $userId): array {
        $query = "SELECT id, user_id, receiver_name, phone, province, district, ward, address_line, is_default, created_at, updated_at
                  FROM {$this->table}
                  WHERE user_id = :user_id
                  ORDER BY is_default DESC, updated_at DESC, id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get one address for a user.
     */
    public function getByIdForUser(int $userId, int $addressId): ?array {
        $query = "SELECT id, user_id, receiver_name, phone, province, district, ward, address_line, is_default, created_at, updated_at
                  FROM {$this->table}
                  WHERE user_id = :user_id AND id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $addressId, PDO::PARAM_INT);
        $stmt->execute();

        $address = $stmt->fetch();
        return $address ?: null;
    }

    /**
     * Get the default address for a user.
     */
    public function getDefaultByUserId(int $userId): ?array {
        $query = "SELECT id, user_id, receiver_name, phone, province, district, ward, address_line, is_default, created_at, updated_at
                  FROM {$this->table}
                  WHERE user_id = :user_id
                  ORDER BY is_default DESC, updated_at DESC, id DESC
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $address = $stmt->fetch();
        return $address ?: null;
    }

    /**
     * Create a new address for a user.
     */
    public function createForUser(int $userId, array $data, bool $makeDefault = false): int {
        $startedTransaction = !$this->conn->inTransaction();

        if ($startedTransaction) {
            $this->conn->beginTransaction();
        }

        try {
            $shouldBeDefault = $makeDefault || $this->countByUserId($userId) === 0;

            if ($shouldBeDefault) {
                $this->resetDefault($userId);
            }

            $query = "INSERT INTO {$this->table}
                      (user_id, receiver_name, phone, province, district, ward, address_line, is_default)
                      VALUES (:user_id, :receiver_name, :phone, :province, :district, :ward, :address_line, :is_default)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $this->bindAddressValues($stmt, $data);
            $stmt->bindValue(':is_default', $shouldBeDefault ? 1 : 0, PDO::PARAM_INT);
            $stmt->execute();

            $addressId = (int)$this->conn->lastInsertId();

            if ($startedTransaction) {
                $this->conn->commit();
            }

            return $addressId;
        } catch (Throwable $e) {
            if ($startedTransaction && $this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Update one address for a user.
     */
    public function updateForUser(int $userId, int $addressId, array $data, bool $makeDefault = false): bool {
        $existing = $this->getByIdForUser($userId, $addressId);
        if (!$existing) {
            return false;
        }

        $startedTransaction = !$this->conn->inTransaction();

        if ($startedTransaction) {
            $this->conn->beginTransaction();
        }

        try {
            $shouldBeDefault = $makeDefault || !empty($existing['is_default']);

            if ($shouldBeDefault) {
                $this->resetDefault($userId);
            }

            $query = "UPDATE {$this->table}
                      SET receiver_name = :receiver_name,
                          phone = :phone,
                          province = :province,
                          district = :district,
                          ward = :ward,
                          address_line = :address_line,
                          is_default = :is_default
                      WHERE user_id = :user_id AND id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':id', $addressId, PDO::PARAM_INT);
            $this->bindAddressValues($stmt, $data);
            $stmt->bindValue(':is_default', $shouldBeDefault ? 1 : 0, PDO::PARAM_INT);
            $stmt->execute();

            if ($startedTransaction) {
                $this->conn->commit();
            }

            return true;
        } catch (Throwable $e) {
            if ($startedTransaction && $this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Delete one address for a user.
     * If the deleted address was default, the newest remaining address becomes default.
     */
    public function deleteByIdForUser(int $userId, int $addressId): bool {
        $existing = $this->getByIdForUser($userId, $addressId);
        if (!$existing) {
            return false;
        }

        $startedTransaction = !$this->conn->inTransaction();

        if ($startedTransaction) {
            $this->conn->beginTransaction();
        }

        try {
            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE user_id = :user_id AND id = :id");
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':id', $addressId, PDO::PARAM_INT);
            $stmt->execute();

            if (!empty($existing['is_default'])) {
                $replacement = $this->getDefaultByUserId($userId);
                if ($replacement) {
                    $this->setDefaultById($userId, (int)$replacement['id']);
                }
            }

            if ($startedTransaction) {
                $this->conn->commit();
            }

            return true;
        } catch (Throwable $e) {
            if ($startedTransaction && $this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Mark an existing address as the default one.
     */
    public function setDefaultById(int $userId, int $addressId): bool {
        $startedTransaction = !$this->conn->inTransaction();

        if ($startedTransaction) {
            $this->conn->beginTransaction();
        }

        try {
            $this->resetDefault($userId);

            $setStmt = $this->conn->prepare("UPDATE {$this->table}
                                             SET is_default = 1
                                             WHERE user_id = :user_id AND id = :id");
            $setStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $setStmt->bindValue(':id', $addressId, PDO::PARAM_INT);
            $setStmt->execute();

            if ($startedTransaction) {
                $this->conn->commit();
            }

            return $setStmt->rowCount() > 0;
        } catch (Throwable $e) {
            if ($startedTransaction && $this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Create or replace the default address for a user.
     */
    public function saveDefault(int $userId, array $data): int {
        $existing = $this->getDefaultByUserId($userId);

        if ($existing) {
            $this->updateForUser($userId, (int)$existing['id'], $data, true);
            return (int)$existing['id'];
        }

        return $this->createForUser($userId, $data, true);
    }

    /**
     * Count addresses for a user.
     */
    private function countByUserId(int $userId): int {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id = :user_id");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    /**
     * Reset all default flags for one user.
     */
    private function resetDefault(int $userId): void {
        $resetStmt = $this->conn->prepare("UPDATE {$this->table} SET is_default = 0 WHERE user_id = :user_id");
        $resetStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $resetStmt->execute();
    }

    /**
     * Bind common address values.
     */
    private function bindAddressValues(PDOStatement $stmt, array $data): void {
        $stmt->bindValue(':receiver_name', $data['receiver_name'], PDO::PARAM_STR);
        $stmt->bindValue(':phone', $data['phone'], PDO::PARAM_STR);
        $stmt->bindValue(':province', $data['province'], PDO::PARAM_STR);
        $stmt->bindValue(':district', $data['district'], PDO::PARAM_STR);
        $stmt->bindValue(':ward', $data['ward'] ?: null, $data['ward'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':address_line', $data['address_line'], PDO::PARAM_STR);
    }
}
