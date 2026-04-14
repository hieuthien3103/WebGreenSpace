<?php
/**
 * Handle storefront actions for legacy public pages while migrating to MVC.
 */
class StorefrontPageService {
    private AuthService $authService;
    private CartService $cartService;
    private CheckoutService $checkoutService;
    private ContactMessage $contactModel;
    private Address $addressModel;
    private User $userModel;
    private Order $orderModel;

    public function __construct(
        ?AuthService $authService = null,
        ?CartService $cartService = null,
        ?CheckoutService $checkoutService = null,
        ?ContactMessage $contactModel = null,
        ?Address $addressModel = null,
        ?User $userModel = null,
        ?Order $orderModel = null,
    ) {
        $this->authService = $authService ?? new AuthService();
        $this->cartService = $cartService ?? new CartService();
        $this->checkoutService = $checkoutService ?? new CheckoutService();
        $this->contactModel = $contactModel ?? new ContactMessage();
        $this->addressModel = $addressModel ?? new Address();
        $this->userModel = $userModel ?? new User();
        $this->orderModel = $orderModel ?? new Order();
    }

    /**
     * Attempt a storefront login.
     */
    public function login(array $input, bool $adminOnly = false): array {
        if (!verify_csrf_token($input['csrf_token'] ?? null)) {
            return ['success' => false, 'errors' => ['general' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.']];
        }

        $identifier = trim((string)($input['identifier'] ?? ''));
        $password = (string)($input['password'] ?? '');

        return $adminOnly
            ? $this->authService->loginAdmin($identifier, $password)
            : $this->authService->login($identifier, $password);
    }

    /**
     * Attempt a storefront registration.
     */
    public function register(array $input): array {
        if (!verify_csrf_token($input['csrf_token'] ?? null)) {
            return ['success' => false, 'errors' => ['general' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.']];
        }

        return $this->authService->register($input);
    }

    /**
     * Submit one contact form.
     */
    public function submitContact(array $input): array {
        $values = [
            'full_name' => trim((string)($input['full_name'] ?? '')),
            'email' => strtolower(trim((string)($input['email'] ?? ''))),
            'phone' => trim((string)($input['phone'] ?? '')),
            'subject' => trim((string)($input['subject'] ?? '')),
            'message' => trim((string)($input['message'] ?? '')),
        ];

        $errors = [];
        if (!verify_csrf_token($input['csrf_token'] ?? null)) {
            $errors['general'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
        }

        if ($values['full_name'] === '') {
            $errors['full_name'] = 'Vui lòng nhập họ và tên.';
        } elseif (string_length($values['full_name']) < 2) {
            $errors['full_name'] = 'Họ và tên cần ít nhất 2 ký tự.';
        }

        if ($values['email'] === '') {
            $errors['email'] = 'Vui lòng nhập email.';
        } elseif (!is_valid_email($values['email'])) {
            $errors['email'] = 'Email không hợp lệ.';
        }

        if ($values['phone'] !== '' && !preg_match('/^[0-9+\s().-]{8,20}$/', $values['phone'])) {
            $errors['phone'] = 'Số điện thoại không hợp lệ.';
        }

        if ($values['subject'] === '') {
            $errors['subject'] = 'Vui lòng nhập tiêu đề.';
        } elseif (string_length($values['subject']) < 4) {
            $errors['subject'] = 'Tiêu đề cần ít nhất 4 ký tự.';
        }

        if ($values['message'] === '') {
            $errors['message'] = 'Vui lòng nhập nội dung.';
        } elseif (string_length($values['message']) < 20) {
            $errors['message'] = 'Nội dung cần ít nhất 20 ký tự để admin có thể hỗ trợ tốt hơn.';
        }

        if ($errors !== []) {
            return ['success' => false, 'values' => $values, 'errors' => $errors];
        }

        $this->contactModel->create($values);

        return ['success' => true, 'values' => $values];
    }

    /**
     * Handle one cart mutation.
     */
    public function mutateCart(array $input): array {
        if (!verify_csrf_token($input['csrf_token'] ?? null)) {
            return [
                'success' => false,
                'message' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.',
                'status' => 419,
            ];
        }

        $action = (string)($input['action'] ?? '');
        $result = ['success' => false, 'message' => 'Hành động không hợp lệ.', 'status' => 422];

        switch ($action) {
            case 'add':
                $result = $this->cartService->addItem((int)($input['product_id'] ?? 0), (int)($input['quantity'] ?? 1));
                $result['status'] = !empty($result['success']) ? 200 : 422;
                break;

            case 'update':
                $this->cartService->updateItems((array)($input['quantities'] ?? []));
                $result = ['success' => true, 'message' => 'Đã cập nhật giỏ hàng.', 'status' => 200];
                break;

            case 'remove':
                $this->cartService->removeItem((int)($input['product_id'] ?? 0));
                $result = ['success' => true, 'message' => 'Đã xóa sản phẩm khỏi giỏ.', 'status' => 200];
                break;

            case 'clear':
                $this->cartService->clear();
                $result = ['success' => true, 'message' => 'Đã xóa toàn bộ giỏ hàng.', 'status' => 200];
                break;
        }

        $result['cart_count'] = cart_item_count();
        return $result;
    }

    /**
     * Build the default checkout draft values.
     */
    public function pullCheckoutDraft(): ?array {
        $draft = $_SESSION['checkout_quick_draft'] ?? null;
        if (is_array($draft)) {
            unset($_SESSION['checkout_quick_draft']);
            return $draft;
        }

        return null;
    }

    /**
     * Save one address quickly from checkout.
     */
    public function quickSaveCheckoutAddress(int $userId, array $input): array {
        $payload = [
            'receiver_name' => trim((string)($input['full_name'] ?? '')),
            'phone' => trim((string)($input['phone'] ?? '')),
            'province' => trim((string)($input['province'] ?? '')),
            'district' => trim((string)($input['district'] ?? '')),
            'ward' => trim((string)($input['ward'] ?? '')),
            'address_line' => trim((string)($input['address_line'] ?? '')),
        ];

        $errors = [];
        if ($payload['receiver_name'] === '') {
            $errors['full_name'] = 'Vui lòng nhập họ tên người nhận.';
        }
        if ($payload['phone'] === '') {
            $errors['phone'] = 'Vui lòng nhập số điện thoại.';
        }
        if ($payload['province'] === '') {
            $errors['province'] = 'Vui lòng nhập tỉnh/thành.';
        }
        if ($payload['district'] === '') {
            $errors['district'] = 'Vui lòng nhập quận/huyện.';
        }
        if ($payload['address_line'] === '') {
            $errors['address_line'] = 'Vui lòng nhập địa chỉ cụ thể.';
        }

        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors];
        }

        $_SESSION['checkout_quick_draft'] = [
            'email' => trim((string)($input['email'] ?? '')),
            'note' => trim((string)($input['note'] ?? '')),
            'payment_method' => trim((string)($input['payment_method'] ?? 'cod')),
            'save_as_default' => !empty($input['save_as_default']),
        ];

        $makeDefault = !empty($input['save_as_default']);
        $newAddressId = $this->addressModel->createForUser($userId, $payload, $makeDefault);

        return [
            'success' => true,
            'address_id' => $newAddressId,
            'message' => $makeDefault
                ? 'Đã lưu nhanh địa chỉ vào hồ sơ và đặt làm mặc định.'
                : 'Đã lưu nhanh địa chỉ vào hồ sơ. Bạn có thể dùng lại cho các lần mua sau.',
        ];
    }

    /**
     * Place one checkout order.
     */
    public function placeOrder(int $userId, array $input): array {
        return $this->checkoutService->placeOrder($userId, $input);
    }

    /**
     * Update one profile.
     */
    public function updateProfile(int $userId, array $input): array {
        $fullName = trim((string)($input['full_name'] ?? ''));
        $phone = trim((string)($input['phone'] ?? ''));

        if ($fullName === '') {
            return ['success' => false, 'message' => 'Họ tên không được để trống.'];
        }

        $this->userModel->updateProfile($userId, [
            'full_name' => $fullName,
            'phone' => $phone,
        ]);

        $freshUser = $this->userModel->findById($userId);
        if ($freshUser) {
            $_SESSION['user_data'] = $this->userModel->withoutPassword($freshUser);
        }

        return ['success' => true, 'message' => 'Đã cập nhật thông tin tài khoản.'];
    }

    /**
     * Set one default address.
     */
    public function setDefaultAddress(int $userId, int $addressId): array {
        $address = $this->addressModel->getByIdForUser($userId, $addressId);
        if (!$address) {
            return ['success' => false, 'message' => 'Không tìm thấy địa chỉ cần cập nhật.'];
        }

        $this->addressModel->setDefaultById($userId, $addressId);
        return ['success' => true, 'message' => 'Đã đặt địa chỉ làm mặc định.'];
    }

    /**
     * Delete one saved address.
     */
    public function deleteAddress(int $userId, int $addressId): array {
        $address = $this->addressModel->getByIdForUser($userId, $addressId);
        if (!$address) {
            return ['success' => false, 'message' => 'Không tìm thấy địa chỉ cần xóa.'];
        }

        $this->addressModel->deleteByIdForUser($userId, $addressId);
        return ['success' => true, 'message' => 'Đã xóa địa chỉ đã chọn.'];
    }

    /**
     * Save or update one profile address.
     */
    public function saveAddress(int $userId, array $input): array {
        $addressId = max(0, (int)($input['address_id'] ?? 0));
        $addressForm = [
            'receiver_name' => trim((string)($input['receiver_name'] ?? '')),
            'phone' => trim((string)($input['address_phone'] ?? '')),
            'province' => trim((string)($input['province'] ?? '')),
            'district' => trim((string)($input['district'] ?? '')),
            'ward' => trim((string)($input['ward'] ?? '')),
            'address_line' => trim((string)($input['address_line'] ?? '')),
            'make_default' => !empty($input['make_default']) ? '1' : '0',
        ];

        $errors = [];
        if ($addressForm['receiver_name'] === '') {
            $errors['receiver_name'] = 'Vui lòng nhập tên người nhận.';
        }
        if ($addressForm['phone'] === '') {
            $errors['phone'] = 'Vui lòng nhập số điện thoại.';
        }
        if ($addressForm['province'] === '') {
            $errors['province'] = 'Vui lòng nhập tỉnh/thành.';
        }
        if ($addressForm['district'] === '') {
            $errors['district'] = 'Vui lòng nhập quận/huyện.';
        }
        if ($addressForm['address_line'] === '') {
            $errors['address_line'] = 'Vui lòng nhập địa chỉ cụ thể.';
        }

        if ($errors !== []) {
            return [
                'success' => false,
                'errors' => $errors,
                'addressForm' => $addressForm,
                'addressFormMode' => $addressId > 0 ? 'edit' : 'create',
                'editingAddressId' => $addressId,
            ];
        }

        $payload = [
            'receiver_name' => $addressForm['receiver_name'],
            'phone' => $addressForm['phone'],
            'province' => $addressForm['province'],
            'district' => $addressForm['district'],
            'ward' => $addressForm['ward'],
            'address_line' => $addressForm['address_line'],
        ];

        if ($addressId > 0) {
            $updated = $this->addressModel->updateForUser($userId, $addressId, $payload, $addressForm['make_default'] === '1');
            if (!$updated) {
                return ['success' => false, 'message' => 'Không tìm thấy địa chỉ cần cập nhật.'];
            }

            return ['success' => true, 'message' => 'Đã cập nhật địa chỉ.'];
        }

        $this->addressModel->createForUser($userId, $payload, $addressForm['make_default'] === '1');
        return ['success' => true, 'message' => 'Đã thêm địa chỉ mới.'];
    }

    /**
     * Confirm one mock QR payment from the user detail page.
     */
    public function confirmOnlineMockPayment(int $userId, int $orderId, bool $qrScanned): array {
        $order = $this->orderModel->getDetailByUserId($userId, $orderId);
        if (!$order) {
            return ['success' => false, 'message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền xem đơn này.'];
        }

        if ((string)$order['payment_method'] !== 'online_mock') {
            return ['success' => false, 'message' => 'Đơn hàng này không sử dụng chuyển khoản giả lập.'];
        }

        if ((string)$order['order_status'] === 'cancelled') {
            return ['success' => false, 'message' => 'Đơn hàng đã bị hủy nên không thể xác nhận thanh toán nữa.'];
        }

        if ((string)$order['payment_status'] !== 'unpaid') {
            return [
                'success' => true,
                'message' => (string)$order['payment_status'] === 'pending_review'
                    ? 'Bạn đã gửi yêu cầu thanh toán QR. Admin đã nhận được và đang duyệt.'
                    : 'Đơn hàng này đã được ghi nhận thanh toán trước đó.',
            ];
        }

        if (!$qrScanned) {
            return ['success' => false, 'message' => 'Vui lòng mô phỏng quét mã QR trước khi thanh toán.'];
        }

        $confirmed = $this->orderModel->confirmOnlineMockPaymentByUser($userId, $orderId);
        if ($confirmed) {
            return ['success' => true, 'message' => 'Đã gửi yêu cầu thanh toán QR đến admin ngay lập tức. Đơn hàng đang chờ duyệt.'];
        }

        return ['success' => false, 'message' => $this->orderModel->getLastErrorMessage() ?: 'Không thể xác nhận thanh toán lúc này. Vui lòng thử lại.'];
    }

    /**
     * Resubmit one rejected mock QR payment request.
     */
    public function resubmitOnlineMockPayment(int $userId, int $orderId): array {
        $order = $this->orderModel->getDetailByUserId($userId, $orderId);
        if (!$order) {
            return ['success' => false, 'message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền xem đơn này.'];
        }

        if ((string)$order['payment_method'] !== 'online_mock') {
            return ['success' => false, 'message' => 'Đơn hàng này không sử dụng chuyển khoản giả lập.'];
        }

        if ((string)$order['order_status'] === 'cancelled') {
            return ['success' => false, 'message' => 'Đơn hàng đã bị hủy nên không thể gửi lại yêu cầu thanh toán.'];
        }

        if ((string)$order['payment_status'] !== 'failed') {
            return ['success' => false, 'message' => 'Đơn hàng này hiện không ở trạng thái cần gửi lại yêu cầu thanh toán.'];
        }

        $resubmitted = $this->orderModel->resubmitOnlineMockPaymentByUser($userId, $orderId);
        if ($resubmitted) {
            return ['success' => true, 'message' => 'Đã gửi lại yêu cầu duyệt chuyển khoản giả lập. Vui lòng chờ admin duyệt.'];
        }

        return ['success' => false, 'message' => $this->orderModel->getLastErrorMessage() ?: 'Không thể gửi lại yêu cầu thanh toán lúc này. Vui lòng thử lại.'];
    }

    /**
     * Confirm one payment from the standalone QR portal.
     */
    public function confirmQrPortalPayment(int $orderId): array {
        $order = $this->orderModel->getOnlineMockOrderForPortal($orderId);
        if (!$order) {
            return ['success' => false, 'error' => 'Lien ket thanh toan khong hop le hoac da het han.'];
        }

        if ((string)($order['order_status'] ?? '') === 'cancelled') {
            return ['success' => false, 'error' => 'Don hang da bi huy nen khong the xac nhan thanh toan QR nua.'];
        }

        if ((string)$order['payment_status'] === 'pending_review') {
            return ['success' => true, 'message' => 'Yeu cau thanh toan da duoc gui truoc do. Admin dang xu ly.'];
        }

        if ((string)$order['payment_status'] === 'paid') {
            return ['success' => true, 'message' => 'Don hang da duoc admin duyet thanh toan.'];
        }

        if ((string)$order['payment_status'] !== 'unpaid') {
            return ['success' => false, 'error' => 'Don hang hien khong o trang thai cho thanh toan QR.'];
        }

        $submitted = $this->orderModel->confirmOnlineMockPaymentByPortal($orderId);
        if ($submitted) {
            return ['success' => true, 'message' => 'Da gui yeu cau thanh toan den admin ngay lap tuc. Vui long cho duyet.'];
        }

        return ['success' => false, 'error' => $this->orderModel->getLastErrorMessage() ?? 'Khong the gui yeu cau thanh toan luc nay. Vui long thu lai.'];
    }

    /**
     * Log the active user out.
     */
    public function logout(): void {
        $this->authService->logout();
    }
}
