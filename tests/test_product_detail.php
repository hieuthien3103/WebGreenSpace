<?php
// Test product detail functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../app/models/Product.php';

echo "=== Testing Product Detail ===\n\n";

// Test 1: Get product by ID
echo "Test 1: Get product by ID=1\n";
$productModel = new Product();
$product = $productModel->getById(1);

if ($product) {
    echo "✓ Product found: {$product['name']}\n";
    echo "  - ID: {$product['id']}\n";
    echo "  - Price: {$product['price']}\n";
    echo "  - Category: " . ($product['category_name'] ?? 'N/A') . "\n";
} else {
    echo "✗ Product not found!\n";
}

echo "\n";

// Test 2: Check incrementViews method
echo "Test 2: Test incrementViews method\n";
if (method_exists($productModel, 'incrementViews')) {
    echo "✓ Method incrementViews exists\n";
    
    if ($product) {
        $result = $productModel->incrementViews($product['id']);
        echo ($result ? "✓" : "✗") . " incrementViews executed\n";
    }
} else {
    echo "✗ Method incrementViews does NOT exist!\n";
}

echo "\n";

// Test 3: Get product by slug
echo "Test 3: Get product by slug\n";
if ($product && !empty($product['slug'])) {
    $productBySlug = $productModel->getBySlug($product['slug']);
    if ($productBySlug) {
        echo "✓ Product found by slug: {$productBySlug['name']}\n";
    } else {
        echo "✗ Product not found by slug!\n";
    }
}

echo "\n";

// Test 4: Check for related products
echo "Test 4: Get related products\n";
if ($product && !empty($product['category_id'])) {
    $relatedProducts = $productModel->getByCategory($product['category_id'], 4);
    echo "✓ Found " . count($relatedProducts) . " related products\n";
}

echo "\n=== Tests Complete ===\n";
