<?php
/**
 * Handle admin mutations for legacy pages while migrating to MVC.
 */
class AdminPageService {
    private AuthService $authService;
    private ContactMessage $contactModel;
    private Order $orderModel;
    private Product $productModel;
    private Category $categoryModel;
    private User $userModel;

    public function __construct(
        ?AuthService $authService = null,
        ?ContactMessage $contactModel = null,
        ?Order $orderModel = null,
        ?Product $productModel = null,
        ?Category $categoryModel = null,
        ?User $userModel = null,
    ) {
        $this->authService = $authService ?? new AuthService();
        $this->contactModel = $contactModel ?? new ContactMessage();
        $this->orderModel = $orderModel ?? new Order();
        $this->productModel = $productModel ?? new Product();
        $this->categoryModel = $categoryModel ?? new Category();
        $this->userModel = $userModel ?? new User();
    }

    /**
     * Attempt one admin login.
     */
    public function login(array $input): array {
        return $this->authService->loginAdmin(
            trim((string)($input['identifier'] ?? '')),
            (string)($input['password'] ?? '')
        );
    }

    /**
     * Update one contact state from admin.
     */
    public function updateContactStatus(array $input): array {
        $nextStatus = (string)($input['next_status'] ?? 'new');
        if (!in_array($nextStatus, ['new', 'in_progress', 'resolved'], true)) {
            return ['success' => false, 'message' => 'Trạng thái liên hệ không hợp lệ.'];
        }

        $updated = $this->contactModel->updateAdminState(
            max(0, (int)($input['contact_id'] ?? 0)),
            $nextStatus,
            trim((string)($input['admin_note'] ?? ''))
        );

        return [
            'success' => $updated,
            'message' => $updated
                ? 'Đã cập nhật trạng thái và ghi chú xử lý liên hệ.'
                : 'Không thể cập nhật liên hệ lúc này.',
        ];
    }

    /**
     * Update one order from admin.
     */
    public function handleOrderAction(array $input): array {
        $action = (string)($input['action'] ?? '');
        $orderId = max(0, (int)($input['order_id'] ?? 0));

        if ($action === 'update_order_status') {
            $nextStatus = (string)($input['next_status'] ?? '');
            $updated = $orderId > 0 ? $this->orderModel->updateAdminOrderStatus($orderId, $nextStatus) : false;
            return [
                'success' => $updated,
                'message' => $updated
                    ? 'Đã cập nhật trạng thái đơn hàng.'
                    : ($this->orderModel->getLastErrorMessage() ?? 'Không thể cập nhật trạng thái đơn hàng lúc này.'),
            ];
        }

        if ($action === 'approve_online_mock_payment') {
            $approved = $orderId > 0 ? $this->orderModel->approveOnlineMockPaymentByAdmin($orderId) : false;
            return [
                'success' => $approved,
                'message' => $approved
                    ? 'Đã duyệt chuyển khoản giả lập và cập nhật trạng thái thanh toán.'
                    : ($this->orderModel->getLastErrorMessage() ?? 'Không thể duyệt thanh toán lúc này.'),
            ];
        }

        if ($action === 'reject_online_mock_payment') {
            $reason = trim((string)($input['reject_reason'] ?? ''));
            if ($reason === '') {
                return ['success' => false, 'message' => 'Vui lòng nhập lý do từ chối duyệt.'];
            }

            $rejected = $orderId > 0 ? $this->orderModel->rejectOnlineMockPaymentByAdmin($orderId, $reason) : false;
            return [
                'success' => $rejected,
                'message' => $rejected
                    ? 'Đã từ chối yêu cầu chuyển khoản giả lập.'
                    : ($this->orderModel->getLastErrorMessage() ?? 'Không thể từ chối thanh toán lúc này.'),
            ];
        }

        return ['success' => false, 'message' => 'Hành động quản lý đơn hàng không hợp lệ.'];
    }

