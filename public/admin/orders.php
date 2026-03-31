<?php
require_once __DIR__ . '/bootstrap.php';

function admin_orders_query(array $params): string {
    $filtered = [];

    foreach ($params as $key => $value) {
        if ($value === null || $value === '' || $value === 'all') {
            continue;
        }

        if ($key === 'page' && (int)$value <= 1) {
            continue;
        }

        if ($key === 'view' && (int)$value <= 0) {
            continue;
        }

        $filtered[$key] = $value;
    }

    $query = http_build_query($filtered);
    return $query !== '' ? '?' . $query : '';
}

function admin_render_order_state_fields(array $params): void {
    foreach ($params as $key => $value) {
        if ($value === null || $value === '') {
            continue;
        }

        echo '<input type="hidden" name="' . clean((string)$key) . '" value="' . clean((string)$value) . '">';
    }
}

function admin_order_payment_status_meta(string $status): array {
    return match ($status) {
        'paid' => ['label' => 'Đã thanh toán', 'class' => 'bg-[#edf8f1] text-[#2e9b63]'],
        'pending_review' => ['label' => 'Chờ duyệt', 'class' => 'bg-[#fff7e8] text-[#b7791f]'],
        'failed' => ['label' => 'Thanh toán lỗi', 'class' => 'bg-[#fdecec] text-[#c43d3d]'],
        default => ['label' => 'Chưa thanh toán', 'class' => 'bg-[#f2f4f3] text-[#5d6d63]'],
    };
}

function admin_order_status_meta(string $status): array {
    return match ($status) {
        'pending' => ['label' => 'Chờ xác nhận', 'class' => 'bg-[#fff7e8] text-[#b7791f]'],
        'confirmed' => ['label' => 'Đã xác nhận', 'class' => 'bg-[#edf8f1] text-[#2e9b63]'],
        'processing' => ['label' => 'Đang chuẩn bị', 'class' => 'bg-[#eef4ff] text-[#3758c7]'],
        'shipping' => ['label' => 'Đang giao', 'class' => 'bg-[#eef6ff] text-[#2563eb]'],
        'delivered' => ['label' => 'Đã giao', 'class' => 'bg-[#eafaf0] text-[#157347]'],
        'cancelled' => ['label' => 'Đã hủy', 'class' => 'bg-[#fdecec] text-[#c43d3d]'],
        default => ['label' => ucfirst($status), 'class' => 'bg-[#f2f4f3] text-[#5d6d63]'],
    };
}

function admin_order_status_options(): array {
    return [
        'all' => 'Tất cả trạng thái đơn',
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'processing' => 'Đang chuẩn bị',
        'shipping' => 'Đang giao',
        'delivered' => 'Đã giao',
        'cancelled' => 'Đã hủy',
    ];
}

function admin_payment_status_options(): array {
    return [
        'all' => 'Tất cả thanh toán',
        'unpaid' => 'Chưa thanh toán',
        'pending_review' => 'Chờ duyệt',
        'paid' => 'Đã thanh toán',
        'failed' => 'Thanh toán lỗi',
    ];
}

$orderModel = new Order();

