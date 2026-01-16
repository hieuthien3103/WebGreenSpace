<!-- Product Card Component -->
<div class="group relative flex flex-col rounded-2xl bg-background-light p-4 transition-all hover:shadow-lg dark:bg-background-dark">
    <div class="relative mb-4 aspect-[4/5] w-full overflow-hidden rounded-xl bg-gray-100">
        <div class="h-full w-full bg-cover bg-center transition-transform duration-500 group-hover:scale-110" 
             style="background-image: url('<?= $product['image_url'] ?? image_url('products/default.jpg') ?>');">
        </div>
        
        <!-- Wishlist Button -->
        <button class="absolute right-3 top-3 flex size-8 items-center justify-center rounded-full bg-white/80 text-text-main opacity-0 backdrop-blur-sm transition-opacity hover:bg-white group-hover:opacity-100">
            <span class="material-symbols-outlined text-sm">favorite</span>
        </button>
        
        <!-- Sale Badge -->
        <?php if (isset($product['sale_price']) && $product['sale_price'] > 0): 
            $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
        ?>
            <span class="absolute left-3 top-3 rounded-lg bg-red-500 px-2 py-1 text-xs font-bold text-white">-<?= $discount ?>%</span>
        <?php endif; ?>
    </div>
    
    <div class="flex flex-1 flex-col">
        <h3 class="text-lg font-bold text-text-main dark:text-white"><?= clean($product['name']) ?></h3>
        <p class="mb-3 text-sm text-text-secondary"><?= clean($product['category_name'] ?? '') ?></p>
        
        <div class="mt-auto flex items-center justify-between">
            <div class="flex flex-col">
                <?php if (isset($product['sale_price']) && $product['sale_price'] > 0): ?>
                    <span class="text-lg font-bold text-primary"><?= format_currency($product['sale_price']) ?></span>
                    <span class="text-xs text-gray-400 line-through"><?= format_currency($product['price']) ?></span>
                <?php else: ?>
                    <span class="text-lg font-bold text-primary"><?= format_currency($product['price']) ?></span>
                <?php endif; ?>
            </div>
            
            <a href="<?= base_url('cart/add/' . $product['id']) ?>" class="flex size-9 items-center justify-center rounded-full bg-primary text-white transition-colors hover:bg-primary-dark">
                <span class="material-symbols-outlined text-sm">add_shopping_cart</span>
            </a>
        </div>
    </div>
</div>
