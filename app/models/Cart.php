<?php
/**
 * Cart Model
 * Handles database operations for shopping cart
 */

class Cart {
    private PDO $conn;
    private string $table = 'cart';

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Add item to cart or update quantity if exists
     * 
     * @param int $userId User ID
     * @param int $productId Product ID
     * @param int $quantity Quantity to add
     * @return bool Success status
     */
    public function addItem(int $userId, int $productId, int $quantity = 1): bool {
        // Check if item already exists
        $existing = $this->getItem($userId, $productId);
        
        if ($existing) {
            // Update quantity
            return $this->updateQuantity($userId, $productId, $existing['quantity'] + $quantity);
        } else {
            // Insert new item
            $query = "INSERT INTO {$this->table} (user_id, product_id, quantity) 
                      VALUES (:user_id, :product_id, :quantity)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            
            return $stmt->execute();
        }
    }

    /**
     * Get cart item by user and product
     * 
     * @param int $userId User ID
     * @param int $productId Product ID
     * @return array|null Cart item or null
     */
    public function getItem(int $userId, int $productId): ?array {
        $query = "SELECT * FROM {$this->table} 
                  WHERE user_id = :user_id AND product_id = :product_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get all cart items for user with product details
     * 
     * @param int $userId User ID
     * @return array Cart items
     */
    public function getCartItems(int $userId): array {
        $query = "SELECT c.*,
                         p.name,
                         p.slug,
                         p.price,
                         p.sale_price,
                         p.image,
                         p.stock
                  FROM {$this->table} c
                  INNER JOIN products p ON c.product_id = p.id
                  WHERE c.user_id = :user_id
                  ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $items = $stmt->fetchAll();

        // Format image URLs
        foreach ($items as &$item) {
            if (!empty($item['image'])) {
                $item['image_url'] = strpos($item['image'], 'http') === 0
                    ? $item['image']
                    : image_url($item['image']);
            } else {
                $item['image_url'] = image_url('products/default.jpg');
            }
        }

        return $items;
    }

    /**
     * Update cart item quantity
     * 
     * @param int $userId User ID
     * @param int $productId Product ID
     * @param int $quantity New quantity
     * @return bool Success status
     */
    public function updateQuantity(int $userId, int $productId, int $quantity): bool {
        if ($quantity <= 0) {
            return $this->removeItem($userId, $productId);
        }
        
        $query = "UPDATE {$this->table} 
                  SET quantity = :quantity, updated_at = NOW()
                  WHERE user_id = :user_id AND product_id = :product_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Remove item from cart
     * 
     * @param int $userId User ID
     * @param int $productId Product ID
     * @return bool Success status
     */
    public function removeItem(int $userId, int $productId): bool {
        $query = "DELETE FROM {$this->table} 
                  WHERE user_id = :user_id AND product_id = :product_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Clear all items from user's cart
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function clearCart(int $userId): bool {
        $query = "DELETE FROM {$this->table} WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Get cart item count for user
     * 
     * @param int $userId User ID
     * @return int Item count
     */
    public function getItemCount(int $userId): int {
        $query = "SELECT SUM(quantity) as total FROM {$this->table} WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Get cart total price for user
     * 
     * @param int $userId User ID
     * @return float Total price
     */
    public function getCartTotal(int $userId): float {
        $query = "SELECT SUM(c.quantity * COALESCE(NULLIF(p.sale_price, 0), p.price)) as total
                  FROM {$this->table} c
                  INNER JOIN products p ON c.product_id = p.id
                  WHERE c.user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return (float)($result['total'] ?? 0);
    }
}
