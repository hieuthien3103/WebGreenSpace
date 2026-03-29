<?php
require_once __DIR__ . '/bootstrap.php';

$dashboardService = new AdminDashboardService();
$dashboard = $dashboardService->getDashboardData();
$stats = $dashboard['stats'];
$recentOrders = $dashboard['recent_orders'];
$recentUsers = $dashboard['recent_users'];
$topProducts = $dashboard['top_products'];
$lowStockProducts = $dashboard['low_stock_products'];

render_admin_header('Dashboard');
?>

<div class="space-y-8">
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($stats as $card): ?>
            <article class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-[#587766]"><?= clean($card['label']) ?></p>
                        <p class="mt-3 text-3xl font-extrabold text-[#102118]">
                            <?= !empty($card['currency']) ? format_currency((float)$card['value']) : clean((string)$card['value']) ?>
                        </p>
                        <p class="mt-2 text-sm text-[#6e8d7b]"><?= clean($card['hint']) ?></p>
                    </div>
                    <span class="flex size-12 items-center justify-center rounded-2xl bg-[#e9f5ee] text-[#2e9b63]">
                        <span class="material-symbols-outlined"><?= clean($card['icon']) ?></span>
                    </span>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="grid gap-8 xl:grid-cols-[1.15fr_0.85fr]">
        <div class="space-y-8">
            <article class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Đơn hàng</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-[#102118]">Đơn hàng gần đây</h2>
                    </div>
                    <a href="../profile.php?tab=orders" class="text-sm font-semibold text-[#2e9b63] hover:text-[#22784d]">Xem hồ sơ</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-[#edf4ef] text-left text-[#6e8d7b]">
                                <th class="pb-3 pr-4 font-semibold">Mã đơn</th>
                                <th class="pb-3 pr-4 font-semibold">Khách hàng</th>
                                <th class="pb-3 pr-4 font-semibold">Tổng tiền</th>
                                <th class="pb-3 pr-4 font-semibold">Thanh toán</th>
                                <th class="pb-3 font-semibold">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr class="border-b border-[#f4f8f5] last:border-b-0">
                                    <td class="py-4 pr-4 font-semibold text-[#102118]"><?= clean($order['order_number']) ?></td>
                                    <td class="py-4 pr-4">
                                        <p class="font-semibold text-[#102118]"><?= clean($order['full_name']) ?></p>
                                        <p class="text-xs text-[#6e8d7b]"><?= format_date($order['created_at'], 'd/m/Y H:i') ?></p>
                                    </td>
                                    <td class="py-4 pr-4 font-semibold text-[#2e9b63]"><?= format_currency((float)$order['total_amount']) ?></td>
                                    <td class="py-4 pr-4">
                                        <span class="rounded-full bg-[#eef6f1] px-3 py-1 text-xs font-semibold text-[#456a57]"><?= clean($order['payment_status']) ?></span>
                                    </td>
                                    <td class="py-4">
                                        <span class="rounded-full bg-[#102118] px-3 py-1 text-xs font-semibold text-white"><?= clean($order['order_status']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Sản phẩm</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-[#102118]">Sản phẩm bán chạy</h2>
                    </div>
                    <a href="products.php" class="text-sm font-semibold text-[#2e9b63] hover:text-[#22784d]">Quản lý sản phẩm</a>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <?php foreach ($topProducts as $product): ?>
                        <article class="rounded-[1.25rem] border border-[#edf4ef] p-4">
                            <p class="text-base font-bold text-[#102118]"><?= clean($product['name']) ?></p>
                            <div class="mt-3 flex items-center justify-between text-sm">
                                <span class="text-[#6e8d7b]">Đã bán: <strong class="text-[#102118]"><?= clean((string)$product['units_sold']) ?></strong></span>
                                <span class="text-[#6e8d7b]">Tồn: <strong class="text-[#102118]"><?= clean((string)$product['stock']) ?></strong></span>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-3">
                                <a href="products.php?edit=<?= clean((string)$product['id']) ?>" class="text-sm font-semibold text-[#2e9b63] hover:text-[#22784d]">Sửa nhanh</a>
                                <a href="../product-detail.php?slug=<?= clean($product['slug']) ?>" class="text-sm font-semibold text-[#102118] hover:text-[#2e9b63]">Xem ngoài site</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </article>
        </div>

        <div class="space-y-8">
            <article class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
                <div class="mb-5">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Người dùng</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-[#102118]">Tài khoản mới</h2>
                </div>

                <div class="space-y-4">
                    <?php foreach ($recentUsers as $user): ?>
                        <div class="rounded-[1.25rem] border border-[#edf4ef] p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-bold text-[#102118]"><?= clean($user['full_name'] ?: $user['username']) ?></p>
                                    <p class="text-sm text-[#6e8d7b]"><?= clean($user['email']) ?></p>
                                </div>
                                <span class="rounded-full bg-[#eef6f1] px-3 py-1 text-xs font-semibold text-[#456a57]"><?= clean($user['role']) ?></span>
                            </div>
                            <p class="mt-3 text-xs text-[#6e8d7b]"><?= format_date($user['created_at'], 'd/m/Y H:i') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Tồn kho</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-[#102118]">Sắp hết hàng</h2>
                    </div>
                    <a href="products.php?status=active" class="text-sm font-semibold text-[#2e9b63] hover:text-[#22784d]">Mở quản lý kho</a>
                </div>

                <?php if (empty($lowStockProducts)): ?>
                    <div class="rounded-[1.25rem] border border-dashed border-[#d9e9de] px-5 py-8 text-center text-sm text-[#6e8d7b]">
                        Chưa có sản phẩm nào cần cảnh báo tồn kho.
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($lowStockProducts as $product): ?>
                            <?php $currentPrice = !empty($product['sale_price']) && (float)$product['sale_price'] > 0 ? (float)$product['sale_price'] : (float)$product['price']; ?>
                            <article class="rounded-[1.25rem] border border-[#edf4ef] p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-bold text-[#102118]"><?= clean($product['name']) ?></p>
                                        <p class="mt-1 text-sm text-[#6e8d7b]"><?= format_currency($currentPrice) ?></p>
                                    </div>
                                    <span class="rounded-full bg-[#fff3e8] px-3 py-1 text-xs font-semibold text-[#b56a16]">Tồn <?= clean((string)$product['stock']) ?></span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>

            <article class="rounded-[1.75rem] border border-[#d9e9de] bg-[#102118] p-6 text-white shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-white/65">Truy cập nhanh</p>
                <h2 class="mt-2 text-2xl font-extrabold">Công cụ admin</h2>
                <div class="mt-5 grid gap-3">
                    <a href="products.php" class="rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold transition-colors hover:bg-white/15">CRUD sản phẩm</a>
                    <a href="categories.php" class="rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold transition-colors hover:bg-white/15">CRUD danh mục</a>
                    <a href="admin_upload_images.php" class="rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold transition-colors hover:bg-white/15">Upload ảnh sản phẩm</a>
                    <a href="check_images.php" class="rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold transition-colors hover:bg-white/15">Kiểm tra dữ liệu ảnh</a>
                    <a href="clear_cache.php" class="rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold transition-colors hover:bg-white/15">Clear cache</a>
                </div>
            </article>
        </div>
    </section>
</div>

<?php render_admin_footer(); ?>
