<?php
/**
 * Handle admin utility pages and endpoints.
 */
class AdminToolController extends Controller {
    public function __construct(
        ?Request $request = null,
        private ?AdminPageService $pageService = null,
        private ?AdminPagePresenter $pagePresenter = null,
    ) {
        parent::__construct($request);
        $this->pageService ??= new AdminPageService();
        $this->pagePresenter ??= new AdminPagePresenter();
    }

    /**
     * Redirect the old admin upload image helper to the main products page.
     */
    public function adminUploadImages(): Response {
        return $this->redirect('products.php');
    }

    /**
     * Show the image audit page.
     */
    public function checkImages(): Response {
        if ($guard = $this->guardProductTools()) {
            return $guard;
        }

        return $this->template(PUBLIC_PATH . '/admin/check_images.php', $this->pagePresenter->presentCheckImages(), 200, [
            'mvc_template_current_page' => 'products.php',
        ]);
    }

    /**
     * Show confirmation form for clearing caches.
     */
    public function clearCacheForm(): Response {
        if ($guard = $this->guardProductTools()) {
            return $guard;
        }

        return $this->template(PUBLIC_PATH . '/admin/clear_cache.php', ['results' => null], 200, [
            'mvc_template_current_page' => 'products.php',
        ]);
    }

    /**
     * Clear caches (POST + CSRF) and show the result page.
     */
    public function clearCache(): Response {
        if ($guard = $this->guardProductTools()) {
            return $guard;
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            return $this->redirect('products.php');
        }

        return $this->template(PUBLIC_PATH . '/admin/clear_cache.php', $this->pagePresenter->presentClearCache($this->pageService->clearCaches()), 200, [
            'mvc_template_current_page' => 'products.php',
        ]);
    }

    /**
     * Show confirmation form for creating placeholder images.
     */
    public function createPlaceholderForm(): Response {
        if ($guard = $this->guardProductTools()) {
            return $guard;
        }

        return $this->template(PUBLIC_PATH . '/admin/create_placeholder.php', ['results' => null], 200, [
            'mvc_template_current_page' => 'products.php',
        ]);
    }

    /**
     * Create placeholder images (POST + CSRF) and show the result page.
     */
    public function createPlaceholder(): Response {
        if ($guard = $this->guardProductTools()) {
            return $guard;
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            return $this->redirect('products.php');
        }

        return $this->template(PUBLIC_PATH . '/admin/create_placeholder.php', [
            'results' => $this->pageService->createPlaceholderImages(),
        ], 200, ['mvc_template_current_page' => 'products.php']);
    }

    /**
     * Show confirmation form for normalizing image paths.
     */
    public function fixImagesForm(): Response {
        if ($guard = $this->guardProductTools()) {
            return $guard;
        }

        return $this->template(PUBLIC_PATH . '/admin/fix_images.php', ['results' => null], 200, [
            'mvc_template_current_page' => 'products.php',
        ]);
    }

    /**
     * Normalize image paths (POST + CSRF) and show the result page.
     */
    public function fixImages(): Response {
        if ($guard = $this->guardProductTools()) {
            return $guard;
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            return $this->redirect('products.php');
        }

        return $this->template(PUBLIC_PATH . '/admin/fix_images.php', [
            'results' => $this->pageService->fixImagePaths(),
        ], 200, ['mvc_template_current_page' => 'products.php']);
    }

    /**
     * Return the active product list as JSON.
     */
    public function getProducts(): Response {
        if ($guard = $this->guardProductTools()) {
            return $guard;
        }

        return $this->json($this->pagePresenter->presentAdminProductsJson());
    }

    /**
     * Update one product image by URL and redirect back.
     */
    public function updateProductImage(): Response {
        if ($guard = $this->guardProductTools()) {
            return $guard;
        }

        if ($this->request->method() !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? null)) {
            set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            return $this->redirect('products.php');
        }

        $result = $this->pageService->updateProductImage($_POST);
        set_flash(!empty($result['success']) ? 'success' : 'error', (string)$result['message']);
        $productId = max(0, (int)$this->request->input('product_id', 0));
        return $this->redirect('products.php' . ($productId > 0 ? '?edit=' . urlencode((string)$productId) : ''));
    }

    /**
     * Upload one product image and redirect back.
     */
    public function uploadProductImage(): Response {
        if ($guard = $this->guardProductTools()) {
            return $guard;
        }

        if ($this->request->method() !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? null)) {
            set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            return $this->redirect('products.php');
        }

        $result = $this->pageService->uploadProductImage($_POST, $_FILES);
        set_flash(!empty($result['success']) ? 'success' : 'error', (string)$result['message']);
        $productId = max(0, (int)$this->request->input('product_id_upload', 0));
        return $this->redirect('products.php' . ($productId > 0 ? '?edit=' . urlencode((string)$productId) : ''));
    }

    /**
     * Guard product utility pages.
     */
    private function guardProductTools(): ?Response {
        if (!is_logged_in()) {
            set_flash('error', 'Vui lòng đăng nhập bằng tài khoản admin.');
            return $this->redirect('login.php?redirect=' . urlencode('dashboard.php'));
        }

        if (!is_admin() || !admin_has_permission('products.manage')) {
            set_flash('error', 'Bạn không có quyền truy cập khu vực admin.');
            return $this->redirect('dashboard.php');
        }

        return null;
    }
}
