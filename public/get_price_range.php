<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->query('SELECT MIN(price) as min_price, MAX(price) as max_price FROM products');
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($result);
