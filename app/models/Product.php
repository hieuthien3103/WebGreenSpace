<?php
/**
 * Product Model
 * Handles database operations for products
 */

class Product {
    private PDO $conn;
    private string $table = 'products';

    private const SELECT_FIELDS = 'p.*, c.name AS category_name, c.slug AS category_slug';
    private const ACTIVE_PRODUCT_WHERE = "p.status = 'active'";

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Get all active products with pagination.
     */
    public function getAll(int $limit = 12, int $offset = 0): array {
        $query = "SELECT " . self::SELECT_FIELDS . "
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE " . self::ACTIVE_PRODUCT_WHERE . "
                  ORDER BY p.featured DESC, p.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->formatProducts($stmt->fetchAll());
    }

    /**
     * Get featured products.
     */
    public function getFeatured(int $limit = 8): array {
        $query = "SELECT " . self::SELECT_FIELDS . "
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE " . self::ACTIVE_PRODUCT_WHERE . " AND p.featured = 1
                  ORDER BY p.updated_at DESC, p.created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $this->formatProducts($stmt->fetchAll());
    }

    /**
     * Get best selling products.
     */
    public function getBestSellers(int $limit = 8, int $offset = 0, array $filters = []): array {
        [$whereClauses, $params] = $this->buildFilterClauses($filters, 'p', 'c');

        $query = "SELECT " . self::SELECT_FIELDS . ",
                         COALESCE(SUM(CASE
                             WHEN o.order_status IS NULL OR o.order_status != 'cancelled' THEN od.quantity
                             ELSE 0
                         END), 0) AS total_sold
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN order_details od ON p.id = od.product_id
                  LEFT JOIN orders o ON od.order_id = o.id
                  WHERE " . implode(' AND ', $whereClauses) . "
                  GROUP BY p.id, c.name, c.slug
                  ORDER BY total_sold DESC, p.featured DESC, p.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $this->bindFilterParams($stmt, $params);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->formatProducts($stmt->fetchAll(), true);
    }

    /**
     * Get product by ID.
     */
    public function getById(int $id): ?array {
        $query = "SELECT " . self::SELECT_FIELDS . "
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.id = :id AND " . self::ACTIVE_PRODUCT_WHERE;

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $product = $stmt->fetch();
        return $product ? $this->formatProduct($product) : null;
    }

    /**
     * Get product by slug.
     */
    public function getBySlug(string $slug): ?array {
        $query = "SELECT " . self::SELECT_FIELDS . "
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.slug = :slug AND " . self::ACTIVE_PRODUCT_WHERE;

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();

        $product = $stmt->fetch();
        return $product ? $this->formatProduct($product) : null;
    }

    /**
     * Get products by category.
     */
    public function getByCategory(int $categoryId, int $limit = 12, int $offset = 0): array {
        $query = "SELECT " . self::SELECT_FIELDS . "
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.category_id = :category_id AND " . self::ACTIVE_PRODUCT_WHERE . "
                  ORDER BY p.featured DESC, p.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->formatProducts($stmt->fetchAll());
    }

    /**
     * Search products by keyword.
     */
    public function search(string $keyword, int $limit = 12, int $offset = 0): array {
        $query = "SELECT " . self::SELECT_FIELDS . "
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE " . self::ACTIVE_PRODUCT_WHERE . "
                    AND (p.name LIKE :keyword OR p.description LIKE :keyword OR c.name LIKE :keyword)
                  ORDER BY p.featured DESC, p.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->formatProducts($stmt->fetchAll());
    }

    /**
     * Get filtered products with advanced filters.
     */
    public function getFilteredProducts(array $filters = [], int $limit = 12, int $offset = 0, string $sort = 'newest'): array {
        [$whereClauses, $params] = $this->buildFilterClauses($filters, 'p', 'c');
        $orderBy = $this->resolveOrderBy($sort);

        $query = "SELECT " . self::SELECT_FIELDS . "
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE " . implode(' AND ', $whereClauses) . "
                  ORDER BY {$orderBy}
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $this->bindFilterParams($stmt, $params);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->formatProducts($stmt->fetchAll());
    }

    /**
     * Get product gallery images.
     */
    public function getImages(int $productId): array {
        $query = "SELECT id, product_id, image_url, sort_order, is_primary
                  FROM product_images
                  WHERE product_id = :product_id
                  ORDER BY is_primary DESC, sort_order ASC, id ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();

        $images = $stmt->fetchAll();
        foreach ($images as &$image) {
            $image['image_url'] = $this->normalizeImageUrl($image['image_url'] ?? null);
        }

        return $images;
    }

