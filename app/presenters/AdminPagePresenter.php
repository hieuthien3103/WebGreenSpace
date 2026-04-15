<?php
/**
 * Build view data for legacy admin pages while migrating to MVC.
 */
class AdminPagePresenter {
    private AdminDashboardService $dashboardService;
    private ContactMessage $contactModel;
    private Order $orderModel;
    private Product $productModel;
    private Category $categoryModel;
    private User $userModel;

    public function __construct(
        ?AdminDashboardService $dashboardService = null,
        ?ContactMessage $contactModel = null,
        ?Order $orderModel = null,
        ?Product $productModel = null,
        ?Category $categoryModel = null,
        ?User $userModel = null,
    ) {
        $this->dashboardService = $dashboardService ?? new AdminDashboardService();
        $this->contactModel = $contactModel ?? new ContactMessage();
        $this->orderModel = $orderModel ?? new Order();
        $this->productModel = $productModel ?? new Product();
        $this->categoryModel = $categoryModel ?? new Category();
        $this->userModel = $userModel ?? new User();
    }

    /**
     * Build the admin login page state.
     */
    public function presentLogin(array $errors = [], array $old = ['identifier' => ''], string $redirectTarget = 'dashboard.php'): array {
        return [
            'pageTitle' => 'Đăng nhập admin - GreenSpace',
            'errors' => $errors,
            'old' => $old + ['identifier' => ''],
            'redirectTarget' => $redirectTarget,
            'switchingAccount' => is_logged_in() && !is_admin(),
            'flash' => get_flash(),
        ];
    }

    /**
     * Build the admin dashboard page state.
     */
    public function presentDashboard(): array {
        $dashboard = $this->dashboardService->getDashboardData();

        return [
            'dashboard' => $dashboard,
            'stats' => $dashboard['stats'],
            'commerceSummary' => $dashboard['commerce_summary'] ?? [],
            'categorySummary' => $dashboard['category_summary'],
            'recentOrders' => $dashboard['recent_orders'],
            'recentUsers' => $dashboard['recent_users'],
            'topCustomers' => $dashboard['top_customers'] ?? [],
            'topCategories' => $dashboard['top_categories'],
            'topProducts' => $dashboard['top_products'],
            'lowStockProducts' => $dashboard['low_stock_products'],
        ];
    }

    /**
     * Build the admin contacts page state.
     */
    public function presentContacts(string $search, string $statusFilter, int $page, int $viewId): array {
        $statusOptions = [
            'all' => 'Tất cả trạng thái',
            'new' => 'Mới',
            'in_progress' => 'Đang xử lý',
            'resolved' => 'Đã xử lý',
        ];

        if (!isset($statusOptions[$statusFilter])) {
            $statusFilter = 'all';
        }

        $perPage = 12;
        $totalMessages = $this->contactModel->getAdminTotal($search, $statusFilter);
        $totalPages = max(1, (int)ceil($totalMessages / $perPage));
        $page = min(max(1, $page), $totalPages);
        $offset = ($page - 1) * $perPage;
        $messages = $this->contactModel->getAdminList($search, $statusFilter, $perPage, $offset);

        $viewMessage = null;
        if ($viewId > 0) {
            $viewMessage = $this->contactModel->findById($viewId);
            if ($viewMessage && empty($viewMessage['is_read'])) {
                $this->contactModel->markAsRead($viewId);
                $viewMessage['is_read'] = 1;
            }
        }

        return [
            'statusOptions' => $statusOptions,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'page' => $page,
            'perPage' => $perPage,
            'stats' => $this->contactModel->getStats(),
            'totalMessages' => $totalMessages,
            'totalPages' => $totalPages,
            'messages' => $messages,
            'viewId' => $viewId,
            'viewMessage' => $viewMessage,
            'currentState' => [
                'q' => $search,
                'status' => $statusFilter,
                'page' => $page,
                'view' => $viewId,
            ],
        ];
    }

