<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!is_logged_in()) {
    redirect('login.php?redirect=' . urlencode('checkout.php'));
}

$cartService = new CartService();
if ($cartService->isEmpty()) {
    set_flash('error', 'Giỏ hàng đang trống. Vui lòng thêm sản phẩm trước khi thanh toán.');
    redirect('cart.php');
}

$summary = $cartService->getSummary();
$items = $summary['items'];
$userModel = new User();
$addressModel = new Address();
$currentUser = $userModel->findById((int)get_user_id()) ?? get_user();
$defaultAddress = $addressModel->getDefaultByUserId((int)get_user_id());

$values = [
    'full_name' => (string)($defaultAddress['receiver_name'] ?? $currentUser['full_name'] ?? ''),
    'email' => (string)($currentUser['email'] ?? ''),
    'phone' => (string)($defaultAddress['phone'] ?? $currentUser['phone'] ?? ''),
    'province' => (string)($defaultAddress['province'] ?? ''),
    'district' => (string)($defaultAddress['district'] ?? ''),
    'ward' => (string)($defaultAddress['ward'] ?? ''),
    'address_line' => (string)($defaultAddress['address_line'] ?? ''),
    'note' => '',
    'payment_method' => 'cod',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $_) {
        $values[$key] = trim((string)($_POST[$key] ?? ''));
    }

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['general'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
    } else {
        $checkoutService = new CheckoutService();
        $result = $checkoutService->placeOrder((int)get_user_id(), $_POST);

        if (!empty($result['success'])) {
            set_flash('success', 'Đặt hàng thành công. Đơn của bạn đã được tạo.');
            redirect('profile.php?tab=orders');
        }

        $errors = $result['errors'] ?? ['general' => 'Không thể thanh toán lúc này.'];
    }
}

$pageTitle = 'Checkout - GreenSpace';
$currentPage = '';

include 'includes/header.php';
?>

