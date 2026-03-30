<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!is_logged_in()) {
    $redirectTarget = 'order-detail.php';
    $rawOrderId = $id ?? ($_GET['id'] ?? null);
    if ($rawOrderId !== null) {
        $redirectTarget .= '?id=' . urlencode((string)$rawOrderId);
    }

    redirect('login.php?redirect=' . urlencode($redirectTarget));
}

function order_detail_order_status_meta(string $status): array {
    $map = [
        'pending' => ['label' => 'Chờ xác nhận', 'class' => 'bg-[#fff7e8] text-[#b7791f]'],
        'confirmed' => ['label' => 'Đã xác nhận', 'class' => 'bg-[#edf8f1] text-[#2e9b63]'],
        'processing' => ['label' => 'Đang chuẩn bị', 'class' => 'bg-[#eef4ff] text-[#3758c7]'],
        'shipping' => ['label' => 'Đang giao', 'class' => 'bg-[#eef6ff] text-[#2563eb]'],
        'completed' => ['label' => 'Hoàn tất', 'class' => 'bg-[#eafaf0] text-[#157347]'],
        'cancelled' => ['label' => 'Đã hủy', 'class' => 'bg-[#fdecec] text-[#c43d3d]'],
    ];

    return $map[$status] ?? ['label' => ucfirst($status), 'class' => 'bg-[#f2f4f3] text-text-secondary'];
}

function order_detail_payment_status_meta(string $status): array {
    $map = [
        'paid' => ['label' => 'Đã thanh toán', 'class' => 'bg-[#edf8f1] text-[#2e9b63]'],
        'unpaid' => ['label' => 'Chưa thanh toán', 'class' => 'bg-[#f5f7f6] text-[#5d6d63]'],
        'failed' => ['label' => 'Thanh toán lỗi', 'class' => 'bg-[#fdecec] text-[#c43d3d]'],
        'refunded' => ['label' => 'Đã hoàn tiền', 'class' => 'bg-[#eef4ff] text-[#3758c7]'],
    ];

    return $map[$status] ?? ['label' => ucfirst($status), 'class' => 'bg-[#f2f4f3] text-text-secondary'];
}

function order_detail_payment_method_label(string $method): string {
    $map = [
        'cod' => 'Thanh toán khi nhận hàng',
        'online_mock' => 'Online mock',
    ];

    return $map[$method] ?? $method;
}

$orderId = max(0, (int)($id ?? ($_GET['id'] ?? 0)));
if ($orderId <= 0) {
    set_flash('error', 'Không tìm thấy đơn hàng cần xem.');
    redirect('profile.php');
}

$orderModel = new Order();
$userId = (int)get_user_id();
$order = $orderModel->getDetailByUserId($userId, $orderId);

if (!$order) {
    set_flash('error', 'Không tìm thấy đơn hàng hoặc bạn không có quyền xem đơn này.');
    redirect('profile.php');
}

$orderStatus = order_detail_order_status_meta((string)$order['order_status']);
$paymentStatus = order_detail_payment_status_meta((string)$order['payment_status']);
$payment = $order['payment'] ?? null;
$orderItems = $order['items'] ?? [];
$pageTitle = 'Chi tiết đơn hàng - GreenSpace';
$currentPage = '';

include 'includes/header.php';
?>