    /**
     * Get derived tags for a product from current schema fields.
     */
    public function getTags(int $productId): array {
        $product = $this->getById($productId);
        if (!$product) {
            return [];
        }

        $labels = [
            ['slug' => 'care-' . ($product['care_level'] ?? 'medium'), 'name' => 'Chăm sóc: ' . $this->translateCareLevel($product['care_level'] ?? 'medium')],
            ['slug' => 'light-' . ($product['light_requirement'] ?? 'medium'), 'name' => 'Ánh sáng: ' . $this->translateRequirement($product['light_requirement'] ?? 'medium')],
            ['slug' => 'water-' . ($product['water_requirement'] ?? 'medium'), 'name' => 'Nước: ' . $this->translateRequirement($product['water_requirement'] ?? 'medium')],
        ];

        return array_map(
            static fn(array $tag, int $index): array => [
                'id' => $index + 1,
                'name' => $tag['name'],
                'slug' => $tag['slug'],
            ],
            $labels,
            array_keys($labels)
        );
    }

    /**
     * Get total active products count.
     */
    public function getTotal(?int $categoryId = null, ?string $keyword = null): int {
        $query = "SELECT COUNT(*) AS total
                  FROM {$this->table}
                  WHERE status = 'active'";

        if ($categoryId) {
            $query .= ' AND category_id = :category_id';
        }

        if ($keyword) {
            $query .= ' AND (name LIKE :keyword OR description LIKE :keyword)';
        }

        $stmt = $this->conn->prepare($query);

        if ($categoryId) {
            $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        }

        if ($keyword) {
            $stmt->bindValue(':keyword', '%' . $keyword . '%', PDO::PARAM_STR);
        }

        $stmt->execute();
        $result = $stmt->fetch();

        return (int)($result['total'] ?? 0);
    }