<main class="flex-1">
    <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-primary">Checkout</p>
            <h1 class="mt-2 text-4xl font-extrabold text-text-main dark:text-white">Thanh toán đơn hàng</h1>
            <p class="mt-3 text-sm text-text-secondary">Nhập thông tin người nhận và hoàn tất đơn mua của bạn.</p>
        </div>

        <div class="grid gap-8 lg:grid-cols-[1.08fr_0.92fr]">
            <section class="rounded-[2rem] border border-[#e7f1ea] bg-white p-6 shadow-sm dark:border-[#24352b] dark:bg-[#16211b]">
                <?php if (!empty($errors['general'])): ?>
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                        <?= clean($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="grid gap-5 md:grid-cols-2">
                    <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">

                    <div class="space-y-2 md:col-span-2">
                        <label for="full_name" class="text-sm font-semibold text-text-main dark:text-white">Họ tên người nhận</label>
                        <input id="full_name" name="full_name" type="text" value="<?= clean($values['full_name']) ?>" class="w-full rounded-2xl border <?= !empty($errors['full_name']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#32483b]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white">
                        <?php if (!empty($errors['full_name'])): ?><p class="text-sm text-red-600"><?= clean($errors['full_name']) ?></p><?php endif; ?>
                    </div>

                    <div class="space-y-2">
                        <label for="email" class="text-sm font-semibold text-text-main dark:text-white">Email</label>
                        <input id="email" name="email" type="email" value="<?= clean($values['email']) ?>" class="w-full rounded-2xl border <?= !empty($errors['email']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#32483b]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white">
                        <?php if (!empty($errors['email'])): ?><p class="text-sm text-red-600"><?= clean($errors['email']) ?></p><?php endif; ?>
                    </div>

                    <div class="space-y-2">
                        <label for="phone" class="text-sm font-semibold text-text-main dark:text-white">Số điện thoại</label>
                        <input id="phone" name="phone" type="text" value="<?= clean($values['phone']) ?>" class="w-full rounded-2xl border <?= !empty($errors['phone']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#32483b]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white">
                        <?php if (!empty($errors['phone'])): ?><p class="text-sm text-red-600"><?= clean($errors['phone']) ?></p><?php endif; ?>
                    </div>

                    <div class="space-y-2">
                        <label for="province" class="text-sm font-semibold text-text-main dark:text-white">Tỉnh / Thành</label>
                        <input id="province" name="province" type="text" value="<?= clean($values['province']) ?>" class="w-full rounded-2xl border <?= !empty($errors['province']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#32483b]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white">
                        <?php if (!empty($errors['province'])): ?><p class="text-sm text-red-600"><?= clean($errors['province']) ?></p><?php endif; ?>
                    </div>

                    <div class="space-y-2">
                        <label for="district" class="text-sm font-semibold text-text-main dark:text-white">Quận / Huyện</label>
                        <input id="district" name="district" type="text" value="<?= clean($values['district']) ?>" class="w-full rounded-2xl border <?= !empty($errors['district']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#32483b]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white">
                        <?php if (!empty($errors['district'])): ?><p class="text-sm text-red-600"><?= clean($errors['district']) ?></p><?php endif; ?>
                    </div>

                    <div class="space-y-2">
                        <label for="ward" class="text-sm font-semibold text-text-main dark:text-white">Phường / Xã</label>
                        <input id="ward" name="ward" type="text" value="<?= clean($values['ward']) ?>" class="w-full rounded-2xl border border-[#d8eadf] bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:border-[#32483b] dark:bg-[#101914] dark:text-white">
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label for="address_line" class="text-sm font-semibold text-text-main dark:text-white">Địa chỉ cụ thể</label>
                        <textarea id="address_line" name="address_line" rows="3" class="w-full rounded-2xl border <?= !empty($errors['address_line']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#32483b]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white"><?= clean($values['address_line']) ?></textarea>
                        <?php if (!empty($errors['address_line'])): ?><p class="text-sm text-red-600"><?= clean($errors['address_line']) ?></p><?php endif; ?>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label for="note" class="text-sm font-semibold text-text-main dark:text-white">Ghi chú giao hàng</label>
                        <textarea id="note" name="note" rows="3" class="w-full rounded-2xl border border-[#d8eadf] bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:border-[#32483b] dark:bg-[#101914] dark:text-white"><?= clean($values['note']) ?></textarea>
                    </div>

                    <div class="space-y-3 md:col-span-2">
                        <p class="text-sm font-semibold text-text-main dark:text-white">Phương thức thanh toán</p>
                        <label class="flex items-start gap-3 rounded-2xl border border-[#d8eadf] p-4 transition-colors hover:border-primary dark:border-[#32483b]">
                            <input type="radio" name="payment_method" value="cod" class="mt-1 text-primary focus:ring-primary" <?= $values['payment_method'] === 'cod' ? 'checked' : '' ?>>
                            <span>
                                <span class="block font-semibold text-text-main dark:text-white">Thanh toán khi nhận hàng</span>
                                <span class="text-sm text-text-secondary">Phù hợp cho đơn hàng cần xác nhận thủ công.</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-3 rounded-2xl border border-[#d8eadf] p-4 transition-colors hover:border-primary dark:border-[#32483b]">
                            <input type="radio" name="payment_method" value="online_mock" class="mt-1 text-primary focus:ring-primary" <?= $values['payment_method'] === 'online_mock' ? 'checked' : '' ?>>
                            <span>
                                <span class="block font-semibold text-text-main dark:text-white">Online mock</span>
                                <span class="text-sm text-text-secondary">Giả lập thanh toán online để chạy demo đồ án.</span>
                            </span>
                        </label>
                        <?php if (!empty($errors['payment_method'])): ?><p class="text-sm text-red-600"><?= clean($errors['payment_method']) ?></p><?php endif; ?>
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-primary px-5 py-3 text-sm font-bold text-white shadow-lg shadow-primary/20 transition-colors hover:bg-primary-dark">
                            Đặt hàng ngay
                        </button>
                    </div>
                </form>
            </section>

            <aside class="rounded-[2rem] border border-[#e7f1ea] bg-white p-6 shadow-sm dark:border-[#24352b] dark:bg-[#16211b]">
                <h2 class="text-xl font-extrabold text-text-main dark:text-white">Đơn hàng của bạn</h2>
                <div class="mt-6 space-y-4">
                    <?php foreach ($items as $item): ?>
                        <div class="flex items-center gap-4 rounded-2xl border border-[#edf5ef] p-3 dark:border-[#24352b]">
                            <img src="<?= clean($item['image_url']) ?>" alt="<?= clean($item['name']) ?>" class="size-16 rounded-2xl object-cover">
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold text-text-main dark:text-white"><?= clean($item['name']) ?></p>
                                <p class="text-sm text-text-secondary"><?= (int)$item['quantity'] ?> x <?= format_currency($item['price']) ?></p>
                            </div>
                            <p class="text-sm font-bold text-primary"><?= format_currency($item['subtotal']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-6 space-y-4 border-t border-dashed border-[#d8eadf] pt-6 text-sm dark:border-[#32483b]">
                    <div class="flex items-center justify-between text-text-secondary">
                        <span>Tạm tính</span>
                        <span class="font-semibold text-text-main dark:text-white"><?= format_currency($summary['subtotal']) ?></span>
                    </div>
                    <div class="flex items-center justify-between text-text-secondary">
                        <span>Phí vận chuyển</span>
                        <span class="font-semibold text-text-main dark:text-white"><?= format_currency($summary['shipping_fee']) ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-base font-bold text-text-main dark:text-white">Tổng cộng</span>
                        <span class="text-2xl font-extrabold text-primary"><?= format_currency($summary['total']) ?></span>
                    </div>
                </div>

                <a href="cart.php" class="mt-8 inline-flex w-full items-center justify-center rounded-full border border-[#d8eadf] px-5 py-3 text-sm font-semibold text-text-main transition-colors hover:border-primary hover:text-primary dark:border-[#32483b] dark:text-white">
                    Quay lại giỏ hàng
                </a>
            </aside>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
