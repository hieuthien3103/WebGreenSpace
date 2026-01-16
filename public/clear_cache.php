<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../app/models/Product.php';

// Clear opcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OPcache đã được xóa<br>";
} else {
    echo "✗ OPcache không được bật<br>";
}

// Clear PHP file stat cache
clearstatcache(true);
echo "✓ File stat cache đã được xóa<br>";

echo "<hr>";

// Test Product Model
$productModel = new Product();
$products = $productModel->getAll(3, 0);

echo "<h3>Test Product Model:</h3>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Tên</th><th>image_url</th></tr>";
foreach ($products as $p) {
    echo "<tr>";
    echo "<td>{$p['id']}</td>";
    echo "<td>{$p['name']}</td>";
    echo "<td><strong style='color: blue;'>{$p['image_url']}</strong></td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<p><a href='products.php'>Xem trang sản phẩm</a> | <a href='home.php'>Xem trang chủ</a></p>";
?>

