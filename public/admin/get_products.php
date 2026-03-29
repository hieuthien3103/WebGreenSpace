<?php
require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$db = new Database();
$conn = $db->getConnection();

$query = "SELECT id, name, slug, price, sale_price, image
          FROM products
          WHERE status = 'active'
          ORDER BY created_at DESC";

$products = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
$payload = array_map(static function (array $product): array {
    $product['thumbnail_url'] = !empty($product['image']) ? upload_url($product['image']) : image_url('products/default.jpg');
    return $product;
}, $products);

echo json_encode($payload, JSON_UNESCAPED_UNICODE);