    /**
     * Build the admin orders page state.
     */
    public function presentOrders(string $search, string $orderStatusFilter, string $paymentStatusFilter, int $page, int $viewId): array {
        $orderStatusOptions = [
            'all' => 'Tất cả trạng thái đơn',
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'processing' => 'Đang chuẩn bị',
            'shipping' => 'Đang giao',
            'delivered' => 'Đã giao',
            'cancelled' => 'Đã hủy',
        ];
        $paymentStatusOptions = [
            'all' => 'Tất cả thanh toán',
            'unpaid' => 'Chưa thanh toán',
            'pending_review' => 'Chờ duyệt',
            'paid' => 'Đã thanh toán',
            'failed' => 'Thanh toán lỗi',
        ];

        if (!isset($orderStatusOptions[$orderStatusFilter])) {
            $orderStatusFilter = 'all';
        }
        if (!isset($paymentStatusOptions[$paymentStatusFilter])) {
            $paymentStatusFilter = 'all';
        }

        $perPage = 12;
        $totalOrders = $this->orderModel->getAdminTotal($search, $orderStatusFilter, $paymentStatusFilter);
        $totalPages = max(1, (int)ceil($totalOrders / $perPage));
        $page = min(max(1, $page), $totalPages);
        $offset = ($page - 1) * $perPage;
        $commerceInsights = $this->dashboardService->getCommerceInsights();

        return [
            'search' => $search,
            'orderStatusFilter' => $orderStatusFilter,
            'paymentStatusFilter' => $paymentStatusFilter,
            'page' => $page,
            'perPage' => $perPage,
            'viewId' => $viewId,
            'orderStatusOptions' => $orderStatusOptions,
            'paymentStatusOptions' => $paymentStatusOptions,
            'stats' => $this->orderModel->getAdminStats(),
            'totalOrders' => $totalOrders,
            'totalPages' => $totalPages,
            'orders' => $this->orderModel->getAdminList($search, $orderStatusFilter, $paymentStatusFilter, $perPage, $offset),
            'viewOrder' => $viewId > 0 ? $this->orderModel->getAdminDetailById($viewId) : null,
            'commerceInsights' => $commerceInsights,
            'commerceSummary' => $commerceInsights['commerce_summary'] ?? [],
            'topCustomers' => $commerceInsights['top_customers'] ?? [],
            'topProducts' => $commerceInsights['top_products'] ?? [],
            'commerceTrends' => $this->dashboardService->getCommerceTrendData(),
            'currentState' => [
                'q' => $search,
                'order_status' => $orderStatusFilter,
                'payment_status' => $paymentStatusFilter,
                'page' => $page,
                'view' => $viewId,
            ],
        ];
    }

