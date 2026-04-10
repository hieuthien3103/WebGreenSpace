<?php
/**
 * Handle admin authentication pages.
 */
class AdminAuthController extends Controller {
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
     * Show or submit the admin login page.
     */
    public function login(): Response {
        if (is_logged_in() && is_admin()) {
            return $this->redirect(admin_path('dashboard.php'));
        }

        $redirectTarget = safe_redirect_target($this->request->query('redirect') ?? $this->request->input('redirect') ?? 'dashboard.php', 'dashboard.php');
        $redirectTarget = str_starts_with($redirectTarget, 'admin/') ? substr($redirectTarget, 6) : ltrim($redirectTarget, '/');
        $redirectTarget = $redirectTarget !== '' ? $redirectTarget : 'dashboard.php';

        $errors = [];
        $old = ['identifier' => ''];
        if ($this->request->method() === 'POST') {
            $old['identifier'] = trim((string)$this->request->input('identifier', ''));
            $result = $this->pageService->login($_POST);
            if (!empty($result['success'])) {
                set_flash('success', 'Đăng nhập admin thành công.');
                return $this->redirect(admin_path($redirectTarget));
            }

            $errors = $result['errors'] ?? ['general' => 'Không thể đăng nhập admin lúc này.'];
        }

        return $this->template(PUBLIC_PATH . '/admin/login.php', $this->pagePresenter->presentLogin($errors, $old, $redirectTarget));
    }
}
