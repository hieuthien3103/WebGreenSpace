<?php
// Test file to check products database connection
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/Category.php';

echo "<h1>Test Products Database</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .product { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 8px; }
    .product img { max-width: 200px; height: auto; border-radius: 4px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
</style>";

try {
    // Test database connection
    echo "<h2 class='success'>✓ Database Connected</h2>";
    
    // Test Product Model
    $productModel = new Product();
    $products = $productModel->getAll(10);
    
    echo "<h2>Total Products: " . count($products) . "</h2>";
    
    if (!empty($products)) {
        foreach ($products as $product) {
            echo "<div class='product'>";
            echo "<h3>" . clean($product['name']) . "</h3>";
            if (!empty($product['image_url'])) {
                echo "<img src='" . $product['image_url'] . "' alt='" . clean($product['name']) . "'>";
            }
            echo "<p><strong>Price:</strong> " . format_currency($product['price']) . "</p>";
            if (!empty($product['sale_price'])) {
                echo "<p><strong>Sale Price:</strong> " . format_currency($product['sale_price']) . "</p>";
            }
            echo "<p><strong>Stock:</strong> " . $product['stock'] . "</p>";
            echo "<p><strong>Category:</strong> " . ($product['category_name'] ?? 'N/A') . "</p>";
            echo "<p><strong>Description:</strong> " . clean(substr($product['description'], 0, 150)) . "...</p>";
            echo "<p><strong>Slug:</strong> <a href='product-detail.php?slug=" . $product['slug'] . "'>" . $product['slug'] . "</a></p>";
            echo "</div>";
        }
    } else {
        echo "<p class='error'>No products found in database!</p>";
        echo "<p>Please import sample_data.sql to add products.</p>";
    }
    
    // Test Categories
    echo "<hr>";
    echo "<h2>Categories</h2>";
    $categoryModel = new Category();
    $categories = $categoryModel->getAll();
    
    if (!empty($categories)) {
        echo "<ul>";
        foreach ($categories as $cat) {
            echo "<li>" . clean($cat['name']) . " (" . $cat['slug'] . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='error'>No categories found!</p>";
    }
    
} catch (Exception $e) {
    echo "<h2 class='error'>✗ Error: " . $e->getMessage() . "</h2>";
    echo "<p>Please check:</p>";
    echo "<ul>";
    echo "<li>Database connection settings in config/config.php</li>";
    echo "<li>Database 'webgreenspace' exists</li>";
    echo "<li>Tables are created (run schema.sql)</li>";
    echo "<li>Sample data is imported (run sample_data.sql)</li>";
    echo "</ul>";
}
?>
