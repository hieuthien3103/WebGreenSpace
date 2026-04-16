<?php
/**
 * Product Controller
 */
class ProductController extends Controller {
    private ProductPresenter $productPresenter;

    public function __construct(?Request $request = null, ?ProductPresenter $productPresenter = null) {
        parent::__construct($request);
        $this->productPresenter = $productPresenter ?? new ProductPresenter();
    }

    /**
     * Display product listing page
     */
    public function index(): ViewResponse {
        return $this->catalogResponse(ProductCatalogRequestData::fromRequest($this->request, [
            'limit' => ITEMS_PER_PAGE,
        ]));
    }

    /**
     * Resolve a product from the legacy ?slug= / ?id= query string, then show the detail page.
     * Used by public/product-detail.php.
     */
    public function detailByRequest(): Response {
        $slug = trim((string)$this->request->query('slug', ''));

        if ($slug === '') {
            $productId = max(0, (int)$this->request->query('id', 0));
            if ($productId > 0) {
                $product = (new Product())->getById($productId);
                $slug = (string)($product['slug'] ?? '');
            }
        }

        if ($slug === '') {
            return $this->redirect('products.php');
        }

        return $this->detail($slug);
    }

    /**
     * Display product detail page
     */
    public function detail(string $slug): Response {
        $viewData = $this->productPresenter->presentDetail($slug);
        if ($viewData === null) {
            return $this->notFoundResponse('Sản phẩm bạn đang tìm không còn tồn tại hoặc đã ngừng kinh doanh.');
        }

        return $this->view('storefront/products/detail', $viewData);
    }

    /**
     * Display products by category
     */
    public function category(string $slug): Response {
        $catalogRequest = ProductCatalogRequestData::fromRequest($this->request, [
            'category' => $slug,
            'limit' => ITEMS_PER_PAGE,
            'use_category_path' => true,
        ]);

        $viewData = $this->productPresenter->presentCatalog($catalogRequest);
        if (empty($viewData['category'])) {
            return $this->notFoundResponse('Danh mục bạn đang mở không tồn tại hoặc đã bị ẩn.');
        }

        return $this->catalogResponse($catalogRequest, $viewData);
    }

    /**
     * Render the product list page or AJAX fragment.
     */
    private function catalogResponse(ProductCatalogRequestData $catalogRequest, ?array $viewData = null): ViewResponse {
        $viewData ??= $this->productPresenter->presentCatalog($catalogRequest);
        $view = $this->request->isAjax()
            ? 'storefront/products/content'
            : 'storefront/products/index';

        return $this->view($view, $viewData);
    }
}