    /**
     * Build the admin categories page state.
     */
    public function presentCategories(
        string $search,
        string $statusFilter,
        int $page,
        int $editId,
        array $errors = [],
        ?array $formData = null,
        string $formMode = 'create',
        ?array $editingCategory = null,
    ): array {
        $statusOptions = [
            'all' => 'Tất cả',
            'active' => 'Đang hiển thị',
            'inactive' => 'Đang ẩn',
        ];
        if (!isset($statusOptions[$statusFilter])) {
            $statusFilter = 'all';
        }

        $formData ??= [
            'name' => '',
            'slug' => '',
            'description' => '',
            'image' => '',
            'parent_id' => '',
            'status' => 'active',
        ];

        if ($formMode !== 'edit' && $editId > 0 && $editingCategory === null) {
            $editingCategory = $this->categoryModel->getAdminById($editId);
            if ($editingCategory) {
                $formMode = 'edit';
                $formData = [
                    'name' => (string)($editingCategory['name'] ?? ''),
                    'slug' => (string)($editingCategory['slug'] ?? ''),
                    'description' => (string)($editingCategory['description'] ?? ''),
                    'image' => (string)($editingCategory['image'] ?? ''),
                    'parent_id' => isset($editingCategory['parent_id']) && $editingCategory['parent_id'] !== null ? (string)$editingCategory['parent_id'] : '',
                    'status' => (string)($editingCategory['status'] ?? 'active'),
                ];
            }
        }

        $perPage = ADMIN_ITEMS_PER_PAGE;
        $totalCategories = $this->categoryModel->getAdminTotal($search, $statusFilter);
        $totalPages = max(1, (int)ceil($totalCategories / $perPage));
        $page = min(max(1, $page), $totalPages);
        $offset = ($page - 1) * $perPage;

        $currentCategoryImagePreview = ($editingCategory['image_url'] ?? null)
            ?: (!empty($formData['image']) && preg_match('#^https?://#i', $formData['image'])
                ? $formData['image']
                : (!empty($formData['image']) ? upload_url($formData['image']) : image_url('categories/default.jpg')));

        return [
            'statusOptions' => $statusOptions,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'page' => $page,
            'editId' => $editId,
            'errors' => $errors,
            'formMode' => $formMode,
            'formData' => $formData,
            'editingCategory' => $editingCategory,
            'parentOptions' => $this->categoryModel->getAdminParentOptions($formMode === 'edit' ? $editId : null),
            'stats' => [
                'total' => $this->categoryModel->getAdminTotal('', 'all'),
                'active' => $this->categoryModel->getAdminTotal('', 'active'),
                'inactive' => $this->categoryModel->getAdminTotal('', 'inactive'),
            ],
            'totalCategories' => $totalCategories,
            'totalPages' => $totalPages,
            'categories' => $this->categoryModel->getAdminList($search, $statusFilter, $perPage, $offset),
            'allParentOptions' => $this->categoryModel->getAdminParentOptions(null),
            'categoryListStateUrl' => 'categories.php' . $this->buildQuery([
                'q' => $search,
                'status' => $statusFilter,
                'page' => $page,
            ]),
            'defaultCategoryFormState' => [
                'name' => '',
                'slug' => '',
                'description' => '',
                'image' => '',
                'parent_id' => '',
                'status' => 'active',
                'id' => '',
                'image_url' => image_url('categories/default.jpg'),
                'site_url' => '',
            ],
            'initialCategoryFormState' => [
                'id' => $formMode === 'edit' ? (string)$editId : '',
                'name' => $formData['name'],
                'slug' => $formData['slug'],
                'description' => $formData['description'],
                'image' => $formData['image'],
                'parent_id' => $formData['parent_id'],
                'status' => $formData['status'],
                'image_url' => $currentCategoryImagePreview,
                'site_url' => $formMode === 'edit' && $formData['slug'] !== '' ? '../products.php?category=' . rawurlencode($formData['slug']) : '',
            ],
            'showCategoryForm' => $formMode === 'edit' || !empty($errors),
        ];
    }

