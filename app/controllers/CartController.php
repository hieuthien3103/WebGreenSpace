<?php
/**
 * Handle cart pages and AJAX cart mutations.
 */
class CartController extends Controller {
    public function __construct(
        ?Request $request = null,
        private ?StorefrontPageService $pageService = null,
        private ?StorefrontPagePresenter $pagePresenter = null,
    ) {
        parent::__construct($request);
        $this->pageService ??= new StorefrontPageService();
        $this->pagePresenter ??= new StorefrontPagePresenter();
    }

    /**
     * Show the cart page or handle one cart mutation.
     */
    public function index(): Response {
        if ($this->request->method() === 'POST') {
            $isAjaxRequest = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest'
                || (string)($_POST['ajax'] ?? '0') === '1';
            $redirectTarget = safe_redirect_target($_POST['redirect_to'] ?? 'cart.php', 'cart.php');
            $result = $this->pageService->mutateCart($_POST);

            if ($isAjaxRequest) {
                return $this->json([
                    'success' => (bool)($result['success'] ?? false),
                    'message' => (string)($result['message'] ?? ''),
                    'cart_count' => (int)($result['cart_count'] ?? cart_item_count()),
                ], (int)($result['status'] ?? 200));
            }

            set_flash(!empty($result['success']) ? 'success' : 'error', (string)($result['message'] ?? ''));
            if (($this->request->input('action') ?? '') !== 'add') {
                $redirectTarget = 'cart.php';
            }

            return $this->redirect($redirectTarget);
        }

        return $this->template(PUBLIC_PATH . '/cart.php', $this->pagePresenter->presentCart());
    }
}
