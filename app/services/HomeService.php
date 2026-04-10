<?php
/**
 * Home page service for storefront data composition.
 */
class HomeService {
    private Product $productModel;
    private Category $categoryModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }

    /**
     * Build the home page view model.
     */
    public function getHomepageData(): array {
        try {
            return [
                'featuredProducts' => $this->productModel->getFeatured(8),
                'bestSellers' => $this->productModel->getBestSellers(8),
                'categories' => $this->categoryModel->getAll(),
            ];
        } catch (Exception $e) {
            error_log("HomeService Error: " . $e->getMessage());

            return [
                'featuredProducts' => [],
                'bestSellers' => [],
                'categories' => [],
            ];
        }
    }
}
