<?php
/**
 * Handle storefront authentication pages.
 */
class AuthController extends Controller {
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
     * Show or submit the storefront login form.
     */
    public function login(): Response {
        if (is_logged_in()) {
            return $this->redirect(is_admin() ? 'admin/dashboard.php' : 'home.php');
        }

        $redirectTarget = safe_redirect_target($this->request->query('redirect') ?? $this->request->input('redirect') ?? 'home.php');
        $errors = [];
        $old = ['identifier' => ''];

        if ($this->request->method() === 'POST') {
            $old['identifier'] = trim((string)$this->request->input('identifier', ''));
            $result = $this->pageService->login($_POST, false);

            if (!empty($result['success'])) {
                set_flash('success', 'Đăng nhập thành công. Chào mừng bạn quay lại.');
                return $this->redirect($redirectTarget);
            }

            $errors = $result['errors'] ?? ['general' => 'Không thể đăng nhập lúc này.'];
        }

        return $this->template(PUBLIC_PATH . '/login.php', $this->pagePresenter->presentLogin($errors, $old, $redirectTarget));
    }

    /**
     * Show or submit the storefront signup form.
     */
    public function signup(): Response {
        if (is_logged_in()) {
            return $this->redirect('home.php');
        }

        $redirectTarget = safe_redirect_target($this->request->query('redirect') ?? $this->request->input('redirect') ?? 'home.php');
        $errors = [];
        $old = [];

        if ($this->request->method() === 'POST') {
            $old = [
                'full_name' => trim((string)($this->request->input('full_name', ''))),
                'username' => trim((string)($this->request->input('username', ''))),
                'email' => trim((string)($this->request->input('email', ''))),
                'phone' => trim((string)($this->request->input('phone', ''))),
            ];

            $result = $this->pageService->register($_POST);
            if (!empty($result['success'])) {
                set_flash('success', 'Tạo tài khoản thành công. Bạn đã được đăng nhập.');
                return $this->redirect($redirectTarget);
            }

            $errors = $result['errors'] ?? ['general' => 'Không thể tạo tài khoản lúc này.'];
        }

        return $this->template(PUBLIC_PATH . '/signup.php', $this->pagePresenter->presentSignup($errors, $old, $redirectTarget));
    }

    /**
     * Logout the active user.
     */
    public function logout(): Response {
        $redirectTarget = safe_redirect_target($this->request->input('redirect') ?? $this->request->query('redirect') ?? 'home.php', 'home.php');

        if ($this->request->method() !== 'POST' || !verify_csrf_token($this->request->input('csrf_token'))) {
            set_flash('error', 'Không thể đăng xuất từ yêu cầu này.');
            return $this->redirect($redirectTarget);
        }

        $this->pageService->logout();
        set_flash('success', 'Bạn đã đăng xuất khỏi hệ thống.');
        return $this->redirect($redirectTarget);
    }
}
