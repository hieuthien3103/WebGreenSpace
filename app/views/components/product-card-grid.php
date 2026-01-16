<!-- Product Card -->
<article class="group bg-white dark:bg-[#1e2b24] rounded-2xl overflow-hidden border border-transparent hover:border-[#e9f2ec] dark:hover:border-gray-700 hover:shadow-xl transition-all duration-300 flex flex-col relative">
    <?php if (isset($product['badge'])): ?>
        <div class="absolute top-3 left-3 z-10">
            <?php if ($product['badge'] == 'new'): ?>
                <span class="px-3 py-1 bg-white/90 dark:bg-black/60 backdrop-blur text-xs font-bold rounded-full text-text-main dark:text-white uppercase tracking-wider">Mới</span>
            <?php elseif ($product['badge'] == 'sale' && isset($product['sale_price'])): ?>
                <?php $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>
                <span class="px-3 py-1 bg-red-500 text-xs font-bold rounded-full text-white uppercase tracking-wider">-<?= $discount ?>%</span>
            <?php elseif ($product['badge'] == 'bestseller'): ?>
                <span class="px-3 py-1 bg-yellow-400 text-xs font-bold rounded-full text-black uppercase tracking-wider">Best Seller</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="relative w-full aspect-[4/5] overflow-hidden bg-gray-100 dark:bg-gray-800">
        <img alt="<?= clean($product['name']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" src="<?= $product['image_url'] ?? image_url('products/default.jpg') ?>"/>
        
        <!-- Quick Action Overlay -->
        <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-3">
            <button class="size-10 rounded-full bg-white text-text-main flex items-center justify-center hover:bg-primary hover:text-white transition-colors shadow-lg transform translate-y-4 group-hover:translate-y-0 duration-300" title="Xem nhanh">
                <span class="material-symbols-outlined text-[20px]">visibility</span>
            </button>
            <button class="size-10 rounded-full bg-white text-text-main flex items-center justify-center hover:bg-primary hover:text-white transition-colors shadow-lg transform translate-y-4 group-hover:translate-y-0 duration-300 delay-75" title="Yêu thích">
                <span class="material-symbols-outlined text-[20px]">favorite</span>
            </button>
        </div>
    </div>
    
    <div class="p-5 flex flex-col flex-1">
        <div class="flex justify-between items-start mb-2">
            <h3 class="text-lg font-bold text-text-main dark:text-white group-hover:text-primary transition-colors">
                <a href="<?= base_url('products/' . $product['slug']) ?>"><?= clean($product['name']) ?></a>
            </h3>
        </div>
        <p class="text-sm text-text-secondary dark:text-gray-400 mb-4 line-clamp-2">
            <?= clean($product['description'] ?? '') ?>
        </p>
        
        <div class="mt-auto flex items-center justify-between">
            <?php if (isset($product['sale_price']) && $product['sale_price'] > 0): ?>
                <div class="flex flex-col">
                    <span class="text-xs text-text-secondary line-through"><?= format_currency($product['price']) ?></span>
                    <span class="text-xl font-extrabold text-primary"><?= format_currency($product['sale_price']) ?></span>
                </div>
            <?php else: ?>
                <span class="text-xl font-extrabold text-primary"><?= format_currency($product['price']) ?></span>
            <?php endif; ?>
            
            <button class="size-10 rounded-full bg-primary text-white flex items-center justify-center shadow-lg shadow-primary/30 hover:bg-primary-dark active:scale-95 transition-all">
                <span class="material-symbols-outlined text-[20px]">add_shopping_cart</span>
            </button>
        </div>
    </div>
</article>
