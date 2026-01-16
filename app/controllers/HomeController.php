<?php
/**
 * Home Controller
 */

class HomeController {
    
    /**
     * Display homepage
     */
    public function index() {
        // Get categories for display
        $categoryModel = new Category();
        $categories = $categoryModel->getTop(5);
        
        // Get best selling products
        $productModel = new Product();
        $bestSellers = $productModel->getBestSellers(8);
        
        // Load view
        include APP_PATH . '/views/home/index.php';
    }
}
