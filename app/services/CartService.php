<?php
/**
 * Session cart service
 */

class CartService {
    private Product $productModel;

    public function __construct() {
        $this->productModel = new Product();

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Add a product to the cart.
     */
    public function addItem(int $productId, int $quantity = 1): array {
        $product = $this->productModel->getById($productId);
        if (!$product) {
            return ['success' => false, 'message' => 'San pham khong ton tai.'];
        }

        if ((int)$product['stock'] <= 0) {
            return ['success' => false, 'message' => 'San pham da het hang.'];
        }

        $quantity = max(1, $quantity);
        $currentQuantity = (int)($_SESSION['cart'][$productId]['quantity'] ?? 0);
        $newQuantity = min((int)$product['stock'], $currentQuantity + $quantity);

        $_SESSION['cart'][$productId] = [
            'product_id' => $productId,
            'quantity' => $newQuantity,
            'added_at' => date('Y-m-d H:i:s'),
        ];

        return ['success' => true, 'message' => 'Da them san pham vao gio hang.'];
    }

    /**
     * Update cart quantities.
     */
    public function updateItems(array $quantities): void {
        foreach ($quantities as $productId => $quantity) {
            $productId = (int)$productId;
            $quantity = (int)$quantity;

            if ($productId <= 0 || !isset($_SESSION['cart'][$productId])) {
                continue;
            }

            if ($quantity <= 0) {
                unset($_SESSION['cart'][$productId]);
                continue;
            }

            $product = $this->productModel->getById($productId);
            if (!$product) {
                unset($_SESSION['cart'][$productId]);
                continue;
            }

            $_SESSION['cart'][$productId]['quantity'] = min(max(1, $quantity), (int)$product['stock']);
        }
    }

    /**
     * Remove one product from the cart.
     */
    public function removeItem(int $productId): void {
        unset($_SESSION['cart'][$productId]);
    }

    /**
     * Clear the whole cart.
     */
    public function clear(): void {
        $_SESSION['cart'] = [];
    }

    /**
     * Get hydrated cart items.
     */
    public function getItems(): array {
        $items = [];

        foreach ($_SESSION['cart'] as $entry) {
            $productId = (int)($entry['product_id'] ?? 0);
            $quantity = (int)($entry['quantity'] ?? 0);

            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }

            $product = $this->productModel->getById($productId);
            if (!$product) {
                continue;
            }

            $price = !empty($product['sale_price']) && (float)$product['sale_price'] > 0
                ? (float)$product['sale_price']
                : (float)$product['price'];

            $quantity = min($quantity, max(0, (int)$product['stock']));
            if ($quantity <= 0) {
                continue;
            }

            $items[] = [
                'product_id' => $productId,
                'slug' => $product['slug'],
                'name' => $product['name'],
                'image_url' => $product['image_url'] ?? image_url('products/default.jpg'),
                'price' => $price,
                'quantity' => $quantity,
                'stock' => (int)$product['stock'],
                'subtotal' => $price * $quantity,
            ];
        }

        return $items;
    }

    /**
     * Get cart totals.
     */
    public function getSummary(): array {
        $items = $this->getItems();
        $subtotal = 0.0;

        foreach ($items as $item) {
            $subtotal += (float)$item['subtotal'];
        }

        $shippingFee = $subtotal >= 500000 || $subtotal <= 0 ? 0.0 : 30000.0;
        $discountAmount = 0.0;

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'shipping_fee' => $shippingFee,
            'total' => $subtotal - $discountAmount + $shippingFee,
        ];
    }

    /**
     * Check whether the cart is empty.
     */
    public function isEmpty(): bool {
        return count($this->getItems()) === 0;
    }
}