    /**
     * Build the admin products page state.
     */
    public function presentProducts(
        string $search,
        string $statusFilter,
        int $page,
        int $editId,
        array $errors = [],
        ?array $formData = null,
        string $formMode = 'create',
        ?array $editingProduct = null,
    ): array {
        $statusOptions = [
            'all' => 'Tất cả',
            'active' => 'Đang bán',
            'inactive' => 'Ngừng bán',
        ];
        if (!isset($statusOptions[$statusFilter])) {
            $statusFilter = 'all';
        }

        $categories = $this->categoryModel->getAll();
        $careOptions = ['easy' => 'Dễ', 'medium' => 'Trung bình', 'hard' => 'Khó'];
        $requirementOptions = ['low' => 'Thấp', 'medium' => 'Vừa', 'high' => 'Cao'];
        $formData ??= [
            'category_id' => '',
            'name' => '',
            'slug' => '',
            'description' => '',
            'price' => '',
            'sale_price' => '',
            'stock' => '0',
            'image' => '',
            'size' => '',
            'care_level' => 'medium',
            'light_requirement' => 'medium',
            'water_requirement' => 'medium',
            'featured' => '0',
            'status' => 'active',
        ];

        if ($formMode !== 'edit' && $editId > 0 && $editingProduct === null) {
            $editingProduct = $this->productModel->getAdminById($editId);
            if ($editingProduct) {
                $formMode = 'edit';
                $formData = [
                    'category_id' => (string)($editingProduct['category_id'] ?? ''),
                    'name' => (string)($editingProduct['name'] ?? ''),
                    'slug' => (string)($editingProduct['slug'] ?? ''),
                    'description' => (string)($editingProduct['description'] ?? ''),
                    'price' => isset($editingProduct['price']) ? (string)(float)$editingProduct['price'] : '',
                    'sale_price' => isset($editingProduct['sale_price']) && $editingProduct['sale_price'] !== null ? (string)(float)$editingProduct['sale_price'] : '',
                    'stock' => (string)($editingProduct['stock'] ?? '0'),
                    'image' => (string)($editingProduct['image'] ?? ''),
                    'size' => (string)($editingProduct['size'] ?? ''),
                    'care_level' => (string)($editingProduct['care_level'] ?? 'medium'),
                    'light_requirement' => (string)($editingProduct['light_requirement'] ?? 'medium'),
                    'water_requirement' => (string)($editingProduct['water_requirement'] ?? 'medium'),
                    'featured' => !empty($editingProduct['featured']) ? '1' : '0',
                    'status' => (string)($editingProduct['status'] ?? 'active'),
                ];
            }
        }

        $perPage = ADMIN_ITEMS_PER_PAGE;
        $totalProducts = $this->productModel->getAdminTotal($search, $statusFilter);
        $totalPages = max(1, (int)ceil($totalProducts / $perPage));
        $page = min(max(1, $page), $totalPages);
        $offset = ($page - 1) * $perPage;

        $currentImagePreview = ($editingProduct['image_url'] ?? null)
            ?: (!empty($formData['image']) && preg_match('#^https?://#i', $formData['image'])
                ? $formData['image']
                : (!empty($formData['image']) ? upload_url($formData['image']) : image_url('products/default.jpg')));

        return [
            'categories' => $categories,
            'statusOptions' => $statusOptions,
            'careOptions' => $careOptions,
            'requirementOptions' => $requirementOptions,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'page' => $page,
            'editId' => $editId,
            'errors' => $errors,
            'formMode' => $formMode,
            'formData' => $formData,
            'editingProduct' => $editingProduct,
            'stats' => [
                'total' => $this->productModel->getAdminTotal('', 'all'),
                'active' => $this->productModel->getAdminTotal('', 'active'),
                'inactive' => $this->productModel->getAdminTotal('', 'inactive'),
            ],
            'totalProducts' => $totalProducts,
            'totalPages' => $totalPages,
            'products' => $this->productModel->getAdminList($search, $statusFilter, $perPage, $offset),
            'productListStateUrl' => 'products.php' . $this->buildQuery([
                'q' => $search,
                'status' => $statusFilter,
                'page' => $page,
            ]),
            'defaultProductFormState' => [
                'category_id' => '',
                'name' => '',
                'slug' => '',
                'description' => '',
                'price' => '',
                'sale_price' => '',
                'stock' => '0',
                'image' => '',
                'size' => '',
                'care_level' => 'medium',
                'light_requirement' => 'medium',
                'water_requirement' => 'medium',
                'featured' => false,
                'status' => 'active',
                'id' => '',
                'image_url' => image_url('products/default.jpg'),
                'site_url' => '',
            ],
            'initialProductFormState' => [
                'id' => $formMode === 'edit' ? (string)$editId : '',
                'category_id' => $formData['category_id'],
                'name' => $formData['name'],
                'slug' => $formData['slug'],
                'description' => $formData['description'],
                'price' => $formData['price'],
                'sale_price' => $formData['sale_price'],
                'stock' => $formData['stock'],
                'image' => $formData['image'],
                'size' => $formData['size'],
                'care_level' => $formData['care_level'],
                'light_requirement' => $formData['light_requirement'],
                'water_requirement' => $formData['water_requirement'],
                'featured' => $formData['featured'] === '1',
                'status' => $formData['status'],
                'image_url' => $currentImagePreview,
                'site_url' => $formMode === 'edit' && $formData['slug'] !== '' ? '../product-detail.php?slug=' . rawurlencode($formData['slug']) : '',
            ],
            'showProductForm' => $formMode === 'edit' || !empty($errors),
        ];
    }

