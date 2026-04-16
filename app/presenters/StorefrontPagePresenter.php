<?php
/**
 * Build view data for legacy storefront pages that are being migrated to MVC.
 */
class StorefrontPagePresenter {
    private CartService $cartService;
    private User $userModel;
    private Address $addressModel;
    private Order $orderModel;

    public function __construct(
        ?CartService $cartService = null,
        ?User $userModel = null,
        ?Address $addressModel = null,
        ?Order $orderModel = null,
    ) {
        $this->cartService = $cartService ?? new CartService();
        $this->userModel = $userModel ?? new User();
        $this->addressModel = $addressModel ?? new Address();
        $this->orderModel = $orderModel ?? new Order();
    }

    /**
     * Build the login page state.
     */
    public function presentLogin(array $errors = [], array $old = ['identifier' => ''], string $redirectTarget = 'home.php'): array {
        $adminRedirect = str_starts_with($redirectTarget, 'admin/') ? substr($redirectTarget, 6) : 'dashboard.php';

        return [
            'pageTitle' => 'Đăng nhập - GreenSpace',
            'currentPage' => '',
            'errors' => $errors,
            'old' => $old + ['identifier' => ''],
            'redirectTarget' => $redirectTarget,
            'adminRedirect' => $adminRedirect,
        ];
    }

    /**
     * Build the signup page state.
     */
    public function presentSignup(
        array $errors = [],
        array $old = [],
        string $redirectTarget = 'home.php',
    ): array {
        return [
            'pageTitle' => 'Đăng ký - GreenSpace',
            'currentPage' => '',
            'errors' => $errors,
            'old' => array_merge([
                'full_name' => '',
                'username' => '',
                'email' => '',
                'phone' => '',
            ], $old),
            'redirectTarget' => $redirectTarget,
        ];
    }

    /**
     * Build the care guide page state.
     */
    public function presentCare(): array {
        return [
            'pageTitle' => 'Chăm sóc cây - GreenSpace',
            'currentPage' => 'care',
        ];
    }

    /**
     * Build the contact page state.
     */
    public function presentContact(array $values = [], array $errors = []): array {
        return [
            'pageTitle' => 'Liên hệ - GreenSpace',
            'currentPage' => 'contact',
            'values' => array_merge([
                'full_name' => '',
                'email' => '',
                'phone' => '',
                'subject' => '',
                'message' => '',
            ], $values),
            'errors' => $errors,
        ];
    }

    /**
     * Build the cart page state.
     */
    public function presentCart(): array {
        $summary = $this->cartService->getSummary();

        return [
            'summary' => $summary,
            'items' => $summary['items'],
            'pageTitle' => 'Giỏ hàng - GreenSpace',
            'currentPage' => '',
        ];
    }

    /**
     * Build the checkout page state.
     */
    public function presentCheckout(
        int $userId,
        array $values = [],
        array $errors = [],
        ?int $selectedAddressId = null,
    ): array {
        $summary = $this->cartService->getSummary();
        $items = $summary['items'];
        $currentUser = $this->userModel->findById($userId) ?? (get_user() ?: []);
        $savedAddresses = $this->addressModel->getAllByUserId($userId);
        $defaultAddress = $this->addressModel->getDefaultByUserId($userId);
        $defaultAddressId = (int)($defaultAddress['id'] ?? 0);
        $selectedAddressId ??= $defaultAddressId;
        $selectedAddress = $selectedAddressId > 0
            ? $this->addressModel->getByIdForUser($userId, $selectedAddressId)
            : $defaultAddress;
        $hasSavedAddress = !empty($savedAddresses);

        $defaultValues = [
            'full_name' => (string)($selectedAddress['receiver_name'] ?? $currentUser['full_name'] ?? ''),
            'email' => (string)($currentUser['email'] ?? ''),
            'phone' => (string)($selectedAddress['phone'] ?? $currentUser['phone'] ?? ''),
            'province' => (string)($selectedAddress['province'] ?? ''),
            'district' => (string)($selectedAddress['district'] ?? ''),
            'ward' => (string)($selectedAddress['ward'] ?? ''),
            'address_line' => (string)($selectedAddress['address_line'] ?? ''),
            'note' => '',
            'payment_method' => 'cod',
            'save_as_default' => $hasSavedAddress ? '0' : '1',
        ];

        $normalizedValues = array_replace($defaultValues, $values);
        if ($selectedAddress) {
            $normalizedValues['full_name'] = (string)$selectedAddress['receiver_name'];
            $normalizedValues['phone'] = (string)$selectedAddress['phone'];
            $normalizedValues['province'] = (string)$selectedAddress['province'];
            $normalizedValues['district'] = (string)$selectedAddress['district'];
            $normalizedValues['ward'] = (string)($selectedAddress['ward'] ?? '');
            $normalizedValues['address_line'] = (string)$selectedAddress['address_line'];
        }

        $addressMap = [];
        foreach ($savedAddresses as $address) {
            $addressMap[(int)$address['id']] = [
                'id' => (int)$address['id'],
                'receiver_name' => (string)$address['receiver_name'],
                'phone' => (string)$address['phone'],
                'province' => (string)$address['province'],
                'district' => (string)$address['district'],
                'ward' => (string)($address['ward'] ?? ''),
                'address_line' => (string)$address['address_line'],
            ];
        }

        return [
            'summary' => $summary,
            'items' => $items,
            'currentUser' => $currentUser,
            'savedAddresses' => $savedAddresses,
            'defaultAddress' => $defaultAddress,
            'defaultAddressId' => $defaultAddressId,
            'selectedAddressId' => $selectedAddressId,
            'selectedAddress' => $selectedAddress,
            'hasSavedAddress' => $hasSavedAddress,
            'values' => $normalizedValues,
            'errors' => $errors,
            'addressMap' => $addressMap,
            'paymentOptions' => payment_checkout_options(),
            'pageTitle' => 'Thanh toán - GreenSpace',
            'currentPage' => '',
        ];
    }

