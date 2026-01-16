<?php
/**
 * Product Model
 * Handles database operations for products
 */

class Product {
    private PDO $conn;
    private string $table = 'products';
    
    // Query select fields
    private const SELECT_FIELDS = "p.*, c.name as category_name, c.slug as category_slug, p.thumbnail_url as image_path, p.old_price as sale_price";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Get all products with pagination
     * 
     * @param int $limit Maximum number of products
     * @param int $offset Starting position
     * @return array Products list
     */
    public function getAll(int $limit = 12, int $offset = 0): array {
        $query = "SELECT " . self::SELECT_FIELDS . "
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  ORDER BY p.created_at DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $this->formatProducts($stmt->fetchAll());
    }

    /**
     * Get best selling products
     * 
     * @param int $limit Maximum number of products
     * @return array Best selling products
     */
    public function getBestSellers(int $limit = 8): array {
        $query = "SELECT " . self::SELECT_FIELDS . ",
                  COALESCE(SUM(oi.quantity), 0) as total_sold
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN order_items oi ON p.id = oi.product_id
                  GROUP BY p.id
                  ORDER BY total_sold DESC, p.created_at DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $this->formatProducts($stmt->fetchAll());
    }

    /**
     * Get product by ID
     * 
     * @param int $id Product ID
     * @return array|null Product data or null if not found
     */
    public function getById(int $id): ?array {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $product = $stmt->fetch();
        if ($product) {
            return $this->formatProduct($product);
        }
        return null;
    }

    /**
     * Get product by slug
     * 
     * @param string $slug Product slug
     * @return array|null Product data or null if not found
     */
    public function getBySlug(string $slug): ?array {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.slug = :slug";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
        
        $product = $stmt->fetch();
        if ($product) {
            return $this->formatProduct($product);
        }
        return null;
    }

    /**
     * Get products by category
     * 
     * @param int $categoryId Category ID
     * @param int $limit Maximum number of products
     * @param int $offset Starting position
     * @return array Products list
     */
    public function getByCategory(int $categoryId, int $limit = 12, int $offset = 0): array {
        $query = "SELECT " . self::SELECT_FIELDS . "
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.category_id = :category_id
                  ORDER BY p.created_at DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $this->formatProducts($stmt->fetchAll());
    }

    /**
     * Search products
     * 
     * @param string $keyword Search keyword
     * @param int $limit Maximum number of products
     * @param int $offset Starting position
     * @return array Products list
     */
    public function search(string $keyword, int $limit = 12, int $offset = 0): array {
        $keyword = "%{$keyword}%";
        $query = "SELECT " . self::SELECT_FIELDS . "
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE (p.name LIKE :keyword OR p.short_description LIKE :keyword OR p.long_description LIKE :keyword)
                  ORDER BY p.created_at DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':keyword', $keyword, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $this->formatProducts($stmt->fetchAll());
    }

    /**
     * Get product images
     * 
     * @param int $productId Product ID
     * @return array Product images
     */
    public function getImages(int $productId): array {
        $query = "SELECT * FROM product_images WHERE product_id = :product_id ORDER BY display_order";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        
        $images = $stmt->fetchAll();
        foreach ($images as &$image) {
            $image['image_url'] = upload_url($image['image_path']);
        }
        return $images;
    }

    /**
     * Get total count
     */
    public function getTotal($categoryId = null, $keyword = null) {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        
        if ($categoryId) {
            $query .= " AND category_id = :category_id";
        }
        
        if ($keyword) {
            $query .= " AND (name LIKE :keyword OR description LIKE :keyword)";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($categoryId) {
            $stmt->bindParam(':category_id', $categoryId);
        }
        
        if ($keyword) {
            $keyword = "%{$keyword}%";
            $stmt->bindParam(':keyword', $keyword);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Get tags for a product
     * 
     * @param int $productId Product ID
     * @return array Product tags
     */
    public function getTags(int $productId): array {
        $query = "SELECT t.id, t.name, t.slug
                  FROM tags t
                  INNER JOIN product_tags pt ON t.id = pt.tag_id
                  WHERE pt.product_id = :product_id
                  ORDER BY t.name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get related products (same category, excluding current product)
     * 
     * @param int $productId Current product ID
     * @param int $categoryId Category ID
     * @param int $limit Maximum number of products
     * @return array Related products
     */
    public function getRelatedProducts(int $productId, int $categoryId, int $limit = 4): array {
        $query = "SELECT " . self::SELECT_FIELDS . "
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.category_id = :category_id AND p.id != :product_id
                  ORDER BY RAND()
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $this->formatProducts($stmt->fetchAll());
    }

    /**
     * Format product with image URL
     * 
     * @param array $product Product data
     * @return array Formatted product
     */
    private function formatProduct(array $product): array {
        // Map database fields to expected fields
        if (isset($product['old_price'])) {
            $product['sale_price'] = $product['old_price'];
        }
        if (isset($product['stock_quantity'])) {
            $product['stock'] = $product['stock_quantity'];
        }
        if (isset($product['thumbnail_url'])) {
            $product['image'] = $product['thumbnail_url'];
        }
        if (isset($product['short_description'])) {
            $product['description'] = $product['short_description'];
        }
        
        // Set image URL
        if (!empty($product['image_path'])) {
            // If it's already a full URL, use it directly
            if (strpos($product['image_path'], 'http') === 0) {
                $product['image_url'] = $product['image_path'];
            } else {
                $product['image_url'] = image_url($product['image_path']);
            }
        } elseif (!empty($product['thumbnail_url'])) {
            if (strpos($product['thumbnail_url'], 'http') === 0) {
                $product['image_url'] = $product['thumbnail_url'];
            } else {
                $product['image_url'] = image_url($product['thumbnail_url']);
            }
        } else {
            $product['image_url'] = image_url('products/default.jpg');
        }
        
        return $product;
    }

    /**
     * Format multiple products
     * 
     * @param array $products Products array
     * @return array Formatted products
     */
    private function formatProducts(array $products): array {
        return array_map([$this, 'formatProduct'], $products);
    }
}
