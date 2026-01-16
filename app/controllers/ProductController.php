<?php
/**
 * Product Controller
 */

class ProductController {
    
    /**
     * Display product listing page
     */
    public function index() {
        // Get all products
        $productModel = new Product();
        $products = $productModel->getAll(12);
        
        // Get categories for filter
        $categoryModel = new Category();
        $categories = $categoryModel->getAll();
        
        // Load view
        include APP_PATH . '/views/products/index.php';
    }
    
    /**
     * Display product detail page
     */
    public function detail($slug) {
        // Get product by slug
        $productModel = new Product();
        $product = $productModel->getBySlug($slug);
        
        if (!$product) {
            http_response_code(404);
            echo '<h1>404 - Sản phẩm không tồn tại</h1>';
            return;
        }
        
        // Get product images
        $images = $productModel->getImages($product['id']);
        
        // Get product tags
        $tags = $productModel->getTags($product['id']);
        
        // Get related products (same category)
        $relatedProducts = [];
        if (!empty($product['category_id'])) {
            $relatedProducts = $productModel->getRelatedProducts($product['id'], $product['category_id'], 4);
        }
        
        // Load view
        include APP_PATH . '/views/products/detail.php';
    }
    
    /**
     * Display products by category
     */
    public function category($slug) {
        // Get category by slug
        $categoryModel = new Category();
        $category = $categoryModel->getBySlug($slug);
        
        if (!$category) {
            http_response_code(404);
            echo '<h1>404 - Danh mục không tồn tại</h1>';
            return;
        }
        
        // Get products in this category
        $productModel = new Product();
        $products = $productModel->getByCategory($category['id'], 12);
        
        // Get all categories for filter
        $categories = $categoryModel->getAll();
        
        // Load view
        include APP_PATH . '/views/products/index.php';
    }
}