    /**
     * Get total count with full filter support.
     */
    public function getFilteredTotal(array $filters = []): int {
        [$whereClauses, $params] = $this->buildFilterClauses($filters, 'p', 'c');

        $query = "SELECT COUNT(*) AS total
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE " . implode(' AND ', $whereClauses);

        $stmt = $this->conn->prepare($query);
        $this->bindFilterParams($stmt, $params);
        $stmt->execute();

        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Get related products from the same category, excluding the current product.
     */
    public function getRelatedProducts(int $productId, int $categoryId, int $limit = 4): array {
        $query = "SELECT " . self::SELECT_FIELDS . "
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.category_id = :category_id
                    AND p.id != :product_id
                    AND " . self::ACTIVE_PRODUCT_WHERE . "
                  ORDER BY p.featured DESC, p.updated_at DESC, p.created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $this->formatProducts($stmt->fetchAll());
    }

    /**
     * Increase product views.
     */
    public function incrementViews(int $productId): void {
        $query = "UPDATE {$this->table}
                  SET views = views + 1
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $productId, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Get products for the admin management table.
     */
    public function getAdminList(string $search = '', string $status = 'all', int $limit = 20, int $offset = 0): array {
        [$whereClauses, $params] = $this->buildAdminWhereClauses($search, $status);

        $query = "SELECT " . self::SELECT_FIELDS . ",
                         (
                             SELECT COUNT(*)
                             FROM order_details od
                             WHERE od.product_id = p.id
                         ) AS order_count
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE " . implode(' AND ', $whereClauses) . "
                  ORDER BY p.updated_at DESC, p.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->formatProducts($stmt->fetchAll());
    }

    /**
     * Count products for the admin management table.
     */
    public function getAdminTotal(string $search = '', string $status = 'all'): int {
        [$whereClauses, $params] = $this->buildAdminWhereClauses($search, $status);

        $query = "SELECT COUNT(*) AS total
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
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
     * Get product by ID for admin pages, including inactive products.
     */
    public function getAdminById(int $id): ?array {
        $query = "SELECT " . self::SELECT_FIELDS . "
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $product = $stmt->fetch();
        return $product ? $this->formatProduct($product) : null;
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
     * Create a new product from the admin dashboard.
     */
    public function createAdminProduct(array $data): int {
        $query = "INSERT INTO {$this->table} (
                        category_id,
                        name,
                        slug,
                        description,
                        price,
                        sale_price,
                        stock,
                        image,
                        size,
                        care_level,
                        light_requirement,
                        water_requirement,
                        featured,
                        status
                  ) VALUES (
                        :category_id,
                        :name,
                        :slug,
                        :description,
                        :price,
                        :sale_price,
                        :stock,
                        :image,
                        :size,
                        :care_level,
                        :light_requirement,
                        :water_requirement,
                        :featured,
                        :status
                  )";

        $stmt = $this->conn->prepare($query);
        $this->bindAdminProductValues($stmt, $data);
        $stmt->execute();

        return (int)$this->conn->lastInsertId();
    }

    /**
     * Update an existing product from the admin dashboard.
     */
    public function updateAdminProduct(int $id, array $data): bool {
        $query = "UPDATE {$this->table}
                  SET category_id = :category_id,
                      name = :name,
                      slug = :slug,
                      description = :description,
                      price = :price,
                      sale_price = :sale_price,
                      stock = :stock,
                      image = :image,
                      size = :size,
                      care_level = :care_level,
                      light_requirement = :light_requirement,
                      water_requirement = :water_requirement,
                      featured = :featured,
                      status = :status
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $this->bindAdminProductValues($stmt, $data);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete a product safely for admin pages.
     * Products with order history are marked inactive instead of being removed.
     */
    public function deleteAdminProduct(int $id): array {
        if ($this->hasOrderHistory($id)) {
            $stmt = $this->conn->prepare("UPDATE {$this->table}
                                          SET status = 'inactive', featured = 0
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
     * Format one product row for the current frontend.
     */
    private function formatProduct(array $product): array {
        $product['stock_quantity'] = isset($product['stock']) ? (int)$product['stock'] : 0;
        $product['image_url'] = $this->normalizeImageUrl($product['image'] ?? null);
        $product['effective_price'] = $this->getEffectivePrice($product);
        $product['short_description'] = $product['description'] ?? '';
        $product['long_description'] = $product['description'] ?? '';
        $product['light_care'] = $this->translateRequirement($product['light_requirement'] ?? 'medium');
        $product['water_care'] = $this->translateRequirement($product['water_requirement'] ?? 'medium');
        $product['temp_care'] = $this->translateCareLevel($product['care_level'] ?? 'medium');
        $product['is_new'] = $this->isRecentlyCreated($product['created_at'] ?? null);
        $product['badge'] = $this->resolveBadge($product);
        $product['discount_percentage'] = $this->calculateDiscountPercentage($product);

        return $product;
    }

    /**
     * Format multiple product rows.
     */
    private function formatProducts(array $products, bool $markBestseller = false): array {
        return array_map(function (array $product) use ($markBestseller): array {
            $formatted = $this->formatProduct($product);

            if ($markBestseller && empty($formatted['badge']) && !empty($formatted['total_sold'])) {
                $formatted['badge'] = 'bestseller';
            }

            return $formatted;
        }, $products);
    }

    /**
     * Build reusable filter clauses for listing queries.
     */
    private function buildFilterClauses(array $filters, string $productAlias = 'p', string $categoryAlias = 'c'): array {
        $priceExpression = $this->getEffectivePriceExpression($productAlias);
        $whereClauses = ["{$productAlias}.status = 'active'"];
        $params = [];

        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $whereClauses[] = "{$priceExpression} >= :min_price";
            $params[':min_price'] = (float)$filters['min_price'];
        }

        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $whereClauses[] = "{$priceExpression} <= :max_price";
            $params[':max_price'] = (float)$filters['max_price'];
        }

        if (!empty($filters['category_id'])) {
            $whereClauses[] = "{$productAlias}.category_id = :category_id";
            $params[':category_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $whereClauses[] = "({$productAlias}.name LIKE :search OR {$productAlias}.description LIKE :search OR {$categoryAlias}.name LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        return [$whereClauses, $params];
    }

    /**
     * Build filters for the admin product table.
     */
    private function buildAdminWhereClauses(string $search = '', string $status = 'all'): array {
        $whereClauses = ['1 = 1'];
        $params = [];

        if ($status === 'active' || $status === 'inactive') {
            $whereClauses[] = 'p.status = :status';
            $params[':status'] = $status;
        }

        if ($search !== '') {
            $whereClauses[] = '(p.name LIKE :search OR p.slug LIKE :search OR c.name LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        return [$whereClauses, $params];
    }

    /**
     * Bind filter params for reusable listing queries.
     */
    private function bindFilterParams(PDOStatement $stmt, array $params): void {
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
                continue;
            }

            $stmt->bindValue($key, (string)$value, PDO::PARAM_STR);
        }
    }

    /**
     * Bind values for create/update admin forms.
     */
    private function bindAdminProductValues(PDOStatement $stmt, array $data): void {
        $stmt->bindValue(':category_id', (int)$data['category_id'], PDO::PARAM_INT);
        $stmt->bindValue(':name', (string)$data['name'], PDO::PARAM_STR);
        $stmt->bindValue(':slug', (string)$data['slug'], PDO::PARAM_STR);
        $stmt->bindValue(':description', $data['description'] !== '' ? (string)$data['description'] : null, $data['description'] !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':price', (string)$data['price'], PDO::PARAM_STR);
        $stmt->bindValue(':sale_price', $data['sale_price'] !== null ? (string)$data['sale_price'] : null, $data['sale_price'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':stock', (int)$data['stock'], PDO::PARAM_INT);
        $stmt->bindValue(':image', $data['image'] !== '' ? (string)$data['image'] : null, $data['image'] !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':size', $data['size'] !== '' ? (string)$data['size'] : null, $data['size'] !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':care_level', (string)$data['care_level'], PDO::PARAM_STR);
        $stmt->bindValue(':light_requirement', (string)$data['light_requirement'], PDO::PARAM_STR);
        $stmt->bindValue(':water_requirement', (string)$data['water_requirement'], PDO::PARAM_STR);
        $stmt->bindValue(':featured', !empty($data['featured']) ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':status', (string)$data['status'], PDO::PARAM_STR);
    }

    /**
     * Check whether the product already appears in orders.
     */
    private function hasOrderHistory(int $productId): bool {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM order_details WHERE product_id = :id");
        $stmt->bindValue(':id', $productId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0) > 0;
    }

    /**
     * Resolve SQL ORDER BY for listing pages.
     */
    private function resolveOrderBy(string $sort): string {
        return match ($sort) {
            'price_asc' => "CASE WHEN p.sale_price IS NOT NULL AND p.sale_price > 0 THEN p.sale_price ELSE p.price END ASC, p.featured DESC, p.created_at DESC",
            'price_desc' => "CASE WHEN p.sale_price IS NOT NULL AND p.sale_price > 0 THEN p.sale_price ELSE p.price END DESC, p.featured DESC, p.created_at DESC",
            default => "p.featured DESC, p.created_at DESC",
        };
    }

    /**
     * Resolve displayed badge.
     */
    private function resolveBadge(array $product): ?string {
        if (!empty($product['sale_price']) && (float)$product['sale_price'] > 0 && (float)$product['sale_price'] < (float)$product['price']) {
            return 'sale';
        }

        if ($this->isRecentlyCreated($product['created_at'] ?? null)) {
            return 'new';
        }

        return null;
    }

    /**
     * Calculate discount percentage when sale_price exists.
     */
    private function calculateDiscountPercentage(array $product): int {
        $price = (float)($product['price'] ?? 0);
        $salePrice = (float)($product['sale_price'] ?? 0);

        if ($price <= 0 || $salePrice <= 0 || $salePrice >= $price) {
            return 0;
        }

        return (int)round((($price - $salePrice) / $price) * 100);
    }

    /**
     * Build expression for price filters based on the displayed price.
     */
    private function getEffectivePriceExpression(string $productAlias = 'p'): string {
        return "CASE WHEN {$productAlias}.sale_price IS NOT NULL AND {$productAlias}.sale_price > 0 THEN {$productAlias}.sale_price ELSE {$productAlias}.price END";
    }

    /**
     * Get displayed price.
     */
    private function getEffectivePrice(array $product): float {
        if (!empty($product['sale_price']) && (float)$product['sale_price'] > 0) {
            return (float)$product['sale_price'];
        }

        return (float)($product['price'] ?? 0);
    }

    /**
     * Normalize image path to a usable URL.
     */
    private function normalizeImageUrl(?string $path): string {
        if (empty($path)) {
            return image_url('products/default.jpg');
        }

        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        return upload_url($path);
    }

    /**
     * Check whether a product is recently created.
     */
    private function isRecentlyCreated(?string $createdAt): bool {
        if (empty($createdAt)) {
            return false;
        }

        $created = strtotime($createdAt);
        if ($created === false) {
            return false;
        }

        return $created >= strtotime('-30 days');
    }

    /**
     * Translate requirement enum for the UI.
     */
    private function translateRequirement(string $value): string {
        return match ($value) {
            'low' => 'Thấp',
            'high' => 'Cao',
            default => 'Vừa',
        };
    }

    /**
     * Translate care level enum for the UI.
     */
    private function translateCareLevel(string $value): string {
        return match ($value) {
            'easy' => 'Dễ',
            'hard' => 'Khó',
            default => 'Trung bình',
        };
    }
}
