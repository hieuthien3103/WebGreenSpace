<?php
/**
 * Product Service - Business Logic Layer
 * Handles product-related business operations
 */

class ProductService {
    private Product $productModel;
    private Category $categoryModel;
    
    // Sorting constants
    public const SORT_NEWEST = 'newest';
    public const SORT_PRICE_ASC = 'price_asc';
    public const SORT_PRICE_DESC = 'price_desc';
    public const SORT_BESTSELLER = 'bestseller';
    
    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }
    
    /**
     * Get products with filters
     * 
     * @param array $filters Filter parameters
     * @return array Products and metadata
     */
    public function getProducts(array $filters = []): array {
        $category = $filters['category'] ?? '';
        $search = $filters['search'] ?? '';
        $sort = $filters['sort'] ?? self::SORT_NEWEST;
        $page = max(1, (int)($filters['page'] ?? 1));
        $limit = (int)($filters['limit'] ?? 12);
        $offset = ($page - 1) * $limit;
        
        $categoryData = null;
        $products = [];
        
        try {
            // Get products based on filters
            if (!empty($search)) {
                $products = $this->productModel->search($search, $limit, $offset);
            } elseif (!empty($category)) {
                $categoryData = $this->categoryModel->getBySlug($category);
                if ($categoryData) {
                    $products = $this->productModel->getByCategory($categoryData['id'], $limit, $offset);
                }
            } else {
                $products = $this->productModel->getAll($limit, $offset);
            }
            
            // Apply sorting
            $products = $this->applySorting($products, $sort, $limit);
            
        } catch (Exception $e) {
            error_log("ProductService Error: " . $e->getMessage());
            $products = [];
        }
        
        return [
            'products' => $products,
            'category' => $categoryData,
            'total' => count($products)
        ];
    }
    
    /**
     * Apply sorting to products
     * 
     * @param array $products Products to sort
     * @param string $sort Sort type
     * @param int $limit Limit for bestsellers
     * @return array Sorted products
     */
    private function applySorting(array $products, string $sort, int $limit): array {
        if (empty($products)) {
            return [];
        }
        
        switch ($sort) {
            case self::SORT_PRICE_ASC:
                usort($products, fn($a, $b) => $this->getEffectivePrice($a) <=> $this->getEffectivePrice($b));
                break;
                
            case self::SORT_PRICE_DESC:
                usort($products, fn($a, $b) => $this->getEffectivePrice($b) <=> $this->getEffectivePrice($a));
                break;
                
            case self::SORT_BESTSELLER:
                return $this->productModel->getBestSellers($limit);
                
            case self::SORT_NEWEST:
            default:
                // Already sorted by newest from query
                break;
        }
        
        return $products;
    }
    
    /**
     * Get effective price (sale price if available, otherwise regular price)
     * 
     * @param array $product Product data
     * @return float Effective price
     */
    private function getEffectivePrice(array $product): float {
        return !empty($product['sale_price']) && $product['sale_price'] > 0 
            ? (float)$product['sale_price'] 
            : (float)$product['price'];
    }
    
    /**
     * Get all categories
     * 
     * @return array Categories
     */
    public function getCategories(): array {
        try {
            return $this->categoryModel->getAll();
        } catch (Exception $e) {
            error_log("ProductService Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get product detail by slug
     * 
     * @param string $slug Product slug
     * @return array|null Product data with images and tags
     */
    public function getProductDetail(string $slug): ?array {
        try {
            $product = $this->productModel->getBySlug($slug);
            
            if (!$product) {
                return null;
            }
            
            // Enrich product data
            $product['images'] = $this->productModel->getImages($product['id']);
            $product['tags'] = $this->productModel->getTags($product['id']);
            
            // Get related products
            if (!empty($product['category_id'])) {
                $product['related'] = $this->productModel->getRelatedProducts(
                    $product['id'], 
                    $product['category_id'], 
                    4
                );
            }
            
            return $product;
            
        } catch (Exception $e) {
            error_log("ProductService Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Validate sort parameter
     * 
     * @param string $sort Sort parameter
     * @return string Valid sort parameter
     */
    public function validateSort(string $sort): string {
        $validSorts = [
            self::SORT_NEWEST,
            self::SORT_PRICE_ASC,
            self::SORT_PRICE_DESC,
            self::SORT_BESTSELLER
        ];
        
        return in_array($sort, $validSorts) ? $sort : self::SORT_NEWEST;
    }
}
