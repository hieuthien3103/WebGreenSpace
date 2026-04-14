<?php
/**
 * Contact message model.
 */
class ContactMessage {
    private PDO $conn;
    private string $table = 'contact_messages';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Create one contact message.
     */
    public function create(array $data): int {
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} (full_name, email, phone, subject, message, status, is_read)
             VALUES (:full_name, :email, :phone, :subject, :message, 'new', 0)"
        );
        $stmt->bindValue(':full_name', $data['full_name'], PDO::PARAM_STR);
        $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindValue(':phone', $data['phone'] ?: null, $data['phone'] !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':subject', $data['subject'], PDO::PARAM_STR);
        $stmt->bindValue(':message', $data['message'], PDO::PARAM_STR);
        $stmt->execute();

        return (int)$this->conn->lastInsertId();
    }

    /**
     * Get paginated messages for admin.
     */
    public function getAdminList(string $search = '', string $status = 'all', int $limit = 20, int $offset = 0): array {
        [$whereSql, $bindings] = $this->buildAdminFilters($search, $status);

        $stmt = $this->conn->prepare(
            "SELECT *
             FROM {$this->table}
             {$whereSql}
             ORDER BY is_read ASC, created_at DESC, id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($bindings as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    /**
     * Count messages for admin pagination.
     */
    public function getAdminTotal(string $search = '', string $status = 'all'): int {
        [$whereSql, $bindings] = $this->buildAdminFilters($search, $status);

        $stmt = $this->conn->prepare(
            "SELECT COUNT(*)
             FROM {$this->table}
             {$whereSql}"
        );

        foreach ($bindings as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get one message by ID.
     */
    public function findById(int $id): ?array {
        $stmt = $this->conn->prepare(
            "SELECT *
             FROM {$this->table}
             WHERE id = :id
             LIMIT 1"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $message = $stmt->fetch();
        return $message ?: null;
    }

    /**
     * Mark one message as read.
     */
    public function markAsRead(int $id): bool {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table}
             SET is_read = 1,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Update message review state and admin note.
     */
    public function updateAdminState(int $id, string $status, string $adminNote = ''): bool {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table}
             SET status = :status,
                 admin_note = :admin_note,
                 is_read = 1,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id"
        );
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':admin_note', $adminNote !== '' ? $adminNote : null, $adminNote !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Small stats block for admin page.
     */
    public function getStats(): array {
        $stmt = $this->conn->query(
            "SELECT COUNT(*) AS total_messages,
                    COALESCE(SUM(status = 'new'), 0) AS new_messages,
                    COALESCE(SUM(status = 'in_progress'), 0) AS in_progress_messages,
                    COALESCE(SUM(status = 'resolved'), 0) AS resolved_messages,
                    COALESCE(SUM(is_read = 0), 0) AS unread_messages
             FROM {$this->table}"
        );

        $row = $stmt->fetch() ?: [];
        return [
            'total_messages' => (int)($row['total_messages'] ?? 0),
            'new_messages' => (int)($row['new_messages'] ?? 0),
            'in_progress_messages' => (int)($row['in_progress_messages'] ?? 0),
            'resolved_messages' => (int)($row['resolved_messages'] ?? 0),
            'unread_messages' => (int)($row['unread_messages'] ?? 0),
        ];
    }

    /**
     * @return array{0: string, 1: array<string, string>}
     */
    private function buildAdminFilters(string $search, string $status): array {
        $conditions = ['1 = 1'];
        $bindings = [];

        $search = trim($search);
        if ($search !== '') {
            $conditions[] = '(full_name LIKE :search OR email LIKE :search OR phone LIKE :search OR subject LIKE :search OR message LIKE :search)';
            $bindings[':search'] = '%' . $search . '%';
        }

        if ($status !== 'all') {
            $conditions[] = 'status = :status';
            $bindings[':status'] = $status;
        }

        return ['WHERE ' . implode(' AND ', $conditions), $bindings];
    }
}
