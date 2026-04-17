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

            $rateCheck = check_login_rate_limit();
            if (!$rateCheck['allowed']) {
                $errors = ['general' => 'Bạn đã thử đăng nhập quá nhiều lần. Vui lòng chờ ' . ($rateCheck['retry_after'] ?? 60) . ' giây.'];
            } elseif (!verify_csrf_token($this->request->input('csrf_token'))) {
                $errors = ['general' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.'];
            } else {
                $result = $this->pageService->login($_POST);
                if (!empty($result['success'])) {
                    clear_login_rate_limit();
                    store_auth_session($result['user']);
                    set_flash('success', 'Đăng nhập admin thành công.');
                    return $this->redirect(admin_path($redirectTarget));
                }

                record_failed_login();
                $errors = $result['errors'] ?? ['general' => 'Không thể đăng nhập admin lúc này.'];
            }
        }

        return $this->template(PUBLIC_PATH . '/admin/login.php', $this->pagePresenter->presentLogin($errors, $old, $redirectTarget));
    }

    /**
     * Log out of the admin session only.
     */
    public function logout(): Response {
        if ($this->request->method() !== 'POST' || !verify_csrf_token($this->request->input('csrf_token'))) {
            set_flash('error', 'Không thể đăng xuất từ yêu cầu này.');
            return $this->redirect('dashboard.php');
        }

        clear_auth_session();
        set_flash('success', 'Đã đăng xuất khỏi khu vực admin.');
        return $this->redirect('login.php');
    }
}