    /**
     * Build the admin users page state.
     */
    public function presentUsers(
        string $search,
        string $roleFilter,
        string $statusFilter,
        int $page,
        int $editId,
        array $errors = [],
        ?array $formData = null,
        ?array $editingUser = null,
    ): array {
        $permissionOptions = admin_permission_catalog();
        $roleOptions = ['all' => 'Tất cả vai trò', 'admin' => 'Admin', 'user' => 'User'];
        $statusOptions = ['all' => 'Tất cả trạng thái', 'active' => 'Đang hoạt động', 'inactive' => 'Đã khóa'];
        $currentUserId = (int)(get_user_id() ?? 0);

        if (!isset($roleOptions[$roleFilter])) {
            $roleFilter = 'all';
        }
        if (!isset($statusOptions[$statusFilter])) {
            $statusFilter = 'all';
        }

        $formData ??= [
            'username' => '',
            'email' => '',
            'full_name' => '',
            'phone' => '',
            'role' => 'user',
            'admin_permissions' => [],
            'status' => 'active',
        ];

        if ($editId > 0 && !$editingUser) {
            $editingUser = $this->userModel->getAdminById($editId);
            if ($editingUser) {
                $formData = [
                    'username' => (string)($editingUser['username'] ?? ''),
                    'email' => (string)($editingUser['email'] ?? ''),
                    'full_name' => (string)($editingUser['full_name'] ?? ''),
                    'phone' => (string)($editingUser['phone'] ?? ''),
                    'role' => (string)($editingUser['role'] ?? 'user'),
                    'admin_permissions' => normalize_admin_permissions($editingUser['admin_permissions'] ?? []),
                    'status' => (string)($editingUser['status'] ?? 'active'),
                ];
            }
        }

        $perPage = ADMIN_ITEMS_PER_PAGE;
        $totalUsers = $this->userModel->getAdminTotal($search, $roleFilter, $statusFilter);
        $totalPages = max(1, (int)ceil($totalUsers / $perPage));
        $page = min(max(1, $page), $totalPages);
        $offset = ($page - 1) * $perPage;
        $users = $this->userModel->getAdminList($search, $roleFilter, $statusFilter, $perPage, $offset);
        $isEditingCurrentUser = $editingUser && (int)$editingUser['id'] === $currentUserId;

        $initialUserPermissionSummary = $this->userPermissionSummary([
            'role' => $formData['role'],
            'admin_permissions' => $formData['admin_permissions'],
            'has_full_admin_access' => in_array('admin.full_access', $formData['admin_permissions'], true),
        ], $permissionOptions);

        return [
            'permissionOptions' => $permissionOptions,
            'roleOptions' => $roleOptions,
            'statusOptions' => $statusOptions,
            'search' => $search,
            'roleFilter' => $roleFilter,
            'statusFilter' => $statusFilter,
            'page' => $page,
            'editId' => $editId,
            'currentUserId' => $currentUserId,
            'errors' => $errors,
            'formData' => $formData,
            'editingUser' => $editingUser,
            'stats' => [
                'total' => $this->userModel->getAdminTotal('', 'all', 'all'),
                'active' => $this->userModel->getAdminTotal('', 'all', 'active'),
                'admin' => $this->userModel->getAdminTotal('', 'admin', 'all'),
                'inactive' => $this->userModel->getAdminTotal('', 'all', 'inactive'),
            ],
            'totalUsers' => $totalUsers,
            'totalPages' => $totalPages,
            'users' => $users,
            'isEditingCurrentUser' => (bool)$isEditingCurrentUser,
            'userListStateUrl' => 'users.php' . $this->buildQuery([
                'q' => $search,
                'role' => $roleFilter,
                'status' => $statusFilter,
                'page' => $page,
            ]),
            'defaultUserFormState' => [
                'username' => '',
                'email' => '',
                'full_name' => '',
                'phone' => '',
                'role' => 'user',
                'admin_permissions' => [],
                'status' => 'active',
                'id' => '',
                'display_name' => '',
                'permission_summary' => '',
                'created_at' => '',
                'updated_at' => '',
                'is_current_user' => false,
                'profile_url' => '../profile.php',
            ],
            'initialUserFormState' => [
                'id' => $editingUser ? (string)$editingUser['id'] : '',
                'username' => $formData['username'],
                'email' => $formData['email'],
                'full_name' => $formData['full_name'],
                'phone' => $formData['phone'],
                'role' => $formData['role'],
                'admin_permissions' => array_values($formData['admin_permissions']),
                'status' => $formData['status'],
                'display_name' => $editingUser ? (string)($formData['full_name'] !== '' ? $formData['full_name'] : $formData['username']) : '',
                'permission_summary' => $initialUserPermissionSummary ?? '',
                'created_at' => $editingUser ? format_date((string)$editingUser['created_at'], 'd/m/Y H:i') : '',
                'updated_at' => $editingUser ? format_date((string)$editingUser['updated_at'], 'd/m/Y H:i') : '',
                'is_current_user' => (bool)$isEditingCurrentUser,
                'profile_url' => '../profile.php',
            ],
        ];
    }

