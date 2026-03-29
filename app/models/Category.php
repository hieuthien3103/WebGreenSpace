<?php
/**
 * Category Model
 * Handles database operations for categories
 */

class Category {
    private PDO $conn;
    private string $table = 'categories';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Get all active categories for the storefront.
     */
    public function getAll(): array {
        $query = "SELECT c.*,
                         COUNT(CASE WHEN p.status = 'active' THEN p.id END) AS product_count,
                         parent.name AS parent_name
                  FROM {$this->table} c
                  LEFT JOIN products p ON c.id = p.category_id
                  LEFT JOIN {$this->table} parent ON c.parent_id = parent.id
                  WHERE c.status = 'active'
                  GROUP BY c.id, parent.name
                  ORDER BY c.name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $this->formatCategories($stmt->fetchAll());
    }

    /**
     * Get top categories with limit.
     */
    public function getTop(int $limit = 5): array {
        $query = "SELECT c.*,
                         parent.name AS parent_name,
                         COUNT(p.id) AS product_count
                  FROM {$this->table} c
                  LEFT JOIN {$this->table} parent ON c.parent_id = parent.id
                  LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
                  WHERE c.status = 'active'
                  GROUP BY c.id, parent.name
                  ORDER BY product_count DESC, c.name ASC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $this->formatCategories($stmt->fetchAll());
    }

    /**
     * Get category by ID for the storefront.
     */
    public function getById(int $id): ?array {
        $query = "SELECT c.*, parent.name AS parent_name
                  FROM {$this->table} c
                  LEFT JOIN {$this->table} parent ON c.parent_id = parent.id
                  WHERE c.id = :id AND c.status = 'active'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $category = $stmt->fetch();
        return $category ? $this->formatCategory($category) : null;
    }

    /**
     * Get category by slug for the storefront.
     */
    public function getBySlug(string $slug): ?array {
        $query = "SELECT c.*, parent.name AS parent_name
                  FROM {$this->table} c
                  LEFT JOIN {$this->table} parent ON c.parent_id = parent.id
                  WHERE c.slug = :slug AND c.status = 'active'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();

        $category = $stmt->fetch();
        return $category ? $this->formatCategory($category) : null;
    }

    /**
     * Get categories for the admin table.
     */
    public function getAdminList(string $search = '', string $status = 'all', int $limit = 20, int $offset = 0): array {
        [$whereClauses, $params] = $this->buildAdminWhereClauses($search, $status);

        $query = "SELECT c.*,
                         parent.name AS parent_name,
                         COUNT(p.id) AS product_count,
                         COUNT(CASE WHEN p.status = 'active' THEN p.id END) AS active_product_count
                  FROM {$this->table} c
                  LEFT JOIN {$this->table} parent ON c.parent_id = parent.id
                  LEFT JOIN products p ON c.id = p.category_id
                  WHERE " . implode(' AND ', $whereClauses) . "
                  GROUP BY c.id, parent.name
                  ORDER BY c.updated_at DESC, c.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->formatCategories($stmt->fetchAll());
    }

