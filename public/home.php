<?php
// Load configuration and dependencies
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/Category.php';

// Initialize page variables
$pageTitle = 'Trang chủ - GreenSpace';
$currentPage = 'home';

// Get featured products
try {
    $productModel = new Product();
    $featuredProducts = $productModel->getAll(8);
    $bestSellers = $productModel->getBestSellers(8);
    
    $categoryModel = new Category();
    $categories = $categoryModel->getAll();
} catch (Exception $e) {
    error_log("Error loading home page: " . $e->getMessage());
    $featuredProducts = [];
    $bestSellers = [];
    $categories = [];
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="relative bg-gradient-to-br from-[#e9f2ec] to-white dark:from-[#1a2620] dark:to-[#0f1612] py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-6">
                <h1 class="text-5xl lg:text-6xl font-extrabold text-text-main dark:text-white leading-tight">
                    Mang thiên nhiên<br>vào ngôi nhà của bạn
                </h1>
                <p class="text-xl text-text-secondary dark:text-gray-400">
                    Khám phá bộ sưu tập cây xanh cao cấp, được chăm sóc tỉ mỉ và giao hàng tận nơi.
                </p>
                <div class="flex gap-4">
                    <a href="products.php" class="px-8 py-4 bg-primary text-white rounded-full font-bold hover:bg-primary-dark transition-all shadow-lg shadow-primary/30">
                        Xem sản phẩm
                    </a>
                    <a href="#categories" class="px-8 py-4 bg-white dark:bg-[#1e2b24] border-2 border-primary text-primary rounded-full font-bold hover:bg-primary hover:text-white transition-all">
                        Danh mục
                    </a>
                </div>
            </div>
            <div class="relative">
                <img src="<?= image_url('banners/hero-plants.jpg') ?>" alt="Plants" class="rounded-3xl shadow-2xl" onerror="this.src='https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?w=800&q=80'">
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-16 bg-white dark:bg-[#0f1612]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-extrabold text-text-main dark:text-white mb-4">Sản phẩm nổi bật</h2>
            <p class="text-lg text-text-secondary dark:text-gray-400">Những cây cảnh được yêu thích nhất</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php if (!empty($featuredProducts)): ?>
                <?php foreach (array_slice($featuredProducts, 0, 8) as $product): ?>
                    <article class="group bg-white dark:bg-[#1e2b24] rounded-2xl overflow-hidden border border-transparent hover:border-[#e9f2ec] dark:hover:border-gray-700 hover:shadow-xl transition-all duration-300">
                        <a href="product-detail.php?slug=<?= $product['slug'] ?>" class="block">
                            <div class="relative aspect-[4/5] overflow-hidden bg-gray-100 dark:bg-gray-800">
                                <img src="<?= $product['image_url'] ?? image_url('products/default.jpg') ?>" alt="<?= clean($product['name']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold text-text-main dark:text-white mb-2 line-clamp-1 group-hover:text-primary transition-colors">
                                    <?= clean($product['name']) ?>
                                </h3>
                                <div class="flex items-center justify-between">
                                    <?php if (!empty($product['sale_price']) && $product['sale_price'] > 0 && $product['sale_price'] < $product['price']): ?>
                                        <div class="flex flex-col">
                                            <span class="text-xs text-text-secondary line-through"><?= format_currency($product['price']) ?></span>
                                            <span class="text-lg font-extrabold text-primary"><?= format_currency($product['sale_price']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-lg font-extrabold text-primary"><?= format_currency($product['price']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <p class="text-text-secondary">Chưa có sản phẩm nào</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-12">
            <a href="products.php" class="inline-flex items-center gap-2 px-8 py-3 bg-primary text-white rounded-full font-bold hover:bg-primary-dark transition-all">
                Xem tất cả sản phẩm
                <span class="material-symbols-outlined">arrow_forward</span>
            </a>
        </div>
    </div>
</section>

<!-- Categories -->
<section id="categories" class="py-16 bg-[#f8faf9] dark:bg-[#1a2620]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-extrabold text-text-main dark:text-white mb-4">Danh mục sản phẩm</h2>
            <p class="text-lg text-text-secondary dark:text-gray-400">Tìm cây phù hợp với không gian của bạn</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <a href="products.php?category=<?= $category['slug'] ?>" class="group text-center">
                        <div class="bg-white dark:bg-[#1e2b24] rounded-2xl p-6 hover:shadow-lg transition-all">
                            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-br from-primary/10 to-primary/5 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined text-4xl text-primary">potted_plant</span>
                            </div>
                            <h3 class="font-bold text-text-main dark:text-white group-hover:text-primary transition-colors">
                                <?= clean($category['name']) ?>
                            </h3>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-16 bg-white dark:bg-[#0f1612]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-extrabold text-text-main dark:text-white mb-4">Tại sao chọn chúng tôi?</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center p-6">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-primary/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl text-primary">verified</span>
                </div>
                <h3 class="text-xl font-bold text-text-main dark:text-white mb-2">Chất lượng đảm bảo</h3>
                <p class="text-text-secondary dark:text-gray-400">100% cây khỏe mạnh, được kiểm tra kỹ lưỡng</p>
            </div>
            <div class="text-center p-6">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-primary/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl text-primary">local_shipping</span>
                </div>
                <h3 class="text-xl font-bold text-text-main dark:text-white mb-2">Giao hàng tận nơi</h3>
                <p class="text-text-secondary dark:text-gray-400">Miễn phí ship cho đơn hàng từ 500k</p>
            </div>
            <div class="text-center p-6">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-primary/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl text-primary">support_agent</span>
                </div>
                <h3 class="text-xl font-bold text-text-main dark:text-white mb-2">Hỗ trợ 24/7</h3>
                <p class="text-text-secondary dark:text-gray-400">Tư vấn chăm sóc cây trọn đời</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
