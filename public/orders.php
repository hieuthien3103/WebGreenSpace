<?php
if (empty($GLOBALS['mvc_template_rendering'])) {
    require_once __DIR__ . '/../config/config.php';
    (new AccountController())->orders()->send();
    return;
}

require_once __DIR__ . '/../config/config.php';

if (!function_exists('user_orders_order_status_meta')) {
    function user_orders_order_status_meta(string $status): array {
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
}

if (!function_exists('user_orders_payment_status_meta')) {
    function user_orders_payment_status_meta(string $status): array {
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

if (!function_exists('user_orders_query')) {
    function user_orders_query(array $overrides = []): string {
        $params = [
            'status' => (string)($_GET['status'] ?? 'all'),
            'page' => max(1, (int)($_GET['page'] ?? 1)),
        ];

        foreach ($overrides as $key => $value) {
            if ($value === null || $value === '') {
                unset($params[$key]);
                continue;
            }

            $params[$key] = $value;
        }

        return '?' . http_build_query($params);
    }
}

include 'includes/header.php';
?>

<main class="flex-1">
    <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <section class="rounded-[2rem] border border-[#dcecdf] bg-[linear-gradient(135deg,#f7fbf8_0%,#eef7f1_55%,#f8fbf9_100%)] px-6 py-8 shadow-sm sm:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-primary">Đơn hàng</p>
                    <h1 class="mt-3 text-4xl font-extrabold tracking-tight text-text-main dark:text-white">Đơn hàng của tôi</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-text-secondary">
                        Theo dõi toàn bộ đơn đã đặt, kiểm tra trạng thái xử lý và mở chi tiết từng đơn khi cần.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="profile.php#orders" class="inline-flex items-center rounded-full border border-[#d8eadf] bg-white px-5 py-3 text-sm font-semibold text-text-main transition-colors hover:border-primary hover:text-primary">
                        Về hồ sơ
                    </a>
                    <a href="products.php" class="inline-flex items-center rounded-full bg-[#102118] px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#1f3b2d]">
                        Mua thêm sản phẩm
                    </a>
                </div>
            </div>
        </section>

        <section class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-[1.5rem] border border-[#dcecdf] bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-[#587766]">Tổng đơn</p>
                <p class="mt-3 text-3xl font-extrabold text-[#102118]"><?= clean((string)$stats['total_orders']) ?></p>
                <p class="mt-2 text-sm text-[#6e8d7b]">Tất cả đơn bạn đã đặt trên hệ thống.</p>
            </article>
            <article class="rounded-[1.5rem] border border-[#dcecdf] bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-[#587766]">Đang xử lý</p>
                <p class="mt-3 text-3xl font-extrabold text-[#b7791f]"><?= clean((string)$stats['active_orders']) ?></p>
                <p class="mt-2 text-sm text-[#6e8d7b]">Bao gồm chờ xác nhận, đã xác nhận và đang chuẩn bị.</p>
            </article>
            <article class="rounded-[1.5rem] border border-[#dcecdf] bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-[#587766]">Đang giao</p>
                <p class="mt-3 text-3xl font-extrabold text-[#2563eb]"><?= clean((string)$stats['shipping_orders']) ?></p>
                <p class="mt-2 text-sm text-[#6e8d7b]">Những đơn đang trên đường đến bạn.</p>
            </article>
            <article class="rounded-[1.5rem] border border-[#dcecdf] bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-[#587766]">Cần chú ý</p>
                <p class="mt-3 text-3xl font-extrabold text-[#c43d3d]"><?= clean((string)$stats['payment_attention_orders']) ?></p>
                <p class="mt-2 text-sm text-[#6e8d7b]">Đơn chưa thanh toán, chờ duyệt hoặc thanh toán lỗi.</p>
            </article>
        </section>

        <section class="mt-8 rounded-[2rem] border border-[#dcecdf] bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-primary">Lịch sử</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-text-main dark:text-white">Danh sách đơn hàng</h2>
                    <p class="mt-2 text-sm text-text-secondary">Lọc theo trạng thái đơn để kiểm tra nhanh tiến độ giao hàng.</p>
                </div>

                <form method="GET" class="w-full max-w-sm">
                    <label for="status" class="mb-2 block text-sm font-semibold text-text-main dark:text-white">Trạng thái đơn</label>
                    <select id="status" name="status" onchange="this.form.submit()" class="w-full rounded-2xl border border-[#d8eadf] px-4 py-3 text-sm text-text-main focus:border-primary focus:ring-primary/20">
                        <?php foreach ($statusOptions as $value => $label): ?>
                            <option value="<?= clean($value) ?>" <?= $statusFilter === $value ? 'selected' : '' ?>><?= clean($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <noscript>
                        <button type="submit" class="mt-3 inline-flex rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white">Lọc</button>
                    </noscript>
                </form>
            </div>

            <?php if (empty($orders)): ?>
                <div class="mt-6 rounded-[1.5rem] border border-dashed border-[#d8eadf] px-6 py-12 text-center">
                    <span class="material-symbols-outlined text-5xl text-primary/70">receipt_long</span>
                    <p class="mt-4 text-base font-semibold text-text-main dark:text-white">Chưa có đơn phù hợp bộ lọc hiện tại.</p>
                    <p class="mt-2 text-sm text-text-secondary">Bạn có thể đổi bộ lọc hoặc quay lại trang sản phẩm để đặt đơn mới.</p>
                </div>
            <?php else: ?>
                <div class="mt-6 space-y-4">
                    <?php foreach ($orders as $order): ?>
                        <?php
                        $orderStatus = user_orders_order_status_meta((string)$order['order_status']);
                        $paymentStatus = user_orders_payment_status_meta((string)$order['payment_status']);
                        ?>
                        <article class="rounded-[1.5rem] border border-[#edf5ef] p-5 dark:border-[#24352b]">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary"><?= clean($order['order_number']) ?></p>
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold <?= clean($orderStatus['class']) ?>"><?= clean($orderStatus['label']) ?></span>
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold <?= clean($paymentStatus['class']) ?>"><?= clean($paymentStatus['label']) ?></span>
                                    </div>

                                    <h3 class="mt-3 text-xl font-bold text-text-main dark:text-white"><?= clean($order['full_name']) ?></h3>
                                    <p class="mt-2 text-sm leading-6 text-text-secondary"><?= clean($order['address']) ?></p>

                                    <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-text-secondary">
                                        <span>Đặt lúc <?= format_date($order['created_at'], 'd/m/Y H:i') ?></span>
                                        <span><?= clean((string)$order['item_count']) ?> dòng sản phẩm</span>
                                        <span><?= clean((string)$order['total_quantity']) ?> sản phẩm</span>
                                    </div>
                                </div>

                                <div class="min-w-[220px] rounded-[1.25rem] bg-[#f8fbf9] p-4 lg:text-right">
                                    <p class="text-sm text-text-secondary">Tổng thanh toán</p>
                                    <p class="mt-2 text-2xl font-extrabold text-primary"><?= format_currency((float)$order['total_amount']) ?></p>
                                    <a href="order-detail.php?id=<?= clean((string)$order['id']) ?>" class="mt-4 inline-flex items-center rounded-full border border-[#d8eadf] px-4 py-2 text-sm font-semibold text-text-main transition-colors hover:border-primary hover:text-primary">
                                        Xem chi tiết đơn
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-[#edf5ef] pt-5">
                        <p class="text-sm text-text-secondary">
                            Trang <?= clean((string)$page) ?> / <?= clean((string)$totalPages) ?>, tổng <?= clean((string)$totalOrders) ?> đơn
                        </p>

                        <div class="flex flex-wrap gap-3">
                            <?php if ($page > 1): ?>
                                <a href="orders.php<?= clean(user_orders_query(['status' => $statusFilter, 'page' => $page - 1])) ?>" class="inline-flex rounded-full border border-[#d8eadf] px-4 py-2 text-sm font-semibold text-text-main transition-colors hover:border-primary hover:text-primary">
                                    Trang trước
                                </a>
                            <?php endif; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="orders.php<?= clean(user_orders_query(['status' => $statusFilter, 'page' => $page + 1])) ?>" class="inline-flex rounded-full border border-[#d8eadf] px-4 py-2 text-sm font-semibold text-text-main transition-colors hover:border-primary hover:text-primary">
                                    Trang sau
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
