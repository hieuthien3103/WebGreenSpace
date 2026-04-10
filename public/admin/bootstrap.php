<?php
require_once __DIR__ . '/../../config/config.php';

if (empty($GLOBALS['mvc_template_rendering'])) {
    if (!is_logged_in()) {
        $redirectTarget = basename((string)parse_url($_SERVER['REQUEST_URI'] ?? 'dashboard.php', PHP_URL_PATH));
        set_flash('error', 'Vui lòng đăng nhập bằng tài khoản admin.');
        redirect('login.php?redirect=' . urlencode($redirectTarget ?: 'dashboard.php'));
    }

    if (!is_admin()) {
        set_flash('error', 'Bạn không có quyền truy cập khu vực admin.');
        redirect('../home.php');
    }
}

function admin_nav_items(): array {
    return [
        'dashboard.php' => ['label' => 'Dashboard'],
        'orders.php' => ['label' => 'Quản lý đơn hàng', 'permission' => 'orders.manage'],
        'contacts.php' => ['label' => 'Liên hệ khách hàng', 'permission' => 'contacts.manage'],
        'products.php' => ['label' => 'Quản lý sản phẩm', 'permission' => 'products.manage'],
        'categories.php' => ['label' => 'Quản lý danh mục', 'permission' => 'categories.manage'],
        'users.php' => ['label' => 'Quản lý user', 'permission' => 'users.manage'],
    ];
}

function visible_admin_nav_items(): array {
    $items = [];

    foreach (admin_nav_items() as $file => $meta) {
        $permission = $meta['permission'] ?? null;
        if ($permission !== null && !admin_has_permission($permission)) {
            continue;
        }

        $items[$file] = $meta['label'];
    }

    return $items;
}

function admin_unread_contact_count(): int {
    static $count = null;

    if ($count !== null) {
        return $count;
    }

    if (!class_exists('ContactMessage')) {
        $count = 0;
        return $count;
    }

    try {
        $contactModel = new ContactMessage();
        $stats = $contactModel->getStats();
        $count = (int)($stats['unread_messages'] ?? 0);
    } catch (Throwable $e) {
        $count = 0;
    }

    return $count;
}

function render_admin_header(string $title): void {
    $flash = get_flash();
    $adminName = get_user_name() ?? 'Admin';
    $currentPage = (string)($GLOBALS['mvc_template_current_page'] ?? basename((string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH)));
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= clean($title) ?> - GreenSpace Admin</title>
        <link href="https://fonts.googleapis.com" rel="preconnect">
        <link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
        <style>
            .material-symbols-outlined {
                font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
            }
        </style>
    </head>
    <body class="min-h-screen bg-[#eef6f1] font-['Plus_Jakarta_Sans'] text-[#102118]">
        <div class="min-h-screen">
            <header class="border-b border-[#d9e9de] bg-white/90 backdrop-blur">
                <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#2e9b63]">GreenSpace Admin</p>
                            <h1 class="mt-1 text-2xl font-extrabold"><?= clean($title) ?></h1>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <a href="<?= clean(base_url('public/home.php')) ?>" class="inline-flex items-center rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">
                                Về trang bán hàng
                            </a>
                            <a href="<?= clean(base_url('public/profile.php')) ?>" class="inline-flex items-center rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">
                                <?= clean($adminName) ?>
                            </a>
                            <form action="<?= clean(base_url('public/logout.php')) ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                                <button type="submit" class="inline-flex items-center rounded-full bg-[#102118] px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-[#1f3b2d]">
                                    Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>

                    <nav class="mt-4 flex flex-wrap gap-2">
                        <?php foreach (visible_admin_nav_items() as $file => $label): ?>
                            <?php
                            $showUnreadContactBadge = $file === 'contacts.php' && admin_has_permission('contacts.manage');
                            $unreadContactCount = $showUnreadContactBadge ? admin_unread_contact_count() : 0;
                            ?>
                            <a
                                href="<?= clean(admin_path($file)) ?>"
                                class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition-colors <?= $currentPage === $file ? 'bg-[#102118] text-white' : 'border border-[#d9e9de] text-[#102118] hover:border-[#2e9b63] hover:text-[#2e9b63]' ?>"
                            >
                                <?= clean($label) ?>
                                <?php if ($showUnreadContactBadge && $unreadContactCount > 0): ?>
                                    <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-[#fff3e8] px-2 py-0.5 text-xs font-bold text-[#b56a16] <?= $currentPage === $file ? 'bg-white/15 text-white' : '' ?>">
                                        <?= clean((string)$unreadContactCount) ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </div>
            </header>

            <?php if ($flash): ?>
                <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
                    <div class="rounded-2xl border px-4 py-3 text-sm font-medium <?= ($flash['type'] ?? '') === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800' ?>">
                        <?= clean($flash['message'] ?? '') ?>
                    </div>
                </div>
            <?php endif; ?>

            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <?php
}

function render_admin_footer(): void {
    ?>
            </main>
        </div>
    </body>
    </html>
    <?php
}