    /**
     * Count categories for the admin table.
     */
    public function getAdminTotal(string $search = '', string $status = 'all'): int {
        [$whereClauses, $params] = $this->buildAdminWhereClauses($search, $status);

        $query = "SELECT COUNT(*) AS total
                  FROM {$this->table} c
                  LEFT JOIN {$this->table} parent ON c.parent_id = parent.id
                  WHERE " . implode(' AND ', $whereClauses);

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->execute();

        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Get category by ID for admin pages, including inactive rows.
     */
    public function getAdminById(int $id): ?array {
        $query = "SELECT c.*, parent.name AS parent_name
                  FROM {$this->table} c
                  LEFT JOIN {$this->table} parent ON c.parent_id = parent.id
                  WHERE c.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $category = $stmt->fetch();
        return $category ? $this->formatCategory($category) : null;
    }

    /**
     * Get category options for parent selection in admin.
     */
    public function getAdminParentOptions(?int $excludeId = null): array {
        $query = "SELECT id, name, status
                  FROM {$this->table}";

        if ($excludeId !== null) {
            $query .= " WHERE id != :exclude_id";
        }

        $query .= " ORDER BY status = 'active' DESC, name ASC";

        $stmt = $this->conn->prepare($query);

        if ($excludeId !== null) {
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Check whether a slug already exists.
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool {
        $query = "SELECT COUNT(*) AS total
                  FROM {$this->table}
                  WHERE slug = :slug";

        if ($excludeId !== null) {
            $query .= " AND id != :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);

        if ($excludeId !== null) {
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();
        $result = $stmt->fetch();

        return (int)($result['total'] ?? 0) > 0;
    }

    /**
     * Create a new category from the admin dashboard.
     */
    public function createAdminCategory(array $data): int {
        $query = "INSERT INTO {$this->table} (
                        name,
                        slug,
                        description,
                        image,
                        parent_id,
                        status
                  ) VALUES (
                        :name,
                        :slug,
                        :description,
                        :image,
                        :parent_id,
                        :status
                  )";

        $stmt = $this->conn->prepare($query);
        $this->bindAdminCategoryValues($stmt, $data);
        $stmt->execute();

        return (int)$this->conn->lastInsertId();
    }

    /**
     * Update an existing category from the admin dashboard.
     */
    public function updateAdminCategory(int $id, array $data): bool {
        $query = "UPDATE {$this->table}
                  SET name = :name,
                      slug = :slug,
                      description = :description,
                      image = :image,
                      parent_id = :parent_id,
                      status = :status
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $this->bindAdminCategoryValues($stmt, $data);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete a category safely for admin pages.
     * Categories with products are marked inactive instead of being removed.
     */
    public function deleteAdminCategory(int $id): array {
        if ($this->hasProducts($id)) {
            $stmt = $this->conn->prepare("UPDATE {$this->table}
                                          SET status = 'inactive'
                                          WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return [
                'success' => $stmt->execute(),
                'mode' => 'inactivated',
            ];
        }

        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return [
            'success' => $stmt->execute(),
            'mode' => 'deleted',
        ];
    }

    /**
     * Format category with image URL.
     */
    private function formatCategory(array $category): array {
        if (isset($category['image']) && !empty($category['image'])) {
            $category['image_url'] = preg_match('#^https?://#i', (string)$category['image']) === 1
                ? (string)$category['image']
                : upload_url((string)$category['image']);
        } else {
            $category['image_url'] = image_url('categories/default.jpg');
        }

        return $category;
    }

    /**
     * Format multiple categories.
     */
    private function formatCategories(array $categories): array {
        return array_map([$this, 'formatCategory'], $categories);
    }

    /**
     * Build filters for the admin category table.
     */
    private function buildAdminWhereClauses(string $search = '', string $status = 'all'): array {
        $whereClauses = ['1 = 1'];
        $params = [];

        if ($status === 'active' || $status === 'inactive') {
            $whereClauses[] = 'c.status = :status';
            $params[':status'] = $status;
        }

        if ($search !== '') {
            $whereClauses[] = '(c.name LIKE :search OR c.slug LIKE :search OR parent.name LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        return [$whereClauses, $params];
    }

    /**
     * Bind values for create/update admin forms.
     */
    private function bindAdminCategoryValues(PDOStatement $stmt, array $data): void {
        $stmt->bindValue(':name', (string)$data['name'], PDO::PARAM_STR);
        $stmt->bindValue(':slug', (string)$data['slug'], PDO::PARAM_STR);
        $stmt->bindValue(':description', $data['description'] !== '' ? (string)$data['description'] : null, $data['description'] !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':image', $data['image'] !== '' ? (string)$data['image'] : null, $data['image'] !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':parent_id', $data['parent_id'] !== null ? (int)$data['parent_id'] : null, $data['parent_id'] !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':status', (string)$data['status'], PDO::PARAM_STR);
    }

    /**
     * Check whether the category already has products.
     */
    private function hasProducts(int $categoryId): bool {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM products WHERE category_id = :id");
        $stmt->bindValue(':id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0) > 0;
    }
}
