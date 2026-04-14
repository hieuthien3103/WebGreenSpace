<?php
require_once __DIR__ . '/../config/config.php';

$slug = trim((string)($_GET['slug'] ?? ''));
if ($slug === '') {
    $productId = max(0, (int)($_GET['id'] ?? 0));
    if ($productId > 0) {
        $product = (new Product())->getById($productId);
        $slug = (string)($product['slug'] ?? '');
    }
}

if ($slug === '') {
    redirect('products.php');
}

(new ProductController())->detail($slug)->send();
