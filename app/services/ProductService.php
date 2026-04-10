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
     * Build a complete view model for the catalog listing page.
     */
    public function buildCatalogPageData(array $filters = []): array {
        $useCategoryPath = (bool)($filters['use_category_path'] ?? false);
        unset($filters['use_category_path']);

        $result = $this->getProducts($filters);
        $category = $result['category'] ?? null;
        $categoryFilter = trim((string)($filters['category'] ?? ''));
        $search = trim((string)($filters['search'] ?? ''));
        $sort = $result['sort'] ?? $this->validateSort((string)($filters['sort'] ?? self::SORT_NEWEST));
        $page = max(1, (int)($result['page'] ?? ($filters['page'] ?? 1)));
        $limit = max(1, (int)($result['limit'] ?? ($filters['limit'] ?? ITEMS_PER_PAGE)));
        $minPrice = isset($filters['min_price']) && is_numeric($filters['min_price']) ? (float)$filters['min_price'] : null;
        $maxPrice = isset($filters['max_price']) && is_numeric($filters['max_price']) ? (float)$filters['max_price'] : null;
        $totalProducts = max(0, (int)($result['total'] ?? 0));
        $totalPages = max(1, (int)($result['total_pages'] ?? 1));
        $catalogPath = $useCategoryPath && $categoryFilter !== ''
            ? 'category/' . $categoryFilter
            : 'products';

        $queryState = [
            'search' => $search,
            'sort' => $sort,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'page' => $page,
        ];

        if (!$useCategoryPath && $categoryFilter !== '') {
            $queryState['category'] = $categoryFilter;
        }

        $buildPath = static function (string $path, array $params): string {
            if (($params['page'] ?? 1) <= 1) {
                unset($params['page']);
            }

            $params = array_filter($params, static function ($value) {
                return $value !== null && $value !== '';
            });

            $query = http_build_query($params);
            return $path . ($query !== '' ? '?' . $query : '');
        };

        $buildCatalogPath = function (array $overrides = []) use ($buildPath, $catalogPath, $queryState): string {
            return $buildPath($catalogPath, array_replace($queryState, $overrides));
        };

        $buildCatalogUrl = function (array $overrides = []) use ($buildCatalogPath): string {
            return base_url($buildCatalogPath($overrides));
        };

        $buildAllProductsUrl = function (array $overrides = []) use ($buildPath, $queryState): string {
            $params = array_replace($queryState, $overrides);
            unset($params['category']);
            return base_url($buildPath('products', $params));
        };

        $buildCategoryUrl = function (string $slug, array $overrides = []) use ($buildPath, $queryState): string {
            $params = array_replace($queryState, $overrides);
            unset($params['category']);
            return base_url($buildPath('category/' . $slug, $params));
        };

        $currentCatalogPath = $buildCatalogPath();

        return [
            'pageTitle' => $category ? ($category['name'] . ' - GreenSpace') : 'Cửa hàng - GreenSpace',
            'currentPage' => 'products',
            'products' => $result['products'] ?? [],
            'category' => $category,
            'categories' => $this->getCategories(),
            'category_filter' => $categoryFilter,
            'search' => $search,
            'sort' => $sort,
            'page' => $page,
            'limit' => $limit,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'totalProducts' => $totalProducts,
            'totalPages' => $totalPages,
            'productsIndexUrl' => base_url('products'),
            'currentCatalogPath' => $currentCatalogPath,
            'currentCatalogUrl' => base_url($currentCatalogPath),
            'buildCatalogUrl' => $buildCatalogUrl,
            'buildAllProductsUrl' => $buildAllProductsUrl,
            'buildCategoryUrl' => $buildCategoryUrl,
        ];
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
     * Build the view model for the product detail page.
     */
    public function buildProductDetailPageData(string $slug): ?array {
        $product = $this->getProductDetail($slug);
        if ($product === null) {
            return null;
        }

        $this->productModel->incrementViews((int)$product['id']);

        $galleryImages = $product['images'] ?? [];
        if (empty($galleryImages)) {
            $galleryImages = [[
                'image_url' => $product['image_url'] ?? image_url('products/default.jpg'),
                'is_primary' => 1,
            ]];
        }

        $currentPrice = !empty($product['sale_price']) && (float)$product['sale_price'] > 0
            ? (float)$product['sale_price']
            : (float)$product['price'];

        $hasSalePrice = !empty($product['sale_price'])
            && (float)$product['sale_price'] > 0
            && (float)$product['sale_price'] < (float)$product['price'];

        return [
            'pageTitle' => ($product['name'] ?? 'Sản phẩm') . ' - GreenSpace',
            'currentPage' => 'products',
            'product' => $product,
            'galleryImages' => $galleryImages,
            'relatedProducts' => $product['related'] ?? [],
            'currentPrice' => $currentPrice,
            'hasSalePrice' => $hasSalePrice,
        ];
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
