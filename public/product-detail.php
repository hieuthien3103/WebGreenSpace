<?php 
// Load configuration and dependencies
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../app/models/Product.php';

// Get product ID or slug from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$productSlug = isset($_GET['slug']) ? clean($_GET['slug']) : '';

// Initialize Product model
$productModel = new Product();

// Get product data
$product = null;
if ($productSlug) {
    $product = $productModel->getBySlug($productSlug);
} elseif ($productId) {
    $product = $productModel->getById($productId);
}

// Redirect to products page if product not found
if (!$product) {
    header('Location: products.php');
    exit();
}

// Update view count
$productModel->incrementViews($product['id']);

// Get related products from same category
$relatedProducts = $productModel->getByCategory($product['category_id'], 4);

// Set page variables
$pageTitle = clean($product['name']) . ' - GreenSpace';
$currentPage = 'products';

include 'includes/header.php'; 
?>

<!-- Main Content -->
<main class="flex-1">
    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="mb-8 text-sm">
            <ol class="flex items-center gap-2 text-text-secondary dark:text-gray-400">
                <li><a class="hover:text-primary transition-colors" href="home.php">Trang chủ</a></li>
                <li class="material-symbols-outlined text-[16px]">chevron_right</li>
                <li><a class="hover:text-primary transition-colors" href="products.php">Cửa hàng</a></li>
                <li class="material-symbols-outlined text-[16px]">chevron_right</li>
                <?php if (!empty($product['category_name'])): ?>
                <li><a class="hover:text-primary transition-colors" href="products.php?category=<?= $product['category_slug'] ?>"><?= clean($product['category_name']) ?></a></li>
                <li class="material-symbols-outlined text-[16px]">chevron_right</li>
                <?php endif; ?>
                <li class="text-text-main dark:text-white font-medium"><?= clean($product['name']) ?></li>
            </ol>
        </nav>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">
            <!-- Product Images -->
            <div class="flex flex-col gap-4">
                <div class="relative w-full aspect-square bg-white dark:bg-[#1e2b24] rounded-2xl overflow-hidden border border-[#e9f2ec] dark:border-gray-800">
                    <img id="mainImage" alt="<?= clean($product['name']) ?>" class="w-full h-full object-cover" src="<?= $product['image_url'] ?? image_url('products/default.jpg') ?>"/>
                    <?php if (!empty($product['is_new'])): ?>
                    <div class="absolute top-4 right-4">
                        <span class="px-4 py-1.5 bg-white/90 dark:bg-black/60 backdrop-blur text-xs font-bold rounded-full text-primary uppercase tracking-wider">
                            Mới
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="grid grid-cols-4 gap-3">
                    <button class="aspect-square rounded-lg overflow-hidden border-2 border-primary" onclick="changeImage(this)">
                        <img alt="Thumbnail 1" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDeUfh4Ykgwe6D8Dp0aagg4se51MC_-ppr0C7vlMOXkteGnE8wSt6dvFPl3VyypivuGhqq1UlFeaPJdvSRD5P4fmfHG35h5zzghk3NVm4Oit-OttvKT8qUu3uTXukK8K-H2IbBupXx0GGNxNZtejP_s0A8RFs78zuSKb7UQbfn7m83z1SSVWhg2aXIikSoXtOOW0EvUVZ6E6fwBczb2xv9ny29TZWohoukUUZ-XmohkIFuQXy8ROcob02cyCpSBefp0OCHzAPuxgME"/>
                    </button>
                    <button class="aspect-square rounded-lg overflow-hidden border-2 border-transparent hover:border-primary transition-colors" onclick="changeImage(this)">
                        <img alt="Thumbnail 2" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCiTTx3YnJNis7fQnRSQAKzUBDO1TSsuGlDLIVcdUz7Wu0hM7tQsiWbRTuBcMgx9D4ZQGZIE48ta2L2BWi-Yh2GiiDdQ9Tt1eFm4ax71vpsrNBABwS8TeMtF8jblvP9HBdRUWEoG9UDwOWPJlp_gQJQq7e9N96dffwp8PS8g_3LntjlURPgMyPtia0NifSSDJjePQBK2rk1S5FzDQZ0zXzhcBltb0X3-PeL7BXTUROtJhfLHE_VWppdE2fXVw3TyAvAhiW23wuq9vY"/>
                    </button>
                    <button class="aspect-square rounded-lg overflow-hidden border-2 border-transparent hover:border-primary transition-colors" onclick="changeImage(this)">
                        <img alt="Thumbnail 3" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCtKxPjhRgmebHH-iUQF7-uOSw_vcf1RFtLONcUgmP2TGYZqi2quchf1jD7WdmN93eaTLNquaPhcRCX_v11jICTSMAnwrETL1TGTRv2ZWBD-IlAD_7KCHcq201S8lDmq_BOlj7q4iQ6mXYWu-4Vg0p2JhURVcb1qh4P6tkWNU8XipSXwV-bRmAK8TPPQwdnrERghdKhvOKmTsZqEn8bWUGvevQuKm7zuJ0qnD4GuaMaEDlTLKRkyhltjDlrL_m8dKnXZ5Cxy-gEz9Y"/>
                    </button>
                    <button class="aspect-square rounded-lg overflow-hidden border-2 border-transparent hover:border-primary transition-colors" onclick="changeImage(this)">
                        <img alt="Thumbnail 4" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBAMVTOVTwmajdmq_VnUwp13SSAqY0ig_BJID1lD0vbihr_kAbaBWOHfLsc_p841FsOy4XFLY7q-epzRTcBxOnceCqj5DfIpHubPVftEkPxde54mT_MvShTO0K1rJAjBX54HYEsW4Dstk2m2Sk0m0dEFQPgcArVbPWrTbXaJEnmofs0vKRCOMHCaZ4SzMU1_HpebhlHqjPlin0dNLxp4eYDUi31XYAxy1hD4X7Aa7wTrB_eK6P0XNX_v4IaPHHsi3S-kVaXd-qaNJs"/>
                    </button>
                </div>
            </div>
            
            <!-- Product Info -->
            <div class="flex flex-col">
                <div class="mb-6">
                    <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-text-main dark:text-white mb-4"><?= clean($product['name']) ?></h1>
                    <div class="flex items-center gap-6 mb-4">
                        <?php if (!empty($product['sku'])): ?>
                        <span class="text-sm text-text-secondary dark:text-gray-400">SKU: <span class="font-mono font-bold text-text-main dark:text-white"><?= clean($product['sku']) ?></span></span>
                        <?php endif; ?>
                        <?php if (!empty($product['stock_quantity'])): ?>
                        <span class="text-sm <?= $product['stock_quantity'] > 0 ? 'text-primary' : 'text-red-500' ?>">
                            <?= $product['stock_quantity'] > 0 ? 'Còn hàng' : 'Hết hàng' ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-baseline gap-4 mb-6">
                        <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
                        <span class="text-5xl font-extrabold text-primary"><?= format_currency($product['price']) ?></span>
                        <span class="text-2xl text-gray-400 line-through"><?= format_currency($product['old_price']) ?></span>
                        <span class="px-3 py-1 bg-red-500 text-white text-sm font-bold rounded-lg">-<?= $product['discount_percentage'] ?>%</span>
                        <?php else: ?>
                        <span class="text-5xl font-extrabold text-primary"><?= format_currency($product['price']) ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="text-text-secondary dark:text-gray-400 text-lg leading-relaxed">
                        <?= clean($product['short_description'] ?? $product['description'] ?? '') ?>
                    </p>
                </div>
                
                <!-- Tags -->
                <div class="mb-6 flex flex-wrap gap-2">
                    <?php if (!empty($product['light_care'])): ?>
                    <span class="px-4 py-2 rounded-full text-xs font-semibold bg-white dark:bg-[#1e2b24] border border-[#e9f2ec] dark:border-gray-700 text-text-secondary flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">wb_sunny</span> <?= clean($product['light_care']) ?>
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($product['water_care'])): ?>
                    <span class="px-4 py-2 rounded-full text-xs font-semibold bg-white dark:bg-[#1e2b24] border border-[#e9f2ec] dark:border-gray-700 text-text-secondary flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">water_drop</span> <?= clean($product['water_care']) ?>
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($product['temp_care'])): ?>
                    <span class="px-4 py-2 rounded-full text-xs font-semibold bg-white dark:bg-[#1e2b24] border border-[#e9f2ec] dark:border-gray-700 text-text-secondary flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">thermostat</span> <?= clean($product['temp_care']) ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <form class="mb-6 space-y-4" action="cart/add" method="POST">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div class="flex items-center gap-4">
                        <label class="block text-sm font-bold text-text-main dark:text-white">Số lượng</label>
                        <div class="flex items-center border border-[#e9f2ec] dark:border-gray-700 rounded-lg overflow-hidden">
                            <button class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" type="button" onclick="decrementQty()">
                                <span class="material-symbols-outlined">remove</span>
                            </button>
                            <input id="quantity" name="quantity" class="w-16 text-center bg-transparent border-x border-[#e9f2ec] dark:border-gray-700 py-2 font-bold text-text-main dark:text-white" min="1" max="<?= $product['stock_quantity'] ?? 999 ?>" type="number" value="1"/>
                            <button class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" type="button" onclick="incrementQty()">
                                <span class="material-symbols-outlined">add</span>
                            </button>
                        </div>
                        <?php if (!empty($product['stock_quantity'])): ?>
                        <span class="text-sm text-text-secondary">(Còn <?= $product['stock_quantity'] ?> sản phẩm)</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex gap-4">
                        <button type="submit" class="flex-1 px-8 py-4 rounded-full bg-primary text-white font-bold shadow-lg shadow-primary/30 hover:bg-primary-dark active:scale-95 transition-all flex items-center justify-center gap-2" <?= empty($product['stock_quantity']) || $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                            <span class="material-symbols-outlined">add_shopping_cart</span>
                            Thêm vào giỏ
                        </button>
                        <button type="button" class="size-14 rounded-full bg-white dark:bg-[#1e2b24] border border-[#e9f2ec] dark:border-gray-700 flex items-center justify-center hover:bg-primary hover:text-white hover:border-primary transition-all">
                            <span class="material-symbols-outlined">favorite</span>
                        </button>
                    </div>
                </form>
                
                <!-- Features -->
                <div class="bg-white dark:bg-[#1e2b24] rounded-2xl border border-[#e9f2ec] dark:border-gray-800 p-6 space-y-4">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-primary">local_shipping</span>
                        <div>
                            <h4 class="font-bold text-sm text-text-main dark:text-white">Miễn phí vận chuyển</h4>
                            <p class="text-xs text-text-secondary dark:text-gray-400">Cho đơn hàng từ 500k</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-primary">verified_user</span>
                        <div>
                            <h4 class="font-bold text-sm text-text-main dark:text-white">Cam kết chất lượng</h4>
                            <p class="text-xs text-text-secondary dark:text-gray-400">Đổi trả trong 7 ngày</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-primary">support_agent</span>
                        <div>
                            <h4 class="font-bold text-sm text-text-main dark:text-white">Hỗ trợ 24/7</h4>
                            <p class="text-xs text-text-secondary dark:text-gray-400">Tư vấn chăm sóc cây miễn phí</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="mb-16">
            <div class="border-b border-[#e9f2ec] dark:border-gray-800 mb-8">
                <nav class="flex gap-8">
                    <button class="pb-4 px-2 border-b-2 border-primary text-primary font-bold transition-colors">Mô tả</button>
                    <button class="pb-4 px-2 border-b-2 border-transparent text-text-secondary hover:text-primary transition-colors">Hướng dẫn chăm sóc</button>
                    <button class="pb-4 px-2 border-b-2 border-transparent text-text-secondary hover:text-primary transition-colors">Đánh giá (24)</button>
                </nav>
            </div>
            
            <div class="prose prose-lg max-w-none dark:prose-invert">
                <h3 class="text-2xl font-bold mb-4">Về <?= clean($product['name']) ?></h3>
                <div class="text-text-secondary dark:text-gray-400 leading-relaxed">
                    <?= nl2br(clean($product['long_description'] ?? $product['short_description'] ?? '')) ?>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <section>
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-3xl font-extrabold tracking-tight text-text-main dark:text-white">Sản phẩm liên quan</h2>
                <a class="text-primary font-bold flex items-center gap-1 hover:gap-2 transition-all" href="products.php">
                    Xem tất cả
                    <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($relatedProducts as $relProduct): ?>
                <article class="group bg-white dark:bg-[#1e2b24] rounded-2xl overflow-hidden border border-transparent hover:border-[#e9f2ec] dark:hover:border-gray-700 hover:shadow-xl transition-all duration-300 flex flex-col">
                    <a href="product-detail.php?id=<?= $relProduct['id'] ?>" class="relative w-full aspect-[4/5] overflow-hidden bg-gray-100 dark:bg-gray-800">
                        <img alt="<?= clean($relProduct['name']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" src="<?= $relProduct['image_url'] ?? image_url('products/default.jpg') ?>"/>
                    </a>
                    <div class="p-5 flex flex-col flex-1">
                        <h3 class="text-lg font-bold text-text-main dark:text-white group-hover:text-primary transition-colors mb-2"><?= clean($relProduct['name']) ?></h3>
                        <p class="text-sm text-text-secondary dark:text-gray-400 mb-4 line-clamp-2"><?= clean($relProduct['short_description'] ?? '') ?></p>
                        <div class="mt-auto flex items-center justify-between">
                            <span class="text-xl font-extrabold text-primary"><?= format_currency($relProduct['price']) ?></span>
                            <a href="product-detail.php?id=<?= $relProduct['id'] ?>" class="size-10 rounded-full bg-primary text-white flex items-center justify-center shadow-lg shadow-primary/30 hover:bg-primary-dark active:scale-95 transition-all">
                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                            </a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

<script>
function changeImage(thumb) {
    const mainImage = document.getElementById('mainImage');
    const thumbImg = thumb.querySelector('img');
    mainImage.src = thumbImg.src;
    
    // Update border
    document.querySelectorAll('.grid button').forEach(btn => {
        btn.classList.remove('border-primary');
        btn.classList.add('border-transparent');
    });
    thumb.classList.remove('border-transparent');
    thumb.classList.add('border-primary');
}

function incrementQty() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.max) || 999;
    if (parseInt(input.value) < max) {
        input.value = parseInt(input.value) + 1;
    }
}

function decrementQty() {
    const input = document.getElementById('quantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}
</script>

<?php include 'includes/footer.php'; ?>
