<?php
if (empty($GLOBALS['mvc_template_rendering'])) {
    require_once __DIR__ . '/../config/config.php';
    (new AccountController())->profile()->send();
    return;
}

require_once __DIR__ . '/../config/config.php';

function profile_order_status_meta(string $status): array {
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

function profile_payment_status_meta(string $status): array {
    $map = [
        'paid' => ['label' => 'Đã thanh toán', 'class' => 'bg-[#edf8f1] text-[#2e9b63]'],
        'pending_review' => ['label' => 'Chờ admin duyệt', 'class' => 'bg-[#fff7e8] text-[#b7791f]'],
        'unpaid' => ['label' => 'Chưa thanh toán', 'class' => 'bg-[#f2f4f3] text-text-secondary'],
        'failed' => ['label' => 'Thanh toán lỗi', 'class' => 'bg-[#fdecec] text-[#c43d3d]'],
        'refunded' => ['label' => 'Đã hoàn tiền', 'class' => 'bg-[#eef4ff] text-[#3758c7]'],
    ];

    return $map[$status] ?? ['label' => ucfirst($status), 'class' => 'bg-[#f2f4f3] text-text-secondary'];
}

include 'includes/header.php';
?>

<main class="flex-1">
    <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-primary">Profile</p>
            <h1 class="mt-2 text-4xl font-extrabold text-text-main dark:text-white">Tài khoản của bạn</h1>
            <p class="mt-3 text-sm text-text-secondary">Quản lý thông tin cá nhân, sổ địa chỉ và lịch sử đơn hàng.</p>
        </div>

        <div class="grid gap-8 lg:grid-cols-[1.05fr_0.95fr]">
            <div class="space-y-8">
                <section class="rounded-[2rem] border border-[#e7f1ea] bg-white p-6 shadow-sm dark:border-[#24352b] dark:bg-[#16211b]">
                    <div class="mb-6 flex items-center gap-4">
                        <span class="flex size-16 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <span class="material-symbols-outlined text-3xl">person</span>
                        </span>
                        <div>
                            <h2 class="text-2xl font-extrabold text-text-main dark:text-white"><?= clean($user['full_name'] ?? $user['username'] ?? 'Tài khoản') ?></h2>
                            <p class="text-sm text-text-secondary"><?= clean($user['email'] ?? '') ?></p>
                        </div>
                    </div>

                    <?php if (is_admin()): ?>
                        <a href="admin/dashboard.php" class="mb-6 inline-flex items-center rounded-full border border-[#d8eadf] px-4 py-2 text-sm font-semibold text-text-main transition-colors hover:border-primary hover:text-primary dark:border-[#32483b] dark:text-white">
                            Vào admin dashboard
                        </a>
                    <?php endif; ?>

                    <form method="POST" class="grid gap-4">
                        <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="space-y-2">
                            <label for="full_name" class="text-sm font-semibold text-text-main dark:text-white">Họ và tên</label>
                            <input id="full_name" name="full_name" type="text" value="<?= clean($user['full_name'] ?? '') ?>" class="w-full rounded-2xl border border-[#d8eadf] bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:border-[#32483b] dark:bg-[#101914] dark:text-white">
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="email" class="text-sm font-semibold text-text-main dark:text-white">Email</label>
                                <input id="email" type="email" value="<?= clean($user['email'] ?? '') ?>" class="w-full rounded-2xl border border-[#d8eadf] bg-gray-50 px-4 py-3 text-text-main dark:border-[#32483b] dark:bg-[#101914] dark:text-white" readonly>
                            </div>
                            <div class="space-y-2">
                                <label for="phone" class="text-sm font-semibold text-text-main dark:text-white">Số điện thoại</label>
                                <input id="phone" name="phone" type="text" value="<?= clean($user['phone'] ?? '') ?>" class="w-full rounded-2xl border border-[#d8eadf] bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:border-[#32483b] dark:bg-[#101914] dark:text-white">
                            </div>
                        </div>

                        <button type="submit" class="mt-2 inline-flex items-center justify-center rounded-full bg-primary px-5 py-3 text-sm font-bold text-white shadow-lg shadow-primary/20 transition-colors hover:bg-primary-dark">
                            Lưu thông tin
                        </button>
                    </form>
                </section>

                <section id="addresses" class="rounded-[2rem] border border-[#e7f1ea] bg-white p-6 shadow-sm dark:border-[#24352b] dark:bg-[#16211b]">
                    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-primary">Sổ địa chỉ</p>
                            <h2 class="mt-2 text-2xl font-extrabold text-text-main dark:text-white">Quản lý nhiều địa chỉ giao hàng</h2>
                            <p class="mt-2 text-sm text-text-secondary">Chọn mặc định cho checkout, thêm địa chỉ mới hoặc chỉnh sửa địa chỉ đã có.</p>
                        </div>
                        <a href="profile.php#addresses" class="inline-flex items-center rounded-full border border-[#d8eadf] px-4 py-2 text-sm font-semibold text-text-main transition-colors hover:border-primary hover:text-primary dark:border-[#32483b] dark:text-white">
                            Thêm địa chỉ mới
                        </a>
                    </div>

                    <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                        <div class="space-y-4">
                            <?php if (empty($addresses)): ?>
                                <div class="rounded-[1.5rem] border border-dashed border-[#d8eadf] px-6 py-10 text-center dark:border-[#32483b]">
                                    <span class="material-symbols-outlined text-5xl text-primary/70">home_pin</span>
                                    <p class="mt-4 text-sm text-text-secondary">Bạn chưa lưu địa chỉ nào.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($addresses as $address): ?>
                                    <?php $addressId = (int)$address['id']; ?>
                                    <article class="rounded-[1.5rem] border border-[#edf5ef] p-5 dark:border-[#24352b]">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h3 class="text-lg font-bold text-text-main dark:text-white"><?= clean($address['receiver_name']) ?></h3>
                                                    <?php if (!empty($address['is_default'])): ?>
                                                        <span class="rounded-full bg-[#e9f5ee] px-3 py-1 text-xs font-semibold text-[#2e9b63]">Mặc định</span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="mt-2 text-sm text-text-secondary"><?= clean($address['phone']) ?></p>
                                                <p class="mt-2 text-sm leading-6 text-text-secondary">
                                                    <?= clean(implode(', ', array_filter([
                                                        (string)$address['address_line'],
                                                        (string)($address['ward'] ?? ''),
                                                        (string)$address['district'],
                                                        (string)$address['province'],
                                                    ]))) ?>
                                                </p>
                                            </div>
                                            <div class="flex flex-wrap gap-2">
                                                <?php if (empty($address['is_default'])): ?>
                                                    <form method="POST">
                                                        <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                                                        <input type="hidden" name="action" value="set_default_address">
                                                        <input type="hidden" name="address_id" value="<?= $addressId ?>">
                                                        <button type="submit" class="inline-flex items-center rounded-full border border-[#d8eadf] px-4 py-2 text-sm font-semibold text-text-main transition-colors hover:border-primary hover:text-primary dark:border-[#32483b] dark:text-white">
                                                            Đặt mặc định
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <a href="profile.php?edit_address=<?= $addressId ?>#addresses" class="inline-flex items-center rounded-full border border-[#d8eadf] px-4 py-2 text-sm font-semibold text-text-main transition-colors hover:border-primary hover:text-primary dark:border-[#32483b] dark:text-white">
                                                    Chỉnh sửa
                                                </a>

                                                <form method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa địa chỉ này không?');">
                                                    <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                                                    <input type="hidden" name="action" value="delete_address">
                                                    <input type="hidden" name="address_id" value="<?= $addressId ?>">
                                                    <button type="submit" class="inline-flex items-center rounded-full border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 transition-colors hover:border-red-300 hover:bg-red-50">
                                                        Xóa
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="rounded-[1.5rem] border border-[#edf5ef] bg-[#f8fbf9] p-5 dark:border-[#24352b] dark:bg-[#101914]">
                            <div class="mb-5 flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-primary"><?= $addressFormMode === 'edit' ? 'Chỉnh sửa' : 'Thêm mới' ?></p>
                                    <h3 class="mt-2 text-xl font-extrabold text-text-main dark:text-white"><?= $addressFormMode === 'edit' ? 'Cập nhật địa chỉ' : 'Thêm địa chỉ giao hàng' ?></h3>
                                </div>
                                <?php if ($addressFormMode === 'edit'): ?>
                                    <a href="profile.php#addresses" class="inline-flex items-center rounded-full border border-[#d8eadf] px-4 py-2 text-sm font-semibold text-text-main transition-colors hover:border-primary hover:text-primary dark:border-[#32483b] dark:text-white">
                                        Tạo mới
                                    </a>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($addressErrors)): ?>
                                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                    Vui lòng kiểm tra lại các trường đang báo lỗi trước khi lưu địa chỉ.
                                </div>
                            <?php elseif (!empty($defaultAddress)): ?>
                                <div class="mb-5 rounded-2xl border border-[#dcecdf] bg-white px-4 py-3 text-sm text-[#456a57]">
                                    Checkout sẽ ưu tiên địa chỉ mặc định hiện tại nếu bạn chưa chọn địa chỉ khác.
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="grid gap-4 md:grid-cols-2">
                                <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                                <input type="hidden" name="action" value="save_address">
                                <?php if ($addressFormMode === 'edit'): ?>
                                    <input type="hidden" name="address_id" value="<?= clean((string)$editingAddressId) ?>">
                                <?php endif; ?>

                                <div class="space-y-2 md:col-span-2">
                                    <label for="receiver_name" class="text-sm font-semibold text-text-main dark:text-white">Người nhận</label>
                                    <input id="receiver_name" name="receiver_name" type="text" value="<?= clean($addressForm['receiver_name']) ?>" class="w-full rounded-2xl border <?= isset($addressErrors['receiver_name']) ? 'border-red-300' : 'border-[#d8eadf]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:border-[#32483b] dark:bg-[#101914] dark:text-white">
                                    <?php if (isset($addressErrors['receiver_name'])): ?><p class="text-sm text-red-600"><?= clean($addressErrors['receiver_name']) ?></p><?php endif; ?>
                                </div>

                                <div class="space-y-2">
                                    <label for="address_phone" class="text-sm font-semibold text-text-main dark:text-white">Số điện thoại</label>
                                    <input id="address_phone" name="address_phone" type="text" value="<?= clean($addressForm['phone']) ?>" class="w-full rounded-2xl border <?= isset($addressErrors['phone']) ? 'border-red-300' : 'border-[#d8eadf]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:border-[#32483b] dark:bg-[#101914] dark:text-white">
                                    <?php if (isset($addressErrors['phone'])): ?><p class="text-sm text-red-600"><?= clean($addressErrors['phone']) ?></p><?php endif; ?>
                                </div>

                                <div class="space-y-2">
                                    <label for="province" class="text-sm font-semibold text-text-main dark:text-white">Tỉnh / Thành</label>
                                    <input id="province" name="province" type="text" value="<?= clean($addressForm['province']) ?>" class="w-full rounded-2xl border <?= isset($addressErrors['province']) ? 'border-red-300' : 'border-[#d8eadf]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:border-[#32483b] dark:bg-[#101914] dark:text-white">
                                    <?php if (isset($addressErrors['province'])): ?><p class="text-sm text-red-600"><?= clean($addressErrors['province']) ?></p><?php endif; ?>
                                </div>

                                <div class="space-y-2">
                                    <label for="district" class="text-sm font-semibold text-text-main dark:text-white">Quận / Huyện</label>
                                    <input id="district" name="district" type="text" value="<?= clean($addressForm['district']) ?>" class="w-full rounded-2xl border <?= isset($addressErrors['district']) ? 'border-red-300' : 'border-[#d8eadf]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:border-[#32483b] dark:bg-[#101914] dark:text-white">
                                    <?php if (isset($addressErrors['district'])): ?><p class="text-sm text-red-600"><?= clean($addressErrors['district']) ?></p><?php endif; ?>
                                </div>

                                <div class="space-y-2">
                                    <label for="ward" class="text-sm font-semibold text-text-main dark:text-white">Phường / Xã</label>
                                    <input id="ward" name="ward" type="text" value="<?= clean($addressForm['ward']) ?>" class="w-full rounded-2xl border border-[#d8eadf] bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:border-[#32483b] dark:bg-[#101914] dark:text-white">
                                </div>

                                <div class="space-y-2 md:col-span-2">
                                    <label for="address_line" class="text-sm font-semibold text-text-main dark:text-white">Địa chỉ cụ thể</label>
                                    <textarea id="address_line" name="address_line" rows="3" class="w-full rounded-2xl border <?= isset($addressErrors['address_line']) ? 'border-red-300' : 'border-[#d8eadf]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:border-[#32483b] dark:bg-[#101914] dark:text-white"><?= clean($addressForm['address_line']) ?></textarea>
                                    <?php if (isset($addressErrors['address_line'])): ?><p class="text-sm text-red-600"><?= clean($addressErrors['address_line']) ?></p><?php endif; ?>
                                </div>

                                <label class="md:col-span-2 flex items-start gap-3 rounded-2xl border border-[#d8eadf] bg-white px-4 py-3 dark:border-[#32483b] dark:bg-[#101914]">
                                    <input type="checkbox" name="make_default" value="1" <?= $addressForm['make_default'] === '1' ? 'checked' : '' ?> class="mt-1 rounded border-[#d8eadf] text-primary focus:ring-primary">
                                    <span>
                                        <span class="block text-sm font-semibold text-text-main dark:text-white">Đặt làm địa chỉ mặc định</span>
                                        <span class="mt-1 block text-xs text-text-secondary">Checkout sẽ ưu tiên địa chỉ này ở lần mua tiếp theo.</span>
                                    </span>
                                </label>

                                <div class="md:col-span-2 flex flex-wrap gap-3">
                                    <button type="submit" class="inline-flex items-center justify-center rounded-full bg-primary px-5 py-3 text-sm font-bold text-white shadow-lg shadow-primary/20 transition-colors hover:bg-primary-dark">
                                        <?= $addressFormMode === 'edit' ? 'Lưu thay đổi' : 'Thêm địa chỉ' ?>
                                    </button>
                                    <?php if ($addressFormMode === 'edit'): ?>
                                        <a href="profile.php#addresses" class="inline-flex items-center justify-center rounded-full border border-[#d8eadf] px-5 py-3 text-sm font-bold text-text-main transition-colors hover:border-primary hover:text-primary dark:border-[#32483b] dark:text-white">
                                            Hủy chỉnh sửa
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>

            <section id="orders" class="rounded-[2rem] border border-[#e7f1ea] bg-white p-6 shadow-sm dark:border-[#24352b] dark:bg-[#16211b]">
                <div class="mb-6 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-extrabold text-text-main dark:text-white">Đơn hàng gần đây</h2>
                        <p class="mt-2 text-sm text-text-secondary">Thông tin này sẽ đồng bộ với đơn tạo từ trang checkout.</p>
                    </div>
                    <a href="orders.php" class="inline-flex items-center rounded-full border border-[#d8eadf] px-4 py-2 text-sm font-semibold text-text-main transition-colors hover:border-primary hover:text-primary dark:border-[#32483b] dark:text-white">
                        Xem tất cả
                    </a>
                </div>

                <?php if (empty($orders)): ?>
                    <div class="rounded-[1.5rem] border border-dashed border-[#d8eadf] px-6 py-12 text-center dark:border-[#32483b]">
                        <span class="material-symbols-outlined text-5xl text-primary/70">receipt_long</span>
                        <p class="mt-4 text-sm text-text-secondary">Bạn chưa có đơn hàng nào.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($orders as $order): ?>
                            <?php
                            $orderStatus = profile_order_status_meta((string)$order['order_status']);
                            $paymentStatus = profile_payment_status_meta((string)$order['payment_status']);
                            ?>
                            <article class="rounded-[1.5rem] border border-[#edf5ef] p-5 dark:border-[#24352b]">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary"><?= clean($order['order_number']) ?></p>
                                        <h3 class="mt-2 text-lg font-bold text-text-main dark:text-white"><?= clean($order['full_name']) ?></h3>
                                        <p class="mt-1 text-sm text-text-secondary"><?= clean($order['address']) ?></p>
                                    </div>
                                    <div class="text-left sm:text-right">
                                        <p class="text-sm font-semibold text-text-main dark:text-white"><?= format_currency((float)$order['total_amount']) ?></p>
                                        <p class="mt-1 text-xs text-text-secondary">
                                            <?= clean((string)($order['total_quantity'] ?? $order['item_count'])) ?> sản phẩm
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap items-center gap-3 text-xs font-semibold">
                                    <span class="rounded-full px-3 py-1 <?= clean($orderStatus['class']) ?>"><?= clean($orderStatus['label']) ?></span>
                                    <span class="rounded-full px-3 py-1 <?= clean($paymentStatus['class']) ?>"><?= clean($paymentStatus['label']) ?></span>
                                    <span class="text-text-secondary"><?= format_date($order['created_at'], 'd/m/Y H:i') ?></span>
                                </div>

                                <div class="mt-4">
                                    <a href="order-detail.php?id=<?= clean((string)$order['id']) ?>" class="inline-flex items-center rounded-full border border-[#d8eadf] px-4 py-2 text-sm font-semibold text-text-main transition-colors hover:border-primary hover:text-primary dark:border-[#32483b] dark:text-white">
                                        Xem chi tiết đơn hàng
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