    /**
     * Build the order listing page state.
     */
    public function presentOrders(int $userId, string $statusFilter = 'all', int $page = 1): array {
        $statusOptions = [
            'all' => 'Tất cả',
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'processing' => 'Đang chuẩn bị',
            'shipping' => 'Đang giao',
            'delivered' => 'Đã giao',
            'cancelled' => 'Đã hủy',
        ];

        if (!array_key_exists($statusFilter, $statusOptions)) {
            $statusFilter = 'all';
        }

        $perPage = 6;
        $page = max(1, $page);
        $totalOrders = $this->orderModel->countByUserId($userId, $statusFilter);
        $totalPages = max(1, (int)ceil($totalOrders / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        return [
            'statusOptions' => $statusOptions,
            'statusFilter' => $statusFilter,
            'page' => $page,
            'stats' => $this->orderModel->getUserOrderStats($userId),
            'totalOrders' => $totalOrders,
            'totalPages' => $totalPages,
            'orders' => $this->orderModel->getPaginatedByUserId($userId, $perPage, $offset, $statusFilter),
            'pageTitle' => 'Đơn hàng của tôi - GreenSpace',
            'currentPage' => '',
        ];
    }

    /**
     * Build the order detail page state.
     */
    public function presentOrderDetail(int $userId, int $orderId): ?array {
        $order = $this->orderModel->getDetailByUserId($userId, $orderId);
        if (!$order) {
            return null;
        }

        $paymentMethod = (string)$order['payment_method'];
        $isOnlineMockOrder = payment_method_requires_manual_review($paymentMethod);
        $isCancelledOrder = (string)$order['order_status'] === 'cancelled';
        $canConfirmMockPayment = $isOnlineMockOrder && !$isCancelledOrder && (string)$order['payment_status'] === 'unpaid';
        $canResubmitMockPayment = $isOnlineMockOrder && !$isCancelledOrder && (string)$order['payment_status'] === 'failed';
        $canCancelOrder = !$isCancelledOrder
            && (string)$order['order_status'] === 'pending'
            && in_array((string)$order['payment_status'], ['unpaid', 'failed'], true);
        $mockQrToken = build_qr_payment_token($orderId, $userId, (string)$order['order_number']);
        $mockQrPayUrl = 'qr-pay.php?order_id=' . urlencode((string)$orderId) . '&token=' . urlencode($mockQrToken);

        return [
            'order' => $order,
            'orderStatus' => $this->orderStatusMeta((string)$order['order_status']),
            'paymentStatus' => $this->paymentStatusMeta((string)$order['payment_status']),
            'payment' => $order['payment'] ?? null,
            'orderItems' => $order['items'] ?? [],
            'paymentMethod' => $paymentMethod,
            'isOnlineMockOrder' => $isOnlineMockOrder,
            'isCancelledOrder' => $isCancelledOrder,
            'canConfirmMockPayment' => $canConfirmMockPayment,
            'canResubmitMockPayment' => $canResubmitMockPayment,
            'canCancelOrder' => $canCancelOrder,
            'mockBankName' => 'GreenSpace Virtual Bank',
            'mockAccountNumber' => '1021182026',
            'mockAccountName' => 'CONG TY GREENSPACE DEMO',
            'mockTransferContent' => (string)$order['order_number'],
            'mockTransferAmount' => (int)round((float)$order['total_amount']),
            'mockQrToken' => $mockQrToken,
            'mockQrPayUrl' => $mockQrPayUrl,
            'mockQrPayload' => $mockQrPayUrl,
            'mockQrImageUrl' => 'https://quickchart.io/qr?size=300&margin=1&text=' . rawurlencode($mockQrPayUrl),
            'pageTitle' => 'Chi tiết đơn hàng - GreenSpace',
            'currentPage' => '',
        ];
    }

    /**
     * Build the profile page state.
     */
    public function presentProfile(
        int $userId,
        array $addressErrors = [],
        ?array $addressForm = null,
        string $addressFormMode = 'create',
        int $editingAddressId = 0,
    ): array {
        $user = $this->userModel->findById($userId) ?? (get_user() ?: []);
        $addressForm ??= [
            'receiver_name' => (string)($user['full_name'] ?? ''),
            'phone' => (string)($user['phone'] ?? ''),
            'province' => '',
            'district' => '',
            'ward' => '',
            'address_line' => '',
            'make_default' => '1',
        ];

        return [
            'user' => $user,
            'addressErrors' => $addressErrors,
            'addressForm' => $addressForm,
            'addressFormMode' => $addressFormMode,
            'editingAddressId' => $editingAddressId,
            'addresses' => $this->addressModel->getAllByUserId($userId),
            'defaultAddress' => $this->addressModel->getDefaultByUserId($userId),
            'orders' => $this->orderModel->getByUserId($userId, 8),
            'pageTitle' => 'Profile - GreenSpace',
            'currentPage' => '',
        ];
    }

    /**
     * Build the QR payment portal page state.
     */
    public function presentQrPay(int $orderId, string $token, ?string $portalError = null, ?string $portalSuccess = null): array {
        $order = $orderId > 0 ? $this->orderModel->getOnlineMockOrderForPortal($orderId) : null;
        $isValidToken = $order
            ? hash_equals(build_qr_payment_token((int)$order['id'], (int)$order['user_id'], (string)$order['order_number']), $token)
            : false;

        if ((!$order || !$isValidToken) && $portalError === null) {
            $portalError = 'Lien ket thanh toan khong hop le hoac da het han.';
        }

        return [
            'order' => $order,
            'token' => $token,
            'portalError' => $portalError,
            'portalSuccess' => $portalSuccess,
            'pageTitle' => 'Thanh toan QR mo phong - GreenSpace',
        ];
    }

    /**
     * Build one status pill config for orders.
     */
    private function orderStatusMeta(string $status): array {
        $map = [
            'pending' => ['label' => 'Chờ xác nhận', 'class' => 'bg-[#fff7e8] text-[#b7791f]'],
            'confirmed' => ['label' => 'Đã xác nhận', 'class' => 'bg-[#edf8f1] text-[#2e9b63]'],
            'processing' => ['label' => 'Đang chuẩn bị', 'class' => 'bg-[#eef4ff] text-[#3758c7]'],
            'shipping' => ['label' => 'Đang giao', 'class' => 'bg-[#eef6ff] text-[#2563eb]'],
            'delivered' => ['label' => 'Đã giao', 'class' => 'bg-[#eafaf0] text-[#157347]'],
            'completed' => ['label' => 'Hoàn tất', 'class' => 'bg-[#eafaf0] text-[#157347]'],
            'cancelled' => ['label' => 'Đã hủy', 'class' => 'bg-[#fdecec] text-[#c43d3d]'],
        ];

        return $map[$status] ?? ['label' => ucfirst($status), 'class' => 'bg-[#f2f4f3] text-text-secondary'];
    }

    /**
     * Build one payment status pill config.
     */
    private function paymentStatusMeta(string $status): array {
        $map = [
            'paid' => ['label' => 'Đã thanh toán', 'class' => 'bg-[#edf8f1] text-[#2e9b63]'],
            'pending_review' => ['label' => 'Chờ admin duyệt', 'class' => 'bg-[#fff7e8] text-[#b7791f]'],
            'unpaid' => ['label' => 'Chưa thanh toán', 'class' => 'bg-[#f2f4f3] text-text-secondary'],
            'failed' => ['label' => 'Thanh toán lỗi', 'class' => 'bg-[#fdecec] text-[#c43d3d]'],
            'refunded' => ['label' => 'Đã hoàn tiền', 'class' => 'bg-[#eef4ff] text-[#3758c7]'],
        ];

        return $map[$status] ?? ['label' => ucfirst($status), 'class' => 'bg-[#f2f4f3] text-text-secondary'];
    }
}
