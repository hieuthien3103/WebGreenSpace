<!-- Categories Section -->
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-8 flex items-end justify-between">
        <h2 class="text-2xl font-bold tracking-tight text-text-main dark:text-white sm:text-3xl">Danh mục sản phẩm</h2>
        <a class="hidden text-sm font-semibold text-primary hover:underline sm:block" href="<?= base_url('categories') ?>">Xem tất cả</a>
    </div>
    
    <div class="grid grid-cols-2 gap-6 sm:grid-cols-3 md:grid-cols-5">
        <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $category): ?>
                <!-- Category Item -->
                <a class="group flex flex-col items-center gap-4 text-center" href="<?= base_url('products/category/' . $category['slug']) ?>">
                    <div class="aspect-square w-full overflow-hidden rounded-full border-2 border-transparent transition-all group-hover:border-primary">
                        <div class="h-full w-full bg-cover bg-center transition-transform duration-500 group-hover:scale-110" 
                             style="background-image: url('<?= $category['image_url'] ?? image_url('categories/default.jpg') ?>');">
                        </div>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-text-main dark:text-white"><?= clean($category['name']) ?></h3>
                        <p class="text-xs text-text-secondary"><?= clean($category['description'] ?? '') ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="col-span-5 text-center text-text-secondary">Chưa có danh mục nào.</p>
        <?php endif; ?>
    </div>
</section>
