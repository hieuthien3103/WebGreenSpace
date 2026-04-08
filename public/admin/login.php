<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

if (is_logged_in() && is_admin()) {
    redirect(admin_path('dashboard.php'));
}

$pageTitle = 'Đăng nhập admin - GreenSpace';
$errors = [];
$old = [
    'identifier' => '',
];
$redirectTarget = safe_redirect_target($_GET['redirect'] ?? $_POST['redirect'] ?? 'dashboard.php', 'dashboard.php');
$redirectTarget = str_starts_with($redirectTarget, 'admin/') ? substr($redirectTarget, 6) : ltrim($redirectTarget, '/');
$redirectTarget = $redirectTarget !== '' ? $redirectTarget : 'dashboard.php';
$switchingAccount = is_logged_in() && !is_admin();
$flash = get_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['identifier'] = trim((string)($_POST['identifier'] ?? ''));

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['general'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
    } else {
        $authService = new AuthService();
        $result = $authService->loginAdmin($old['identifier'], (string)($_POST['password'] ?? ''));

        if (!empty($result['success'])) {
            set_flash('success', 'Đăng nhập admin thành công.');
            redirect(admin_path($redirectTarget));
        }

        $errors = $result['errors'] ?? ['general' => 'Không thể đăng nhập admin lúc này.'];
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= clean($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#102118',
                        accent: '#2e9b63',
                    },
                    fontFamily: {
                        display: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                },
            },
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(46,155,99,0.20),_transparent_32%),linear-gradient(160deg,#eef6f1_0%,#f9fcfa_45%,#e5f0e9_100%)] font-display text-[#102118]">
    <main class="mx-auto flex min-h-screen w-full max-w-7xl items-center px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid w-full overflow-hidden rounded-[2rem] border border-[#d7e7dc] bg-white shadow-[0_32px_90px_rgba(16,33,24,0.14)] lg:grid-cols-[0.95fr_1.05fr]">
            <section class="flex flex-col justify-between bg-[linear-gradient(160deg,#102118_0%,#173526_55%,#1f4733_100%)] p-8 text-white sm:p-10">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-semibold backdrop-blur">
                        <span class="material-symbols-outlined text-[18px]">shield_person</span>
                        GreenSpace Admin
                    </span>
                    <h1 class="mt-6 text-4xl font-extrabold leading-tight">Đăng nhập riêng cho khu vực quản trị.</h1>
                    <p class="mt-4 max-w-md text-sm leading-7 text-white/75">
                        Trang này chỉ chấp nhận tài khoản có quyền admin. Người dùng thường sẽ tiếp tục đăng nhập ở khu mua sắm chính.
                    </p>
                </div>

                <div class="space-y-3 text-sm text-white/80">
                    <div class="rounded-2xl bg-white/10 px-4 py-3">
                        <p class="font-semibold text-white">URL admin</p>
                        <p class="mt-1">/admin/login</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 px-4 py-3">
                        <p class="font-semibold text-white">Tài khoản seed mẫu</p>
                        <p class="mt-1">admin@webgreenspace.com / password</p>
                    </div>
                </div>
            </section>

            <section class="p-6 sm:p-8 lg:p-10">
                <div class="mx-auto w-full max-w-md">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-[#2e9b63]">Secure access</p>
                            <h2 class="mt-3 text-3xl font-extrabold text-[#102118]">Admin login</h2>
                        </div>
                        <a href="../home.php" class="inline-flex items-center rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">
                            Về shop
                        </a>
                    </div>

                    <p class="mt-3 text-sm leading-6 text-[#527263]">
                        Dùng email hoặc tên đăng nhập admin để vào dashboard, duyệt đơn và quản lý dữ liệu hệ thống.
                    </p>

                    <?php if ($flash): ?>
                        <div class="mt-6 rounded-2xl border px-4 py-3 text-sm font-medium <?= ($flash['type'] ?? '') === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800' ?>">
                            <?= clean($flash['message'] ?? '') ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($switchingAccount): ?>
                        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                            Bạn đang có phiên người dùng thường. Đăng nhập tại đây sẽ chuyển sang phiên admin nếu tài khoản hợp lệ.
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors['general'])): ?>
                        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                            <?= clean($errors['general']) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="mt-8 space-y-5">
                        <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                        <input type="hidden" name="redirect" value="<?= clean($redirectTarget) ?>">

                        <div class="space-y-2">
                            <label for="identifier" class="text-sm font-semibold text-[#102118]">Email hoặc tên đăng nhập admin</label>
                            <input
                                id="identifier"
                                name="identifier"
                                type="text"
                                value="<?= clean($old['identifier']) ?>"
                                class="w-full rounded-2xl border <?= !empty($errors['identifier']) ? 'border-red-300' : 'border-[#d8eadf]' ?> bg-white px-4 py-3 text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]/20"
                                placeholder="admin@webgreenspace.com"
                                autocomplete="username"
                            >
                            <?php if (!empty($errors['identifier'])): ?>
                                <p class="text-sm text-red-600"><?= clean($errors['identifier']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="space-y-2">
                            <label for="password" class="text-sm font-semibold text-[#102118]">Mật khẩu</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="w-full rounded-2xl border <?= !empty($errors['password']) ? 'border-red-300' : 'border-[#d8eadf]' ?> bg-white px-4 py-3 text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]/20"
                                placeholder="Nhập mật khẩu"
                                autocomplete="current-password"
                            >
                            <?php if (!empty($errors['password'])): ?>
                                <p class="text-sm text-red-600"><?= clean($errors['password']) ?></p>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-[#102118] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-[#102118]/15 transition-colors hover:bg-[#1d392b]">
                            Đăng nhập admin
                        </button>
                    </form>

                    <div class="mt-6 flex flex-col gap-3 text-sm text-[#527263] sm:flex-row sm:items-center sm:justify-between">
                        <a href="../login.php" class="font-semibold text-[#2e9b63] hover:text-[#1f7a4b]">
                            Đăng nhập người dùng
                        </a>
                        <a href="../home.php" class="font-semibold text-[#102118] hover:text-[#2e9b63]">
                            Quay lại trang chủ
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
