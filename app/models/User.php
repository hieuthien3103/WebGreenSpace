<?php
/**
 * User model
 */

class User {
    private PDO $conn;
    private string $table = 'users';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Find active user by ID.
     */
    public function findById(int $id): ?array {
        $query = "SELECT id, username, email, password, full_name, phone, role, status, created_at, updated_at
                  FROM {$this->table}
                  WHERE id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?array {
        $query = "SELECT id, username, email, password, full_name, phone, role, status, created_at, updated_at
                  FROM {$this->table}
                  WHERE email = :email
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user by username.
     */
    public function findByUsername(string $username): ?array {
        $query = "SELECT id, username, email, password, full_name, phone, role, status, created_at, updated_at
                  FROM {$this->table}
                  WHERE username = :username
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user by email or username.
     */
    public function findByLogin(string $identifier): ?array {
        $query = "SELECT id, username, email, password, full_name, phone, role, status, created_at, updated_at
                  FROM {$this->table}
                  WHERE email = :email_identifier OR username = :username_identifier
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':email_identifier', $identifier, PDO::PARAM_STR);
        $stmt->bindValue(':username_identifier', $identifier, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Create a new user.
     */
    public function create(array $data): int {
        $query = "INSERT INTO {$this->table} (username, email, password, full_name, phone, role, status)
                  VALUES (:username, :email, :password, :full_name, :phone, :role, :status)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':username', $data['username'], PDO::PARAM_STR);
        $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindValue(':password', $data['password'], PDO::PARAM_STR);
        $stmt->bindValue(':full_name', $data['full_name'], PDO::PARAM_STR);
        $stmt->bindValue(':phone', $data['phone'] ?: null, $data['phone'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':role', $data['role'] ?? 'user', PDO::PARAM_STR);
        $stmt->bindValue(':status', $data['status'] ?? 'active', PDO::PARAM_STR);
        $stmt->execute();

        return (int)$this->conn->lastInsertId();
    }

    /**
     * Update editable profile fields.
     */
    public function updateProfile(int $userId, array $data): bool {
        $query = "UPDATE {$this->table}
                  SET full_name = :full_name,
                      phone = :phone
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':full_name', $data['full_name'], PDO::PARAM_STR);
        $stmt->bindValue(':phone', $data['phone'] ?: null, $data['phone'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Remove password from a user array.
     */
    public function withoutPassword(array $user): array {
        unset($user['password']);
        return $user;
    }
}