    /**
     * Save or delete one admin category.
     */
    public function handleCategoryAction(array $input): array {
        $action = (string)($input['action'] ?? '');

        if ($action === 'delete') {
            $categoryId = max(0, (int)($input['category_id'] ?? 0));
            $category = $this->categoryModel->getAdminById($categoryId);
            if (!$category) {
                return ['success' => false, 'message' => 'Không tìm thấy danh mục cần xóa.'];
            }

            $result = $this->categoryModel->deleteAdminCategory($categoryId);
            if (!empty($result['success'])) {
                return ['success' => true, 'message' => ($result['mode'] ?? '') === 'inactivated'
                    ? 'Danh mục đã có sản phẩm nên được chuyển sang trạng thái ẩn để giữ dữ liệu.'
                    : 'Đã xóa danh mục khỏi hệ thống.'];
            }

            return ['success' => false, 'message' => 'Không thể xóa danh mục lúc này.'];
        }

        $formData = [
            'name' => trim((string)($input['name'] ?? '')),
            'slug' => trim((string)($input['slug'] ?? '')),
            'description' => trim((string)($input['description'] ?? '')),
            'image' => trim((string)($input['image'] ?? '')),
            'parent_id' => trim((string)($input['parent_id'] ?? '')),
            'status' => trim((string)($input['status'] ?? 'active')),
        ];

        $editId = max(0, (int)($input['category_id'] ?? 0));
        $formMode = $action === 'update' ? 'edit' : 'create';
        $errors = [];

        $parentOptions = $this->categoryModel->getAdminParentOptions($action === 'update' ? $editId : null);
        $parentIds = array_map(static fn(array $item): int => (int)$item['id'], $parentOptions);

        if ($formData['name'] === '') {
            $errors['name'] = 'Tên danh mục không được để trống.';
        }

        if ($formData['parent_id'] !== '' && !in_array((int)$formData['parent_id'], $parentIds, true)) {
            $errors['parent_id'] = 'Danh mục cha không hợp lệ.';
        }

        if (!in_array($formData['status'], ['active', 'inactive'], true)) {
            $errors['status'] = 'Trạng thái không hợp lệ.';
        }

        $baseSlug = create_slug($formData['slug'] !== '' ? $formData['slug'] : $formData['name']);
        if ($baseSlug === '') {
            $errors['slug'] = 'Không thể tạo slug từ tên danh mục. Vui lòng nhập slug thủ công.';
        }

        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors, 'formData' => $formData, 'formMode' => $formMode, 'editId' => $editId];
        }

        $payload = [
            'name' => $formData['name'],
            'slug' => $this->generateUniqueCategorySlug($baseSlug, $action === 'update' ? $editId : null),
            'description' => $formData['description'],
            'image' => $formData['image'],
            'parent_id' => $formData['parent_id'] !== '' ? (int)$formData['parent_id'] : null,
            'status' => $formData['status'],
        ];

        if ($action === 'update') {
            $this->categoryModel->updateAdminCategory($editId, $payload);
            return ['success' => true, 'message' => 'Đã cập nhật thông tin danh mục.', 'editId' => $editId];
        }

        $newId = $this->categoryModel->createAdminCategory($payload);
        return ['success' => true, 'message' => 'Đã thêm danh mục mới.', 'editId' => $newId];
    }

    /**
     * Save or delete one admin product.
     */
    public function handleProductAction(array $input, array $files): array {
        $action = (string)($input['action'] ?? '');

        if ($action === 'delete') {
            $productId = max(0, (int)($input['product_id'] ?? 0));
            $product = $this->productModel->getAdminById($productId);
            if (!$product) {
                return ['success' => false, 'message' => 'Không tìm thấy sản phẩm cần xóa.'];
            }

            $result = $this->productModel->deleteAdminProduct($productId);
            if (!empty($result['success'])) {
                return ['success' => true, 'message' => ($result['mode'] ?? '') === 'inactivated'
                    ? 'Sản phẩm đã có đơn hàng nên được chuyển sang trạng thái ngừng bán để giữ lịch sử.'
                    : 'Đã xóa sản phẩm khỏi hệ thống.'];
            }

            return ['success' => false, 'message' => 'Không thể xóa sản phẩm lúc này.'];
        }

        $formData = [
            'category_id' => trim((string)($input['category_id'] ?? '')),
            'name' => trim((string)($input['name'] ?? '')),
            'slug' => trim((string)($input['slug'] ?? '')),
            'description' => trim((string)($input['description'] ?? '')),
            'price' => trim((string)($input['price'] ?? '')),
            'sale_price' => trim((string)($input['sale_price'] ?? '')),
            'stock' => trim((string)($input['stock'] ?? '0')),
            'image' => trim((string)($input['image'] ?? '')),
            'size' => trim((string)($input['size'] ?? '')),
            'care_level' => trim((string)($input['care_level'] ?? 'medium')),
            'light_requirement' => trim((string)($input['light_requirement'] ?? 'medium')),
            'water_requirement' => trim((string)($input['water_requirement'] ?? 'medium')),
            'featured' => !empty($input['featured']) ? '1' : '0',
            'status' => trim((string)($input['status'] ?? 'active')),
        ];

        $formMode = $action === 'update' ? 'edit' : 'create';
        $editId = max(0, (int)($input['product_id'] ?? 0));
        $categories = (new Category())->getAll();
        $categoryIds = array_map(static fn(array $category): int => (int)$category['id'], $categories);
        $careOptions = ['easy' => 'Dễ', 'medium' => 'Trung bình', 'hard' => 'Khó'];
        $requirementOptions = ['low' => 'Thấp', 'medium' => 'Vừa', 'high' => 'Cao'];
        $errors = [];

        if (empty($categories)) {
            $errors['category_id'] = 'Hiện chưa có danh mục hoạt động để gán cho sản phẩm.';
        } elseif (!in_array((int)$formData['category_id'], $categoryIds, true)) {
            $errors['category_id'] = 'Vui lòng chọn danh mục hợp lệ.';
        }
        if ($formData['name'] === '') {
            $errors['name'] = 'Tên sản phẩm không được để trống.';
        }

        $price = $this->normalizeDecimal($formData['price']);
        if ($price === null || (float)$price <= 0) {
            $errors['price'] = 'Giá bán phải lớn hơn 0.';
        }

        $salePrice = $this->normalizeDecimal($formData['sale_price']);
        if ($formData['sale_price'] !== '' && $salePrice === null) {
            $errors['sale_price'] = 'Giá khuyến mãi không hợp lệ.';
        }
        if ($salePrice !== null && $price !== null && (float)$salePrice >= (float)$price) {
            $errors['sale_price'] = 'Giá khuyến mãi phải nhỏ hơn giá bán.';
        }

        if (!preg_match('/^\d+$/', $formData['stock'])) {
            $errors['stock'] = 'Tồn kho phải là số nguyên không âm.';
        }

        if (!isset($careOptions[$formData['care_level']])) {
            $errors['care_level'] = 'Mức chăm sóc không hợp lệ.';
        }
        if (!isset($requirementOptions[$formData['light_requirement']])) {
            $errors['light_requirement'] = 'Mức ánh sáng không hợp lệ.';
        }
        if (!isset($requirementOptions[$formData['water_requirement']])) {
            $errors['water_requirement'] = 'Mức nước không hợp lệ.';
        }
        if (!in_array($formData['status'], ['active', 'inactive'], true)) {
            $errors['status'] = 'Trạng thái không hợp lệ.';
        }

        $baseSlug = create_slug($formData['slug'] !== '' ? $formData['slug'] : $formData['name']);
        if ($baseSlug === '') {
            $errors['slug'] = 'Không thể tạo đường dẫn từ tên sản phẩm. Vui lòng nhập slug thủ công.';
        }

        $uploadedImagePath = null;
        if ($this->uploadedFilePresent($files['product_image'] ?? [])) {
            $uploadResult = $this->storeUploadedProductImage($files['product_image']);
            if (empty($uploadResult['success'])) {
                $errors['product_image'] = (string)($uploadResult['error'] ?? 'Không thể tải ảnh lên lúc này.');
            } else {
                $uploadedImagePath = (string)$uploadResult['path'];
                $formData['image'] = $uploadedImagePath;
            }
        }

        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors, 'formData' => $formData, 'formMode' => $formMode, 'editId' => $editId];
        }

        $payload = [
            'category_id' => (int)$formData['category_id'],
            'name' => $formData['name'],
            'slug' => $this->generateUniqueProductSlug($baseSlug, $action === 'update' ? $editId : null),
            'description' => $formData['description'],
            'price' => $price,
            'sale_price' => $salePrice,
            'stock' => (int)$formData['stock'],
            'image' => $formData['image'],
            'size' => $formData['size'],
            'care_level' => $formData['care_level'],
            'light_requirement' => $formData['light_requirement'],
            'water_requirement' => $formData['water_requirement'],
            'featured' => $formData['featured'] === '1',
            'status' => $formData['status'],
        ];

        try {
            if ($action === 'update') {
                $this->productModel->updateAdminProduct($editId, $payload);
                return [
                    'success' => true,
                    'message' => $uploadedImagePath !== null ? 'Đã cập nhật sản phẩm và ảnh đại diện.' : 'Đã cập nhật thông tin sản phẩm.',
                    'editId' => $editId,
                ];
            }

            $newId = $this->productModel->createAdminProduct($payload);
            return [
                'success' => true,
                'message' => $uploadedImagePath !== null ? 'Đã thêm sản phẩm mới và tải ảnh đại diện lên thành công.' : 'Đã thêm sản phẩm mới vào hệ thống.',
                'editId' => $newId,
            ];
        } catch (Throwable $e) {
            if ($uploadedImagePath !== null) {
                $uploadedImageFullPath = BASE_PATH . '/uploads/' . $uploadedImagePath;
                if (is_file($uploadedImageFullPath)) {
                    @unlink($uploadedImageFullPath);
                }
            }

            error_log('Admin product save error: ' . $e->getMessage());
            return ['success' => false, 'errors' => ['general' => 'Không thể lưu sản phẩm lúc này.'], 'formData' => $formData, 'formMode' => $formMode, 'editId' => $editId];
        }
    }

    /**
     * Update one admin user.
     */
    public function handleUserAction(array $input): array {
        $editId = max(0, (int)($input['user_id'] ?? 0));
        $editingUser = $this->userModel->getAdminById($editId);
        if (!$editingUser) {
            return ['success' => false, 'message' => 'Không tìm thấy tài khoản cần cập nhật.'];
        }

        $permissionOptions = admin_permission_catalog();
        $currentUserId = (int)(get_user_id() ?? 0);
        $formData = [
            'username' => trim((string)($input['username'] ?? '')),
            'email' => strtolower(trim((string)($input['email'] ?? ''))),
            'full_name' => trim((string)($input['full_name'] ?? '')),
            'phone' => trim((string)($input['phone'] ?? '')),
            'role' => trim((string)($input['role'] ?? 'user')),
            'admin_permissions' => normalize_admin_permissions($input['admin_permissions'] ?? []),
            'status' => trim((string)($input['status'] ?? 'active')),
        ];

        $errors = [];
        if ($formData['full_name'] === '') {
            $errors['full_name'] = 'Họ tên không được để trống.';
        } elseif (string_length($formData['full_name']) < 2) {
            $errors['full_name'] = 'Họ tên cần ít nhất 2 ký tự.';
        }

        if ($formData['username'] === '') {
            $errors['username'] = 'Vui lòng nhập tên đăng nhập.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{4,30}$/', $formData['username'])) {
            $errors['username'] = 'Tên đăng nhập gồm 4-30 ký tự, chỉ dùng chữ, số hoặc dấu gạch dưới.';
        } elseif ($this->userModel->usernameExists($formData['username'], $editId)) {
            $errors['username'] = 'Tên đăng nhập này đã tồn tại.';
        }

        if ($formData['email'] === '') {
            $errors['email'] = 'Vui lòng nhập email.';
        } elseif (!is_valid_email($formData['email'])) {
            $errors['email'] = 'Email không hợp lệ.';
        } elseif ($this->userModel->emailExists($formData['email'], $editId)) {
            $errors['email'] = 'Email này đã được sử dụng.';
        }

        if ($formData['phone'] !== '' && !preg_match('/^[0-9+\s.-]{8,20}$/', $formData['phone'])) {
            $errors['phone'] = 'Số điện thoại không hợp lệ.';
        }

        if (!in_array($formData['role'], ['admin', 'user'], true)) {
            $errors['role'] = 'Vai trò không hợp lệ.';
        }

        if ($formData['role'] !== 'admin') {
            $formData['admin_permissions'] = [];
        }

        foreach ($formData['admin_permissions'] as $permission) {
            if (!array_key_exists($permission, $permissionOptions)) {
                $errors['admin_permissions'] = 'Danh sách quyền admin phụ không hợp lệ.';
                break;
            }
        }

        if (in_array('admin.full_access', $formData['admin_permissions'], true)) {
            $formData['admin_permissions'] = ['admin.full_access'];
        }

        if (!in_array($formData['status'], ['active', 'inactive'], true)) {
            $errors['status'] = 'Trạng thái không hợp lệ.';
        }

        if ($editId === $currentUserId) {
            if ($formData['role'] !== (string)$editingUser['role']) {
                $errors['role'] = 'Bạn không thể tự đổi vai trò của chính mình tại trang này.';
            }
            if ($formData['status'] !== (string)$editingUser['status']) {
                $errors['status'] = 'Bạn không thể tự khóa tài khoản admin đang đăng nhập.';
            }
            if ($formData['admin_permissions'] !== normalize_admin_permissions($editingUser['admin_permissions'] ?? [])) {
                $errors['admin_permissions'] = 'Bạn không thể tự thay đổi phạm vi quyền admin của chính mình tại trang này.';
            }
        }

        $removingAdminAccess = ($editingUser['role'] ?? 'user') === 'admin'
            && ($editingUser['status'] ?? 'inactive') === 'active'
            && ($formData['role'] !== 'admin' || $formData['status'] !== 'active');
        if ($removingAdminAccess && $this->userModel->countByRoleAndStatus('admin', 'active', $editId) === 0) {
            $errors['general'] = 'Hệ thống cần ít nhất một tài khoản admin đang hoạt động.';
        }

        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors, 'formData' => $formData, 'editId' => $editId, 'editingUser' => $editingUser];
        }

        $updated = $this->userModel->updateAdminUser($editId, [
            'username' => $formData['username'],
            'email' => $formData['email'],
            'full_name' => $formData['full_name'],
            'phone' => $formData['phone'],
            'role' => $formData['role'],
            'admin_permissions' => $formData['admin_permissions'],
            'status' => $formData['status'],
        ]);

        if ($updated) {
            $result = ['success' => true, 'message' => 'Đã cập nhật thông tin tài khoản.', 'editId' => $editId];

            if ($editId === $currentUserId) {
                $freshUser = $this->userModel->findById($editId);
                if ($freshUser) {
                    $result['fresh_user'] = $this->userModel->withoutPassword($freshUser);
                }
            }

            return $result;
        }

        return ['success' => false, 'errors' => ['general' => 'Không thể cập nhật tài khoản lúc này.'], 'formData' => $formData, 'editId' => $editId, 'editingUser' => $editingUser];
    }

    /**
     * Update one product image by URL.
     */
    public function updateProductImage(array $input): array {
        $productId = max(0, (int)($input['product_id'] ?? 0));
        if ($productId <= 0) {
            return ['success' => false, 'message' => 'Sản phẩm không hợp lệ.'];
        }

        $imageUrl = trim((string)($input['image_url'] ?? ''));
        $urlError = validate_image_source_url($imageUrl);
        if ($urlError !== null) {
            return ['success' => false, 'message' => $urlError];
        }

        $product = $this->productModel->getAdminById($productId);
        if (!$product) {
            return ['success' => false, 'message' => 'Không tìm thấy sản phẩm cần cập nhật ảnh.'];
        }

        try {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("UPDATE products SET image = :image_url WHERE id = :product_id");
            $stmt->bindValue(':image_url', $imageUrl, PDO::PARAM_STR);
            $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
            $stmt->execute();

            return ['success' => true, 'message' => 'Đã cập nhật URL ảnh cho sản phẩm.'];
        } catch (Throwable $e) {
            error_log('Admin update_product_image error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Không thể cập nhật ảnh lúc này.'];
        }
    }

    /**
     * Upload one product image from admin.
     */
    public function uploadProductImage(array $input, array $files): array {
        $productId = max(0, (int)($input['product_id_upload'] ?? 0));
        if ($productId <= 0) {
            return ['success' => false, 'message' => 'Sản phẩm không hợp lệ.'];
        }

        $product = $this->productModel->getAdminById($productId);
        if (!$product) {
            return ['success' => false, 'message' => 'Không tìm thấy sản phẩm cần upload ảnh.'];
        }

        if (!isset($files['product_image'])) {
            return ['success' => false, 'message' => 'Vui lòng chọn một ảnh để tải lên.'];
        }

        $uploadResult = $this->storeUploadedProductImage($files['product_image']);
        if (empty($uploadResult['success'])) {
            return ['success' => false, 'message' => (string)($uploadResult['error'] ?? 'Không thể upload ảnh lúc này.')];
        }

        $imagePath = (string)$uploadResult['path'];
        $uploadPath = BASE_PATH . '/uploads/' . $imagePath;

        try {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("UPDATE products SET image = :image_path WHERE id = :product_id");
            $stmt->bindValue(':image_path', $imagePath, PDO::PARAM_STR);
            $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
            $stmt->execute();

            return ['success' => true, 'message' => 'Đã upload ảnh và cập nhật sản phẩm thành công.'];
        } catch (Throwable $e) {
            if (is_file($uploadPath)) {
                @unlink($uploadPath);
            }

            error_log('Admin upload_product_image error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Không thể upload ảnh lúc này.'];
        }
    }

    /**
     * Create placeholder images for known missing products.
     *
     * @return array<int, array{status: string, text: string}>
     */
    public function createPlaceholderImages(): array {
        $missingImages = [
            'combo-sen.png' => 'Combo Sen Đá',
            'bang-singapore.png' => 'Cây Bàng Singapore',
        ];

        $outputDir = PUBLIC_PATH . '/images/products/';
        $results = [];

        foreach ($missingImages as $filename => $productName) {
            $filepath = $outputDir . $filename;
            if (file_exists($filepath)) {
                $results[] = ['status' => 'success', 'text' => $filename . ' đã tồn tại.'];
                continue;
            }

            $image = imagecreatetruecolor(500, 500);
            $bgColor = imagecolorallocate($image, 200, 230, 201);
            $textColor = imagecolorallocate($image, 46, 125, 50);
            $borderColor = imagecolorallocate($image, 129, 199, 132);

            imagefill($image, 0, 0, $bgColor);
            imagerectangle($image, 10, 10, 490, 490, $borderColor);
            imagerectangle($image, 11, 11, 489, 489, $borderColor);

            $font = 5;
            $textWidth = imagefontwidth($font) * strlen($productName);
            $textHeight = imagefontheight($font);
            $x = (int)((500 - $textWidth) / 2);
            $y = (int)((500 - $textHeight) / 2);

            imagestring($image, $font, $x, $y, $productName, $textColor);
            imagepng($image, $filepath);
            imagedestroy($image);

            $results[] = ['status' => 'success', 'text' => 'Đã tạo ' . $filename];
        }

        return $results;
    }

    /**
     * Normalize all product image paths.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fixImagePaths(): array {
        $db = new Database();
        $conn = $db->getConnection();
        $products = $conn->query("SELECT id, name, image FROM products")->fetchAll(PDO::FETCH_ASSOC);
        $updateStmt = $conn->prepare("UPDATE products SET image = :image WHERE id = :id");
        $results = [];

        foreach ($products as $product) {
            $oldPath = (string)($product['image'] ?? '');
            $newPath = basename(str_replace('"', '', $oldPath));
            if ($newPath !== '' && strpos($newPath, 'products/') !== 0) {
                $newPath = 'products/' . $newPath;
            }

            try {
                $updateStmt->execute([
                    ':image' => $newPath !== '' ? $newPath : null,
                    ':id' => (int)$product['id'],
                ]);

                $results[] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'old' => $oldPath,
                    'new' => $newPath,
                    'status' => 'success',
                ];
            } catch (Throwable $e) {
                $results[] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'old' => $oldPath,
                    'new' => $newPath,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Clear caches and return messages for the admin tool page.
     *
     * @return array<int, array<string, string>>
     */
    public function clearCaches(): array {
        $messages = [];

        if (function_exists('opcache_reset')) {
            opcache_reset();
            $messages[] = ['type' => 'success', 'text' => 'Đã xóa OPcache.'];
        } else {
            $messages[] = ['type' => 'error', 'text' => 'OPcache không được bật trên môi trường này.'];
        }

        clearstatcache(true);
        $messages[] = ['type' => 'success', 'text' => 'Đã xóa file stat cache.'];

        return $messages;
    }

    /**
     * Generate one unique category slug.
     */
    private function generateUniqueCategorySlug(string $baseSlug, ?int $excludeId = null): string {
        $baseSlug = trim($baseSlug, '-');
        if ($baseSlug === '') {
            $baseSlug = 'danh-muc';
        }

        $slug = $baseSlug;
        $suffix = 2;
        while ($this->categoryModel->slugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * Generate one unique product slug.
     */
    private function generateUniqueProductSlug(string $baseSlug, ?int $excludeId = null): string {
        $baseSlug = trim($baseSlug, '-');
        if ($baseSlug === '') {
            $baseSlug = 'san-pham';
        }

        $slug = $baseSlug;
        $suffix = 2;
        while ($this->productModel->slugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * Normalize one decimal string coming from admin forms.
     */
    private function normalizeDecimal(string $value): ?string {
        $value = trim(str_replace([' ', ','], ['', ''], $value));
        if ($value === '') {
            return null;
        }

        return is_numeric($value) ? (string)(float)$value : null;
    }

    /**
     * Check whether one product upload was provided.
     */
    private function uploadedFilePresent(array $file): bool {
        return isset($file['error']) && (int)$file['error'] !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Store one uploaded product image.
     */
    private function storeUploadedProductImage(array $file): array {
        $validation = validate_uploaded_image($file);
        if (empty($validation['valid'])) {
            return ['success' => false, 'error' => (string)($validation['error'] ?? 'Ảnh tải lên không hợp lệ.')];
        }

        $uploadDir = BASE_PATH . '/uploads/products/';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            return ['success' => false, 'error' => 'Không thể tạo thư mục lưu ảnh.'];
        }

        $extension = (string)($validation['extension'] ?? 'jpg');
        $newFileName = 'product_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $uploadPath = $uploadDir . $newFileName;
        $imagePath = 'products/' . $newFileName;

        if (!move_uploaded_file((string)$file['tmp_name'], $uploadPath)) {
            return ['success' => false, 'error' => 'Không thể lưu file ảnh đã tải lên.'];
        }

        return ['success' => true, 'path' => $imagePath];
    }
}
