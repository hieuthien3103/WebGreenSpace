<?php
/**
 * Order model
 */

class Order {
    private PDO $conn;
    private string $table = 'orders';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Get the raw PDO connection for transactions.
     */
    public function getConnection(): PDO {
        return $this->conn;
    }

    /**
     * Create an order row.
     */
    public function create(array $data): int {
        $query = "INSERT INTO {$this->table}
                  (user_id, order_number, full_name, email, phone, address, note, subtotal, discount_amount, shipping_fee, total_amount, coupon_code, payment_method, payment_status, order_status)
                  VALUES
                  (:user_id, :order_number, :full_name, :email, :phone, :address, :note, :subtotal, :discount_amount, :shipping_fee, :total_amount, :coupon_code, :payment_method, :payment_status, :order_status)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':user_id', $data['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':order_number', $data['order_number'], PDO::PARAM_STR);
        $stmt->bindValue(':full_name', $data['full_name'], PDO::PARAM_STR);
        $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindValue(':phone', $data['phone'], PDO::PARAM_STR);
        $stmt->bindValue(':address', $data['address'], PDO::PARAM_STR);
        $stmt->bindValue(':note', $data['note'] ?: null, $data['note'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':subtotal', $data['subtotal']);
        $stmt->bindValue(':discount_amount', $data['discount_amount']);
        $stmt->bindValue(':shipping_fee', $data['shipping_fee']);
        $stmt->bindValue(':total_amount', $data['total_amount']);
        $stmt->bindValue(':coupon_code', $data['coupon_code'] ?: null, $data['coupon_code'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':payment_method', $data['payment_method'], PDO::PARAM_STR);
        $stmt->bindValue(':payment_status', $data['payment_status'], PDO::PARAM_STR);
        $stmt->bindValue(':order_status', $data['order_status'], PDO::PARAM_STR);
        $stmt->execute();

        return (int)$this->conn->lastInsertId();
    }

    /**
     * Add a line item to an order.
     */
    public function addItem(int $orderId, array $item): void {
        $query = "INSERT INTO order_details
                  (order_id, product_id, variant_id, product_name, product_image, price, quantity, subtotal)
                  VALUES
                  (:order_id, :product_id, NULL, :product_name, :product_image, :price, :quantity, :subtotal)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->bindValue(':product_id', $item['product_id'], PDO::PARAM_INT);
        $stmt->bindValue(':product_name', $item['product_name'], PDO::PARAM_STR);
        $stmt->bindValue(':product_image', $item['product_image'] ?: null, $item['product_image'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':price', $item['price']);
        $stmt->bindValue(':quantity', $item['quantity'], PDO::PARAM_INT);
        $stmt->bindValue(':subtotal', $item['subtotal']);
        $stmt->execute();
    }

    /**
     * Add a payment row for an order.
     */
    public function addPayment(int $orderId, array $payment): void {
        $query = "INSERT INTO payments (order_id, provider, transaction_code, status, amount, paid_at, note)
                  VALUES (:order_id, :provider, :transaction_code, :status, :amount, :paid_at, :note)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->bindValue(':provider', $payment['provider'], PDO::PARAM_STR);
        $stmt->bindValue(':transaction_code', $payment['transaction_code'] ?: null, $payment['transaction_code'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':status', $payment['status'], PDO::PARAM_STR);
        $stmt->bindValue(':amount', $payment['amount']);
        $stmt->bindValue(':paid_at', $payment['paid_at'] ?: null, $payment['paid_at'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':note', $payment['note'] ?: null, $payment['note'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->execute();
    }

    /**
     * Get recent orders with item counts for a user.
     */
    public function getByUserId(int $userId, int $limit = 10): array {
        $query = "SELECT o.*,
                         COUNT(od.id) AS item_count
                  FROM {$this->table} o
                  LEFT JOIN order_details od ON od.order_id = o.id
                  WHERE o.user_id = :user_id
                  GROUP BY o.id
                  ORDER BY o.created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get a full order detail with items and latest payment for one user.
     */
    public function getDetailByUserId(int $userId, int $orderId): ?array {
        $query = "SELECT o.*,
                         COUNT(od.id) AS item_count,
                         COALESCE(SUM(od.quantity), 0) AS total_quantity
                  FROM {$this->table} o
                  LEFT JOIN order_details od ON od.order_id = o.id
                  WHERE o.user_id = :user_id AND o.id = :order_id
                  GROUP BY o.id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->execute();

        $order = $stmt->fetch();
        if (!$order) {
            return null;
        }

        $itemStmt = $this->conn->prepare(
            "SELECT od.*,
                    p.slug AS product_slug
             FROM order_details od
             INNER JOIN {$this->table} o ON o.id = od.order_id
             LEFT JOIN products p ON p.id = od.product_id
             WHERE o.user_id = :user_id AND od.order_id = :order_id
             ORDER BY od.id ASC"
        );
        $itemStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $itemStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $itemStmt->execute();
        $order['items'] = $itemStmt->fetchAll();

        $paymentStmt = $this->conn->prepare(
            "SELECT p.*
             FROM payments p
             INNER JOIN {$this->table} o ON o.id = p.order_id
             WHERE o.user_id = :user_id AND p.order_id = :order_id
             ORDER BY p.id DESC
             LIMIT 1"
        );
        $paymentStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $paymentStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $paymentStmt->execute();
        $order['payment'] = $paymentStmt->fetch() ?: null;

        return $order;
    }
}
