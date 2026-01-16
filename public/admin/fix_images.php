<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "<h2>Cập nhật đường dẫn ảnh trong Database</h2>";

// Lấy tất cả sản phẩm
$stmt = $conn->query("SELECT id, name, thumbnail_url FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Tên</th><th>Đường dẫn cũ</th><th>Đường dẫn mới</th><th>Kết quả</th></tr>";

$updateStmt = $conn->prepare("UPDATE products SET thumbnail_url = :thumbnail WHERE id = :id");

foreach ($products as $product) {
    $oldPath = $product['thumbnail_url'];
    
    // Xóa đường dẫn tuyệt đối nếu có
    $newPath = $oldPath;
    $newPath = str_replace('"', '', $newPath); // Xóa dấu ngoặc kép
    $newPath = basename($newPath); // Chỉ lấy tên file
    
    // Thêm thư mục products/ nếu chưa có
    if (strpos($newPath, 'products/') !== 0) {
        $newPath = 'products/' . $newPath;
    }
    
    // Cập nhật database
    try {
        $updateStmt->execute([
            ':thumbnail' => $newPath,
            ':id' => $product['id']
        ]);
        $result = "<span style='color: green;'>✓ Đã cập nhật</span>";
    } catch (Exception $e) {
        $result = "<span style='color: red;'>✗ Lỗi: " . $e->getMessage() . "</span>";
    }
    
    echo "<tr>";
    echo "<td>{$product['id']}</td>";
    echo "<td>{$product['name']}</td>";
    echo "<td>{$oldPath}</td>";
    echo "<td>{$newPath}</td>";
    echo "<td>{$result}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<p><strong>Hoàn tất!</strong> <a href='check_images.php'>Kiểm tra lại ảnh</a> | <a href='products.php'>Xem trang sản phẩm</a></p>";
?>
