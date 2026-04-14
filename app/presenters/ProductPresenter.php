<?php
/**
 * Prepare storefront product pages from domain services.
 */
class ProductPresenter {
    private ProductService $productService;

    public function __construct(?ProductService $productService = null) {
        $this->productService = $productService ?? new ProductService();
    }

    /**
     * Build the catalog page view model.
     */
    public function presentCatalog(ProductCatalogRequestData $catalogRequest): array {
        $result = $this->productService->getProducts($catalogRequest->toServiceFilters());
        $category = $result['category'] ?? null;
        $categoryFilter = $catalogRequest->category();
        $search = $catalogRequest->search();
        $sort = $result['sort'] ?? $this->productService->validateSort($catalogRequest->sort());
        $page = max(1, (int)($result['page'] ?? $catalogRequest->page()));
        $limit = max(1, (int)($result['limit'] ?? $catalogRequest->limit()));
        $minPrice = $catalogRequest->minPrice();
        $maxPrice = $catalogRequest->maxPrice();
        $totalProducts = max(0, (int)($result['total'] ?? 0));
        $totalPages = max(1, (int)($result['total_pages'] ?? 1));
        $catalogPath = $catalogRequest->usesCategoryPath() && $categoryFilter !== ''
            ? 'category/' . $categoryFilter
            : 'products';

        $queryState = [
            'search' => $search,
            'sort' => $sort,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'page' => $page,
        ];

        if (!$catalogRequest->usesCategoryPath() && $categoryFilter !== '') {
            $queryState['category'] = $categoryFilter;
        }

        $buildCatalogPath = function (array $overrides = []) use ($catalogPath, $queryState): string {
            return $this->buildPath($catalogPath, array_replace($queryState, $overrides));
        };

        $buildCatalogUrl = function (array $overrides = []) use ($buildCatalogPath): string {
            return base_url($buildCatalogPath($overrides));
        };

        $buildAllProductsUrl = function (array $overrides = []) use ($queryState): string {
            $params = array_replace($queryState, $overrides);
            unset($params['category']);
            return base_url($this->buildPath('products', $params));
        };

        $buildCategoryUrl = function (string $slug, array $overrides = []) use ($queryState): string {
            $params = array_replace($queryState, $overrides);
            unset($params['category']);
            return base_url($this->buildPath('category/' . $slug, $params));
        };

        $currentCatalogPath = $buildCatalogPath();

        return [
            'pageTitle' => $category ? ($category['name'] . ' - GreenSpace') : 'Cửa hàng - GreenSpace',
            'currentPage' => 'products',
            'products' => $result['products'] ?? [],
            'category' => $category,
            'categories' => $this->productService->getCategories(),
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
     * Build the product detail page view model.
     */
    public function presentDetail(string $slug): ?array {
        $product = $this->productService->getProductDetail($slug);
        if ($product === null) {
            return null;
        }

        $this->productService->incrementViews((int)$product['id']);

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
     * Normalize pagination and filter params back to a path.
     */
    private function buildPath(string $path, array $params): string {
        if (($params['page'] ?? 1) <= 1) {
            unset($params['page']);
        }

        $params = array_filter($params, static function ($value) {
            return $value !== null && $value !== '';
        });

        $query = http_build_query($params);
        return $path . ($query !== '' ? '?' . $query : '');
    }
}
