<?php
// Test cart functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/Cart.php';
require_once __DIR__ . '/../app/services/CartService.php';

echo "=== Testing Cart Functionality ===\n\n";

$cartService = new CartService();
$cartModel = new Cart();
$userId = 1; // Test user

// Clean up first
echo "Step 0: Clear existing cart\n";
$cartModel->clearCart($userId);
echo "✓ Cart cleared\n\n";

// Test 1: Add item to cart
echo "Test 1: Add product to cart\n";
$result = $cartService->addToCart($userId, 1, 2); // Product ID 1, Quantity 2
if ($result['success']) {
    echo "✓ {$result['message']}\n";
    echo "  Cart count: {$result['cart_count']}\n";
} else {
    echo "✗ {$result['message']}\n";
}
echo "\n";

// Test 2: Add another item
echo "Test 2: Add another product\n";
$result = $cartService->addToCart($userId, 2, 1); // Product ID 2, Quantity 1
if ($result['success']) {
    echo "✓ {$result['message']}\n";
    echo "  Cart count: {$result['cart_count']}\n";
} else {
    echo "✗ {$result['message']}\n";
}
echo "\n";

// Test 3: Add duplicate item (should increase quantity)
echo "Test 3: Add duplicate product (should increase quantity)\n";
$result = $cartService->addToCart($userId, 1, 1); // Product ID 1 again
if ($result['success']) {
    echo "✓ {$result['message']}\n";
    echo "  Cart count: {$result['cart_count']}\n";
} else {
    echo "✗ {$result['message']}\n";
}
echo "\n";

// Test 4: Get cart items
echo "Test 4: Get cart items\n";
$result = $cartService->getCart($userId);
if ($result['success']) {
    echo "✓ Found {$result['count']} items\n";
    echo "  Total: " . number_format($result['total'], 0, ',', '.') . "đ\n";
    echo "  Items:\n";
    foreach ($result['items'] as $item) {
        echo "    - {$item['name']} x{$item['quantity']} = " . number_format($item['price'] * $item['quantity'], 0, ',', '.') . "đ\n";
    }
} else {
    echo "✗ Failed to get cart\n";
}
echo "\n";

// Test 5: Update quantity
echo "Test 5: Update product quantity\n";
$result = $cartService->updateCartItem($userId, 1, 1); // Reduce to 1
if ($result['success']) {
    echo "✓ {$result['message']}\n";
    echo "  Cart count: {$result['cart_count']}\n";
    echo "  Cart total: " . number_format($result['cart_total'], 0, ',', '.') . "đ\n";
} else {
    echo "✗ {$result['message']}\n";
}
echo "\n";

// Test 6: Remove item
echo "Test 6: Remove product from cart\n";
$result = $cartService->removeFromCart($userId, 2); // Remove product 2
if ($result['success']) {
    echo "✓ {$result['message']}\n";
    echo "  Cart count: {$result['cart_count']}\n";
} else {
    echo "✗ {$result['message']}\n";
}
echo "\n";

// Test 7: Check final cart state
echo "Test 7: Final cart state\n";
$result = $cartService->getCart($userId);
if ($result['success']) {
    echo "✓ Cart has {$result['count']} items\n";
    echo "  Total: " . number_format($result['total'], 0, ',', '.') . "đ\n";
} else {
    echo "✗ Failed to get cart\n";
}
echo "\n";

// Test 8: Test invalid operations
echo "Test 8: Test error handling\n";

// Invalid product
$result = $cartService->addToCart($userId, 99999, 1);
echo ($result['success'] ? "✗" : "✓") . " Invalid product: {$result['message']}\n";

// Invalid quantity
$result = $cartService->addToCart($userId, 1, -1);
echo ($result['success'] ? "✗" : "✓") . " Invalid quantity: {$result['message']}\n";

echo "\n=== All Cart Tests Complete ===\n";
