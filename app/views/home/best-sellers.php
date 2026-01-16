<!-- Best Sellers Section -->
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 bg-white dark:bg-[#1a2c22] rounded-3xl mb-12">
    <div class="mb-8 flex items-center justify-between">
        <h2 class="text-2xl font-bold tracking-tight text-text-main dark:text-white sm:text-3xl">Sản phẩm bán chạy</h2>
        <div class="flex gap-2">
            <button class="flex size-10 items-center justify-center rounded-full border border-[#e9f2ec] bg-white text-text-main hover:bg-[#e9f2ec] dark:border-[#2a3b30] dark:bg-transparent dark:text-white dark:hover:bg-[#2a3b30]">
                <span class="material-symbols-outlined">arrow_back</span>
            </button>
            <button class="flex size-10 items-center justify-center rounded-full border border-[#e9f2ec] bg-white text-text-main hover:bg-[#e9f2ec] dark:border-[#2a3b30] dark:bg-transparent dark:text-white dark:hover:bg-[#2a3b30]">
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
        </div>
    </div>
    
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <?php if (!empty($bestSellers)): ?>
            <?php foreach ($bestSellers as $product): ?>
                <?php include __DIR__ . '/../components/product-card.php'; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="col-span-4 text-center text-text-secondary">Chưa có sản phẩm nào.</p>
        <?php endif; ?>
    </div>
</section>
