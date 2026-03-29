<?php
/**
 * Checkout service
 */

class CheckoutService {
    private CartService $cartService;
    private Order $orderModel;
    private Address $addressModel;
    private User $userModel;

    public function __construct() {
        $this->cartService = new CartService();
        $this->orderModel = new Order();
        $this->addressModel = new Address();
        $this->userModel = new User();
    }

    /**
     * Place an order from the session cart.
     */
    public function placeOrder(int $userId, array $data): array {
        $user = $this->userModel->findById($userId);
        if (!$user) {
            return ['success' => false, 'errors' => ['general' => 'Không tìm thấy tài khoản.']];
        }

        $summary = $this->cartService->getSummary();
        if (empty($summary['items'])) {
            return ['success' => false, 'errors' => ['general' => 'Giỏ hàng đang trống.']];
        }

        $payload = [
            'full_name' => trim((string)($data['full_name'] ?? '')),
            'email' => strtolower(trim((string)($data['email'] ?? ''))),
            'phone' => trim((string)($data['phone'] ?? '')),
            'province' => trim((string)($data['province'] ?? '')),
            'district' => trim((string)($data['district'] ?? '')),
            'ward' => trim((string)($data['ward'] ?? '')),
            'address_line' => trim((string)($data['address_line'] ?? '')),
            'note' => trim((string)($data['note'] ?? '')),
            'payment_method' => (string)($data['payment_method'] ?? 'cod'),
        ];

        $errors = $this->validateCheckout($payload);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $conn = $this->orderModel->getConnection();
        $conn->beginTransaction();

        try {
            $this->userModel->updateProfile($userId, [
                'full_name' => $payload['full_name'],
                'phone' => $payload['phone'],
            ]);

            $this->addressModel->saveDefault($userId, [
                'receiver_name' => $payload['full_name'],
                'phone' => $payload['phone'],
                'province' => $payload['province'],
                'district' => $payload['district'],
                'ward' => $payload['ward'],
                'address_line' => $payload['address_line'],
            ]);

            $fullAddress = $this->formatAddress($payload);
            $isOnlineMock = $payload['payment_method'] === 'online_mock';
            $orderId = $this->orderModel->create([
                'user_id' => $userId,
                'order_number' => $this->generateOrderNumber(),
                'full_name' => $payload['full_name'],
                'email' => $payload['email'] !== '' ? $payload['email'] : (string)$user['email'],
                'phone' => $payload['phone'],
                'address' => $fullAddress,
                'note' => $payload['note'],
                'subtotal' => $summary['subtotal'],
                'discount_amount' => $summary['discount_amount'],
                'shipping_fee' => $summary['shipping_fee'],
                'total_amount' => $summary['total'],
                'coupon_code' => null,
                'payment_method' => $payload['payment_method'],
                'payment_status' => $isOnlineMock ? 'paid' : 'unpaid',
                'order_status' => $isOnlineMock ? 'confirmed' : 'pending',
            ]);

            foreach ($summary['items'] as $item) {
                $this->orderModel->addItem($orderId, [
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'product_image' => $item['image_url'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            $this->orderModel->addPayment($orderId, [
                'provider' => $payload['payment_method'],
                'transaction_code' => $isOnlineMock ? 'MOCK' . date('YmdHis') . random_int(100, 999) : null,
                'status' => $isOnlineMock ? 'paid' : 'unpaid',
                'amount' => $summary['total'],
                'paid_at' => $isOnlineMock ? date('Y-m-d H:i:s') : null,
                'note' => $isOnlineMock ? 'Mock online payment completed.' : 'Thanh toán khi nhận hàng.',
            ]);

            $conn->commit();
            $this->cartService->clear();

            $freshUser = $this->userModel->findById($userId);
            if ($freshUser) {
                $_SESSION['user_data'] = $this->userModel->withoutPassword($freshUser);
            }

            return ['success' => true, 'order_id' => $orderId];
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            error_log('CheckoutService Error: ' . $e->getMessage());
            return ['success' => false, 'errors' => ['general' => 'Không thể tạo đơn hàng lúc này.']];
        }
    }

    /**
     * Validate checkout input.
     */
    private function validateCheckout(array $payload): array {
        $errors = [];

        if ($payload['full_name'] === '') {
            $errors['full_name'] = 'Vui lòng nhập họ tên người nhận.';
        }

        if ($payload['email'] === '') {
            $errors['email'] = 'Vui lòng nhập email.';
        } elseif (!is_valid_email($payload['email'])) {
            $errors['email'] = 'Email không hợp lệ.';
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

        if (!in_array($payload['payment_method'], ['cod', 'online_mock'], true)) {
            $errors['payment_method'] = 'Phương thức thanh toán không hợp lệ.';
        }

        return $errors;
    }

    /**
     * Build address string for the order row.
     */
    private function formatAddress(array $payload): string {
        $parts = [
            $payload['address_line'],
            $payload['ward'],
            $payload['district'],
            $payload['province'],
        ];

        $parts = array_values(array_filter($parts, static fn(string $value): bool => $value !== ''));
        return implode(', ', $parts);
    }

    /**
     * Generate a unique order number.
     */
    private function generateOrderNumber(): string {
        return 'ORD' . date('YmdHis') . random_int(100, 999);
    }
}
