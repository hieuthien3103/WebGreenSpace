<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$cartService = new CartService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectTarget = safe_redirect_target($_POST['redirect_to'] ?? 'cart.php', 'cart.php');

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
        redirect($redirectTarget);
    }

    $action = (string)($_POST['action'] ?? '');

    switch ($action) {
        case 'add':
            $result = $cartService->addItem((int)($_POST['product_id'] ?? 0), (int)($_POST['quantity'] ?? 1));
            set_flash($result['success'] ? 'success' : 'error', $result['message']);
            break;

        case 'update':
            $cartService->updateItems((array)($_POST['quantities'] ?? []));
            set_flash('success', 'Đã cập nhật giỏ hàng.');
            $redirectTarget = 'cart.php';
            break;

        case 'remove':
            $cartService->removeItem((int)($_POST['product_id'] ?? 0));
            set_flash('success', 'Đã xóa sản phẩm khỏi giỏ.');
            $redirectTarget = 'cart.php';
            break;

        case 'clear':
            $cartService->clear();
            set_flash('success', 'Đã xóa toàn bộ giỏ hàng.');
            $redirectTarget = 'cart.php';
            break;
    }

    redirect($redirectTarget);
}

$summary = $cartService->getSummary();
$items = $summary['items'];
$pageTitle = 'Giỏ hàng - GreenSpace';
$currentPage = '';

include 'includes/header.php';
?>

