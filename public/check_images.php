<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../app/models/Product.php';

$db = new Database();
$conn = $db->getConnection();

// Test với Product Model
$productModel = new Product();
$products = $productModel->getAll(5, 0);

echo "<h2>Test Product Model - image_url</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Tên</th><th>thumbnail_url (DB)</th><th>image_url (Model)</th><th>Ảnh</th></tr>";

foreach ($products as $product) {
    echo "<tr>";
    echo "<td>{$product['id']}</td>";
    echo "<td>{$product['name']}</td>";
    echo "<td>" . ($product['thumbnail_url'] ?? 'N/A') . "</td>";
    echo "<td><strong style='color: blue;'>{$product['image_url']}</strong></td>";
    echo "<td><img src='{$product['image_url']}' width='100' style='border: 2px solid red;'></td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h2>Cấu hình:</h2>";
echo "<ul>";
echo "<li><strong>IMG_URL:</strong> " . IMG_URL . "</li>";
echo "<li><strong>UPLOAD_URL:</strong> " . UPLOAD_URL . "</li>";
echo "<li><strong>image_url('products/test.png'):</strong> " . image_url('products/test.png') . "</li>";
echo "<li><strong>upload_url('products/test.png'):</strong> " . upload_url('products/test.png') . "</li>";
echo "</ul>";
?>
