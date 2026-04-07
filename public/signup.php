<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (is_logged_in()) {
    redirect('home.php');
}

$pageTitle = 'Đăng ký - GreenSpace';
$currentPage = '';
$errors = [];
$old = [
    'full_name' => '',
    'username' => '',
    'email' => '',
    'phone' => '',
];
$redirectTarget = safe_redirect_target($_GET['redirect'] ?? $_POST['redirect'] ?? 'home.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'full_name' => trim((string)($_POST['full_name'] ?? '')),
        'username' => trim((string)($_POST['username'] ?? '')),
        'email' => trim((string)($_POST['email'] ?? '')),
        'phone' => trim((string)($_POST['phone'] ?? '')),
    ];

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['general'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
    } else {
        $authService = new AuthService();
        $result = $authService->register($_POST);

        if (!empty($result['success'])) {
            set_flash('success', 'Tạo tài khoản thành công. Bạn đã được đăng nhập.');
            redirect($redirectTarget);
        }

        $errors = $result['errors'] ?? ['general' => 'Không thể tạo tài khoản lúc này.'];
    }
}

include 'includes/header.php';
?>

<main class="flex-1">
    <div class="mx-auto flex w-full max-w-7xl items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid w-full max-w-6xl overflow-hidden rounded-[2rem] border border-[#e7f1ea] bg-white shadow-[0_30px_80px_rgba(15,26,20,0.08)] lg:grid-cols-[0.95fr_1.05fr] dark:border-[#24352b] dark:bg-[#16211b]">
            <section class="p-6 sm:p-8 lg:p-10">
                <div class="mx-auto w-full max-w-xl">
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-primary">Create account</p>
                    <h1 class="mt-3 text-3xl font-extrabold text-text-main dark:text-white">Đăng ký tài khoản mới</h1>
                    <p class="mt-3 text-sm leading-6 text-text-secondary">Điền thông tin cơ bản để bắt đầu mua sắm và lưu thông tin giao hàng của bạn.</p>

                    <?php if (!empty($errors['general'])): ?>
                        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                            <?= clean($errors['general']) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="mt-8 grid gap-5 md:grid-cols-2">
                        <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                        <input type="hidden" name="redirect" value="<?= clean($redirectTarget) ?>">

                        <div class="space-y-2 md:col-span-2">
                            <label for="full_name" class="text-sm font-semibold text-text-main dark:text-white">Họ và tên</label>
                            <input
                                id="full_name"
                                name="full_name"
                                type="text"
                                value="<?= clean($old['full_name']) ?>"
                                class="w-full rounded-2xl border <?= !empty($errors['full_name']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#2d4337]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white"
                                placeholder="Nguyễn Văn A"
                                autocomplete="name"
                            >
                            <?php if (!empty($errors['full_name'])): ?>
                                <p class="text-sm text-red-600"><?= clean($errors['full_name']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="space-y-2">
                            <label for="username" class="text-sm font-semibold text-text-main dark:text-white">Tên đăng nhập</label>
                            <input
                                id="username"
                                name="username"
                                type="text"
                                value="<?= clean($old['username']) ?>"
                                class="w-full rounded-2xl border <?= !empty($errors['username']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#2d4337]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white"
                                placeholder="nguyenvana"
                                autocomplete="username"
                            >
                            <?php if (!empty($errors['username'])): ?>
                                <p class="text-sm text-red-600"><?= clean($errors['username']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="space-y-2">
                            <label for="phone" class="text-sm font-semibold text-text-main dark:text-white">Số điện thoại</label>
                            <input
                                id="phone"
                                name="phone"
                                type="text"
                                value="<?= clean($old['phone']) ?>"
                                class="w-full rounded-2xl border <?= !empty($errors['phone']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#2d4337]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white"
                                placeholder="0901234567"
                                autocomplete="tel"
                            >
                            <?php if (!empty($errors['phone'])): ?>
                                <p class="text-sm text-red-600"><?= clean($errors['phone']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <label for="email" class="text-sm font-semibold text-text-main dark:text-white">Email</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="<?= clean($old['email']) ?>"
                                class="w-full rounded-2xl border <?= !empty($errors['email']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#2d4337]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white"
                                placeholder="ban@example.com"
                                autocomplete="email"
                            >
                            <?php if (!empty($errors['email'])): ?>
                                <p class="text-sm text-red-600"><?= clean($errors['email']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="space-y-2">
                            <label for="password" class="text-sm font-semibold text-text-main dark:text-white">Mật khẩu</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="w-full rounded-2xl border <?= !empty($errors['password']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#2d4337]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white"
                                placeholder="Ít nhất 6 ký tự"
                                autocomplete="new-password"
                            >
                            <?php if (!empty($errors['password'])): ?>
                                <p class="text-sm text-red-600"><?= clean($errors['password']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="space-y-2">
                            <label for="confirm_password" class="text-sm font-semibold text-text-main dark:text-white">Nhập lại mật khẩu</label>
                            <input
                                id="confirm_password"
                                name="confirm_password"
                                type="password"
                                class="w-full rounded-2xl border <?= !empty($errors['confirm_password']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#2d4337]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white"
                                placeholder="Nhập lại mật khẩu"
                                autocomplete="new-password"
                            >
                            <?php if (!empty($errors['confirm_password'])): ?>
                                <p class="text-sm text-red-600"><?= clean($errors['confirm_password']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-primary px-5 py-3 text-sm font-bold text-white shadow-lg shadow-primary/20 transition-colors hover:bg-primary-dark">
                                Tạo tài khoản
                            </button>
                        </div>
                    </form>

                    <p class="mt-6 text-sm text-text-secondary">
                        Đã có tài khoản?
                        <a href="login.php<?= $redirectTarget !== 'home.php' ? '?redirect=' . urlencode($redirectTarget) : '' ?>" class="font-semibold text-primary hover:text-primary-dark">
                            Đăng nhập ngay
                        </a>
                    </p>
                </div>
            </section>

            <section class="hidden lg:flex flex-col justify-between bg-[linear-gradient(160deg,#eef8f1_0%,#dff3e7_45%,#cbeac8_100%)] p-10 text-[#12301f] dark:bg-[linear-gradient(160deg,#143322_0%,#1e4a2f_100%)] dark:text-white">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/60 px-4 py-2 text-sm font-semibold backdrop-blur dark:bg-white/10">
                        <span class="material-symbols-outlined text-[18px]">eco</span>
                        GreenSpace starter
                    </span>
                    <h2 class="mt-6 text-4xl font-extrabold leading-tight">Bắt đầu hành trình xanh hóa không gian sống của bạn.</h2>
                    <p class="mt-4 max-w-md text-sm leading-7 text-[#2c5b40] dark:text-white/75">
                        Sau khi tạo tài khoản, bạn có thể lưu thông tin đặt hàng, theo dõi lịch sử mua sắm và sẵn sàng cho các tính năng profile, cart, checkout tiếp theo.
                    </p>
                </div>

                <div class="grid gap-3 text-sm">
                    <div class="rounded-2xl bg-white/70 px-4 py-4 dark:bg-white/10">
                        <p class="font-semibold">Nhanh hơn khi đặt hàng</p>
                        <p class="mt-1 text-[#41684f] dark:text-white/70">Thông tin liên hệ và địa chỉ sẽ dễ tiếp tục mở rộng ở các bước sau.</p>
                    </div>
                    <div class="rounded-2xl bg-white/70 px-4 py-4 dark:bg-white/10">
                        <p class="font-semibold">Theo dõi tài khoản dễ dàng</p>
                        <p class="mt-1 text-[#41684f] dark:text-white/70">Hệ thống đang sử dụng bảng users hiện tại trong MySQL, không tạo schema riêng.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
