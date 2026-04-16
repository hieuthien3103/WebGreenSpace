<?php
/**
 * Handle the checkout page.
 */
class CheckoutController extends Controller {
    public function __construct(
        ?Request $request = null,
        private ?StorefrontPageService $pageService = null,
        private ?StorefrontPagePresenter $pagePresenter = null,
        private ?CartService $cartService = null,
    ) {
        parent::__construct($request);
        $this->pageService ??= new StorefrontPageService();
        $this->pagePresenter ??= new StorefrontPagePresenter();
        $this->cartService ??= new CartService();
    }

    /**
     * Show or submit checkout.
     */
    public function index(): Response {
        if (!is_logged_in()) {
            return $this->redirect('login.php?redirect=' . urlencode('checkout.php'));
        }

        if ($this->cartService->isEmpty()) {
            set_flash('error', 'Giỏ hàng đang trống. Vui lòng thêm sản phẩm trước khi thanh toán.');
            return $this->redirect('cart.php');
        }

        $userId = (int)get_user_id();
        $draft = null;
        if (is_array($_SESSION['checkout_quick_draft'] ?? null)) {
            $draft = $_SESSION['checkout_quick_draft'];
            unset($_SESSION['checkout_quick_draft']);
        }
        $values = [];
        $errors = [];
        $selectedAddressId = max(0, (int)($this->request->query('saved_address', 0)));

        if (is_array($draft)) {
            $values = [
                'email' => (string)($draft['email'] ?? ''),
                'note' => (string)($draft['note'] ?? ''),
                'payment_method' => (string)($draft['payment_method'] ?? 'cod'),
                'save_as_default' => !empty($draft['save_as_default']) ? '1' : '0',
            ];
        }

        if ($this->request->method() === 'POST') {
            $selectedAddressId = max(0, (int)$this->request->input('selected_address_id', 0));
            $values = [
                'full_name' => trim((string)$this->request->input('full_name', '')),
                'email' => trim((string)$this->request->input('email', '')),
                'phone' => trim((string)$this->request->input('phone', '')),
                'province' => trim((string)$this->request->input('province', '')),
                'district' => trim((string)$this->request->input('district', '')),
                'ward' => trim((string)$this->request->input('ward', '')),
                'address_line' => trim((string)$this->request->input('address_line', '')),
                'note' => trim((string)$this->request->input('note', '')),
                'payment_method' => trim((string)$this->request->input('payment_method', 'cod')),
                'save_as_default' => !empty($_POST['save_as_default']) ? '1' : '0',
            ];

            if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
                $errors['general'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
            } else {
                $action = (string)$this->request->input('action', 'place_order');
                if ($action === 'quick_save_address') {
                    if ($selectedAddressId > 0) {
                        $errors['address_book'] = 'Địa chỉ này đã có trong sổ địa chỉ. Hãy chọn "Nhập địa chỉ mới" nếu bạn muốn lưu thêm.';
                    } else {
                        $result = $this->pageService->quickSaveCheckoutAddress($userId, $_POST);
                        if (!empty($result['success'])) {
                            if (!empty($result['draft_data'])) {
                                $_SESSION['checkout_quick_draft'] = $result['draft_data'];
                            }
                            set_flash('success', (string)$result['message']);
                            return $this->redirect('checkout.php?saved_address=' . urlencode((string)$result['address_id']) . '#saved-addresses');
                        }

                        $errors = array_merge($errors, $result['errors'] ?? ['address_book' => 'Không thể lưu nhanh địa chỉ lúc này. Vui lòng thử lại.']);
                    }
                } else {
                    $result = $this->pageService->placeOrder($userId, $_POST);
                    if (!empty($result['success'])) {
                        $orderId = max(0, (int)($result['order_id'] ?? 0));
                        $paymentMethod = (string)($this->request->input('payment_method', ''));
                        if (payment_method_requires_manual_review($paymentMethod) && $orderId > 0) {
                            set_flash('success', 'Đơn hàng đã được tạo. Vui lòng xác nhận sau khi bạn hoàn tất chuyển khoản giả lập.');
                            return $this->redirect('order-detail.php?id=' . urlencode((string)$orderId) . '#payment-confirmation');
                        }

                        set_flash('success', 'Đặt hàng thành công. Đơn của bạn đã được tạo.');
                        return $this->redirect('orders.php');
                    }

                    if (!empty($result['cart_changed'])) {
                        set_flash('error', (string)($result['errors']['general'] ?? 'Giỏ hàng vừa thay đổi tồn kho. Vui lòng kiểm tra lại trước khi đặt đơn.'));
                        return $this->redirect('cart.php');
                    }

                    $errors = $result['errors'] ?? ['general' => 'Không thể thanh toán lúc này.'];
                }
            }
        }

        return $this->template(
            PUBLIC_PATH . '/checkout.php',
            $this->pagePresenter->presentCheckout($userId, $values, $errors, $selectedAddressId > 0 ? $selectedAddressId : null)
        );
    }
}
