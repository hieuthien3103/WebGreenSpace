<?php
$pageTitle = $pageTitle ?? '404 - Không tìm thấy trang';
$currentPage = '';
include __DIR__ . '/../layouts/header.php';
?>

<section class="mx-auto flex w-full max-w-4xl flex-1 items-center justify-center px-4 py-20 sm:px-6 lg:px-8">
    <div class="text-center">
        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-primary">404 Error</p>
        <h1 class="mt-4 text-5xl font-extrabold text-text-main dark:text-white sm:text-6xl">Trang không tồn tại</h1>
        <p class="mt-4 text-base text-text-secondary dark:text-gray-400">
            <?= clean($message ?? 'Không tìm thấy nội dung bạn đang tìm kiếm.') ?>
        </p>
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="<?= base_url() ?>" class="inline-flex items-center justify-center rounded-full bg-primary px-6 py-3 text-sm font-bold text-white shadow-lg shadow-primary/20 transition-colors hover:bg-primary-dark">
                Về trang chủ
            </a>
            <a href="<?= base_url('products') ?>" class="inline-flex items-center justify-center rounded-full border border-[#d8eadf] px-6 py-3 text-sm font-semibold text-text-main transition-colors hover:border-primary hover:text-primary dark:border-[#32483b] dark:text-white">
                Xem sản phẩm
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