<main class="flex-1">
    <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.22em] text-primary">Cart</p>
                <h1 class="mt-2 text-4xl font-extrabold text-text-main dark:text-white">Giỏ hàng của bạn</h1>
                <p class="mt-3 text-sm text-text-secondary">Kiểm tra số lượng, giá tiền và sẵn sàng cho bước thanh toán.</p>
            </div>
            <?php if (!empty($items)): ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="inline-flex items-center justify-center rounded-full border border-[#d8eadf] px-5 py-2.5 text-sm font-semibold text-text-main transition-colors hover:border-red-300 hover:text-red-600 dark:border-[#32483b] dark:text-white">
                        Xóa giỏ hàng
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (empty($items)): ?>
            <div class="rounded-[2rem] border border-dashed border-[#d8eadf] bg-white px-6 py-16 text-center dark:border-[#32483b] dark:bg-[#16211b]">
                <span class="material-symbols-outlined text-6xl text-primary/70">shopping_bag</span>
                <h2 class="mt-6 text-2xl font-bold text-text-main dark:text-white">Giỏ hàng đang trống</h2>
                <p class="mt-3 text-sm text-text-secondary">Thêm vài cây xanh vào giỏ để tiếp tục mua sắm nhé.</p>
                <a href="products.php" class="mt-8 inline-flex items-center justify-center rounded-full bg-primary px-6 py-3 text-sm font-bold text-white shadow-lg shadow-primary/20 transition-colors hover:bg-primary-dark">
                    Xem sản phẩm
                </a>
            </div>
        <?php else: ?>
            <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
                <section class="rounded-[2rem] border border-[#e7f1ea] bg-white p-5 shadow-sm dark:border-[#24352b] dark:bg-[#16211b]">
                    <div class="space-y-5">
                        <?php foreach ($items as $item): ?>
                            <article class="flex flex-col gap-4 rounded-[1.5rem] border border-[#edf5ef] p-4 sm:flex-row sm:items-center dark:border-[#24352b]">
                                <a href="product-detail.php?slug=<?= clean($item['slug']) ?>" class="w-full max-w-[110px] overflow-hidden rounded-2xl bg-gray-100 dark:bg-[#101914]">
                                    <img src="<?= clean($item['image_url']) ?>" alt="<?= clean($item['name']) ?>" class="aspect-square h-full w-full object-cover">
                                </a>
                                <div class="flex-1">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <h2 class="text-lg font-bold text-text-main dark:text-white"><?= clean($item['name']) ?></h2>
                                            <p class="text-sm text-text-secondary">Đơn giá: <?= format_currency($item['price']) ?></p>
                                            <p class="text-xs text-text-secondary">Tồn kho: <?= (int)$item['stock'] ?></p>
                                        </div>
                                        <div class="text-left sm:text-right">
                                            <p class="text-lg font-extrabold text-primary"><?= format_currency($item['subtotal']) ?></p>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex flex-wrap items-center gap-3">
                                        <label for="qty-<?= (int)$item['product_id'] ?>" class="text-sm font-semibold text-text-main dark:text-white">Số lượng</label>
                                        <input
                                            id="qty-<?= (int)$item['product_id'] ?>"
                                            form="update-cart-form"
                                            type="number"
                                            name="quantities[<?= (int)$item['product_id'] ?>]"
                                            min="1"
                                            max="<?= (int)$item['stock'] ?>"
                                            value="<?= (int)$item['quantity'] ?>"
                                            class="w-24 rounded-xl border border-[#d8eadf] bg-white px-3 py-2 text-sm font-semibold text-text-main focus:border-primary focus:ring-primary/20 dark:border-[#32483b] dark:bg-[#101914] dark:text-white"
                                        >

                                        <form method="POST">
                                            <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                                            <button type="submit" class="inline-flex items-center rounded-full border border-[#f1d6d6] px-4 py-2 text-sm font-semibold text-red-600 transition-colors hover:border-red-300 hover:bg-red-50">
                                                Xóa
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>

                        <form id="update-cart-form" method="POST" class="flex flex-wrap items-center justify-between gap-3 border-t border-[#edf5ef] pt-5 dark:border-[#24352b]">
                            <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                            <input type="hidden" name="action" value="update">
                            <a href="products.php" class="inline-flex items-center rounded-full border border-[#d8eadf] px-5 py-2.5 text-sm font-semibold text-text-main transition-colors hover:border-primary hover:text-primary dark:border-[#32483b] dark:text-white">
                                Tiếp tục mua sắm
                            </a>
                            <button type="submit" class="inline-flex items-center justify-center rounded-full bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-primary/20 transition-colors hover:bg-primary-dark">
                                Cập nhật giỏ hàng
                            </button>
                        </form>
                    </div>
                </section>

                <aside class="rounded-[2rem] border border-[#e7f1ea] bg-white p-6 shadow-sm dark:border-[#24352b] dark:bg-[#16211b]">
                    <h2 class="text-xl font-extrabold text-text-main dark:text-white">Tóm tắt đơn hàng</h2>
                    <div class="mt-6 space-y-4 text-sm">
                        <div class="flex items-center justify-between text-text-secondary">
                            <span>Tạm tính</span>
                            <span class="font-semibold text-text-main dark:text-white"><?= format_currency($summary['subtotal']) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-text-secondary">
                            <span>Giảm giá</span>
                            <span class="font-semibold text-text-main dark:text-white"><?= format_currency($summary['discount_amount']) ?></span>
                        </div>
                        <div class="flex items-center justify-between text-text-secondary">
                            <span>Phí vận chuyển</span>
                            <span class="font-semibold text-text-main dark:text-white"><?= format_currency($summary['shipping_fee']) ?></span>
                        </div>
                        <div class="border-t border-dashed border-[#d8eadf] pt-4 dark:border-[#32483b]">
                            <div class="flex items-center justify-between">
                                <span class="text-base font-bold text-text-main dark:text-white">Tổng cộng</span>
                                <span class="text-2xl font-extrabold text-primary"><?= format_currency($summary['total']) ?></span>
                            </div>
                        </div>
                    </div>

                    <a href="<?= is_logged_in() ? 'checkout.php' : 'login.php?redirect=' . urlencode('checkout.php') ?>" class="mt-8 inline-flex w-full items-center justify-center rounded-full bg-primary px-5 py-3 text-sm font-bold text-white shadow-lg shadow-primary/20 transition-colors hover:bg-primary-dark">
                        <?= is_logged_in() ? 'Tiến hành thanh toán' : 'Đăng nhập để thanh toán' ?>
                    </a>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
