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

        $cartEntries = $this->getRequestedCartEntries();
        if ($cartEntries === []) {
            return ['success' => false, 'errors' => ['general' => 'Giỏ hàng đang trống.']];
        }

        $selectedAddressId = max(0, (int)($data['selected_address_id'] ?? 0));
        $selectedAddress = $selectedAddressId > 0
            ? $this->addressModel->getByIdForUser($userId, $selectedAddressId)
            : null;
        $shouldSaveAsDefault = !empty($data['save_as_default']);

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

        if ($selectedAddress) {
            $payload['full_name'] = (string)$selectedAddress['receiver_name'];
            $payload['phone'] = (string)$selectedAddress['phone'];
            $payload['province'] = (string)$selectedAddress['province'];
            $payload['district'] = (string)$selectedAddress['district'];
            $payload['ward'] = (string)($selectedAddress['ward'] ?? '');
            $payload['address_line'] = (string)$selectedAddress['address_line'];
        }

        $errors = $this->validateCheckout($payload);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->orderModel->expireStalePendingOrdersIfNeeded();

        $conn = $this->orderModel->getConnection();
        $conn->beginTransaction();

        try {
            $summaryResult = $this->buildLockedCheckoutSummary($conn, $cartEntries);
            if (empty($summaryResult['success'])) {
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }

                return [
                    'success' => false,
                    'errors' => $summaryResult['errors'] ?? ['general' => 'Không thể kiểm tra tồn kho lúc này.'],
                    'cart_changed' => !empty($summaryResult['cart_changed']),
                ];
            }

            $summary = $summaryResult['summary'];

            $this->userModel->updateProfile($userId, [
                'full_name' => $payload['full_name'],
                'phone' => $payload['phone'],
            ]);

            if ($selectedAddress && $shouldSaveAsDefault) {
                $this->addressModel->setDefaultById($userId, (int)$selectedAddress['id']);
            } elseif (!$selectedAddress && $shouldSaveAsDefault) {
                $this->addressModel->saveDefault($userId, [
                    'receiver_name' => $payload['full_name'],
                    'phone' => $payload['phone'],
                    'province' => $payload['province'],
                    'district' => $payload['district'],
                    'ward' => $payload['ward'],
                    'address_line' => $payload['address_line'],
                ]);
            }

            $fullAddress = $this->formatAddress($payload);
            $isOnlineMock = payment_method_requires_manual_review($payload['payment_method']);
            $orderId = $this->orderModel->create([
                'user_id' => $userId,
                'order_number' => $summary['order_number'],
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
                'payment_status' => 'unpaid',
                'order_status' => 'pending',
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

            $this->reserveInventoryForOrder($conn, $orderId, $summary['order_number'], $summary['items']);

            $this->orderModel->addPayment($orderId, [
                'provider' => $payload['payment_method'],
                'transaction_code' => $isOnlineMock ? 'MOCK' . date('YmdHis') . random_int(100, 999) : null,
                'status' => 'unpaid',
                'amount' => $summary['total'],
                'paid_at' => null,
                'note' => $isOnlineMock
                    ? 'Chờ khách xác nhận chuyển khoản giả lập.'
                    : 'Thanh toán khi nhận hàng.',
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
     * Collect raw requested quantities from the session cart.
     *
     * @return array<int, int>
     */
    private function getRequestedCartEntries(): array {
        $cart = $_SESSION['cart'] ?? [];
        if (!is_array($cart)) {
            return [];
        }

        $entries = [];
        foreach ($cart as $entry) {
            $productId = (int)($entry['product_id'] ?? 0);
            $quantity = (int)($entry['quantity'] ?? 0);

            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }

            $entries[$productId] = ($entries[$productId] ?? 0) + $quantity;
        }

        ksort($entries);
        return $entries;
    }

    /**
     * Re-check and lock stock directly from the database before creating an order.
     *
     * @param PDO $conn
     * @param array<int, int> $cartEntries
     * @return array{success: bool, summary?: array<string, mixed>, errors?: array<string, string>, cart_changed?: bool}
     */
    private function buildLockedCheckoutSummary(PDO $conn, array $cartEntries): array {
        $items = [];
        $messages = [];
        $cartChanged = false;

        $stmt = $conn->prepare(
            "SELECT id, name, slug, image, price, sale_price, stock, status
             FROM products
             WHERE id = :product_id
             LIMIT 1
             FOR UPDATE"
        );

        foreach ($cartEntries as $productId => $requestedQuantity) {
            $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
            $stmt->execute();
            $product = $stmt->fetch();

            if (!$product || (string)($product['status'] ?? 'inactive') !== 'active') {
                unset($_SESSION['cart'][$productId]);
                $cartChanged = true;
                $messages[] = 'Một sản phẩm trong giỏ không còn khả dụng và đã được gỡ khỏi giỏ hàng.';
                continue;
            }

            $availableStock = max(0, (int)($product['stock'] ?? 0));
            $productName = (string)($product['name'] ?? ('SP #' . $productId));

            if ($availableStock <= 0) {
                unset($_SESSION['cart'][$productId]);
                $cartChanged = true;
                $messages[] = 'Sản phẩm "' . $productName . '" đã hết hàng và đã được gỡ khỏi giỏ.';
                continue;
            }

            if ($requestedQuantity > $availableStock) {
                $_SESSION['cart'][$productId]['quantity'] = $availableStock;
                $cartChanged = true;
                $messages[] = 'Sản phẩm "' . $productName . '" hiện chỉ còn ' . $availableStock . ' sản phẩm. Giỏ hàng đã được cập nhật lại.';
                continue;
            }

            $price = !empty($product['sale_price']) && (float)$product['sale_price'] > 0
                ? (float)$product['sale_price']
                : (float)$product['price'];

            $items[] = [
                'product_id' => $productId,
                'slug' => (string)($product['slug'] ?? ''),
                'name' => $productName,
                'image_url' => $this->resolveProductImageUrl((string)($product['image'] ?? '')),
                'price' => $price,
                'quantity' => $requestedQuantity,
                'stock' => $availableStock,
                'subtotal' => $price * $requestedQuantity,
            ];
        }

        if ($messages !== []) {
            return [
                'success' => false,
                'errors' => [
                    'general' => implode(' ', array_unique($messages)) . ' Vui lòng kiểm tra lại giỏ hàng trước khi đặt đơn.',
                ],
                'cart_changed' => $cartChanged,
            ];
        }

        if ($items === []) {
            return [
                'success' => false,
                'errors' => ['general' => 'Giỏ hàng không còn sản phẩm khả dụng để đặt hàng.'],
                'cart_changed' => $cartChanged,
            ];
        }

        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += (float)$item['subtotal'];
        }

        $shippingFee = $subtotal >= 500000 || $subtotal <= 0 ? 0.0 : 30000.0;
        $discountAmount = 0.0;

        return [
            'success' => true,
            'summary' => [
                'items' => $items,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'shipping_fee' => $shippingFee,
                'total' => $subtotal - $discountAmount + $shippingFee,
                'order_number' => $this->generateOrderNumber(),
            ],
            'cart_changed' => $cartChanged,
        ];
    }

    /**
     * Reserve inventory immediately when an order is created.
     * When inventory_batches table exists, deducts using FIFO (oldest batch first)
     * and keeps product stock synchronized.
     *
     * @param PDO $conn
     * @param int $orderId
     * @param string $orderNumber
     * @param array<int, array<string, mixed>> $items
     * @return void
     */
    private function reserveInventoryForOrder(PDO $conn, int $orderId, string $orderNumber, array $items): void {
        $batchModel = new InventoryBatch($conn);
        $useBatches = $batchModel->tableExists();

        $stockStmt = $conn->prepare(
            "UPDATE products
             SET stock = stock - :quantity,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :product_id"
        );
        $logStmt = $conn->prepare(
            "INSERT INTO inventory_logs (product_id, order_id, action, quantity, note)
             VALUES (:product_id, :order_id, 'deduct', :quantity, :note)"
        );

        foreach ($items as $item) {
            $quantity = max(0, (int)($item['quantity'] ?? 0));
            $productId = max(0, (int)($item['product_id'] ?? 0));
            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }

            if ($useBatches) {
                $batchModel->deductFifo($productId, $quantity, $orderId);
                $batchModel->syncProductStock($productId);
            } else {
                $stockStmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
                $stockStmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
                $stockStmt->execute();
            }

            $logStmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
            $logStmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
            $logStmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
            $logStmt->bindValue(':note', 'Giữ kho khi tạo đơn hàng ' . $orderNumber . '.', PDO::PARAM_STR);
            $logStmt->execute();
        }
    }

    /**
     * Normalize one product image for order snapshots.
     */
    private function resolveProductImageUrl(string $image): string {
        $image = trim($image);
        if ($image === '') {
            return image_url('products/default.jpg');
        }

        if (preg_match('#^https?://#i', $image)) {
            return $image;
        }

        return upload_url($image);
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

        if (!array_key_exists($payload['payment_method'], payment_method_catalog())) {
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
