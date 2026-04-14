<?php
if (empty($GLOBALS['mvc_template_rendering'])) {
    require_once __DIR__ . '/../../config/config.php';
    (new AdminPageController())->orders()->send();
    return;
}

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

    <?php if (!empty($commerceSummary)): ?>
        <section class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Kinh doanh</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-[#102118]">Thống kê sản phẩm và khách đã mua</h2>
                    <p class="mt-2 text-sm text-[#6e8d7b]">Theo dõi nhanh số khách phát sinh mua hàng, tổng sản phẩm đã bán và nhóm mặt hàng đang tạo doanh thu tốt nhất.</p>
                </div>
                <a href="dashboard.php" class="inline-flex items-center rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">
                    Mở dashboard tổng quan
                </a>
            </div>

            <div class="mt-6 grid gap-4 xl:grid-cols-3">
                <?php foreach ($commerceSummary as $card): ?>
                    <article class="rounded-[1.35rem] border border-[#edf4ef] bg-[#f8fbf9] p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-[#587766]"><?= clean($card['label']) ?></p>
                                <p class="mt-3 text-3xl font-extrabold text-[#102118]">
                                    <?= !empty($card['currency']) ? format_currency((float)$card['value']) : clean((string)$card['value']) ?>
                                </p>
                                <p class="mt-2 text-sm text-[#6e8d7b]"><?= clean($card['hint']) ?></p>
                            </div>
                            <span class="flex size-11 items-center justify-center rounded-2xl bg-white text-[#2e9b63] shadow-sm">
                                <span class="material-symbols-outlined"><?= clean($card['icon']) ?></span>
                            </span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_1fr]">
                <article class="rounded-[1.5rem] border border-[#edf4ef] bg-[#102118] p-5 text-white">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-white/65">Khách hàng</p>
                            <h3 class="mt-2 text-xl font-extrabold">Khách mua nhiều nhất</h3>
                        </div>
                        <a href="users.php" class="text-sm font-semibold text-white/80 transition-colors hover:text-white">Quản lý user</a>
                    </div>

                    <?php if (empty($topCustomers)): ?>
                        <div class="mt-5 rounded-[1.25rem] bg-white/10 px-4 py-6 text-sm text-white/80">
                            Chưa có dữ liệu khách mua hàng để thống kê.
                        </div>
                    <?php else: ?>
                        <div class="mt-5 space-y-3">
                            <?php foreach (array_slice($topCustomers, 0, 4) as $customer): ?>
                                <article class="rounded-[1.25rem] bg-white/10 px-4 py-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-bold"><?= clean($customer['full_name'] ?: $customer['username']) ?></p>
                                            <p class="mt-1 text-sm text-white/70"><?= clean($customer['email']) ?></p>
                                        </div>
                                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">
                                            <?= clean((string)$customer['completed_order_count']) ?> đơn
                                        </span>
                                    </div>
                                    <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-white/80">
                                        <span>Đã mua: <strong class="text-white"><?= clean((string)$customer['units_bought']) ?></strong> SP</span>
                                        <span>Doanh thu: <strong class="text-white"><?= format_currency((float)$customer['gross_revenue']) ?></strong></span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>

                <article class="rounded-[1.5rem] border border-[#edf4ef] p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Sản phẩm</p>
                            <h3 class="mt-2 text-xl font-extrabold text-[#102118]">Mặt hàng bán chạy</h3>
                        </div>
                        <a href="products.php" class="text-sm font-semibold text-[#2e9b63] transition-colors hover:text-[#22784d]">Quản lý sản phẩm</a>
                    </div>

                    <?php if (empty($topProducts)): ?>
                        <div class="mt-5 rounded-[1.25rem] border border-dashed border-[#d9e9de] px-4 py-6 text-center text-sm text-[#6e8d7b]">
                            Chưa có dữ liệu bán hàng để xếp hạng sản phẩm.
                        </div>
                    <?php else: ?>
                        <div class="mt-5 space-y-3">
                            <?php foreach (array_slice($topProducts, 0, 4) as $product): ?>
                                <article class="rounded-[1.25rem] border border-[#edf4ef] bg-[#f8fbf9] px-4 py-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-bold text-[#102118]"><?= clean($product['name']) ?></p>
                                            <p class="mt-1 text-sm text-[#6e8d7b]">Khách mua: <strong class="text-[#102118]"><?= clean((string)$product['customer_count']) ?></strong></p>
                                        </div>
                                        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-[#456a57] shadow-sm">
                                            <?= clean((string)$product['order_count']) ?> đơn
                                        </span>
                                    </div>
                                    <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-[#6e8d7b]">
                                        <span>Đã bán: <strong class="text-[#102118]"><?= clean((string)$product['units_sold']) ?></strong> SP</span>
                                        <span>Doanh thu: <strong class="text-[#102118]"><?= format_currency((float)$product['gross_revenue']) ?></strong></span>
                                        <span>Tồn kho: <strong class="text-[#102118]"><?= clean((string)$product['stock']) ?></strong></span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($commerceTrends['last_7_days']['points'] ?? []) || !empty($commerceTrends['last_30_days']['points'] ?? []) || !empty($commerceTrends['last_12_months']['points'] ?? [])): ?>
        <section class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Biểu đồ</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-[#102118]">Doanh thu và số lượng bán theo ngày/tháng</h2>
                    <p class="mt-2 text-sm text-[#6e8d7b]">Doanh thu chỉ tính đơn đã thanh toán và không bị hủy. Số lượng bán tính trên các đơn không bị hủy.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <div class="inline-flex rounded-full border border-[#d9e9de] bg-[#f8fbf9] p-1">
                        <button type="button" data-trend-period="last_7_days" class="trend-period-button rounded-full px-4 py-2 text-sm font-semibold text-[#102118] transition-colors">
                            7 ngày
                        </button>
                        <button type="button" data-trend-period="last_30_days" class="trend-period-button rounded-full px-4 py-2 text-sm font-semibold text-[#587766] transition-colors">
                            30 ngày
                        </button>
                    </div>
                    <div class="inline-flex rounded-full border border-[#d9e9de] bg-[#f8fbf9] p-1">
                        <button type="button" data-trend-period="last_12_months" class="trend-period-button rounded-full px-4 py-2 text-sm font-semibold text-[#587766] transition-colors">
                            12 tháng
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-3">
                <article class="rounded-[1.35rem] border border-[#edf4ef] bg-[#f8fbf9] p-5">
                    <p class="text-sm font-semibold text-[#587766]">Tổng doanh thu kỳ đang xem</p>
                    <p id="trendRevenueTotal" class="mt-3 text-3xl font-extrabold text-[#102118]">0 đ</p>
                    <p id="trendRangeLabel" class="mt-2 text-sm text-[#6e8d7b]">Đang tải khoảng thời gian...</p>
                </article>
                <article class="rounded-[1.35rem] border border-[#edf4ef] bg-[#f8fbf9] p-5">
                    <p class="text-sm font-semibold text-[#587766]">Số lượng bán trong kỳ</p>
                    <p id="trendUnitsTotal" class="mt-3 text-3xl font-extrabold text-[#102118]">0</p>
                    <p class="mt-2 text-sm text-[#6e8d7b]">Tổng số sản phẩm đã bán theo kỳ đang chọn.</p>
                </article>
                <article class="rounded-[1.35rem] border border-[#edf4ef] bg-[#102118] p-5 text-white">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-white/65">Ghi chú</p>
                    <h3 id="trendPeriodTitle" class="mt-2 text-xl font-extrabold">Đang xem 7 ngày gần nhất</h3>
                    <p class="mt-3 text-sm leading-6 text-white/80">Biểu đồ hỗ trợ so sánh nhịp tăng trưởng doanh thu với lượng hàng bán ra để mình phát hiện nhanh ngày cao điểm hoặc tháng chững lại.</p>
                </article>
            </div>

            <div class="mt-6 grid gap-6 xl:grid-cols-2">
                <article class="rounded-[1.5rem] border border-[#edf4ef] p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Doanh thu</p>
                            <h3 class="mt-2 text-xl font-extrabold text-[#102118]">Xu hướng doanh thu</h3>
                        </div>
                        <span class="rounded-full bg-[#eef6f1] px-3 py-1 text-xs font-semibold text-[#456a57]">Đơn đã paid</span>
                    </div>
                    <div id="revenueTrendChart" class="mt-5"></div>
                </article>

                <article class="rounded-[1.5rem] border border-[#edf4ef] p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Sản lượng</p>
                            <h3 class="mt-2 text-xl font-extrabold text-[#102118]">Xu hướng số lượng bán</h3>
                        </div>
                        <span class="rounded-full bg-[#eef4ff] px-3 py-1 text-xs font-semibold text-[#3758c7]">Đơn không hủy</span>
                    </div>
                    <div id="unitsTrendChart" class="mt-5"></div>
                </article>
            </div>
        </section>
    <?php endif; ?>

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
                $viewPaymentMethod = (string)$viewOrder['payment_method'];
                $isCancelledOrder = (string)$viewOrder['order_status'] === 'cancelled';
                $canReviewPayment = payment_method_requires_manual_review($viewPaymentMethod)
                    && !$isCancelledOrder
                    && (string)$viewOrder['payment_status'] === 'pending_review';
                $orderStatusLockedByPayment = payment_method_is_online($viewPaymentMethod)
                    && (string)$viewOrder['payment_status'] !== 'paid';
                $currentOrderStatus = (string)$viewOrder['order_status'];
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
                            <p>Thanh toán: <strong class="text-[#102118]"><?= clean(payment_method_label($viewPaymentMethod)) ?></strong></p>
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
                                    <?php
                                    $optionLockedByPayment = $orderStatusLockedByPayment
                                        && $value !== 'cancelled'
                                        && $value !== $currentOrderStatus;
                                    ?>
                                    <option value="<?= clean($value) ?>" <?= $currentOrderStatus === $value ? 'selected' : '' ?> <?= $optionLockedByPayment ? 'disabled' : '' ?>><?= clean($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($orderStatusLockedByPayment): ?>
                                <p class="text-xs text-[#b7791f]">Đơn thanh toán online chưa ở trạng thái "Đã thanh toán" chỉ nên giữ nguyên trạng thái hiện tại hoặc chuyển sang "Đã hủy" để đóng đơn và hoàn kho.</p>
                            <?php endif; ?>
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
                                            <p class="font-semibold text-[#102118]"><?= clean(payment_method_label((string)$payment['provider'])) ?></p>
                                            <p class="mt-1 text-sm text-[#6e8d7b]">Provider key: <?= clean((string)$payment['provider']) ?></p>
                                            <p class="text-sm text-[#6e8d7b]">Mã GD: <?= clean((string)($payment['transaction_code'] ?: 'Chưa có')) ?></p>
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
const commerceTrendData = <?= json_encode($commerceTrends, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
let currentPendingCount = <?= (int)$stats['pending_payment_reviews'] ?>;

const trendPeriodButtons = Array.from(document.querySelectorAll('[data-trend-period]'));
const trendRevenueTotal = document.getElementById('trendRevenueTotal');
const trendUnitsTotal = document.getElementById('trendUnitsTotal');
const trendRangeLabel = document.getElementById('trendRangeLabel');
const trendPeriodTitle = document.getElementById('trendPeriodTitle');
const revenueTrendChart = document.getElementById('revenueTrendChart');
const unitsTrendChart = document.getElementById('unitsTrendChart');

const currencyFormatter = new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
    maximumFractionDigits: 0,
});
const numberFormatter = new Intl.NumberFormat('vi-VN');

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function buildRevenueChartSvg(points) {
    if (!points.length) {
        return '<div class="rounded-[1.25rem] border border-dashed border-[#d9e9de] px-4 py-10 text-center text-sm text-[#6e8d7b]">Chưa có dữ liệu doanh thu trong kỳ này.</div>';
    }

    const width = 760;
    const height = 280;
    const paddingLeft = 42;
    const paddingRight = 20;
    const paddingTop = 18;
    const paddingBottom = 48;
    const plotWidth = width - paddingLeft - paddingRight;
    const plotHeight = height - paddingTop - paddingBottom;
    const values = points.map((point) => Number(point.revenue || 0));
    const maxValue = Math.max(...values, 1);
    const xStep = points.length > 1 ? plotWidth / (points.length - 1) : 0;

    const linePoints = points.map((point, index) => {
        const x = paddingLeft + xStep * index;
        const y = paddingTop + plotHeight - ((Number(point.revenue || 0) / maxValue) * plotHeight);
        return { x, y, point };
    });

    const polyline = linePoints.map((item) => `${item.x},${item.y}`).join(' ');
    const areaPath = [
        `M ${linePoints[0].x} ${paddingTop + plotHeight}`,
        ...linePoints.map((item) => `L ${item.x} ${item.y}`),
        `L ${linePoints[linePoints.length - 1].x} ${paddingTop + plotHeight}`,
        'Z',
    ].join(' ');

    const gridLines = [0, 0.25, 0.5, 0.75, 1].map((step) => {
        const y = paddingTop + (plotHeight * step);
        const labelValue = maxValue - (maxValue * step);

        return `
            <g>
                <line x1="${paddingLeft}" y1="${y}" x2="${width - paddingRight}" y2="${y}" stroke="#e5efe8" stroke-dasharray="4 6" />
                <text x="${paddingLeft - 8}" y="${y + 4}" text-anchor="end" font-size="11" fill="#6e8d7b">${escapeHtml(numberFormatter.format(Math.round(labelValue)))}</text>
            </g>
        `;
    }).join('');

    const dots = linePoints.map((item) => `
        <g>
            <circle cx="${item.x}" cy="${item.y}" r="4.5" fill="#2e9b63" />
            <title>${escapeHtml(item.point.label)}: ${escapeHtml(currencyFormatter.format(Number(item.point.revenue || 0)))}</title>
        </g>
    `).join('');

    const labels = linePoints.map((item) => `
        <text x="${item.x}" y="${height - 18}" text-anchor="middle" font-size="11" fill="#6e8d7b">${escapeHtml(item.point.short_label)}</text>
    `).join('');

    return `
        <svg viewBox="0 0 ${width} ${height}" class="w-full overflow-visible">
            <defs>
                <linearGradient id="revenueAreaGradient" x1="0" x2="0" y1="0" y2="1">
                    <stop offset="0%" stop-color="#7bd4a4" stop-opacity="0.45"></stop>
                    <stop offset="100%" stop-color="#7bd4a4" stop-opacity="0.02"></stop>
                </linearGradient>
            </defs>
            ${gridLines}
            <path d="${areaPath}" fill="url(#revenueAreaGradient)"></path>
            <polyline points="${polyline}" fill="none" stroke="#2e9b63" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></polyline>
            ${dots}
            ${labels}
        </svg>
    `;
}

function buildUnitsChartSvg(points) {
    if (!points.length) {
        return '<div class="rounded-[1.25rem] border border-dashed border-[#d9e9de] px-4 py-10 text-center text-sm text-[#6e8d7b]">Chưa có dữ liệu số lượng bán trong kỳ này.</div>';
    }

    const width = 760;
    const height = 280;
    const paddingLeft = 42;
    const paddingRight = 20;
    const paddingTop = 18;
    const paddingBottom = 48;
    const plotWidth = width - paddingLeft - paddingRight;
    const plotHeight = height - paddingTop - paddingBottom;
    const values = points.map((point) => Number(point.units || 0));
    const maxValue = Math.max(...values, 1);
    const segmentWidth = plotWidth / Math.max(points.length, 1);
    const barWidth = Math.min(28, Math.max(10, segmentWidth * 0.55));

    const gridLines = [0, 0.25, 0.5, 0.75, 1].map((step) => {
        const y = paddingTop + (plotHeight * step);
        const labelValue = maxValue - (maxValue * step);

        return `
            <g>
                <line x1="${paddingLeft}" y1="${y}" x2="${width - paddingRight}" y2="${y}" stroke="#e6ebff" stroke-dasharray="4 6" />
                <text x="${paddingLeft - 8}" y="${y + 4}" text-anchor="end" font-size="11" fill="#6e8d7b">${escapeHtml(numberFormatter.format(Math.round(labelValue)))}</text>
            </g>
        `;
    }).join('');

    const bars = points.map((point, index) => {
        const barHeight = (Number(point.units || 0) / maxValue) * plotHeight;
        const x = paddingLeft + (segmentWidth * index) + ((segmentWidth - barWidth) / 2);
        const y = paddingTop + plotHeight - barHeight;
        const labelX = paddingLeft + (segmentWidth * index) + (segmentWidth / 2);

        return `
            <g>
                <rect x="${x}" y="${y}" width="${barWidth}" height="${Math.max(barHeight, 2)}" rx="10" fill="#5b7cff"></rect>
                <title>${escapeHtml(point.label)}: ${escapeHtml(numberFormatter.format(Number(point.units || 0)))} sản phẩm</title>
                <text x="${labelX}" y="${height - 18}" text-anchor="middle" font-size="11" fill="#6e8d7b">${escapeHtml(point.short_label)}</text>
            </g>
        `;
    }).join('');

    return `
        <svg viewBox="0 0 ${width} ${height}" class="w-full overflow-visible">
            ${gridLines}
            ${bars}
        </svg>
    `;
}

function renderCommerceTrend(period) {
    const trend = commerceTrendData?.[period];
    if (!trend || !Array.isArray(trend.points)) {
        return;
    }

    const points = trend.points;
    const firstLabel = points[0]?.label || '';
    const lastLabel = points[points.length - 1]?.label || '';
    const periodTitle = `Đang xem ${trend.title || ''}`.trim();

    if (trendRevenueTotal) {
        trendRevenueTotal.textContent = currencyFormatter.format(Number(trend.summary?.revenue_total || 0));
    }

    if (trendUnitsTotal) {
        trendUnitsTotal.textContent = numberFormatter.format(Number(trend.summary?.units_total || 0));
    }

    if (trendRangeLabel) {
        trendRangeLabel.textContent = firstLabel && lastLabel ? `${firstLabel} - ${lastLabel}` : 'Không có dữ liệu';
    }

    if (trendPeriodTitle) {
        trendPeriodTitle.textContent = periodTitle;
    }

    if (revenueTrendChart) {
        revenueTrendChart.innerHTML = buildRevenueChartSvg(points);
    }

    if (unitsTrendChart) {
        unitsTrendChart.innerHTML = buildUnitsChartSvg(points);
    }

    trendPeriodButtons.forEach((button) => {
        const isActive = button.dataset.trendPeriod === period;
        button.classList.toggle('bg-[#102118]', isActive);
        button.classList.toggle('text-white', isActive);
        button.classList.toggle('text-[#102118]', !isActive);
        button.classList.toggle('text-[#587766]', !isActive);
    });
}

trendPeriodButtons.forEach((button) => {
    button.addEventListener('click', () => {
        renderCommerceTrend(button.dataset.trendPeriod || 'last_7_days');
    });
});

renderCommerceTrend('last_7_days');

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
