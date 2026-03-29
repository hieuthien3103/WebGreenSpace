<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->query(
    "SELECT
        MIN(CASE WHEN sale_price IS NOT NULL AND sale_price > 0 THEN sale_price ELSE price END) AS min_price,
        MAX(CASE WHEN sale_price IS NOT NULL AND sale_price > 0 THEN sale_price ELSE price END) AS max_price
     FROM products
     WHERE status = 'active'"
);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($result);
