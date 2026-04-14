<?php
/**
 * Handle account pages such as profile, orders, and payment confirmation.
 */
class AccountController extends Controller {
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
     * Show and update the profile page.
     */
    public function profile(): Response {
        if (!is_logged_in()) {
            return $this->redirect('login.php?redirect=' . urlencode('profile.php'));
        }

        $userId = (int)get_user_id();
        $addressErrors = [];
        $addressForm = null;
        $addressFormMode = 'create';
        $editingAddressId = max(0, (int)$this->request->query('edit_address', 0));

        if ($this->request->method() === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
                set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
                return $this->redirect('profile.php');
            }

            $action = (string)$this->request->input('action', '');
            if ($action === 'update_profile') {
                $result = $this->pageService->updateProfile($userId, $_POST);
                if (!empty($result['success']) && !empty($result['fresh_user'])) {
                    $_SESSION['user_data'] = $result['fresh_user'];
                }
                set_flash(!empty($result['success']) ? 'success' : 'error', (string)$result['message']);
                return $this->redirect('profile.php');
            }

            if ($action === 'set_default_address') {
                $result = $this->pageService->setDefaultAddress($userId, max(0, (int)$this->request->input('address_id', 0)));
                set_flash(!empty($result['success']) ? 'success' : 'error', (string)$result['message']);
                return $this->redirect('profile.php#addresses');
            }

            if ($action === 'delete_address') {
                $result = $this->pageService->deleteAddress($userId, max(0, (int)$this->request->input('address_id', 0)));
                set_flash(!empty($result['success']) ? 'success' : 'error', (string)$result['message']);
                return $this->redirect('profile.php#addresses');
            }

            if ($action === 'save_address') {
                $result = $this->pageService->saveAddress($userId, $_POST);
                if (!empty($result['success'])) {
                    set_flash('success', (string)$result['message']);
                    return $this->redirect('profile.php#addresses');
                }

                if (!empty($result['message'])) {
                    set_flash('error', (string)$result['message']);
                    return $this->redirect('profile.php#addresses');
                }

                $addressErrors = $result['errors'] ?? [];
                $addressForm = $result['addressForm'] ?? null;
                $addressFormMode = (string)($result['addressFormMode'] ?? 'create');
                $editingAddressId = (int)($result['editingAddressId'] ?? 0);
            }
        }

        return $this->template(
            PUBLIC_PATH . '/profile.php',
            $this->pagePresenter->presentProfile($userId, $addressErrors, $addressForm, $addressFormMode, $editingAddressId)
        );
    }

    /**
     * Show the order history page.
     */
    public function orders(): Response {
        if (!is_logged_in()) {
            return $this->redirect('login.php?redirect=' . urlencode('orders.php'));
        }

        $userId = (int)get_user_id();
        $statusFilter = (string)$this->request->query('status', 'all');
        $page = max(1, (int)$this->request->query('page', 1));

        return $this->template(PUBLIC_PATH . '/orders.php', $this->pagePresenter->presentOrders($userId, $statusFilter, $page));
    }

    /**
     * Show the order detail page and handle payment confirmation.
     */
    public function orderDetail(?string $id = null): Response {
        if (!is_logged_in()) {
            $redirectTarget = 'order-detail.php';
            $rawOrderId = $id ?? (string)$this->request->query('id', '');
            if ($rawOrderId !== '') {
                $redirectTarget .= '?id=' . urlencode($rawOrderId);
            }

            return $this->redirect('login.php?redirect=' . urlencode($redirectTarget));
        }

        $userId = (int)get_user_id();
        $orderId = max(0, (int)($id ?? $this->request->query('id', 0)));
        if ($orderId <= 0) {
            set_flash('error', 'Không tìm thấy đơn hàng cần xem.');
            return $this->redirect('orders.php');
        }

        if ($this->request->method() === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
                set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
                return $this->redirect('order-detail.php?id=' . urlencode((string)$orderId) . '#payment-confirmation');
            }

            $action = (string)$this->request->input('action', '');
            if ($action === 'confirm_online_mock_payment') {
                $result = $this->pageService->confirmOnlineMockPayment($userId, $orderId, (string)$this->request->input('qr_scanned', '0') === '1');
                set_flash(!empty($result['success']) ? 'success' : 'error', (string)$result['message']);
                return $this->redirect('order-detail.php?id=' . urlencode((string)$orderId) . '#payment-confirmation');
            }

            if ($action === 'resubmit_online_mock_payment') {
                $result = $this->pageService->resubmitOnlineMockPayment($userId, $orderId);
                set_flash(!empty($result['success']) ? 'success' : 'error', (string)$result['message']);
                return $this->redirect('order-detail.php?id=' . urlencode((string)$orderId) . '#payment-confirmation');
            }
        }

        $viewData = $this->pagePresenter->presentOrderDetail($userId, $orderId);
        if ($viewData === null) {
            set_flash('error', 'Không tìm thấy đơn hàng hoặc bạn không có quyền xem đơn này.');
            return $this->redirect('orders.php');
        }

        return $this->template(PUBLIC_PATH . '/order-detail.php', $viewData);
    }

    /**
     * Show and submit the QR payment portal.
     */
    public function qrPay(): Response {
        $orderId = max(0, (int)$this->request->input('order_id', $this->request->query('order_id', 0)));
        $token = trim((string)$this->request->input('token', $this->request->query('token', '')));
        $portalError = null;
        $portalSuccess = null;

        if ($this->request->method() === 'POST' && (string)$this->request->input('action', '') === 'confirm_qr_payment') {
            $result = $this->pageService->confirmQrPortalPayment($orderId);
            if (!empty($result['success'])) {
                $portalSuccess = (string)$result['message'];
            } else {
                $portalError = (string)$result['error'];
            }
        }

        return $this->template(PUBLIC_PATH . '/qr-pay.php', $this->pagePresenter->presentQrPay($orderId, $token, $portalError, $portalSuccess));
    }
}
