<?php
/**
 * Product Controller
 */
class ProductController extends Controller {
    private ProductService $productService;

    public function __construct(?Request $request = null, ?ProductService $productService = null) {
        parent::__construct($request);
        $this->productService = $productService ?? new ProductService();
    }

    /**
     * Display product listing page
     */
    public function index(): void {
        $this->renderListing($this->productService->buildCatalogPageData($this->buildListingFilters()));
    }

    /**
     * Display product detail page
     */
    public function detail(string $slug): void {
        $viewData = $this->productService->buildProductDetailPageData($slug);
        if ($viewData === null) {
            $this->notFound('Sản phẩm bạn đang tìm không còn tồn tại hoặc đã ngừng kinh doanh.');
            return;
        }

        $this->render('storefront/products/detail', $viewData);
    }

    /**
     * Display products by category
     */
    public function category(string $slug): void {
        $filters = $this->buildListingFilters([
            'category' => $slug,
            'use_category_path' => true,
        ]);

        $viewData = $this->productService->buildCatalogPageData($filters);
        if (empty($viewData['category'])) {
            $this->notFound('Danh mục bạn đang mở không tồn tại hoặc đã bị ẩn.');
            return;
        }

        $this->renderListing($viewData);
    }

    /**
     * Render the product list page or AJAX fragment.
     */
    private function renderListing(array $viewData): void {
        if ($this->request->isAjax()) {
            $this->render('storefront/products/content', $viewData);
            return;
        }

        $this->render('storefront/products/index', $viewData);
    }

    /**
     * Collect and normalize storefront listing filters.
     */
    private function buildListingFilters(array $overrides = []): array {
        $filters = [
            'category' => $this->request->string('category'),
            'search' => $this->request->string('search'),
            'sort' => $this->request->string('sort', ProductService::SORT_NEWEST),
            'min_price' => $this->request->float('min_price'),
            'max_price' => $this->request->float('max_price'),
            'page' => max(1, $this->request->integer('page', 1)),
            'limit' => ITEMS_PER_PAGE,
            'use_category_path' => false,
        ];

        return array_replace($filters, $overrides);
    }
}
