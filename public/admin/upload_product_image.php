<?php
// Upload product image from computer
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id_upload'] ?? null;
    
    if (!$product_id) {
        die('Error: Missing product ID');
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
        die('Error: No file uploaded or upload error');
    }
    
    $file = $_FILES['product_image'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    // Get file extension
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    
    if (!in_array($fileExt, $allowed)) {
        die('Error: Invalid file type. Only JPG, PNG, WEBP, GIF allowed.');
    }
    
    // Check file size (max 5MB)
    if ($fileSize > 5000000) {
        die('Error: File too large. Max 5MB allowed.');
    }
    
    try {
        // Create uploads directory if not exists
        $uploadDir = __DIR__ . '/../uploads/products/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate unique filename
        $newFileName = 'product_' . $product_id . '_' . time() . '.' . $fileExt;
        $uploadPath = $uploadDir . $newFileName;
        
        // Move uploaded file
        if (move_uploaded_file($fileTmpName, $uploadPath)) {
            // Update database
            $db = new Database();
            $conn = $db->getConnection();
            
            $image_path = 'products/' . $newFileName;
            
            $query = "UPDATE products SET thumbnail_url = :image_path WHERE id = :product_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':image_path', $image_path);
            $stmt->bindParam(':product_id', $product_id);
            
            if ($stmt->execute()) {
                echo '<script>alert("✅ Upload ảnh thành công!\\n📁 File: ' . $newFileName . '"); window.location.href="admin_upload_images.php";</script>';
            } else {
                unlink($uploadPath); // Delete file if database update fails
                echo '<script>alert("❌ Lỗi cập nhật database!"); window.history.back();</script>';
            }
        } else {
            die('Error: Failed to move uploaded file');
        }
        
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
} else {
    header('Location: admin_upload_images.php');
}
?>
