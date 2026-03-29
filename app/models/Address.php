<?php
/**
 * Address model
 */

class Address {
    private PDO $conn;
    private string $table = 'addresses';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
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
     * Create or replace the default address for a user.
     */
    public function saveDefault(int $userId, array $data): int {
        $existing = $this->getDefaultByUserId($userId);

        $this->conn->beginTransaction();

        try {
            $resetStmt = $this->conn->prepare("UPDATE {$this->table} SET is_default = 0 WHERE user_id = :user_id");
            $resetStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $resetStmt->execute();

            if ($existing) {
                $query = "UPDATE {$this->table}
                          SET receiver_name = :receiver_name,
                              phone = :phone,
                              province = :province,
                              district = :district,
                              ward = :ward,
                              address_line = :address_line,
                              is_default = 1
                          WHERE id = :id";

                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':id', (int)$existing['id'], PDO::PARAM_INT);
            } else {
                $query = "INSERT INTO {$this->table}
                          (user_id, receiver_name, phone, province, district, ward, address_line, is_default)
                          VALUES (:user_id, :receiver_name, :phone, :province, :district, :ward, :address_line, 1)";

                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }

            $stmt->bindValue(':receiver_name', $data['receiver_name'], PDO::PARAM_STR);
            $stmt->bindValue(':phone', $data['phone'], PDO::PARAM_STR);
            $stmt->bindValue(':province', $data['province'], PDO::PARAM_STR);
            $stmt->bindValue(':district', $data['district'], PDO::PARAM_STR);
            $stmt->bindValue(':ward', $data['ward'] ?: null, $data['ward'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':address_line', $data['address_line'], PDO::PARAM_STR);
            $stmt->execute();

            $addressId = $existing ? (int)$existing['id'] : (int)$this->conn->lastInsertId();
            $this->conn->commit();

            return $addressId;
        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            throw $e;
        }
    }
}
