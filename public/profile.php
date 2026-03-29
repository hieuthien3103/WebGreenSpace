<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!is_logged_in()) {
    redirect('login.php?redirect=' . urlencode('profile.php'));
}

$userModel = new User();
$addressModel = new Address();
$orderModel = new Order();
$userId = (int)get_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
        redirect('profile.php');
    }

    $action = (string)($_POST['action'] ?? '');

    if ($action === 'update_profile') {
        $fullName = trim((string)($_POST['full_name'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));

        if ($fullName === '') {
            set_flash('error', 'Họ tên không được để trống.');
        } else {
            $userModel->updateProfile($userId, [
                'full_name' => $fullName,
                'phone' => $phone,
            ]);

            $freshUser = $userModel->findById($userId);
            if ($freshUser) {
                $_SESSION['user_data'] = $userModel->withoutPassword($freshUser);
            }

            set_flash('success', 'Đã cập nhật thông tin tài khoản.');
        }
    } elseif ($action === 'update_address') {
        $addressModel->saveDefault($userId, [
            'receiver_name' => trim((string)($_POST['receiver_name'] ?? '')),
            'phone' => trim((string)($_POST['address_phone'] ?? '')),
            'province' => trim((string)($_POST['province'] ?? '')),
            'district' => trim((string)($_POST['district'] ?? '')),
            'ward' => trim((string)($_POST['ward'] ?? '')),
            'address_line' => trim((string)($_POST['address_line'] ?? '')),
        ]);

        set_flash('success', 'Đã cập nhật địa chỉ mặc định.');
    }

    redirect('profile.php');
}

$user = $userModel->findById($userId) ?? get_user();
$defaultAddress = $addressModel->getDefaultByUserId($userId);
$orders = $orderModel->getByUserId($userId, 8);
$pageTitle = 'Profile - GreenSpace';
$currentPage = '';

include 'includes/header.php';
?>

