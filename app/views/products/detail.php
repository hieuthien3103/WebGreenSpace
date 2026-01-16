<?php
$currentPage = 'shop';
require_once __DIR__ . '/../layouts/header.php';
?>

<main class="flex-grow w-full max-w-7xl mx-auto px-6 py-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm mb-8 text-text-secondary dark:text-gray-400">
        <a href="<?php echo base_url(); ?>" class="hover:text-primary transition-colors">Trang chủ</a>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <a href="<?php echo base_url('products'); ?>" class="hover:text-primary transition-colors">Cửa hàng</a>
        <?php if (!empty($product['category_name'])): ?>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <a href="<?php echo base_url('category/' . $product['category_slug']); ?>" class="hover:text-primary transition-colors"><?php echo clean($product['category_name']); ?></a>
        <?php endif; ?>
        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        <span class="text-text-main dark:text-white font-medium"><?php echo clean($product['name']); ?></span>
    </nav>

    <!-- Product Detail Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">
        <!-- Product Images -->
        <div class="flex flex-col gap-4">
            <!-- Main Image -->
            <div class="relative w-full aspect-square rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-800 group">
                <img id="mainImage" alt="<?php echo clean($product['name']); ?>" class="w-full h-full object-cover" src="<?php echo clean($product['image_url']); ?>"/>
                <div class="absolute top-4 left-4 flex flex-col gap-2">
                    <?php if (!empty($product['is_new'])): ?>
                    <span class="px-3 py-1 bg-white/90 dark:bg-black/60 backdrop-blur text-xs font-bold rounded-full text-text-main dark:text-white uppercase tracking-wider">Mới</span>
                    <?php endif; ?>
                    <?php if ($product['stock_quantity'] > 0): ?>
                    <span class="px-3 py-1 bg-green-500/90 backdrop-blur text-xs font-bold rounded-full text-white uppercase tracking-wider">Còn hàng</span>
                    <?php else: ?>
                    <span class="px-3 py-1 bg-red-500/90 backdrop-blur text-xs font-bold rounded-full text-white uppercase tracking-wider">Hết hàng</span>
                    <?php endif; ?>
                    <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                    <?php 
                        $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
                    ?>
                    <span class="px-3 py-1 bg-red-500/90 backdrop-blur text-xs font-bold rounded-full text-white uppercase tracking-wider">-<?php echo $discount; ?>%</span>
                    <?php endif; ?>
                </div>
                <button class="absolute top-4 right-4 size-12 rounded-full bg-white/90 dark:bg-black/60 backdrop-blur text-text-main dark:text-white hover:bg-primary hover:text-white transition-colors flex items-center justify-center">
                    <span class="material-symbols-outlined">favorite_border</span>
                </button>
            </div>
            
            <!-- Thumbnail Images -->
            <?php if (!empty($images) && count($images) > 1): ?>
            <div class="grid grid-cols-4 gap-3">
                <?php foreach (array_slice($images, 0, 4) as $index => $image): ?>
                <button class="aspect-square rounded-xl overflow-hidden border-2 <?php echo $index === 0 ? 'border-primary' : 'border-transparent hover:border-primary'; ?> transition-colors" onclick="document.getElementById('mainImage').src='<?php echo clean($image['image_url']); ?>'">
                    <img alt="<?php echo clean($product['name']); ?>" class="w-full h-full object-cover" src="<?php echo clean($image['image_url']); ?>"/>
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="flex flex-col gap-6">
            <div>
                <h1 class="text-4xl md:text-5xl font-extrabold text-text-main dark:text-white mb-4"><?php echo clean($product['name']); ?></h1>
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-yellow-400" style="font-variation-settings: 'FILL' 1;">star</span>
                        <span class="material-symbols-outlined text-yellow-400" style="font-variation-settings: 'FILL' 1;">star</span>
                        <span class="material-symbols-outlined text-yellow-400" style="font-variation-settings: 'FILL' 1;">star</span>
                        <span class="material-symbols-outlined text-yellow-400" style="font-variation-settings: 'FILL' 1;">star</span>
                        <span class="material-symbols-outlined text-yellow-400" style="font-variation-settings: 'FILL' 1;">star_half</span>
                    </div>
                    <span class="text-sm text-text-secondary dark:text-gray-400">(24 đánh giá)</span>
                </div>
                <div class="flex items-baseline gap-4 mb-6">
                    <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                    <span class="text-4xl font-extrabold text-primary"><?php echo format_currency($product['sale_price']); ?></span>
                    <span class="text-xl text-text-secondary line-through"><?php echo format_currency($product['price']); ?></span>
                    <?php else: ?>
                    <span class="text-4xl font-extrabold text-primary"><?php echo format_currency($product['price']); ?></span>
                    <?php endif; ?>
                    <?php if ($product['stock_quantity'] > 0): ?>
                    <span class="text-sm text-text-secondary px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full font-semibold">Còn hàng: <?php echo $product['stock_quantity']; ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="border-t border-b border-[#e9f2ec] dark:border-gray-800 py-6">
                <p class="text-text-secondary dark:text-gray-300 text-base leading-relaxed">
                    <?php echo nl2br(clean($product['description'])); ?>
                </p>
            </div>

            <!-- Care Instructions -->
            <div>
                <h3 class="text-lg font-bold text-text-main dark:text-white mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">info</span>
                    Hướng dẫn chăm sóc
                </h3>
                <div class="grid grid-cols-3 gap-4">
                    <div class="flex flex-col items-center text-center gap-3 p-4 rounded-2xl bg-surface-light dark:bg-surface-dark border border-[#e9f2ec] dark:border-gray-800">
                        <span class="material-symbols-outlined text-4xl text-primary">water_drop</span>
                        <div>
                            <p class="text-xs font-semibold text-text-main dark:text-white mb-1">Tưới nước</p>
                            <p class="text-xs text-text-secondary dark:text-gray-400">1 tuần/lần</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-center text-center gap-3 p-4 rounded-2xl bg-surface-light dark:bg-surface-dark border border-[#e9f2ec] dark:border-gray-800">
                        <span class="material-symbols-outlined text-4xl text-primary">wb_sunny</span>
                        <div>
                            <p class="text-xs font-semibold text-text-main dark:text-white mb-1">Ánh sáng</p>
                            <p class="text-xs text-text-secondary dark:text-gray-400">Bán râm</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-center text-center gap-3 p-4 rounded-2xl bg-surface-light dark:bg-surface-dark border border-[#e9f2ec] dark:border-gray-800">
                        <span class="material-symbols-outlined text-4xl text-primary">thermostat</span>
                        <div>
                            <p class="text-xs font-semibold text-text-main dark:text-white mb-1">Nhiệt độ</p>
                            <p class="text-xs text-text-secondary dark:text-gray-400">18-25°C</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tags -->
            <?php if (!empty($tags)): ?>
            <div>
                <h4 class="text-sm font-bold text-text-main dark:text-white mb-3">Đặc điểm:</h4>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($tags as $tag): ?>
                    <span class="px-4 py-2 rounded-full text-xs font-semibold bg-primary/10 text-primary border border-primary/20"><?php echo clean($tag['name']); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Add to Cart -->
            <div class="border-t border-[#e9f2ec] dark:border-gray-800 pt-6">
                <?php if ($product['stock_quantity'] > 0): ?>
                <form method="POST" action="<?php echo base_url('cart/add'); ?>">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex items-center bg-surface-light dark:bg-surface-dark rounded-2xl px-5 py-3 border border-[#e9f2ec] dark:border-gray-800 w-max">
                            <button type="button" class="size-10 flex items-center justify-center text-text-secondary hover:text-primary transition-colors" onclick="decrementQuantity()">
                                <span class="material-symbols-outlined">remove</span>
                            </button>
                            <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" class="w-12 text-center font-bold text-xl text-text-main dark:text-white bg-transparent border-none focus:ring-0" readonly>
                            <button type="button" class="size-10 flex items-center justify-center text-text-secondary hover:text-primary transition-colors" onclick="incrementQuantity()">
                                <span class="material-symbols-outlined">add</span>
                            </button>
                        </div>
                        <button type="submit" class="flex-1 bg-primary hover:bg-primary-dark text-white font-bold py-4 px-8 rounded-2xl shadow-lg shadow-primary/30 flex items-center justify-center gap-3 transition-all active:scale-95">
                            <span class="material-symbols-outlined">shopping_cart</span>
                            Thêm vào giỏ hàng
                        </button>
                    </div>
                </form>
                <button class="w-full mt-3 bg-surface-light dark:bg-surface-dark border border-[#e9f2ec] dark:border-gray-800 text-text-main dark:text-white font-bold py-4 px-8 rounded-2xl hover:border-primary hover:text-primary transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">bolt</span>
                    Mua ngay
                </button>
                <?php else: ?>
                <button disabled class="w-full bg-gray-400 text-white font-bold py-4 px-8 rounded-2xl cursor-not-allowed flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">block</span>
                    Sản phẩm tạm hết hàng
                </button>
                <?php endif; ?>
            </div>

            <!-- Additional Info -->
            <div class="grid grid-cols-2 gap-4 p-4 bg-[#e9f2ec]/50 dark:bg-white/5 rounded-2xl">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary">local_shipping</span>
                    <div class="text-xs">
                        <p class="font-semibold text-text-main dark:text-white">Miễn phí vận chuyển</p>
                        <p class="text-text-secondary dark:text-gray-400">Đơn từ 500k</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary">verified</span>
                    <div class="text-xs">
                        <p class="font-semibold text-text-main dark:text-white">Đảm bảo chất lượng</p>
                        <p class="text-text-secondary dark:text-gray-400">Đổi trả 7 ngày</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Section -->
    <div class="mb-16">
        <div class="border-b border-[#e9f2ec] dark:border-gray-800 mb-8">
            <nav class="flex gap-8">
                <button class="pb-4 px-2 font-bold text-primary border-b-2 border-primary">Mô tả chi tiết</button>
                <button class="pb-4 px-2 font-semibold text-text-secondary hover:text-primary transition-colors">Hướng dẫn chăm sóc</button>
                <button class="pb-4 px-2 font-semibold text-text-secondary hover:text-primary transition-colors">Đánh giá (24)</button>
            </nav>
        </div>

        <!-- Description Tab Content -->
        <div class="prose prose-lg max-w-none dark:prose-invert">
            <h3 class="text-2xl font-bold text-text-main dark:text-white mb-4">Về <?php echo clean($product['name']); ?></h3>
            <div class="text-text-secondary dark:text-gray-300 leading-relaxed">
                <?php echo nl2br(clean($product['description'])); ?>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
    <section class="mb-16">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-3xl font-extrabold text-text-main dark:text-white">Sản phẩm liên quan</h2>
            <a href="<?php echo base_url('products'); ?>" class="text-sm font-semibold text-primary hover:text-primary-dark transition-colors flex items-center gap-1">
                Xem tất cả
                <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($relatedProducts as $relatedProduct): ?>
            <article class="group bg-surface-light dark:bg-surface-dark rounded-2xl overflow-hidden border border-transparent hover:border-[#e9f2ec] dark:hover:border-gray-700 hover:shadow-xl transition-all duration-300 flex flex-col">
                <a href="<?php echo base_url('products/' . $relatedProduct['slug']); ?>" class="relative w-full aspect-[4/5] overflow-hidden bg-gray-100 dark:bg-gray-800">
                    <img alt="<?php echo clean($relatedProduct['name']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" src="<?php echo clean($relatedProduct['image_url']); ?>"/>
                </a>
                <div class="p-5 flex flex-col flex-1">
                    <a href="<?php echo base_url('products/' . $relatedProduct['slug']); ?>">
                        <h3 class="text-lg font-bold text-text-main dark:text-white group-hover:text-primary transition-colors mb-2"><?php echo clean($relatedProduct['name']); ?></h3>
                    </a>
                    <p class="text-sm text-text-secondary dark:text-gray-400 mb-4 line-clamp-2"><?php echo clean($relatedProduct['description']); ?></p>
                    <div class="mt-auto flex items-center justify-between">
                        <?php if (!empty($relatedProduct['sale_price']) && $relatedProduct['sale_price'] < $relatedProduct['price']): ?>
                        <div class="flex flex-col">
                            <span class="text-xs text-text-secondary line-through"><?php echo format_currency($relatedProduct['price']); ?></span>
                            <span class="text-xl font-extrabold text-primary"><?php echo format_currency($relatedProduct['sale_price']); ?></span>
                        </div>
                        <?php else: ?>
                        <span class="text-xl font-extrabold text-primary"><?php echo format_currency($relatedProduct['price']); ?></span>
                        <?php endif; ?>
                        <button class="size-10 rounded-full bg-primary text-white flex items-center justify-center shadow-lg shadow-primary/30 hover:bg-primary-dark">
                            <span class="material-symbols-outlined text-[20px]">add_shopping_cart</span>
                        </button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<script>
function incrementQuantity() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.max);
    const current = parseInt(input.value);
    if (current < max) {
        input.value = current + 1;
    }
}

function decrementQuantity() {
    const input = document.getElementById('quantity');
    const min = parseInt(input.min);
    const current = parseInt(input.value);
    if (current > min) {
        input.value = current - 1;
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
