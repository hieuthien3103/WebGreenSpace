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
     * Get all active categories
     * 
     * @return array Categories list
     */
    public function getAll(): array {
        $query = "SELECT * FROM {$this->table} ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $this->formatCategories($stmt->fetchAll());
    }

    /**
     * Get top categories with limit
     * 
     * @param int $limit Maximum number of categories
     * @return array Top categories
     */
    public function getTop(int $limit = 5): array {
        $query = "SELECT c.*, COUNT(p.id) as product_count
                  FROM {$this->table} c
                  LEFT JOIN products p ON c.id = p.category_id
                  GROUP BY c.id
                  ORDER BY product_count DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $this->formatCategories($stmt->fetchAll());
    }

    /**
     * Get category by ID
     * 
     * @param int $id Category ID
     * @return array|null Category data or null
     */
    public function getById(int $id): ?array {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $category = $stmt->fetch();
        if ($category) {
            return $this->formatCategory($category);
        }
        return null;
    }

    /**
     * Get category by slug
     * 
     * @param string $slug Category slug
     * @return array|null Category data or null
     */
    public function getBySlug(string $slug): ?array {
        $query = "SELECT * FROM {$this->table} WHERE slug = :slug";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        
        $category = $stmt->fetch();
        if ($category) {
            return $this->formatCategory($category);
        }
        return null;
    }

    /**
     * Format category with image URL
     * 
     * @param array $category Category data
     * @return array Formatted category
     */
    private function formatCategory(array $category): array {
        if (isset($category['image']) && !empty($category['image'])) {
            $category['image_url'] = upload_url($category['image']);
        } else {
            $category['image_url'] = image_url('categories/default.jpg');
        }
        return $category;
    }

    /**
     * Format multiple categories
     * 
     * @param array $categories Categories array
     * @return array Formatted categories
     */
    private function formatCategories(array $categories): array {
        return array_map([$this, 'formatCategory'], $categories);
    }
}
