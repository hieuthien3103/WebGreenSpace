<?php
require_once __DIR__ . '/bootstrap.php';
require_admin_permission('uploads.manage', 'update_product_image.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin_upload_images.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
    redirect('admin_upload_images.php');
}

$productId = max(0, (int)($_POST['product_id'] ?? 0));
$imageUrl = trim((string)($_POST['image_url'] ?? ''));

if ($productId <= 0) {
    set_flash('error', 'Sản phẩm không hợp lệ.');
    redirect('admin_upload_images.php');
}

$urlError = validate_image_source_url($imageUrl);
if ($urlError !== null) {
    set_flash('error', $urlError);
    redirect('admin_upload_images.php');
}

$productModel = new Product();
$product = $productModel->getAdminById($productId);
if (!$product) {
    set_flash('error', 'Không tìm thấy sản phẩm cần cập nhật ảnh.');
    redirect('admin_upload_images.php');
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("UPDATE products SET image = :image_url WHERE id = :product_id");
    $stmt->bindValue(':image_url', $imageUrl, PDO::PARAM_STR);
    $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
    $stmt->execute();

    set_flash('success', 'Đã cập nhật URL ảnh cho sản phẩm.');
} catch (Throwable $e) {
    error_log('Admin update_product_image error: ' . $e->getMessage());
    set_flash('error', 'Không thể cập nhật ảnh lúc này.');
}

redirect('admin_upload_images.php');
