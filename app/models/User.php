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
        $query = "SELECT id, username, email, password, full_name, phone, role, admin_permissions, status, created_at, updated_at
                  FROM {$this->table}
                  WHERE id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $this->hydrateUser($stmt->fetch() ?: null);
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?array {
        $query = "SELECT id, username, email, password, full_name, phone, role, admin_permissions, status, created_at, updated_at
                  FROM {$this->table}
                  WHERE email = :email
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        return $this->hydrateUser($stmt->fetch() ?: null);
    }

    /**
     * Find user by username.
     */
    public function findByUsername(string $username): ?array {
        $query = "SELECT id, username, email, password, full_name, phone, role, admin_permissions, status, created_at, updated_at
                  FROM {$this->table}
                  WHERE username = :username
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        return $this->hydrateUser($stmt->fetch() ?: null);
    }

    /**
     * Find user by email or username.
     */
    public function findByLogin(string $identifier): ?array {
        $query = "SELECT id, username, email, password, full_name, phone, role, admin_permissions, status, created_at, updated_at
                  FROM {$this->table}
                  WHERE email = :email_identifier OR username = :username_identifier
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':email_identifier', $identifier, PDO::PARAM_STR);
        $stmt->bindValue(':username_identifier', $identifier, PDO::PARAM_STR);
        $stmt->execute();

        return $this->hydrateUser($stmt->fetch() ?: null);
    }

    /**
     * Create a new user.
     */
    public function create(array $data): int {
        $query = "INSERT INTO {$this->table} (username, email, password, full_name, phone, role, admin_permissions, status)
                  VALUES (:username, :email, :password, :full_name, :phone, :role, :admin_permissions, :status)";

        $stmt = $this->conn->prepare($query);
        $encodedPermissions = $this->encodeAdminPermissions($data['admin_permissions'] ?? [], (string)($data['role'] ?? 'user'));
        $stmt->bindValue(':username', $data['username'], PDO::PARAM_STR);
        $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindValue(':password', $data['password'], PDO::PARAM_STR);
        $stmt->bindValue(':full_name', $data['full_name'], PDO::PARAM_STR);
        $stmt->bindValue(':phone', $data['phone'] ?: null, $data['phone'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':role', $data['role'] ?? 'user', PDO::PARAM_STR);
        $stmt->bindValue(':admin_permissions', $encodedPermissions, $encodedPermissions !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
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
     * Get a user for admin management.
     */
    public function getAdminById(int $id): ?array {
        return $this->findById($id);
    }

    /**
     * Get users for the admin management table.
     */
    public function getAdminList(string $search = '', string $role = 'all', string $status = 'all', int $limit = 20, int $offset = 0): array {
        [$whereClauses, $params] = $this->buildAdminFilters('u', $search, $role, $status);
        $whereSql = empty($whereClauses) ? '' : 'WHERE ' . implode(' AND ', $whereClauses);

        $query = "SELECT u.id,
                         u.username,
                         u.email,
                         u.full_name,
                         u.phone,
                         u.role,
                         u.admin_permissions,
                         u.status,
                         u.created_at,
                         u.updated_at,
                         COUNT(o.id) AS order_count,
                         COALESCE(SUM(CASE WHEN o.payment_status = 'paid' THEN o.total_amount ELSE 0 END), 0) AS total_spent
                  FROM {$this->table} u
                  LEFT JOIN orders o ON o.user_id = u.id
                  {$whereSql}
                  GROUP BY u.id, u.username, u.email, u.full_name, u.phone, u.role, u.admin_permissions, u.status, u.created_at, u.updated_at
                  ORDER BY u.updated_at DESC, u.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $this->bindValues($stmt, $params);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        $users = $stmt->fetchAll() ?: [];
        return array_map(fn(array $user): array => $this->hydrateUser($user), $users);
    }

    /**
     * Count users for the admin management table.
     */
    public function getAdminTotal(string $search = '', string $role = 'all', string $status = 'all'): int {
        [$whereClauses, $params] = $this->buildAdminFilters('u', $search, $role, $status);
        $whereSql = empty($whereClauses) ? '' : 'WHERE ' . implode(' AND ', $whereClauses);

        $query = "SELECT COUNT(*) FROM {$this->table} u {$whereSql}";
        $stmt = $this->conn->prepare($query);
        $this->bindValues($stmt, $params);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    /**
     * Count users by role and status.
     */
    public function countByRoleAndStatus(string $role, string $status = 'all', ?int $excludeUserId = null): int {
        $query = "SELECT COUNT(*)
                  FROM {$this->table}
                  WHERE role = :role";

        if ($status === 'active' || $status === 'inactive') {
            $query .= " AND status = :status";
        }

        if ($excludeUserId !== null) {
            $query .= " AND id <> :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':role', $role, PDO::PARAM_STR);

        if ($status === 'active' || $status === 'inactive') {
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        }

        if ($excludeUserId !== null) {
            $stmt->bindValue(':exclude_id', $excludeUserId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Check whether a username already exists.
     */
    public function usernameExists(string $username, ?int $excludeId = null): bool {
        $query = "SELECT COUNT(*) FROM {$this->table} WHERE username = :username";

        if ($excludeId !== null) {
            $query .= " AND id <> :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);

        if ($excludeId !== null) {
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Check whether an email already exists.
     */
    public function emailExists(string $email, ?int $excludeId = null): bool {
        $query = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email";

        if ($excludeId !== null) {
            $query .= " AND id <> :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);

        if ($excludeId !== null) {
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Update a user from the admin dashboard.
     */
    public function updateAdminUser(int $userId, array $data): bool {
        $query = "UPDATE {$this->table}
                  SET username = :username,
                      email = :email,
                      full_name = :full_name,
                      phone = :phone,
                      role = :role,
                      admin_permissions = :admin_permissions,
                      status = :status
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $encodedPermissions = $this->encodeAdminPermissions($data['admin_permissions'] ?? [], (string)$data['role']);
        $stmt->bindValue(':username', $data['username'], PDO::PARAM_STR);
        $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindValue(':full_name', $data['full_name'], PDO::PARAM_STR);
        $stmt->bindValue(':phone', $data['phone'] ?: null, $data['phone'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':role', $data['role'], PDO::PARAM_STR);
        $stmt->bindValue(':admin_permissions', $encodedPermissions, $encodedPermissions !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':status', $data['status'], PDO::PARAM_STR);
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

    /**
     * Build filters for the admin user table.
     */
    private function buildAdminFilters(string $alias, string $search, string $role, string $status): array {
        $whereClauses = [];
        $params = [];

        $search = trim($search);
        if ($search !== '') {
            $whereClauses[] = "({$alias}.username LIKE :search OR {$alias}.email LIKE :search OR {$alias}.full_name LIKE :search OR {$alias}.phone LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if ($role === 'admin' || $role === 'user') {
            $whereClauses[] = "{$alias}.role = :role";
            $params[':role'] = $role;
        }

        if ($status === 'active' || $status === 'inactive') {
            $whereClauses[] = "{$alias}.status = :status";
            $params[':status'] = $status;
        }

        return [$whereClauses, $params];
    }

    /**
     * Bind parameters to a prepared statement.
     */
    private function bindValues(PDOStatement $stmt, array $params): void {
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
                continue;
            }

            if ($value === null) {
                $stmt->bindValue($key, null, PDO::PARAM_NULL);
                continue;
            }

            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
    }

    /**
     * Hydrate user data with normalized admin permission metadata.
     */
    private function hydrateUser(?array $user): ?array {
        if ($user === null) {
            return null;
        }

        $rawPermissions = $user['admin_permissions'] ?? null;
        $user['admin_permissions'] = normalize_admin_permissions($rawPermissions);
        $user['has_full_admin_access'] = ($user['role'] ?? 'user') === 'admin'
            && ($rawPermissions === null || $rawPermissions === '');

        return $user;
    }

    /**
     * Encode admin permissions for storage.
     */
    private function encodeAdminPermissions(array $permissions, string $role): ?string {
        if ($role !== 'admin') {
            return null;
        }

        $permissions = normalize_admin_permissions($permissions);
        if ($permissions === []) {
            return null;
        }

        return json_encode(array_values($permissions), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null;
    }
}