<main class="flex-1">
    <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <section class="rounded-[2rem] border border-[#dcecdf] bg-[linear-gradient(135deg,#f7fbf8_0%,#eef7f1_55%,#f8fbf9_100%)] px-6 py-8 shadow-sm sm:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <a href="profile.php" class="text-sm font-semibold text-primary hover:text-primary-dark">← Quay lại hồ sơ</a>
                    <p class="mt-4 text-sm font-semibold uppercase tracking-[0.22em] text-primary">Chi tiết đơn hàng</p>
                    <h1 class="mt-3 text-4xl font-extrabold tracking-tight text-text-main dark:text-white"><?= clean($order['order_number']) ?></h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-text-secondary">
                        Xem lại thông tin giao nhận, danh sách sản phẩm và trạng thái thanh toán của đơn hàng.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <span class="rounded-full px-4 py-2 text-sm font-semibold <?= clean($orderStatus['class']) ?>"><?= clean($orderStatus['label']) ?></span>
                    <span class="rounded-full px-4 py-2 text-sm font-semibold <?= clean($paymentStatus['class']) ?>"><?= clean($paymentStatus['label']) ?></span>
                    <span class="rounded-full bg-white/90 px-4 py-2 text-sm font-semibold text-[#102118]">
                        <?= format_date($order['created_at'], 'd/m/Y H:i') ?>
                    </span>
                </div>
            </div>
        </section>

        <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1.08fr)_minmax(320px,0.92fr)]">
            <section class="space-y-6">
                <article class="rounded-[2rem] border border-[#e7f1ea] bg-white p-6 shadow-sm dark:border-[#24352b] dark:bg-[#16211b]">
                    <div class="mb-6 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-primary">Sản phẩm</p>
                            <h2 class="mt-2 text-2xl font-extrabold text-text-main dark:text-white">Danh sách trong đơn</h2>
                        </div>
                        <span class="rounded-full bg-[#f5f8f6] px-4 py-2 text-sm font-semibold text-text-secondary">
                            <?= clean((string)$order['item_count']) ?> dòng, <?= clean((string)$order['total_quantity']) ?> sản phẩm
                        </span>
                    </div>

                    <div class="space-y-4">
                        <?php foreach ($orderItems as $item): ?>
                            <article class="flex flex-col gap-4 rounded-[1.5rem] border border-[#edf5ef] p-4 sm:flex-row sm:items-center dark:border-[#24352b]">
                                <img
                                    src="<?= clean($item['product_image'] ?: image_url('products/default.jpg')) ?>"
                                    alt="<?= clean($item['product_name']) ?>"
                                    class="h-20 w-20 rounded-2xl object-cover"
                                >
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <h3 class="text-lg font-bold text-text-main dark:text-white"><?= clean($item['product_name']) ?></h3>
                                            <p class="mt-1 text-sm text-text-secondary">
                                                <?= (int)$item['quantity'] ?> x <?= format_currency((float)$item['price']) ?>
                                            </p>
                                        </div>
                                        <p class="text-base font-extrabold text-primary"><?= format_currency((float)$item['subtotal']) ?></p>
                                    </div>

                                    <?php if (!empty($item['product_slug'])): ?>
                                        <a href="product-detail.php?slug=<?= clean($item['product_slug']) ?>" class="mt-3 inline-flex text-sm font-semibold text-primary hover:text-primary-dark">
                                            Xem lại sản phẩm
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </article>

                <article class="rounded-[2rem] border border-[#e7f1ea] bg-white p-6 shadow-sm dark:border-[#24352b] dark:bg-[#16211b]">
                    <div class="mb-6">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-primary">Giao nhận</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-text-main dark:text-white">Thông tin nhận hàng</h2>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="rounded-[1.5rem] bg-[#f8fbf9] p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-text-secondary">Người nhận</p>
                            <p class="mt-2 font-bold text-text-main dark:text-white"><?= clean($order['full_name']) ?></p>
                        </div>
                        <div class="rounded-[1.5rem] bg-[#f8fbf9] p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-text-secondary">Điện thoại</p>
                            <p class="mt-2 font-bold text-text-main dark:text-white"><?= clean($order['phone']) ?></p>
                        </div>
                        <div class="rounded-[1.5rem] bg-[#f8fbf9] p-4 md:col-span-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-text-secondary">Địa chỉ giao hàng</p>
                            <p class="mt-2 font-bold leading-7 text-text-main dark:text-white"><?= clean($order['address']) ?></p>
                        </div>
                        <div class="rounded-[1.5rem] bg-[#f8fbf9] p-4 md:col-span-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-text-secondary">Ghi chú</p>
                            <p class="mt-2 leading-7 text-text-main dark:text-white">
                                <?= !empty($order['note']) ? nl2br(clean($order['note'])) : 'Không có ghi chú cho đơn hàng này.' ?>
                            </p>
                        </div>
                    </div>
                </article>
            </section>

            <aside class="space-y-6 lg:sticky lg:top-24">
                <article class="overflow-hidden rounded-[2rem] border border-[#dcecdf] bg-white shadow-sm">
                    <div class="bg-[#102118] px-6 py-6 text-white">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-white/65">Tóm tắt thanh toán</p>
                        <h2 class="mt-3 text-2xl font-extrabold">Giá trị đơn hàng</h2>
                        <p class="mt-2 text-sm text-white/75">Kiểm tra lại các thành phần của đơn đã đặt.</p>
                    </div>

                    <div class="space-y-4 px-6 py-6 text-sm">
                        <div class="flex items-center justify-between text-text-secondary">
                            <span>Tạm tính</span>
                            <span class="font-semibold text-text-main dark:text-white"><?= format_currency((float)$order['subtotal']) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-text-secondary">
                            <span>Giảm giá</span>
                            <span class="font-semibold text-text-main dark:text-white"><?= format_currency((float)$order['discount_amount']) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-text-secondary">
                            <span>Phí vận chuyển</span>
                            <span class="font-semibold text-text-main dark:text-white"><?= format_currency((float)$order['shipping_fee']) ?></span>
                        </div>
                        <div class="flex items-center justify-between border-t border-dashed border-[#d8eadf] pt-4">
                            <span class="text-base font-bold text-text-main dark:text-white">Tổng cộng</span>
                            <span class="text-2xl font-extrabold text-primary"><?= format_currency((float)$order['total_amount']) ?></span>
                        </div>
                    </div>
                </article>

                <article class="rounded-[2rem] border border-[#e7f1ea] bg-white p-6 shadow-sm dark:border-[#24352b] dark:bg-[#16211b]">
                    <div class="mb-5">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-primary">Thanh toán</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-text-main dark:text-white">Thông tin giao dịch</h2>
                    </div>

                    <div class="space-y-4 text-sm">
                        <div class="rounded-[1.5rem] bg-[#f8fbf9] p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-text-secondary">Phương thức</p>
                            <p class="mt-2 font-bold text-text-main dark:text-white"><?= clean(order_detail_payment_method_label((string)$order['payment_method'])) ?></p>
                        </div>

                        <div class="rounded-[1.5rem] bg-[#f8fbf9] p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-text-secondary">Trạng thái thanh toán</p>
                            <p class="mt-2 font-bold text-text-main dark:text-white"><?= clean($paymentStatus['label']) ?></p>
                        </div>

                        <?php if ($payment): ?>
                            <div class="rounded-[1.5rem] bg-[#f8fbf9] p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-text-secondary">Mã giao dịch</p>
                                <p class="mt-2 font-bold text-text-main dark:text-white"><?= clean($payment['transaction_code'] ?: 'Chưa có') ?></p>
                            </div>

                            <div class="rounded-[1.5rem] bg-[#f8fbf9] p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-text-secondary">Ghi chú thanh toán</p>
                                <p class="mt-2 leading-7 text-text-main dark:text-white"><?= clean($payment['note'] ?: 'Không có ghi chú.') ?></p>
                            </div>

                            <?php if (!empty($payment['paid_at'])): ?>
                                <div class="rounded-[1.5rem] bg-[#f8fbf9] p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-text-secondary">Thời điểm ghi nhận</p>
                                    <p class="mt-2 font-bold text-text-main dark:text-white"><?= format_date($payment['paid_at'], 'd/m/Y H:i') ?></p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </article>
            </aside>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
