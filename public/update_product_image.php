<?php
// Update product image with URL
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    $image_url = $_POST['image_url'] ?? null;
    
    if (!$product_id || !$image_url) {
        die('Error: Missing product ID or image URL');
    }
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Update product thumbnail_url
        $query = "UPDATE products SET thumbnail_url = :image_url WHERE id = :product_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':product_id', $product_id);
        
        if ($stmt->execute()) {
            echo '<script>alert("✅ Cập nhật ảnh thành công!"); window.location.href="admin_upload_images.php";</script>';
        } else {
            echo '<script>alert("❌ Lỗi cập nhật database!"); window.history.back();</script>';
        }
        
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
} else {
    header('Location: admin_upload_images.php');
}
?>
