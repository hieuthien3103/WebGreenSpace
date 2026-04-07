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
        $sort = $this->validateSort($filters['sort'] ?? self::SORT_NEWEST);
        $page = max(1, (int)($filters['page'] ?? 1));
        $limit = max(1, (int)($filters['limit'] ?? 12));
        $offset = ($page - 1) * $limit;
        $minPrice = $filters['min_price'] ?? null;
        $maxPrice = $filters['max_price'] ?? null;
        
        $categoryData = null;
        $products = [];
        $total = 0;
        
        try {
            $filterParams = [];

            if (!empty($search)) {
                $filterParams['search'] = $search;
            }

            if (!empty($category)) {
                $categoryData = $this->categoryModel->getBySlug($category);
                if ($categoryData) {
                    $filterParams['category_id'] = $categoryData['id'];
                }
            }

            if ($minPrice !== null && is_numeric($minPrice)) {
                $filterParams['min_price'] = (float)$minPrice;
            }

            if ($maxPrice !== null && is_numeric($maxPrice)) {
                $filterParams['max_price'] = (float)$maxPrice;
            }

            $hasFilters = !empty($filterParams);
            $total = $hasFilters
                ? $this->productModel->getFilteredTotal($filterParams)
                : $this->productModel->getTotal();

            $totalPages = max(1, (int)ceil($total / $limit));
            if ($total > 0 && $page > $totalPages) {
                $page = $totalPages;
                $offset = ($page - 1) * $limit;
            }

            if ($sort === self::SORT_BESTSELLER) {
                $products = $this->productModel->getBestSellers($limit, $offset, $filterParams);
            } else {
                $products = $this->productModel->getFilteredProducts($filterParams, $limit, $offset, $sort);
            }
        } catch (Exception $e) {
            error_log("ProductService Error: " . $e->getMessage());
            $products = [];
            $total = 0;
        }

        $totalPages = max(1, (int)ceil($total / $limit));
        
        return [
            'products' => $products,
            'category' => $categoryData,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => $totalPages,
            'sort' => $sort,
        ];
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
