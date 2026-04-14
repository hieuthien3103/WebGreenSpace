<?php
if (empty($GLOBALS['mvc_template_rendering'])) {
    require_once __DIR__ . '/../config/config.php';
    (new AuthController())->login()->send();
    return;
}

require_once __DIR__ . '/../config/config.php';

include 'includes/header.php';
?>

<main class="flex-1">
    <div class="mx-auto flex w-full max-w-7xl items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid w-full max-w-5xl overflow-hidden rounded-[2rem] border border-[#e7f1ea] bg-white shadow-[0_30px_80px_rgba(15,26,20,0.08)] lg:grid-cols-[1.05fr_0.95fr] dark:border-[#24352b] dark:bg-[#16211b]">
            <section class="hidden lg:flex flex-col justify-between bg-[radial-gradient(circle_at_top_left,_rgba(46,204,112,0.28),_transparent_48%),linear-gradient(135deg,#143322_0%,#0f1a14_100%)] p-10 text-white">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-semibold backdrop-blur">
                        <span class="material-symbols-outlined text-[18px]">forest</span>
                        GreenSpace member
                    </span>
                    <h1 class="mt-6 text-4xl font-extrabold leading-tight">Đăng nhập để theo dõi đơn hàng và lưu cây bạn yêu thích.</h1>
                    <p class="mt-4 max-w-md text-sm leading-7 text-white/75">
                        Tài khoản sẽ giúp bạn mua hàng nhanh hơn, quản lý thông tin giao hàng và sẵn sàng cho các bước checkout tiếp theo.
                    </p>
                </div>
                <div class="space-y-3 text-sm text-white/80">
                    <p class="rounded-2xl bg-white/10 px-4 py-3 font-medium">
                        Lưu địa chỉ nhận hàng, theo dõi đơn và quay lại giỏ hàng nhanh hơn trên mọi thiết bị.
                    </p>
                </div>
            </section>

            <section class="p-6 sm:p-8 lg:p-10">
                <div class="mx-auto w-full max-w-md">
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-primary">Welcome back</p>
                    <h2 class="mt-3 text-3xl font-extrabold text-text-main dark:text-white">Đăng nhập</h2>
                    <p class="mt-3 text-sm leading-6 text-text-secondary">Nhập email hoặc tên đăng nhập của bạn để tiếp tục mua sắm.</p>

                    <?php if (!empty($errors['general'])): ?>
                        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                            <?= clean($errors['general']) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="mt-8 space-y-5">
                        <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                        <input type="hidden" name="redirect" value="<?= clean($redirectTarget) ?>">

                        <div class="space-y-2">
                            <label for="identifier" class="text-sm font-semibold text-text-main dark:text-white">Email hoặc tên đăng nhập</label>
                            <input
                                id="identifier"
                                name="identifier"
                                type="text"
                                value="<?= clean($old['identifier']) ?>"
                                class="w-full rounded-2xl border <?= !empty($errors['identifier']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#2d4337]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white"
                                placeholder="name@example.com"
                                autocomplete="username"
                            >
                            <?php if (!empty($errors['identifier'])): ?>
                                <p class="text-sm text-red-600"><?= clean($errors['identifier']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="space-y-2">
                            <label for="password" class="text-sm font-semibold text-text-main dark:text-white">Mật khẩu</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="w-full rounded-2xl border <?= !empty($errors['password']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#2d4337]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white"
                                placeholder="Nhập mật khẩu"
                                autocomplete="current-password"
                            >
                            <?php if (!empty($errors['password'])): ?>
                                <p class="text-sm text-red-600"><?= clean($errors['password']) ?></p>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-primary px-5 py-3 text-sm font-bold text-white shadow-lg shadow-primary/20 transition-colors hover:bg-primary-dark">
                            Đăng nhập
                        </button>
                    </form>

                    <p class="mt-6 text-sm text-text-secondary">
                        Chưa có tài khoản?
                        <a href="signup.php<?= $redirectTarget !== 'home.php' ? '?redirect=' . urlencode($redirectTarget) : '' ?>" class="font-semibold text-primary hover:text-primary-dark">
                            Đăng ký ngay
                        </a>
                    </p>
                </div>
            </section>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
