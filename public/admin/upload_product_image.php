<?php
require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin_upload_images.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
    redirect('admin_upload_images.php');
}

$productId = max(0, (int)($_POST['product_id_upload'] ?? 0));
if ($productId <= 0) {
    set_flash('error', 'Sản phẩm không hợp lệ.');
    redirect('admin_upload_images.php');
}

$productModel = new Product();
$product = $productModel->getAdminById($productId);
if (!$product) {
    set_flash('error', 'Không tìm thấy sản phẩm cần upload ảnh.');
    redirect('admin_upload_images.php');
}

if (!isset($_FILES['product_image'])) {
    set_flash('error', 'Vui lòng chọn một ảnh để tải lên.');
    redirect('admin_upload_images.php');
}

$validation = validate_uploaded_image($_FILES['product_image']);
if (empty($validation['valid'])) {
    set_flash('error', (string)($validation['error'] ?? 'Ảnh tải lên không hợp lệ.'));
    redirect('admin_upload_images.php');
}

$uploadDir = __DIR__ . '/../../uploads/products/';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
    set_flash('error', 'Không thể tạo thư mục lưu ảnh.');
    redirect('admin_upload_images.php');
}

$extension = (string)$validation['extension'];
$newFileName = 'product_' . $productId . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
$uploadPath = $uploadDir . $newFileName;
$imagePath = 'products/' . $newFileName;

try {
    if (!move_uploaded_file((string)$_FILES['product_image']['tmp_name'], $uploadPath)) {
        set_flash('error', 'Không thể lưu file ảnh đã tải lên.');
        redirect('admin_upload_images.php');
    }

    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare("UPDATE products SET image = :image_path WHERE id = :product_id");
    $stmt->bindValue(':image_path', $imagePath, PDO::PARAM_STR);
    $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
    $stmt->execute();

    set_flash('success', 'Đã upload ảnh và cập nhật sản phẩm thành công.');
} catch (Throwable $e) {
    if (is_file($uploadPath)) {
        @unlink($uploadPath);
    }

    error_log('Admin upload_product_image error: ' . $e->getMessage());
    set_flash('error', 'Không thể upload ảnh lúc này.');
}

redirect('admin_upload_images.php');
