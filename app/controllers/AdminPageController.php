<?php
/**
 * Handle admin dashboard and CRUD pages.
 */
class AdminPageController extends Controller {
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
     * Show the admin dashboard page.
     */
    public function dashboard(): Response {
        if ($guard = $this->guardAdmin()) {
            return $guard;
        }

        return $this->template(PUBLIC_PATH . '/admin/dashboard.php', $this->pagePresenter->presentDashboard(), 200, [
            'mvc_template_current_page' => 'dashboard.php',
        ]);
    }

    /**
     * Show and update admin contacts.
     */
    public function contacts(): Response {
        if ($guard = $this->guardPermission('contacts.manage')) {
            return $guard;
        }

        $search = trim((string)$this->request->query('q', ''));
        $statusFilter = (string)$this->request->query('status', 'all');
        $page = max(1, (int)$this->request->query('page', 1));
        $viewId = max(0, (int)$this->request->query('view', 0));

        if ($this->request->method() === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
                set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
                return $this->redirect('contacts.php');
            }

            $result = $this->pageService->updateContactStatus($_POST);
            set_flash(!empty($result['success']) ? 'success' : 'error', (string)$result['message']);
            $redirectView = max(0, (int)$this->request->input('contact_id', 0));
            return $this->redirect('contacts.php' . ($redirectView > 0 ? '?view=' . urlencode((string)$redirectView) . '#contact-detail' : ''));
        }

        $viewData = $this->pagePresenter->presentContacts($search, $statusFilter, $page, $viewId);
        if ($viewId > 0 && empty($viewData['viewMessage'])) {
            set_flash('error', 'Không tìm thấy liên hệ cần xem.');
            return $this->redirect('contacts.php');
        }

