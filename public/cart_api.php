<?php
/**
 * Cart API Endpoint
 * Handles AJAX requests for cart operations
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load dependencies
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/Cart.php';
require_once __DIR__ . '/../app/services/CartService.php';

// Set JSON header
header('Content-Type: application/json');

// Handle CORS if needed
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// For now, use a temporary user ID (replace with actual auth system)
$userId = $_SESSION['user_id'] ?? 1; // Default user ID for testing

// Initialize service
$cartService = new CartService();

try {
    switch ($method) {
        case 'POST':
            handleAddToCart($cartService, $userId);
            break;
            
        case 'GET':
            handleGetCart($cartService, $userId);
            break;
            
        case 'PUT':
            handleUpdateCart($cartService, $userId);
            break;
            
        case 'DELETE':
            handleRemoveFromCart($cartService, $userId);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed'
            ]);
    }
} catch (Exception $e) {
    error_log("Cart API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra'
    ]);
}

/**
 * Handle add to cart request
 */
function handleAddToCart(CartService $cartService, int $userId): void {
    $productId = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    if ($productId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Sản phẩm không hợp lệ'
        ]);
        return;
    }
    
    $result = $cartService->addToCart($userId, $productId, $quantity);
    echo json_encode($result);
}

/**
 * Handle get cart request
 */
function handleGetCart(CartService $cartService, int $userId): void {
    $result = $cartService->getCart($userId);
    echo json_encode($result);
}

/**
 * Handle update cart request
 */
function handleUpdateCart(CartService $cartService, int $userId): void {
    // Parse JSON input for PUT requests
    $input = json_decode(file_get_contents('php://input'), true);
    
    $productId = (int)($input['product_id'] ?? 0);
    $quantity = (int)($input['quantity'] ?? 0);
    
    if ($productId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Sản phẩm không hợp lệ'
        ]);
        return;
    }
    
    $result = $cartService->updateCartItem($userId, $productId, $quantity);
    echo json_encode($result);
}

/**
 * Handle remove from cart request
 */
function handleRemoveFromCart(CartService $cartService, int $userId): void {
    // Parse JSON input for DELETE requests
    $input = json_decode(file_get_contents('php://input'), true);
    
    $productId = (int)($input['product_id'] ?? 0);
    
    if ($productId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Sản phẩm không hợp lệ'
        ]);
        return;
    }
    
    $result = $cartService->removeFromCart($userId, $productId);
    echo json_encode($result);
}