    /**
     * Build the admin inventory batch page state.
     */
    public function presentInventory(int $productFilter, int $page): array {
        $batchModel = new InventoryBatch();
        $tableExists = $batchModel->tableExists();

        $products = [];
        $batches = [];
        $totalBatches = 0;
        $totalPages = 1;
        $perPage = ADMIN_ITEMS_PER_PAGE;
        $selectedProduct = null;

        if ($tableExists) {
            $db = new Database();
            $conn = $db->getConnection();
            $products = $conn->query(
                "SELECT id, name, slug, stock FROM products ORDER BY name ASC"
            )->fetchAll(PDO::FETCH_ASSOC) ?: [];

            if ($productFilter > 0) {
                $selectedProduct = $this->productModel->getAdminById($productFilter);
            }

            $totalBatches = $batchModel->getAdminTotal($productFilter);
            $totalPages = max(1, (int)ceil($totalBatches / $perPage));
            $page = min(max(1, $page), $totalPages);
            $offset = ($page - 1) * $perPage;
            $batches = $batchModel->getAdminList($productFilter, $perPage, $offset);
        }

        return [
            'tableExists' => $tableExists,
            'products' => $products,
            'batches' => $batches,
            'productFilter' => $productFilter,
            'selectedProduct' => $selectedProduct,
            'page' => $page,
            'totalBatches' => $totalBatches,
            'totalPages' => $totalPages,
        ];
    }

    /**
     * Build data for the image audit tool.
     */
    public function presentCheckImages(): array {
        return [
            'products' => $this->productModel->getAll(8, 0),
        ];
    }

    /**
     * Build data for the clear cache tool.
     *
     * @param array<int, array<string, string>> $messages
     */
    public function presentClearCache(array $messages): array {
        return [
            'messages' => $messages,
            'products' => $this->productModel->getAll(3, 0),
        ];
    }

    /**
     * Build the product catalog payload for admin AJAX tooling.
     */
    public function presentAdminProductsJson(): array {
        $db = new Database();
        $conn = $db->getConnection();
        $products = $conn->query(
            "SELECT id, name, slug, price, sale_price, image
             FROM products
             WHERE status = 'active'
             ORDER BY created_at DESC"
        )->fetchAll(PDO::FETCH_ASSOC);

        return array_map(static function (array $product): array {
            $product['thumbnail_url'] = !empty($product['image']) ? upload_url($product['image']) : image_url('products/default.jpg');
            return $product;
        }, $products);
    }

    /**
     * Build a short permission summary for one admin user row.
     */
    private function userPermissionSummary(array $user, array $permissionOptions): ?string {
        if (($user['role'] ?? 'user') !== 'admin') {
            return null;
        }

        if (!empty($user['has_full_admin_access'])) {
            return 'Toàn quyền quản trị';
        }

        $labels = [];
        foreach (normalize_admin_permissions($user['admin_permissions'] ?? []) as $permission) {
            if ($permission === 'admin.full_access') {
                continue;
            }

            $labels[] = $permissionOptions[$permission]['label'] ?? $permission;
        }

        if ($labels === []) {
            return 'Chưa cấp quyền';
        }

        $summary = implode(', ', array_slice($labels, 0, 2));
        if (count($labels) > 2) {
            $summary .= ' +' . (count($labels) - 2) . ' quyền';
        }

        return $summary;
    }

    /**
     * Build a query string while omitting empty values.
     */
    private function buildQuery(array $params): string {
        $filtered = [];
        foreach ($params as $key => $value) {
            if ($value === null || $value === '' || $value === 'all') {
                continue;
            }

            if ($key === 'page' && (int)$value <= 1) {
                continue;
            }

            $filtered[$key] = $value;
        }

        $query = http_build_query($filtered);
        return $query !== '' ? '?' . $query : '';
    }
}