if (($_GET['ajax'] ?? '') === 'pending_count') {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'pending_count' => $orderModel->countAdminOnlineMockOrdersByPaymentStatus('pending_review'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

$orderStatusOptions = admin_order_status_options();
$paymentStatusOptions = admin_payment_status_options();

$search = trim((string)($_GET['q'] ?? ''));
$orderStatusFilter = (string)($_GET['order_status'] ?? 'all');
$paymentStatusFilter = (string)($_GET['payment_status'] ?? 'all');
$page = max(1, (int)($_GET['page'] ?? 1));
$viewId = max(0, (int)($_GET['view'] ?? 0));
$perPage = 12;

if (!isset($orderStatusOptions[$orderStatusFilter])) {
    $orderStatusFilter = 'all';
}

if (!isset($paymentStatusOptions[$paymentStatusFilter])) {
    $paymentStatusFilter = 'all';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $returnState = [
        'q' => trim((string)($_POST['q'] ?? '')),
        'order_status' => (string)($_POST['order_status'] ?? 'all'),
        'payment_status' => (string)($_POST['payment_status'] ?? 'all'),
        'page' => max(1, (int)($_POST['page'] ?? 1)),
        'view' => max(0, (int)($_POST['view'] ?? 0)),
    ];

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
        redirect('orders.php' . admin_orders_query($returnState));
    }

    $action = (string)($_POST['action'] ?? '');
    $targetOrderId = max(0, (int)($_POST['order_id'] ?? 0));
    if ($targetOrderId > 0) {
        $returnState['view'] = $targetOrderId;
    }

    if ($action === 'update_order_status') {
        $nextStatus = (string)($_POST['next_status'] ?? '');
        $updated = $targetOrderId > 0 ? $orderModel->updateAdminOrderStatus($targetOrderId, $nextStatus) : false;

        if ($updated) {
            $statusMeta = admin_order_status_meta($nextStatus);
            set_flash('success', 'Đã cập nhật trạng thái đơn hàng sang "' . $statusMeta['label'] . '".');
        } else {
            set_flash('error', $orderModel->getLastErrorMessage() ?? 'Không thể cập nhật trạng thái đơn hàng lúc này.');
        }

        redirect('orders.php' . admin_orders_query($returnState) . '#order-detail');
    }

    if ($action === 'approve_online_mock_payment') {
        $approved = $targetOrderId > 0 ? $orderModel->approveOnlineMockPaymentByAdmin($targetOrderId) : false;

        if ($approved) {
            set_flash('success', 'Đã duyệt chuyển khoản giả lập và cập nhật trạng thái thanh toán.');
        } else {
            set_flash('error', $orderModel->getLastErrorMessage() ?? 'Không thể duyệt thanh toán lúc này.');
        }

        redirect('orders.php' . admin_orders_query($returnState) . '#order-detail');
    }

    if ($action === 'reject_online_mock_payment') {
        $reason = trim((string)($_POST['reject_reason'] ?? ''));

        if ($reason === '') {
            set_flash('error', 'Vui lòng nhập lý do từ chối duyệt.');
            redirect('orders.php' . admin_orders_query($returnState) . '#order-detail');
        }

        $rejected = $targetOrderId > 0 ? $orderModel->rejectOnlineMockPaymentByAdmin($targetOrderId, $reason) : false;

        if ($rejected) {
            set_flash('success', 'Đã từ chối yêu cầu chuyển khoản giả lập.');
        } else {
            set_flash('error', $orderModel->getLastErrorMessage() ?? 'Không thể từ chối thanh toán lúc này.');
        }

        redirect('orders.php' . admin_orders_query($returnState) . '#order-detail');
    }
}

$stats = $orderModel->getAdminStats();
$totalOrders = $orderModel->getAdminTotal($search, $orderStatusFilter, $paymentStatusFilter);
$totalPages = max(1, (int)ceil($totalOrders / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$orders = $orderModel->getAdminList($search, $orderStatusFilter, $paymentStatusFilter, $perPage, $offset);

$viewOrder = null;
if ($viewId > 0) {
    $viewOrder = $orderModel->getAdminDetailById($viewId);
    if (!$viewOrder) {
        set_flash('error', 'Không tìm thấy đơn hàng cần xem chi tiết.');
        redirect('orders.php' . admin_orders_query([
            'q' => $search,
            'order_status' => $orderStatusFilter,
            'payment_status' => $paymentStatusFilter,
            'page' => $page,
        ]));
    }
}

$currentState = [
    'q' => $search,
    'order_status' => $orderStatusFilter,
    'payment_status' => $paymentStatusFilter,
    'page' => $page,
    'view' => $viewId,
];

render_admin_header('Quản lý đơn hàng');
?>

<div class="space-y-8">
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[1.5rem] border border-[#d9e9de] bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-[#587766]">Tổng đơn hàng</p>
            <p class="mt-3 text-3xl font-extrabold text-[#102118]"><?= clean((string)$stats['total_orders']) ?></p>
            <p class="mt-2 text-sm text-[#6e8d7b]">Toàn bộ đơn đang lưu trong hệ thống.</p>
        </article>
        <article class="rounded-[1.5rem] border border-[#d9e9de] bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-[#587766]">Đơn đang xử lý</p>
            <p class="mt-3 text-3xl font-extrabold text-[#b7791f]"><?= clean((string)($stats['pending_orders'] + $stats['confirmed_orders'] + $stats['processing_orders'])) ?></p>
            <p class="mt-2 text-sm text-[#6e8d7b]">Bao gồm chờ xác nhận, đã xác nhận và đang chuẩn bị.</p>
        </article>
        <article class="rounded-[1.5rem] border border-[#d9e9de] bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-[#587766]">Đang giao</p>
            <p class="mt-3 text-3xl font-extrabold text-[#2563eb]"><?= clean((string)$stats['shipping_orders']) ?></p>
            <p class="mt-2 text-sm text-[#6e8d7b]">Các đơn đã rời kho và đang trên đường giao.</p>
        </article>
        <article class="rounded-[1.5rem] border border-[#d9e9de] bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-[#587766]">Chờ duyệt thanh toán</p>
            <p class="mt-3 text-3xl font-extrabold text-[#2e9b63]"><?= clean((string)$stats['pending_payment_reviews']) ?></p>
            <p class="mt-2 text-sm text-[#6e8d7b]">Đơn chuyển khoản giả lập cần admin xác nhận.</p>
        </article>
    </section>

    <section class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Bộ lọc</p>
                <h2 class="mt-2 text-2xl font-extrabold text-[#102118]">Tra cứu và theo dõi đơn hàng</h2>
            </div>
            <p class="text-sm text-[#6e8d7b]">Kết quả hiện tại: <strong class="text-[#102118]"><?= clean((string)$totalOrders) ?></strong> đơn</p>
        </div>

        <form method="GET" class="mt-6 grid gap-4 lg:grid-cols-[1.3fr_1fr_1fr_auto]">
            <div class="space-y-2">
                <label for="q" class="text-sm font-semibold text-[#102118]">Tìm theo mã đơn, tên, email, số điện thoại</label>
                <input id="q" name="q" type="text" value="<?= clean($search) ?>" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="ORD2026..., Nguyễn Văn A...">
            </div>
            <div class="space-y-2">
                <label for="order_status" class="text-sm font-semibold text-[#102118]">Trạng thái đơn</label>
                <select id="order_status" name="order_status" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                    <?php foreach ($orderStatusOptions as $value => $label): ?>
                        <option value="<?= clean($value) ?>" <?= $orderStatusFilter === $value ? 'selected' : '' ?>><?= clean($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="space-y-2">
                <label for="payment_status" class="text-sm font-semibold text-[#102118]">Thanh toán</label>
                <select id="payment_status" name="payment_status" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                    <?php foreach ($paymentStatusOptions as $value => $label): ?>
                        <option value="<?= clean($value) ?>" <?= $paymentStatusFilter === $value ? 'selected' : '' ?>><?= clean($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-3 lg:items-end">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#102118] px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#1f3b2d]">
                    Lọc đơn
                </button>
                <a href="orders.php" class="inline-flex items-center justify-center rounded-2xl border border-[#d9e9de] px-5 py-3 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">
                    Xóa lọc
                </a>
            </div>
        </form>
    </section>

    <section class="grid gap-8 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Danh sách đơn</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-[#102118]">Quản lý đơn hàng</h2>
                </div>
            </div>

            <?php if (empty($orders)): ?>
                <div class="mt-6 rounded-[1.25rem] border border-dashed border-[#d9e9de] px-5 py-10 text-center text-sm text-[#6e8d7b]">
                    Không có đơn hàng nào khớp với bộ lọc hiện tại.
                </div>
            <?php else: ?>
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-[#edf4ef] text-left text-[#6e8d7b]">
                                <th class="pb-3 pr-4 font-semibold">Mã đơn</th>
                                <th class="pb-3 pr-4 font-semibold">Khách hàng</th>
                                <th class="pb-3 pr-4 font-semibold">Tổng tiền</th>
                                <th class="pb-3 pr-4 font-semibold">Thanh toán</th>
                                <th class="pb-3 pr-4 font-semibold">Trạng thái</th>
                                <th class="pb-3 font-semibold">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <?php
                                $paymentMeta = admin_order_payment_status_meta((string)$order['payment_status']);
                                $orderMeta = admin_order_status_meta((string)$order['order_status']);
                                $isActiveRow = $viewId === (int)$order['id'];
                                ?>
                                <tr class="border-b border-[#f4f8f5] last:border-b-0 <?= $isActiveRow ? 'bg-[#f8fbf9]' : '' ?>">
                                    <td class="py-4 pr-4">
                                        <p class="font-semibold text-[#102118]"><?= clean($order['order_number']) ?></p>
                                        <p class="mt-1 text-xs text-[#6e8d7b]"><?= format_date($order['created_at'], 'd/m/Y H:i') ?></p>
                                    </td>
                                    <td class="py-4 pr-4">
                                        <p class="font-semibold text-[#102118]"><?= clean($order['full_name']) ?></p>
                                        <p class="mt-1 text-xs text-[#6e8d7b]"><?= clean($order['phone']) ?></p>
                                    </td>
                                    <td class="py-4 pr-4 font-semibold text-[#2e9b63]"><?= format_currency((float)$order['total_amount']) ?></td>
                                    <td class="py-4 pr-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold <?= clean($paymentMeta['class']) ?>"><?= clean($paymentMeta['label']) ?></span>
                                    </td>
                                    <td class="py-4 pr-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold <?= clean($orderMeta['class']) ?>"><?= clean($orderMeta['label']) ?></span>
                                    </td>
                                    <td class="py-4">
                                        <a href="orders.php<?= clean(admin_orders_query([
                                            'q' => $search,
                                            'order_status' => $orderStatusFilter,
                                            'payment_status' => $paymentStatusFilter,
                                            'page' => $page,
                                            'view' => (int)$order['id'],
                                        ])) ?>#order-detail" class="inline-flex items-center rounded-full border border-[#d9e9de] px-4 py-2 text-xs font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">
                                            <?= $isActiveRow ? 'Đang xem' : 'Xem chi tiết' ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-sm text-[#6e8d7b]">Trang <?= clean((string)$page) ?> / <?= clean((string)$totalPages) ?></p>
                        <div class="flex flex-wrap gap-2">
                            <?php if ($page > 1): ?>
                                <a href="orders.php<?= clean(admin_orders_query([
                                    'q' => $search,
                                    'order_status' => $orderStatusFilter,
                                    'payment_status' => $paymentStatusFilter,
                                    'page' => $page - 1,
                                    'view' => $viewId,
                                ])) ?>" class="inline-flex items-center rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">
                                    Trang trước
                                </a>
                            <?php endif; ?>
                            <?php if ($page < $totalPages): ?>
                                <a href="orders.php<?= clean(admin_orders_query([
                                    'q' => $search,
                                    'order_status' => $orderStatusFilter,
                                    'payment_status' => $paymentStatusFilter,
                                    'page' => $page + 1,
                                    'view' => $viewId,
                                ])) ?>" class="inline-flex items-center rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">
                                    Trang sau
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div id="order-detail" class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
            <?php if (!$viewOrder): ?>
                <div class="flex h-full min-h-[420px] items-center justify-center rounded-[1.25rem] border border-dashed border-[#d9e9de] px-6 text-center text-sm text-[#6e8d7b]">
                    Chọn một đơn hàng ở danh sách bên trái để xem chi tiết, cập nhật trạng thái hoặc duyệt thanh toán.
                </div>
            <?php else: ?>
                <?php
                $viewPaymentMeta = admin_order_payment_status_meta((string)$viewOrder['payment_status']);
                $viewOrderMeta = admin_order_status_meta((string)$viewOrder['order_status']);
                $canReviewPayment = (string)$viewOrder['payment_method'] === 'online_mock' && (string)$viewOrder['payment_status'] === 'pending_review';
                ?>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Chi tiết đơn</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-[#102118]"><?= clean($viewOrder['order_number']) ?></h2>
                        <p class="mt-2 text-sm text-[#6e8d7b]">Tạo lúc <?= format_date($viewOrder['created_at'], 'd/m/Y H:i') ?></p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full px-3 py-1 text-xs font-semibold <?= clean($viewPaymentMeta['class']) ?>"><?= clean($viewPaymentMeta['label']) ?></span>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold <?= clean($viewOrderMeta['class']) ?>"><?= clean($viewOrderMeta['label']) ?></span>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <article class="rounded-[1.25rem] border border-[#edf4ef] bg-[#f8fbf9] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#6e8d7b]">Khách hàng</p>
                        <p class="mt-3 font-bold text-[#102118]"><?= clean($viewOrder['full_name']) ?></p>
                        <p class="mt-1 text-sm text-[#456a57]"><?= clean($viewOrder['email']) ?></p>
                        <p class="mt-1 text-sm text-[#456a57]"><?= clean($viewOrder['phone']) ?></p>
                        <p class="mt-3 text-sm leading-6 text-[#6e8d7b]"><?= nl2br(clean($viewOrder['address'])) ?></p>
                    </article>
                    <article class="rounded-[1.25rem] border border-[#edf4ef] bg-[#f8fbf9] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#6e8d7b]">Tổng quan</p>
                        <div class="mt-3 space-y-2 text-sm text-[#456a57]">
                            <p>Tổng tiền: <strong class="text-[#102118]"><?= format_currency((float)$viewOrder['total_amount']) ?></strong></p>
                            <p>Thanh toán: <strong class="text-[#102118]"><?= clean((string)$viewOrder['payment_method']) ?></strong></p>
                            <p>Tài khoản: <strong class="text-[#102118]"><?= clean((string)($viewOrder['account_full_name'] ?: $viewOrder['username'])) ?></strong></p>
                            <p>Số mặt hàng: <strong class="text-[#102118]"><?= clean((string)count($viewOrder['items'])) ?></strong></p>
                        </div>
                        <?php if (!empty($viewOrder['note'])): ?>
                            <div class="mt-4 rounded-xl border border-[#d9e9de] bg-white px-4 py-3 text-sm text-[#6e8d7b]">
                                <p class="font-semibold text-[#102118]">Ghi chú khách hàng</p>
                                <p class="mt-2 leading-6"><?= nl2br(clean((string)$viewOrder['note'])) ?></p>
                            </div>
                        <?php endif; ?>
                    </article>
                </div>

                <section class="mt-6 rounded-[1.25rem] border border-[#edf4ef] p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#6e8d7b]">Cập nhật trạng thái</p>
                            <h3 class="mt-2 text-lg font-extrabold text-[#102118]">Luồng xử lý đơn hàng</h3>
                        </div>
                    </div>

                    <form method="POST" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                        <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                        <input type="hidden" name="action" value="update_order_status">
                        <input type="hidden" name="order_id" value="<?= clean((string)$viewOrder['id']) ?>">
                        <?php admin_render_order_state_fields($currentState); ?>
                        <div class="flex-1 space-y-2">
                            <label for="next_status" class="text-sm font-semibold text-[#102118]">Trạng thái mới</label>
                            <select id="next_status" name="next_status" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                                <?php foreach (admin_order_status_options() as $value => $label): ?>
                                    <?php if ($value === 'all') { continue; } ?>
                                    <option value="<?= clean($value) ?>" <?= (string)$viewOrder['order_status'] === $value ? 'selected' : '' ?>><?= clean($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#102118] px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#1f3b2d]">
                            Cập nhật trạng thái
                        </button>
                    </form>
                </section>

                <?php if ($canReviewPayment): ?>
                    <section class="mt-6 rounded-[1.25rem] border border-[#f5e4c7] bg-[#fffaf1] p-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b7791f]">Thanh toán chờ duyệt</p>
                            <h3 class="mt-2 text-lg font-extrabold text-[#102118]">Duyệt hoặc từ chối chuyển khoản giả lập</h3>
                        </div>

                        <div class="mt-4 rounded-xl border border-[#f3ead7] bg-white px-4 py-3 text-sm text-[#6e8d7b]">
                            <p>Mã giao dịch: <strong class="text-[#102118]"><?= clean((string)($viewOrder['payment']['transaction_code'] ?? 'Chưa có')) ?></strong></p>
                            <p class="mt-2">Ghi chú: <?= clean((string)($viewOrder['payment']['note'] ?? 'Không có ghi chú.')) ?></p>
                        </div>

                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                                <input type="hidden" name="action" value="approve_online_mock_payment">
                                <input type="hidden" name="order_id" value="<?= clean((string)$viewOrder['id']) ?>">
                                <?php admin_render_order_state_fields($currentState); ?>
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-[#102118] px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#1f3b2d]">
                                    Duyệt thanh toán
                                </button>
                            </form>

                            <form method="POST" class="space-y-2">
                                <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                                <input type="hidden" name="action" value="reject_online_mock_payment">
                                <input type="hidden" name="order_id" value="<?= clean((string)$viewOrder['id']) ?>">
                                <?php admin_render_order_state_fields($currentState); ?>
                                <textarea name="reject_reason" rows="3" required maxlength="255" class="w-full rounded-2xl border border-[#f3d7d7] px-4 py-3 text-sm text-[#102118] focus:border-[#b24141] focus:ring-[#b24141]" placeholder="Ví dụ: Nội dung chuyển khoản không khớp mã đơn"></textarea>
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl border border-[#f3d7d7] bg-[#fff5f5] px-5 py-3 text-sm font-semibold text-[#b24141] transition-colors hover:bg-[#ffeaea]">
                                    Từ chối duyệt
                                </button>
                            </form>
                        </div>
                    </section>
                <?php endif; ?>

                <section class="mt-6 rounded-[1.25rem] border border-[#edf4ef] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#6e8d7b]">Sản phẩm trong đơn</p>
                    <div class="mt-4 space-y-3">
                        <?php foreach ($viewOrder['items'] as $item): ?>
                            <article class="flex items-center gap-4 rounded-[1rem] border border-[#edf4ef] p-3">
                                <div class="h-16 w-16 overflow-hidden rounded-2xl bg-[#f4f8f5]">
                                    <?php if (!empty($item['product_image'])): ?>
                                        <img src="<?= clean((string)$item['product_image']) ?>" alt="<?= clean($item['product_name']) ?>" class="h-full w-full object-cover">
                                    <?php else: ?>
                                        <div class="flex h-full w-full items-center justify-center text-xs text-[#6e8d7b]">No image</div>
                                    <?php endif; ?>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-[#102118]"><?= clean($item['product_name']) ?></p>
                                    <p class="mt-1 text-sm text-[#6e8d7b]"><?= clean((string)$item['quantity']) ?> x <?= format_currency((float)$item['price']) ?></p>
                                    <?php if (!empty($item['product_slug'])): ?>
                                        <a href="../product-detail.php?slug=<?= clean((string)$item['product_slug']) ?>" class="mt-2 inline-flex text-sm font-semibold text-[#2e9b63] hover:text-[#22784d]">Xem sản phẩm</a>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm font-semibold text-[#2e9b63]"><?= format_currency((float)$item['subtotal']) ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="mt-6 rounded-[1.25rem] border border-[#edf4ef] p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#6e8d7b]">Lịch sử thanh toán</p>
                    <?php if (empty($viewOrder['payments'])): ?>
                        <div class="mt-4 rounded-xl border border-dashed border-[#d9e9de] px-4 py-6 text-sm text-[#6e8d7b]">
                            Chưa có bản ghi thanh toán cho đơn này.
                        </div>
                    <?php else: ?>
                        <div class="mt-4 space-y-3">
                            <?php foreach ($viewOrder['payments'] as $payment): ?>
                                <?php $paymentMeta = admin_order_payment_status_meta((string)$payment['status']); ?>
                                <article class="rounded-[1rem] border border-[#edf4ef] p-4">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <p class="font-semibold text-[#102118]"><?= clean((string)$payment['provider']) ?></p>
                                            <p class="mt-1 text-sm text-[#6e8d7b]">Mã GD: <?= clean((string)($payment['transaction_code'] ?: 'Chưa có')) ?></p>
                                        </div>
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold <?= clean($paymentMeta['class']) ?>"><?= clean($paymentMeta['label']) ?></span>
                                    </div>
                                    <div class="mt-3 grid gap-2 text-sm text-[#6e8d7b]">
                                        <p>Số tiền: <strong class="text-[#102118]"><?= format_currency((float)$payment['amount']) ?></strong></p>
                                        <p>Thời gian: <strong class="text-[#102118]"><?= !empty($payment['paid_at']) ? format_date((string)$payment['paid_at'], 'd/m/Y H:i') : '-' ?></strong></p>
                                        <p>Ghi chú: <?= clean((string)($payment['note'] ?: 'Không có ghi chú.')) ?></p>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php render_admin_footer(); ?>

<script>
let currentPendingCount = <?= (int)$stats['pending_payment_reviews'] ?>;

window.setInterval(async () => {
    try {
        const response = await fetch('orders.php?ajax=pending_count', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        const payload = await response.json();
        const nextCount = Number(payload.pending_count || 0);

        if (nextCount > currentPendingCount) {
            window.location.reload();
            return;
        }

        currentPendingCount = nextCount;
    } catch (_error) {
        // Silent fail to avoid interrupting admin workflow.
    }
}, 4000);
</script>
