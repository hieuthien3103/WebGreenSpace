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
            'commerce_summary' => $this->getCommerceSummary(),
            'category_summary' => $this->getCategorySummary(),
            'contact_summary' => $this->getContactSummary(),
            'recent_contacts' => $this->getRecentContacts(),
            'recent_orders' => $this->getRecentOrders(),
            'recent_users' => $this->getRecentUsers(),
            'top_customers' => $this->getTopCustomers(),
            'top_categories' => $this->getTopCategories(),
            'top_products' => $this->getTopProducts(),
            'low_stock_products' => $this->getLowStockProducts(),
        ];
    }

    /**
     * Get commerce-focused insights for reuse outside the dashboard page.
     */
    public function getCommerceInsights(): array {
        return [
            'commerce_summary' => $this->getCommerceSummary(),
            'top_customers' => $this->getTopCustomers(),
            'top_products' => $this->getTopProducts(),
        ];
    }

    /**
     * Get trend datasets for revenue and sold units by day and month.
     */
    public function getCommerceTrendData(): array {
        return [
            'last_7_days' => $this->buildTrendDataset('day', 7, '7 ngày gần nhất'),
            'last_30_days' => $this->buildTrendDataset('day', 30, '30 ngày gần nhất'),
            'last_12_months' => $this->buildTrendDataset('month', 12, '12 tháng gần nhất'),
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
     * Get commerce KPIs focused on buyers and products sold.
     */
    private function getCommerceSummary(): array {
        return [
            [
                'label' => 'Khách đã mua',
                'value' => $this->scalar("
                    SELECT COUNT(DISTINCT user_id)
                    FROM orders
                    WHERE order_status <> 'cancelled'
                "),
                'hint' => 'Số khách từng phát sinh đơn không bị hủy',
                'icon' => 'groups',
            ],
            [
                'label' => 'Khách quay lại',
                'value' => $this->scalar("
                    SELECT COUNT(*)
                    FROM (
                        SELECT user_id
                        FROM orders
                        WHERE order_status <> 'cancelled'
                        GROUP BY user_id
                        HAVING COUNT(*) >= 2
                    ) AS repeat_customers
                "),
                'hint' => 'Khách có từ 2 đơn hợp lệ trở lên',
                'icon' => 'person_check',
            ],
            [
                'label' => 'Sản phẩm đã bán',
                'value' => $this->scalar("
                    SELECT COALESCE(SUM(CASE WHEN o.order_status <> 'cancelled' THEN od.quantity ELSE 0 END), 0)
                    FROM order_details od
                    INNER JOIN orders o ON o.id = od.order_id
                "),
                'hint' => 'Tổng số lượng sản phẩm đã bán ra',
                'icon' => 'shopping_bag',
            ],
            [
                'label' => 'Mặt hàng phát sinh bán',
                'value' => $this->scalar("
                    SELECT COUNT(DISTINCT od.product_id)
                    FROM order_details od
                    INNER JOIN orders o ON o.id = od.order_id
                    WHERE o.order_status <> 'cancelled'
                "),
                'hint' => 'Số sản phẩm đã từng có giao dịch',
                'icon' => 'inventory_2',
            ],
            [
                'label' => 'Đơn phát sinh mua',
                'value' => $this->scalar("
                    SELECT COUNT(*)
                    FROM orders
                    WHERE order_status <> 'cancelled'
                "),
                'hint' => 'Đơn hàng hợp lệ để tính kinh doanh',
                'icon' => 'receipt_long',
            ],
            [
                'label' => 'Giá trị TB / đơn',
                'value' => (float)$this->scalar("
                    SELECT COALESCE(AVG(total_amount), 0)
                    FROM orders
                    WHERE order_status <> 'cancelled'
                "),
                'hint' => 'Tính trên các đơn không bị hủy',
                'icon' => 'monitoring',
                'currency' => true,
            ],
        ];
    }

    /**
     * Get headline contact metrics for the dashboard.
     */
    private function getContactSummary(): array {
        if (!$this->tableExists('contact_messages')) {
            return [];
        }

        return [
            [
                'label' => 'Tổng liên hệ',
                'value' => $this->scalar("SELECT COUNT(*) FROM contact_messages"),
                'hint' => 'Toàn bộ biểu mẫu đã gửi',
                'icon' => 'contact_mail',
            ],
            [
                'label' => 'Chưa đọc',
                'value' => $this->scalar("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0"),
                'hint' => 'Cần mở và phân loại sớm',
                'icon' => 'mark_email_unread',
            ],
            [
                'label' => 'Hôm nay',
                'value' => $this->scalar("SELECT COUNT(*) FROM contact_messages WHERE DATE(created_at) = CURRENT_DATE"),
                'hint' => 'Liên hệ mới trong ngày',
                'icon' => 'today',
            ],
            [
                'label' => 'Đang xử lý',
                'value' => $this->scalar("SELECT COUNT(*) FROM contact_messages WHERE status = 'in_progress'"),
                'hint' => 'Lead đang theo dõi',
                'icon' => 'support_agent',
            ],
        ];
    }

    /**
     * Get recent contact messages for the dashboard feed.
     */
    private function getRecentContacts(): array {
        if (!$this->tableExists('contact_messages')) {
            return [];
        }

        $query = "SELECT id, full_name, email, phone, subject, status, is_read, created_at
                  FROM contact_messages
                  ORDER BY is_read ASC, created_at DESC, id DESC
                  LIMIT 6";

        return $this->conn->query($query)->fetchAll();
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
     * Get top customers by successful/valid order value.
     */
    private function getTopCustomers(): array {
        $query = "SELECT u.id,
                         u.username,
                         u.full_name,
                         u.email,
                         order_summary.completed_order_count,
                         order_summary.gross_revenue,
                         order_summary.last_order_at,
                         COALESCE(unit_summary.units_bought, 0) AS units_bought
                  FROM users u
                  INNER JOIN (
                      SELECT user_id,
                             COUNT(*) AS completed_order_count,
                             COALESCE(SUM(total_amount), 0) AS gross_revenue,
                             MAX(created_at) AS last_order_at
                      FROM orders
                      WHERE order_status <> 'cancelled'
                      GROUP BY user_id
                  ) AS order_summary ON order_summary.user_id = u.id
                  LEFT JOIN (
                      SELECT o.user_id,
                             COALESCE(SUM(od.quantity), 0) AS units_bought
                      FROM orders o
                      INNER JOIN order_details od ON od.order_id = o.id
                      WHERE o.order_status <> 'cancelled'
                      GROUP BY o.user_id
                  ) AS unit_summary ON unit_summary.user_id = u.id
                  ORDER BY order_summary.gross_revenue DESC,
                           order_summary.completed_order_count DESC,
                           units_bought DESC,
                           order_summary.last_order_at DESC
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
                         COALESCE(sales.units_sold, 0) AS units_sold,
                         COALESCE(sales.order_count, 0) AS order_count,
                         COALESCE(sales.customer_count, 0) AS customer_count,
                         COALESCE(sales.gross_revenue, 0) AS gross_revenue
                  FROM products p
                  LEFT JOIN (
                      SELECT od.product_id,
                             COALESCE(SUM(od.quantity), 0) AS units_sold,
                             COUNT(DISTINCT o.id) AS order_count,
                             COUNT(DISTINCT o.user_id) AS customer_count,
                             COALESCE(SUM(od.subtotal), 0) AS gross_revenue
                      FROM order_details od
                      INNER JOIN orders o ON o.id = od.order_id
                      WHERE o.order_status <> 'cancelled'
                      GROUP BY od.product_id
                  ) AS sales ON sales.product_id = p.id
                  WHERE p.status = 'active'
                  ORDER BY units_sold DESC, gross_revenue DESC, p.created_at DESC
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
     * Build a trend dataset with missing buckets filled.
     */
    private function buildTrendDataset(string $period, int $limit, string $title): array {
        $bucketMap = $this->generateEmptyBuckets($period, $limit);
        $revenueMap = $this->getRevenueTrendMap($period, $limit);
        $unitsMap = $this->getUnitsTrendMap($period, $limit);

        $points = [];
        foreach ($bucketMap as $key => $meta) {
            $points[] = [
                'key' => $key,
                'label' => $meta['label'],
                'short_label' => $meta['short_label'],
                'revenue' => (float)($revenueMap[$key] ?? 0),
                'units' => (int)($unitsMap[$key] ?? 0),
            ];
        }

        return [
            'period' => $period,
            'title' => $title,
            'points' => $points,
            'summary' => [
                'revenue_total' => array_sum(array_map(static fn(array $point): float => (float)$point['revenue'], $points)),
                'units_total' => array_sum(array_map(static fn(array $point): int => (int)$point['units'], $points)),
            ],
        ];
    }

    /**
     * Generate sequential day/month buckets for the chart axis.
     *
     * @return array<string, array{label: string, short_label: string}>
     */
    private function generateEmptyBuckets(string $period, int $limit): array {
        $buckets = [];

        if ($period === 'month') {
            $cursor = new DateTimeImmutable('first day of this month 00:00:00');
            $cursor = $cursor->modify('-' . max(0, $limit - 1) . ' months');

            for ($index = 0; $index < $limit; $index++) {
                $bucketDate = $cursor->modify('+' . $index . ' months');
                $key = $bucketDate->format('Y-m-01');
                $buckets[$key] = [
                    'label' => 'Tháng ' . $bucketDate->format('m/Y'),
                    'short_label' => 'T' . $bucketDate->format('m'),
                ];
            }

            return $buckets;
        }

        $cursor = new DateTimeImmutable('today');
        $cursor = $cursor->modify('-' . max(0, $limit - 1) . ' days');

        for ($index = 0; $index < $limit; $index++) {
            $bucketDate = $cursor->modify('+' . $index . ' days');
            $key = $bucketDate->format('Y-m-d');
            $buckets[$key] = [
                'label' => $bucketDate->format('d/m/Y'),
                'short_label' => $bucketDate->format('d/m'),
            ];
        }

        return $buckets;
    }

    /**
     * Revenue trend based on paid and non-cancelled orders.
     *
     * @return array<string, float>
     */
    private function getRevenueTrendMap(string $period, int $limit): array {
        if ($period === 'month') {
            $startDate = (new DateTimeImmutable('first day of this month 00:00:00'))
                ->modify('-' . max(0, $limit - 1) . ' months')
                ->format('Y-m-d H:i:s');

            $query = "SELECT DATE_FORMAT(created_at, '%Y-%m-01') AS bucket_key,
                             COALESCE(SUM(total_amount), 0) AS revenue_total
                      FROM orders
                      WHERE created_at >= :start_date
                        AND order_status <> 'cancelled'
                        AND payment_status = 'paid'
                      GROUP BY bucket_key
                      ORDER BY bucket_key ASC";
        } else {
            $startDate = (new DateTimeImmutable('today 00:00:00'))
                ->modify('-' . max(0, $limit - 1) . ' days')
                ->format('Y-m-d H:i:s');

            $query = "SELECT DATE(created_at) AS bucket_key,
                             COALESCE(SUM(total_amount), 0) AS revenue_total
                      FROM orders
                      WHERE created_at >= :start_date
                        AND order_status <> 'cancelled'
                        AND payment_status = 'paid'
                      GROUP BY bucket_key
                      ORDER BY bucket_key ASC";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':start_date', $startDate, PDO::PARAM_STR);
        $stmt->execute();

        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[(string)$row['bucket_key']] = (float)($row['revenue_total'] ?? 0);
        }

        return $map;
    }

    /**
     * Sold units trend based on non-cancelled orders.
     *
     * @return array<string, int>
     */
    private function getUnitsTrendMap(string $period, int $limit): array {
        if ($period === 'month') {
            $startDate = (new DateTimeImmutable('first day of this month 00:00:00'))
                ->modify('-' . max(0, $limit - 1) . ' months')
                ->format('Y-m-d H:i:s');

            $query = "SELECT DATE_FORMAT(o.created_at, '%Y-%m-01') AS bucket_key,
                             COALESCE(SUM(od.quantity), 0) AS units_total
                      FROM order_details od
                      INNER JOIN orders o ON o.id = od.order_id
                      WHERE o.created_at >= :start_date
                        AND o.order_status <> 'cancelled'
                      GROUP BY bucket_key
                      ORDER BY bucket_key ASC";
        } else {
            $startDate = (new DateTimeImmutable('today 00:00:00'))
                ->modify('-' . max(0, $limit - 1) . ' days')
                ->format('Y-m-d H:i:s');

            $query = "SELECT DATE(o.created_at) AS bucket_key,
                             COALESCE(SUM(od.quantity), 0) AS units_total
                      FROM order_details od
                      INNER JOIN orders o ON o.id = od.order_id
                      WHERE o.created_at >= :start_date
                        AND o.order_status <> 'cancelled'
                      GROUP BY bucket_key
                      ORDER BY bucket_key ASC";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':start_date', $startDate, PDO::PARAM_STR);
        $stmt->execute();

        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[(string)$row['bucket_key']] = (int)($row['units_total'] ?? 0);
        }

        return $map;
    }

    /**
     * Get a single scalar result.
     */
    private function scalar(string $query): mixed {
        $stmt = $this->conn->query($query);
        return $stmt->fetchColumn();
    }

    /**
     * Check whether a table exists before querying optional modules.
     */
    private function tableExists(string $table): bool {
        $stmt = $this->conn->prepare("SHOW TABLES LIKE :table_name");
        $stmt->bindValue(':table_name', $table, PDO::PARAM_STR);
        $stmt->execute();

        return (bool)$stmt->fetchColumn();
    }
}
