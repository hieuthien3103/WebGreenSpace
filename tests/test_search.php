<?php
// Test search functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/Category.php';
require_once __DIR__ . '/../app/services/ProductService.php';

echo "=== Testing Search Functionality ===\n\n";

$productService = new ProductService();

// Test 1: Search with keyword
echo "Test 1: Search for 'cây'\n";
$result = $productService->getProducts(['search' => 'cây', 'limit' => 5]);
$products = $result['products'];
echo "Found " . count($products) . " products\n";
foreach ($products as $product) {
    echo "  - {$product['name']} (ID: {$product['id']})\n";
}
echo "\n";

// Test 2: Search with keyword 2
echo "Test 2: Search for 'trầu bà'\n";
$result = $productService->getProducts(['search' => 'trầu bà', 'limit' => 5]);
$products = $result['products'];
echo "Found " . count($products) . " products\n";
foreach ($products as $product) {
    echo "  - {$product['name']} (ID: {$product['id']})\n";
}
echo "\n";

// Test 3: Search with no results
echo "Test 3: Search for 'xyz123' (should return no results)\n";
$result = $productService->getProducts(['search' => 'xyz123', 'limit' => 5]);
$products = $result['products'];
echo "Found " . count($products) . " products\n";
if (empty($products)) {
    echo "  ✓ Correctly returns empty results\n";
}
echo "\n";

// Test 4: Search with category filter
echo "Test 4: Search 'cây' in specific category\n";
$result = $productService->getProducts(['search' => 'cây', 'category' => 'cay-de-ban', 'limit' => 5]);
$products = $result['products'];
echo "Found " . count($products) . " products in category\n";
foreach ($products as $product) {
    echo "  - {$product['name']} (Category: {$product['category_name']})\n";
}
echo "\n";

// Test 5: Search with price range
echo "Test 5: Search products under 300000 VND\n";
$result = $productService->getProducts(['search' => 'cây', 'max_price' => 300000, 'limit' => 5]);
$products = $result['products'];
echo "Found " . count($products) . " products under 300,000đ\n";
foreach ($products as $product) {
    $price = $product['price'];
    echo "  - {$product['name']} ({$price}đ)\n";
}
echo "\n";

// Test 6: Search model directly
echo "Test 6: Test Product model search method\n";
$productModel = new Product();
$products = $productModel->search('sen', 3);
echo "Found " . count($products) . " products with 'sen'\n";
foreach ($products as $product) {
    echo "  - {$product['name']}\n";
}

echo "\n=== All Search Tests Complete ===\n";