<main class="flex-1">
    <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-primary">Profile</p>
            <h1 class="mt-2 text-4xl font-extrabold text-text-main dark:text-white">Tài khoản của bạn</h1>
            <p class="mt-3 text-sm text-text-secondary">Quản lý thông tin cá nhân, địa chỉ mặc định và lịch sử đơn hàng.</p>
        </div>

        <div class="grid gap-8 lg:grid-cols-[0.95fr_1.05fr]">
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

                <section class="rounded-[2rem] border border-[#e7f1ea] bg-white p-6 shadow-sm dark:border-[#24352b] dark:bg-[#16211b]">
                    <div class="mb-6">
                        <h2 class="text-2xl font-extrabold text-text-main dark:text-white">Địa chỉ mặc định</h2>
                        <p class="mt-2 text-sm text-text-secondary">Địa chỉ này sẽ được điền sẵn trong checkout.</p>
                    </div>

                    <form method="POST" class="grid gap-4 md:grid-cols-2">
                        <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                        <input type="hidden" name="action" value="update_address">

                        <div class="space-y-2 md:col-span-2">
                            <label for="receiver_name" class="text-sm font-semibold text-text-main dark:text-white">Người nhận</label>
                            <input id="receiver_name" name="receiver_name" type="text" value="<?= clean($defaultAddress['receiver_name'] ?? $user['full_name'] ?? '') ?>" class="w-full rounded-2xl border border-[#d8eadf] bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:border-[#32483b] dark:bg-[#101914] dark:text-white">
                        </div>

                        <div class="space-y-2">
                            <label for="address_phone" class="text-sm font-semibold text-text-main dark:text-white">Số điện thoại</label>
                            <input id="address_phone" name="address_phone" type="text" value="<?= clean($defaultAddress['phone'] ?? $user['phone'] ?? '') ?>" class="w-full rounded-2xl border border-[#d8eadf] bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:border-[#32483b] dark:bg-[#101914] dark:text-white">
                        </div>

                        <div class="space-y-2">
                            <label for="province" class="text-sm font-semibold text-text-main dark:text-white">Tỉnh / Thành</label>
                            <input id="province" name="province" type="text" value="<?= clean($defaultAddress['province'] ?? '') ?>" class="w-full rounded-2xl border border-[#d8eadf] bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:border-[#32483b] dark:bg-[#101914] dark:text-white">
                        </div>

                        <div class="space-y-2">
                            <label for="district" class="text-sm font-semibold text-text-main dark:text-white">Quận / Huyện</label>
                            <input id="district" name="district" type="text" value="<?= clean($defaultAddress['district'] ?? '') ?>" class="w-full rounded-2xl border border-[#d8eadf] bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:border-[#32483b] dark:bg-[#101914] dark:text-white">
                        </div>

                        <div class="space-y-2">
                            <label for="ward" class="text-sm font-semibold text-text-main dark:text-white">Phường / Xã</label>
                            <input id="ward" name="ward" type="text" value="<?= clean($defaultAddress['ward'] ?? '') ?>" class="w-full rounded-2xl border border-[#d8eadf] bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:border-[#32483b] dark:bg-[#101914] dark:text-white">
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <label for="address_line" class="text-sm font-semibold text-text-main dark:text-white">Địa chỉ cụ thể</label>
                            <textarea id="address_line" name="address_line" rows="3" class="w-full rounded-2xl border border-[#d8eadf] bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:border-[#32483b] dark:bg-[#101914] dark:text-white"><?= clean($defaultAddress['address_line'] ?? '') ?></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit" class="inline-flex items-center justify-center rounded-full border border-[#d8eadf] px-5 py-3 text-sm font-bold text-text-main transition-colors hover:border-primary hover:text-primary dark:border-[#32483b] dark:text-white">
                                Lưu địa chỉ
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <section class="rounded-[2rem] border border-[#e7f1ea] bg-white p-6 shadow-sm dark:border-[#24352b] dark:bg-[#16211b]">
                <div class="mb-6 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-extrabold text-text-main dark:text-white">Đơn hàng gần đây</h2>
                        <p class="mt-2 text-sm text-text-secondary">Thông tin này sẽ đồng bộ với đơn tạo từ trang checkout.</p>
                    </div>
                </div>

                <?php if (empty($orders)): ?>
                    <div class="rounded-[1.5rem] border border-dashed border-[#d8eadf] px-6 py-12 text-center dark:border-[#32483b]">
                        <span class="material-symbols-outlined text-5xl text-primary/70">receipt_long</span>
                        <p class="mt-4 text-sm text-text-secondary">Bạn chưa có đơn hàng nào.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($orders as $order): ?>
                            <article class="rounded-[1.5rem] border border-[#edf5ef] p-5 dark:border-[#24352b]">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary"><?= clean($order['order_number']) ?></p>
                                        <h3 class="mt-2 text-lg font-bold text-text-main dark:text-white"><?= clean($order['full_name']) ?></h3>
                                        <p class="mt-1 text-sm text-text-secondary"><?= clean($order['address']) ?></p>
                                    </div>
                                    <div class="text-left sm:text-right">
                                        <p class="text-sm font-semibold text-text-main dark:text-white"><?= format_currency((float)$order['total_amount']) ?></p>
                                        <p class="mt-1 text-xs text-text-secondary"><?= clean((string)$order['item_count']) ?> sản phẩm</p>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap items-center gap-3 text-xs font-semibold">
                                    <span class="rounded-full bg-[#edf8f1] px-3 py-1 text-primary dark:bg-[#0f2a1c]"><?= clean($order['order_status']) ?></span>
                                    <span class="rounded-full bg-[#f2f4f3] px-3 py-1 text-text-secondary dark:bg-[#101914]"><?= clean($order['payment_status']) ?></span>
                                    <span class="text-text-secondary"><?= format_date($order['created_at'], 'd/m/Y H:i') ?></span>
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
