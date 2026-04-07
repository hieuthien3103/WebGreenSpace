<?php
/**
 * Admin dashboard analytics service
 */

class AdminDashboardService {
    private PDO $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Get all dashboard metrics.
     */
    public function getDashboardData(): array {
        return [
            'stats' => $this->getStats(),
            'category_summary' => $this->getCategorySummary(),
            'recent_orders' => $this->getRecentOrders(),
            'recent_users' => $this->getRecentUsers(),
            'top_categories' => $this->getTopCategories(),
            'top_products' => $this->getTopProducts(),
            'low_stock_products' => $this->getLowStockProducts(),
        ];
    }

    /**
     * Get headline stat cards.
     */
    private function getStats(): array {
        $cards = [];

        $cards[] = [
            'label' => 'Người dùng',
            'value' => $this->scalar("SELECT COUNT(*) FROM users WHERE status = 'active'"),
            'hint' => 'Tài khoản đang hoạt động',
            'icon' => 'group',
        ];

        $cards[] = [
            'label' => 'Sản phẩm',
            'value' => $this->scalar("SELECT COUNT(*) FROM products WHERE status = 'active'"),
            'hint' => 'Sản phẩm đang bán',
            'icon' => 'inventory_2',
        ];

        $cards[] = [
            'label' => 'Đơn hàng',
            'value' => $this->scalar("SELECT COUNT(*) FROM orders"),
            'hint' => 'Tổng số đơn trong hệ thống',
            'icon' => 'receipt_long',
        ];

        $cards[] = [
            'label' => 'Doanh thu đã thanh toán',
            'value' => (float)$this->scalar("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status = 'paid'"),
            'hint' => 'Chỉ tính đơn đã paid',
            'icon' => 'payments',
            'currency' => true,
        ];

        $cards[] = [
            'label' => 'Đơn chờ xử lý',
            'value' => $this->scalar("SELECT COUNT(*) FROM orders WHERE order_status IN ('pending', 'confirmed', 'processing', 'shipping')"),
            'hint' => 'Cần admin theo dõi',
            'icon' => 'local_shipping',
        ];

        $cards[] = [
            'label' => 'Sắp hết hàng',
            'value' => $this->scalar("SELECT COUNT(*) FROM products WHERE status = 'active' AND stock BETWEEN 1 AND 5"),
            'hint' => 'Tồn kho từ 1 đến 5',
            'icon' => 'warning',
        ];

        return $cards;
    }

    /**
     * Get headline category metrics for the dashboard.
     */
    private function getCategorySummary(): array {
        return [
            [
                'label' => 'Tổng danh mục',
                'value' => $this->scalar("SELECT COUNT(*) FROM categories"),
                'hint' => 'Tất cả danh mục trong hệ thống',
                'icon' => 'category',
            ],
            [
                'label' => 'Đang hiển thị',
                'value' => $this->scalar("SELECT COUNT(*) FROM categories WHERE status = 'active'"),
                'hint' => 'Khách có thể truy cập ngoài cửa hàng',
                'icon' => 'visibility',
            ],
            [
                'label' => 'Danh mục gốc',
                'value' => $this->scalar("SELECT COUNT(*) FROM categories WHERE parent_id IS NULL"),
                'hint' => 'Không thuộc danh mục cha nào',
                'icon' => 'account_tree',
            ],
            [
                'label' => 'Chưa có sản phẩm',
                'value' => $this->scalar("
                    SELECT COUNT(*)
                    FROM (
                        SELECT c.id
                        FROM categories c
                        LEFT JOIN products p ON p.category_id = c.id
                        GROUP BY c.id
                        HAVING COUNT(p.id) = 0
                    ) AS empty_categories
                "),
                'hint' => 'Nên rà lại để tránh danh mục trống',
                'icon' => 'inventory',
            ],
        ];
    }

    /**
     * Get recent orders for the admin feed.
     */
    private function getRecentOrders(): array {
        $query = "SELECT o.order_number, o.full_name, o.total_amount, o.payment_status, o.order_status, o.created_at
                  FROM orders o
                  ORDER BY o.created_at DESC
                  LIMIT 8";

        return $this->conn->query($query)->fetchAll();
    }

    /**
     * Get recent user registrations.
     */
    private function getRecentUsers(): array {
        $query = "SELECT username, full_name, email, role, created_at
                  FROM users
                  ORDER BY created_at DESC
                  LIMIT 6";

        return $this->conn->query($query)->fetchAll();
    }

    /**
     * Get top categories by active product count.
     */
    private function getTopCategories(): array {
        $query = "SELECT c.id,
                         c.name,
                         c.slug,
                         c.status,
                         parent.name AS parent_name,
                         COUNT(p.id) AS product_count,
                         COUNT(CASE WHEN p.status = 'active' THEN p.id END) AS active_product_count
                  FROM categories c
                  LEFT JOIN categories parent ON c.parent_id = parent.id
                  LEFT JOIN products p ON p.category_id = c.id
                  GROUP BY c.id, parent.name
                  ORDER BY active_product_count DESC, product_count DESC, c.name ASC
                  LIMIT 6";

        return $this->conn->query($query)->fetchAll();
    }

    /**
     * Get top products by units sold.
     */
    private function getTopProducts(): array {
        $query = "SELECT p.id,
                         p.name,
                         p.slug,
                         p.stock,
                         COALESCE(SUM(CASE WHEN o.order_status != 'cancelled' THEN od.quantity ELSE 0 END), 0) AS units_sold
                  FROM products p
                  LEFT JOIN order_details od ON od.product_id = p.id
                  LEFT JOIN orders o ON o.id = od.order_id
                  WHERE p.status = 'active'
                  GROUP BY p.id
                  ORDER BY units_sold DESC, p.created_at DESC
                  LIMIT 6";

        return $this->conn->query($query)->fetchAll();
    }

    /**
     * Get low-stock products needing attention.
     */
    private function getLowStockProducts(): array {
        $query = "SELECT name, slug, stock, price, sale_price
                  FROM products
                  WHERE status = 'active' AND stock <= 5
                  ORDER BY stock ASC, updated_at DESC
                  LIMIT 8";

        return $this->conn->query($query)->fetchAll();
    }

    /**
     * Get a single scalar result.
     */
    private function scalar(string $query): mixed {
        $stmt = $this->conn->query($query);
        return $stmt->fetchColumn();
    }
}
