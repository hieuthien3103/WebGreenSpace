<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../app/models/Product.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$productSlug = isset($_GET['slug']) ? clean($_GET['slug']) : '';

$productModel = new Product();

$product = null;
if ($productSlug !== '') {
    $product = $productModel->getBySlug($productSlug);
} elseif ($productId > 0) {
    $product = $productModel->getById($productId);
}

if (!$product) {
    header('Location: products.php');
    exit();
}

$productModel->incrementViews((int)$product['id']);

$galleryImages = $productModel->getImages((int)$product['id']);
if (empty($galleryImages)) {
    $galleryImages = [[
        'image_url' => $product['image_url'],
        'is_primary' => 1,
    ]];
}

$relatedProducts = $productModel->getRelatedProducts((int)$product['id'], (int)$product['category_id'], 4);
$currentPrice = !empty($product['sale_price']) && (float)$product['sale_price'] > 0
    ? (float)$product['sale_price']
    : (float)$product['price'];
$hasSalePrice = !empty($product['sale_price']) && (float)$product['sale_price'] > 0 && (float)$product['sale_price'] < (float)$product['price'];

$pageTitle = clean($product['name']) . ' - GreenSpace';
$currentPage = 'products';

include 'includes/header.php';
?>

<main class="flex-1">
    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="mb-8 text-sm">
            <ol class="flex items-center gap-2 text-text-secondary dark:text-gray-400">
                <li><a class="hover:text-primary transition-colors" href="home.php">Trang chủ</a></li>
                <li class="material-symbols-outlined text-[16px]">chevron_right</li>
                <li><a class="hover:text-primary transition-colors" href="products.php">Cửa hàng</a></li>
                <li class="material-symbols-outlined text-[16px]">chevron_right</li>
                <?php if (!empty($product['category_name'])): ?>
                <li><a class="hover:text-primary transition-colors" href="products.php?category=<?= clean($product['category_slug']) ?>"><?= clean($product['category_name']) ?></a></li>
                <li class="material-symbols-outlined text-[16px]">chevron_right</li>
                <?php endif; ?>
                <li class="text-text-main dark:text-white font-medium"><?= clean($product['name']) ?></li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">
            <div class="flex flex-col gap-4">
                <div class="relative w-full aspect-square bg-white dark:bg-[#1e2b24] rounded-2xl overflow-hidden border border-[#e9f2ec] dark:border-gray-800">
                    <img id="mainImage" alt="<?= clean($product['name']) ?>" class="w-full h-full object-cover" src="<?= clean($galleryImages[0]['image_url']) ?>"/>
                    <?php if (!empty($product['badge']) && $product['badge'] === 'new'): ?>
                    <div class="absolute top-4 right-4">
                        <span class="px-4 py-1.5 bg-white/90 dark:bg-black/60 backdrop-blur text-xs font-bold rounded-full text-primary uppercase tracking-wider">
                            Mới
                        </span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (count($galleryImages) > 1): ?>
                <div class="grid grid-cols-4 gap-3">
                    <?php foreach ($galleryImages as $index => $image): ?>
                    <button class="aspect-square rounded-lg overflow-hidden border-2 <?= $index === 0 ? 'border-primary' : 'border-transparent hover:border-primary transition-colors' ?>" onclick="changeImage(this)">
                        <img alt="<?= clean($product['name']) ?> thumbnail <?= $index + 1 ?>" class="w-full h-full object-cover" src="<?= clean($image['image_url']) ?>"/>
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="flex flex-col">
                <div class="mb-6">
                    <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-text-main dark:text-white mb-4"><?= clean($product['name']) ?></h1>

                    <div class="flex flex-wrap items-center gap-4 mb-4">
                        <span class="text-sm <?= (int)$product['stock'] > 0 ? 'text-primary' : 'text-red-500' ?>">
                            <?= (int)$product['stock'] > 0 ? 'Còn hàng' : 'Hết hàng' ?>
                        </span>
                        <?php if (!empty($product['size'])): ?>
                        <span class="text-sm text-text-secondary dark:text-gray-400">Kích thước: <span class="font-semibold text-text-main dark:text-white"><?= clean($product['size']) ?></span></span>
                        <?php endif; ?>
                    </div>

                    <div class="flex items-baseline gap-4 mb-6">
                        <span class="text-5xl font-extrabold text-primary"><?= format_currency($currentPrice) ?></span>
                        <?php if ($hasSalePrice): ?>
                        <span class="text-2xl text-gray-400 line-through"><?= format_currency((float)$product['price']) ?></span>
                        <span class="px-3 py-1 bg-red-500 text-white text-sm font-bold rounded-lg">-<?= (int)$product['discount_percentage'] ?>%</span>
                        <?php endif; ?>
                    </div>

                    <p class="text-text-secondary dark:text-gray-400 text-lg leading-relaxed">
                        <?= clean($product['description'] ?? '') ?>
                    </p>
                </div>

                <div class="mb-6 flex flex-wrap gap-2">
                    <span class="px-4 py-2 rounded-full text-xs font-semibold bg-white dark:bg-[#1e2b24] border border-[#e9f2ec] dark:border-gray-700 text-text-secondary flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">spa</span> Chăm sóc: <?= clean($product['temp_care']) ?>
                    </span>
                    <span class="px-4 py-2 rounded-full text-xs font-semibold bg-white dark:bg-[#1e2b24] border border-[#e9f2ec] dark:border-gray-700 text-text-secondary flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">wb_sunny</span> Ánh sáng: <?= clean($product['light_care']) ?>
                    </span>
                    <span class="px-4 py-2 rounded-full text-xs font-semibold bg-white dark:bg-[#1e2b24] border border-[#e9f2ec] dark:border-gray-700 text-text-secondary flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">water_drop</span> Nước: <?= clean($product['water_care']) ?>
                    </span>
                </div>

                <form class="mb-6 space-y-4" action="cart.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                    <input type="hidden" name="redirect_to" value="<?= clean('product-detail.php?slug=' . $product['slug']) ?>">

                    <div class="flex items-center gap-4">
                        <label class="block text-sm font-bold text-text-main dark:text-white">Số lượng</label>
                        <div class="flex items-center border border-[#e9f2ec] dark:border-gray-700 rounded-lg overflow-hidden">
                            <button class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" type="button" onclick="decrementQty()">
                                <span class="material-symbols-outlined">remove</span>
                            </button>
                            <input id="quantity" name="quantity" class="w-16 text-center bg-transparent border-x border-[#e9f2ec] dark:border-gray-700 py-2 font-bold text-text-main dark:text-white" min="1" max="<?= max(1, (int)$product['stock']) ?>" type="number" value="1"/>
                            <button class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" type="button" onclick="incrementQty()">
                                <span class="material-symbols-outlined">add</span>
                            </button>
                        </div>
                        <span class="text-sm text-text-secondary">(Còn <?= (int)$product['stock'] ?> sản phẩm)</span>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="flex-1 px-8 py-4 rounded-full bg-primary text-white font-bold shadow-lg shadow-primary/30 hover:bg-primary-dark active:scale-95 transition-all flex items-center justify-center gap-2" <?= (int)$product['stock'] <= 0 ? 'disabled' : '' ?>>
                            <span class="material-symbols-outlined">add_shopping_cart</span>
                            Thêm vào giỏ
                        </button>
                        <button type="button" class="size-14 rounded-full bg-white dark:bg-[#1e2b24] border border-[#e9f2ec] dark:border-gray-700 flex items-center justify-center hover:bg-primary hover:text-white hover:border-primary transition-all">
                            <span class="material-symbols-outlined">favorite</span>
                        </button>
                    </div>
                </form>

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

        <div class="mb-16">
            <div class="border-b border-[#e9f2ec] dark:border-gray-800 mb-8">
                <nav class="flex gap-8">
                    <button class="pb-4 px-2 border-b-2 border-primary text-primary font-bold transition-colors">Mô tả</button>
                    <button class="pb-4 px-2 border-b-2 border-transparent text-text-secondary hover:text-primary transition-colors">Hướng dẫn chăm sóc</button>
                </nav>
            </div>

            <div class="prose prose-lg max-w-none dark:prose-invert">
                <h3 class="text-2xl font-bold mb-4">Về <?= clean($product['name']) ?></h3>
                <div class="text-text-secondary dark:text-gray-400 leading-relaxed">
                    <?= nl2br(clean($product['description'] ?? '')) ?>
                </div>
            </div>
        </div>

        <?php if (!empty($relatedProducts)): ?>
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
                <?php
                $relatedPrice = !empty($relProduct['sale_price']) && (float)$relProduct['sale_price'] > 0
                    ? (float)$relProduct['sale_price']
                    : (float)$relProduct['price'];
                ?>
                <article class="group bg-white dark:bg-[#1e2b24] rounded-2xl overflow-hidden border border-transparent hover:border-[#e9f2ec] dark:hover:border-gray-700 hover:shadow-xl transition-all duration-300 flex flex-col">
                    <a href="product-detail.php?slug=<?= clean($relProduct['slug']) ?>" class="relative w-full aspect-[4/5] overflow-hidden bg-gray-100 dark:bg-gray-800">
                        <img alt="<?= clean($relProduct['name']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" src="<?= clean($relProduct['image_url']) ?>"/>
                    </a>
                    <div class="p-5 flex flex-col flex-1">
                        <h3 class="text-lg font-bold text-text-main dark:text-white group-hover:text-primary transition-colors mb-2"><?= clean($relProduct['name']) ?></h3>
                        <p class="text-sm text-text-secondary dark:text-gray-400 mb-4 line-clamp-2"><?= clean($relProduct['description'] ?? '') ?></p>
                        <div class="mt-auto flex items-center justify-between">
                            <span class="text-xl font-extrabold text-primary"><?= format_currency($relatedPrice) ?></span>
                            <a href="product-detail.php?slug=<?= clean($relProduct['slug']) ?>" class="size-10 rounded-full bg-primary text-white flex items-center justify-center shadow-lg shadow-primary/30 hover:bg-primary-dark active:scale-95 transition-all">
                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                            </a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</main>

<script>
function changeImage(thumb) {
    const mainImage = document.getElementById('mainImage');
    const thumbImg = thumb.querySelector('img');

    if (!mainImage || !thumbImg) {
        return;
    }

    mainImage.src = thumbImg.src;

    const gallery = thumb.parentElement;
    if (!gallery) {
        return;
    }

    gallery.querySelectorAll('button').forEach((button) => {
        button.classList.remove('border-primary');
        button.classList.add('border-transparent');
    });

    thumb.classList.remove('border-transparent');
    thumb.classList.add('border-primary');
}

function incrementQty() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.max, 10) || 1;
    const current = parseInt(input.value, 10) || 1;

    if (current < max) {
        input.value = current + 1;
    }
}

function decrementQty() {
    const input = document.getElementById('quantity');
    const current = parseInt(input.value, 10) || 1;

    if (current > 1) {
        input.value = current - 1;
    }
}
</script>

<?php include 'includes/footer.php'; ?>
