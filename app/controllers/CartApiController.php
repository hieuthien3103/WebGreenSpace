<?php
/**
 * Handle AJAX/API cart requests.
 * Replaces the legacy procedural public/cart_api.php.
 */
class CartApiController extends Controller {
    public function __construct(
        ?Request $request = null,
        private ?StorefrontPageService $pageService = null,
    ) {
        parent::__construct($request);
        $this->pageService ??= new StorefrontPageService();
    }

    /**
     * Handle all cart API requests.
     * GET  → return current cart state.
     * POST → mutate cart (action: add | update | remove | clear).
     */
    public function handle(): Response {
        if ($this->request->method() === 'GET') {
            return $this->json([
                'success' => true,
                'cart_count' => cart_item_count(),
            ]);
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            return $this->json([
                'success' => false,
                'message' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.',
                'cart_count' => cart_item_count(),
            ], 419);
        }

        $result = $this->pageService->mutateCart($_POST);

        return $this->json([
            'success' => (bool)($result['success'] ?? false),
            'message' => (string)($result['message'] ?? ''),
            'cart_count' => (int)($result['cart_count'] ?? cart_item_count()),
        ], (int)($result['status'] ?? 200));
    }
}