        return $this->template(PUBLIC_PATH . '/admin/contacts.php', $viewData, 200, [
            'mvc_template_current_page' => 'contacts.php',
        ]);
    }

    /**
     * Show and update admin orders.
     */
    public function orders(): Response {
        if ($guard = $this->guardPermission('orders.manage')) {
            return $guard;
        }

        if ((string)$this->request->query('ajax', '') === 'pending_count') {
            return $this->json([
                'pending_count' => (new Order())->countAdminOnlineMockOrdersByPaymentStatus('pending_review'),
            ]);
        }

        $search = trim((string)$this->request->query('q', ''));
        $orderStatusFilter = (string)$this->request->query('order_status', 'all');
        $paymentStatusFilter = (string)$this->request->query('payment_status', 'all');
        $page = max(1, (int)$this->request->query('page', 1));
        $viewId = max(0, (int)$this->request->query('view', 0));

        if ($this->request->method() === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
                set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
                return $this->redirect('orders.php');
            }

            $result = $this->pageService->handleOrderAction($_POST);
            set_flash(!empty($result['success']) ? 'success' : 'error', (string)$result['message']);
            $redirectView = max(0, (int)$this->request->input('order_id', 0));
            return $this->redirect('orders.php' . ($redirectView > 0 ? '?view=' . urlencode((string)$redirectView) . '#order-detail' : ''));
        }

        $viewData = $this->pagePresenter->presentOrders($search, $orderStatusFilter, $paymentStatusFilter, $page, $viewId);
        if ($viewId > 0 && empty($viewData['viewOrder'])) {
            set_flash('error', 'Không tìm thấy đơn hàng cần xem chi tiết.');
            return $this->redirect('orders.php');
        }

        return $this->template(PUBLIC_PATH . '/admin/orders.php', $viewData, 200, [
            'mvc_template_current_page' => 'orders.php',
        ]);
    }

    /**
     * Show and update admin categories.
     */
    public function categories(): Response {
        if ($guard = $this->guardPermission('categories.manage')) {
            return $guard;
        }

        $search = trim((string)$this->request->query('q', ''));
        $statusFilter = (string)$this->request->query('status', 'all');
        $page = max(1, (int)$this->request->query('page', 1));
        $editId = max(0, (int)$this->request->query('edit', 0));
        $errors = [];
        $formData = null;
        $formMode = 'create';
        $editingCategory = null;

        if ($this->request->method() === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
                set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
                return $this->redirect('categories.php');
            }

            $result = $this->pageService->handleCategoryAction($_POST);
            if (!empty($result['success'])) {
                set_flash('success', (string)$result['message']);
                $nextEditId = max(0, (int)($result['editId'] ?? 0));
                return $this->redirect($nextEditId > 0 ? 'categories.php?edit=' . urlencode((string)$nextEditId) : 'categories.php');
            }

            if (!empty($result['message']) && empty($result['errors'])) {
                set_flash('error', (string)$result['message']);
                return $this->redirect('categories.php');
            }

            $errors = $result['errors'] ?? [];
            $formData = $result['formData'] ?? null;
            $formMode = (string)($result['formMode'] ?? 'create');
            $editId = (int)($result['editId'] ?? $editId);
        }

        return $this->template(
            PUBLIC_PATH . '/admin/categories.php',
            $this->pagePresenter->presentCategories($search, $statusFilter, $page, $editId, $errors, $formData, $formMode, $editingCategory),
            200,
            ['mvc_template_current_page' => 'categories.php']
        );
    }

    /**
     * Show and update admin products.
     */
    public function products(): Response {
        if ($guard = $this->guardPermission('products.manage')) {
            return $guard;
        }

        $search = trim((string)$this->request->query('q', ''));
        $statusFilter = (string)$this->request->query('status', 'all');
        $page = max(1, (int)$this->request->query('page', 1));
        $editId = max(0, (int)$this->request->query('edit', 0));
        $errors = [];
        $formData = null;
        $formMode = 'create';
        $editingProduct = null;

        if ($this->request->method() === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
                set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
                return $this->redirect('products.php');
            }

            $result = $this->pageService->handleProductAction($_POST, $_FILES);
            if (!empty($result['success'])) {
                set_flash('success', (string)$result['message']);
                $nextEditId = max(0, (int)($result['editId'] ?? 0));
                return $this->redirect($nextEditId > 0 ? 'products.php?edit=' . urlencode((string)$nextEditId) : 'products.php');
            }

            if (!empty($result['message']) && empty($result['errors'])) {
                set_flash('error', (string)$result['message']);
                return $this->redirect('products.php');
            }

            $errors = $result['errors'] ?? [];
            $formData = $result['formData'] ?? null;
            $formMode = (string)($result['formMode'] ?? 'create');
            $editId = (int)($result['editId'] ?? $editId);
        }

        return $this->template(
            PUBLIC_PATH . '/admin/products.php',
            $this->pagePresenter->presentProducts($search, $statusFilter, $page, $editId, $errors, $formData, $formMode, $editingProduct),
            200,
            ['mvc_template_current_page' => 'products.php']
        );
    }

    /**
     * Show and update admin users.
     */
    public function users(): Response {
        if ($guard = $this->guardPermission('users.manage')) {
            return $guard;
        }

        $search = trim((string)$this->request->query('q', ''));
        $roleFilter = (string)$this->request->query('role', 'all');
        $statusFilter = (string)$this->request->query('status', 'all');
        $page = max(1, (int)$this->request->query('page', 1));
        $editId = max(0, (int)$this->request->query('edit', 0));
        $errors = [];
        $formData = null;
        $editingUser = null;

        if ($this->request->method() === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
                set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
                return $this->redirect('users.php');
            }

            $result = $this->pageService->handleUserAction($_POST);
            if (!empty($result['success'])) {
                if (!empty($result['fresh_user'])) {
                    $_SESSION['user_role'] = $result['fresh_user']['role'] ?? 'user';
                    $_SESSION['user_data'] = $result['fresh_user'];
                }
                set_flash('success', (string)$result['message']);
                $nextEditId = max(0, (int)($result['editId'] ?? 0));
                return $this->redirect($nextEditId > 0 ? 'users.php?edit=' . urlencode((string)$nextEditId) : 'users.php');
            }

            if (!empty($result['message']) && empty($result['errors'])) {
                set_flash('error', (string)$result['message']);
                return $this->redirect('users.php');
            }

            $errors = $result['errors'] ?? [];
            $formData = $result['formData'] ?? null;
            $editId = (int)($result['editId'] ?? $editId);
            $editingUser = $result['editingUser'] ?? null;
        }

        return $this->template(
            PUBLIC_PATH . '/admin/users.php',
            $this->pagePresenter->presentUsers($search, $roleFilter, $statusFilter, $page, $editId, $errors, $formData, $editingUser),
            200,
            ['mvc_template_current_page' => 'users.php']
        );
    }

    /**
     * Redirect legacy /admin path to the dashboard.
     */
    public function index(): Response {
        return $this->redirect(admin_path('dashboard.php'));
    }

    /**
     * Guard one admin-only page.
     */
    private function guardAdmin(): ?Response {
        if (!is_logged_in()) {
            set_flash('error', 'Vui lòng đăng nhập bằng tài khoản admin.');
            return $this->redirect('login.php?redirect=' . urlencode('dashboard.php'));
        }

        if (!is_admin()) {
            set_flash('error', 'Bạn không có quyền truy cập khu vực admin.');
            return $this->redirect('../home.php');
        }

        return null;
    }

    /**
     * Guard one permission-specific admin page.
     */
    private function guardPermission(string $permission): ?Response {
        $adminGuard = $this->guardAdmin();
        if ($adminGuard instanceof Response) {
            return $adminGuard;
        }

        if (!admin_has_permission($permission)) {
            set_flash('error', 'Bạn không có quyền truy cập khu vực admin.');
            return $this->redirect('dashboard.php');
        }

        return null;
    }
}
