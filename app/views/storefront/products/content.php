<div id="productsPageContent" data-page-title="<?= clean($pageTitle) ?>" class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-text-main dark:text-white">
                <?php if (!empty($category) && isset($category['name'])): ?>
                    <?= clean($category['name']) ?>
                <?php else: ?>
                    Cửa hàng
                <?php endif; ?>
            </h1>
            <p class="text-text-secondary dark:text-gray-400 text-lg max-w-2xl">
                <?php if (!empty($category) && isset($category['description']) && trim((string)$category['description']) !== ''): ?>
                    <?= clean($category['description']) ?>
                <?php else: ?>
                    Mang thiên nhiên vào không gian sống và làm việc của bạn với bộ sưu tập cây xanh được tuyển chọn kỹ lưỡng.
                <?php endif; ?>
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <form method="GET" action="<?= clean($currentCatalogUrl) ?>" class="relative" data-products-search-form="true">
                <input type="text"
                       name="search"
                       value="<?= clean($search) ?>"
                       placeholder="Tìm kiếm sản phẩm..."
                       class="pl-10 pr-4 py-2 rounded-full border border-[#e9f2ec] dark:border-gray-700 bg-white dark:bg-[#1e2b24] text-text-main dark:text-white focus:border-primary focus:ring-1 focus:ring-primary w-full sm:w-64">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary text-[20px]">search</span>
            </form>

            <div class="flex items-center gap-3 whitespace-nowrap">
                <span class="text-sm font-medium text-text-secondary">Sắp xếp:</span>
                <select id="sortSelect" onchange="handleSort(this.value)" class="bg-transparent border-none text-sm font-bold text-text-main dark:text-white focus:ring-0 cursor-pointer pr-8 py-0">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                    <option value="bestseller" <?= $sort === 'bestseller' ? 'selected' : '' ?>>Bán chạy</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Giá thấp đến cao</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Giá cao đến thấp</option>
                </select>
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-10 items-start">
        <aside class="w-full lg:w-72 shrink-0 flex flex-col gap-8 lg:sticky lg:top-28">
            <div class="flex flex-col gap-4">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">filter_list</span> Bộ lọc
                </h3>
                <div class="flex flex-col gap-3">
                    <details class="group bg-white dark:bg-[#1e2b24] rounded-xl border border-[#e9f2ec] dark:border-gray-800 overflow-hidden" open>
                        <summary class="flex cursor-pointer items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                            <span class="font-semibold text-sm">Danh mục</span>
                            <span class="material-symbols-outlined text-lg transition-transform duration-200 group-open:rotate-180">expand_more</span>
                        </summary>
                        <div class="px-4 pb-4 pt-0 text-sm space-y-2 text-text-secondary">
                            <a href="<?= clean($buildAllProductsUrl(['page' => 1])) ?>" data-products-ajax-link="true" class="flex items-center gap-3 cursor-pointer hover:text-primary transition-colors py-1 <?= empty($category_filter) ? 'text-primary font-semibold' : '' ?>">
                                <span class="material-symbols-outlined text-[18px]"><?= empty($category_filter) ? 'radio_button_checked' : 'radio_button_unchecked' ?></span>
                                Tất cả sản phẩm
                            </a>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $cat): ?>
                                    <a href="<?= clean($buildCategoryUrl($cat['slug'], ['page' => 1])) ?>"
                                       data-products-ajax-link="true"
                                       class="flex items-center gap-3 cursor-pointer hover:text-primary transition-colors py-1 <?= $category_filter === $cat['slug'] ? 'text-primary font-semibold' : '' ?>">
                                        <span class="material-symbols-outlined text-[18px]"><?= $category_filter === $cat['slug'] ? 'radio_button_checked' : 'radio_button_unchecked' ?></span>
                                        <?= clean($cat['name']) ?>
                                        <?php if (!empty($cat['product_count'])): ?>
                                            <span class="ml-auto text-xs bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full"><?= (int)$cat['product_count'] ?></span>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </details>

                    <details class="group bg-white dark:bg-[#1e2b24] rounded-xl border border-[#e9f2ec] dark:border-gray-800 overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                            <span class="font-semibold text-sm">Đặc điểm</span>
                            <span class="material-symbols-outlined text-lg transition-transform duration-200 group-open:rotate-180">expand_more</span>
                        </summary>
                        <div class="px-4 pb-4 pt-0 text-sm space-y-2 text-text-secondary">
                            <label class="flex items-center gap-3 cursor-pointer hover:text-primary transition-colors">
                                <input class="rounded border-gray-300 text-primary focus:ring-primary" type="checkbox"/> Dễ chăm sóc
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer hover:text-primary transition-colors">
                                <input class="rounded border-gray-300 text-primary focus:ring-primary" type="checkbox"/> Lọc không khí
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer hover:text-primary transition-colors">
                                <input class="rounded border-gray-300 text-primary focus:ring-primary" type="checkbox"/> Ưa bóng râm
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer hover:text-primary transition-colors">
                                <input class="rounded border-gray-300 text-primary focus:ring-primary" type="checkbox"/> Phong thủy
                            </label>
                        </div>
                    </details>
                </div>
            </div>

            <div class="flex flex-col gap-3">
                <div class="flex justify-between items-center">
                    <h4 class="text-sm font-bold">Khoảng giá</h4>
                    <span class="text-xs text-primary font-medium cursor-pointer" id="resetPrice">Reset</span>
                </div>
                <div class="bg-white dark:bg-[#1e2b24] rounded-xl border border-[#e9f2ec] dark:border-gray-800 p-4">
                    <div class="mb-6">
                        <div class="relative h-2 mb-4">
                            <div class="absolute w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                            <div id="priceSliderTrack" class="absolute h-2 bg-primary rounded-full"></div>
                            <input type="range"
                                   id="minPriceSlider"
                                   min="0"
                                   max="5000000"
                                   value="<?= $min_price ?? 0 ?>"
                                   step="10000"
                                   class="absolute w-full h-2 bg-transparent appearance-none pointer-events-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-5 [&::-webkit-slider-thumb]:h-5 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-white [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-primary [&::-webkit-slider-thumb]:cursor-pointer [&::-webkit-slider-thumb]:shadow-md [&::-webkit-slider-thumb]:hover:scale-110 [&::-webkit-slider-thumb]:transition-transform [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:appearance-none [&::-moz-range-thumb]:w-5 [&::-moz-range-thumb]:h-5 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-white [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-primary [&::-moz-range-thumb]:cursor-pointer [&::-moz-range-thumb]:shadow-md">
                            <input type="range"
                                   id="maxPriceSlider"
                                   min="0"
                                   max="5000000"
                                   value="<?= $max_price ?? 5000000 ?>"
                                   step="10000"
                                   class="absolute w-full h-2 bg-transparent appearance-none pointer-events-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:w-5 [&::-webkit-slider-thumb]:h-5 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-white [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-primary [&::-webkit-slider-thumb]:cursor-pointer [&::-webkit-slider-thumb]:shadow-md [&::-webkit-slider-thumb]:hover:scale-110 [&::-webkit-slider-thumb]:transition-transform [&::-moz-range-thumb]:pointer-events-auto [&::-moz-range-thumb]:appearance-none [&::-moz-range-thumb]:w-5 [&::-moz-range-thumb]:h-5 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:bg-white [&::-moz-range-thumb]:border-2 [&::-moz-range-thumb]:border-primary [&::-moz-range-thumb]:cursor-pointer [&::-moz-range-thumb]:shadow-md">
                        </div>

                        <div class="flex justify-between items-center text-sm font-semibold text-text-main dark:text-gray-300 mb-4">
                            <span id="minPriceDisplay">0đ</span>
                            <span class="text-xs text-gray-400">-</span>
                            <span id="maxPriceDisplay">5tr</span>
                        </div>

                        <button type="button"
                                id="applyPriceFilter"
                                class="w-full px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary-dark transition-colors shadow-sm">
                            Áp dụng lọc giá
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <section class="flex-1 w-full">
            <?php if (!empty($search)): ?>
            <div class="mb-4 flex items-center gap-2 text-text-secondary">
                <span class="material-symbols-outlined text-lg">search</span>
                <span>Kết quả tìm kiếm cho "<strong class="text-text-main"><?= clean($search) ?></strong>"</span>
                <span class="text-sm">(<?= $totalProducts ?> sản phẩm)</span>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                    <article class="group bg-white dark:bg-[#1e2b24] rounded-2xl overflow-hidden border border-transparent hover:border-[#e9f2ec] dark:hover:border-gray-700 hover:shadow-xl transition-all duration-300 flex flex-col relative">
                        <?php if (!empty($product['badge'])): ?>
                        <div class="absolute top-3 left-3 z-10">
                            <?php if ($product['badge'] === 'new'): ?>
                                <span class="px-3 py-1 bg-white/90 dark:bg-black/60 backdrop-blur text-xs font-bold rounded-full text-text-main dark:text-white uppercase tracking-wider">Mới</span>
                            <?php elseif ($product['badge'] === 'sale' && !empty($product['sale_price'])): ?>
                                <?php $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>
                                <span class="px-3 py-1 bg-red-500 text-xs font-bold rounded-full text-white uppercase tracking-wider">-<?= $discount ?>%</span>
                            <?php elseif ($product['badge'] === 'bestseller'): ?>
                                <span class="px-3 py-1 bg-yellow-400 text-xs font-bold rounded-full text-black uppercase tracking-wider">Best Seller</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <a href="<?= base_url('product/' . $product['slug']) ?>" class="relative w-full aspect-[4/5] overflow-hidden bg-gray-100 dark:bg-gray-800">
                            <img alt="<?= clean($product['name']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" src="<?= clean($product['image_url'] ?? image_url('products/default.jpg')) ?>"/>

                            <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-3">
                                <span class="size-10 rounded-full bg-white text-text-main flex items-center justify-center shadow-lg transform translate-y-4 group-hover:translate-y-0 duration-300">
                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                </span>
                                <span class="size-10 rounded-full bg-white text-text-main flex items-center justify-center shadow-lg transform translate-y-4 group-hover:translate-y-0 duration-300 delay-75">
                                    <span class="material-symbols-outlined text-[20px]">favorite</span>
                                </span>
                            </div>
                        </a>

                        <div class="p-5 flex flex-col flex-1">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-lg font-bold text-text-main dark:text-white group-hover:text-primary transition-colors flex-1">
                                    <a href="<?= base_url('product/' . $product['slug']) ?>">
                                        <?= clean($product['name']) ?>
                                    </a>
                                </h3>
                            </div>

                            <?php if (!empty($product['category_name'])): ?>
                            <p class="text-xs text-text-secondary mb-2">
                                <span class="material-symbols-outlined text-[14px] align-middle">category</span>
                                <?= clean($product['category_name']) ?>
                            </p>
                            <?php endif; ?>

                            <p class="text-sm text-text-secondary dark:text-gray-400 mb-4 line-clamp-2">
                                <?= clean($product['short_description'] ?? substr($product['description'] ?? '', 0, 100)) ?>
                            </p>

                            <div class="mt-auto flex items-center justify-between gap-3">
                                <?php if (!empty($product['sale_price']) && $product['sale_price'] > 0 && $product['sale_price'] < $product['price']): ?>
                                    <div class="flex flex-col">
                                        <span class="text-xs text-text-secondary line-through"><?= format_currency($product['price']) ?></span>
                                        <span class="text-xl font-extrabold text-primary"><?= format_currency($product['sale_price']) ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xl font-extrabold text-primary"><?= format_currency($product['price']) ?></span>
                                <?php endif; ?>

                                <form action="<?= base_url('cart') ?>" method="POST" data-product-image="<?= clean($product['image_url'] ?? image_url('products/default.jpg')) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="redirect_to" value="<?= clean($currentCatalogPath) ?>">
                                    <button type="submit" class="size-10 rounded-full bg-primary text-white flex items-center justify-center shadow-lg shadow-primary/30 hover:bg-primary-dark active:scale-95 transition-all" title="Thêm vào giỏ hàng">
                                        <span class="material-symbols-outlined text-[20px]">add_shopping_cart</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-16">
                        <span class="material-symbols-outlined text-6xl text-gray-300 mb-4 block">search_off</span>
                        <p class="text-text-secondary text-lg mb-2">Không tìm thấy sản phẩm nào</p>
                        <?php if (!empty($search)): ?>
                            <p class="text-sm text-text-secondary mb-4">Thử tìm kiếm với từ khóa khác</p>
                            <a href="<?= clean($buildAllProductsUrl(['search' => '', 'page' => 1])) ?>" data-products-ajax-link="true" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-full hover:bg-primary-dark transition-colors">
                                <span class="material-symbols-outlined text-[20px]">refresh</span>
                                Xem tất cả sản phẩm
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($totalProducts > 0 && $totalPages > 1): ?>
            <?php
            $pageNumbers = array_unique(array_merge(
                [1, $totalPages],
                range(max(1, $page - 2), min($totalPages, $page + 2))
            ));
            sort($pageNumbers);
            ?>
            <div class="mt-12 flex flex-col items-center gap-4">
                <nav class="flex flex-wrap items-center justify-center gap-2" aria-label="Pagination">
                    <a href="<?= clean($buildCatalogUrl(['page' => max(1, $page - 1)])) ?>"
                       data-products-ajax-link="true"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-[#e9f2ec] dark:border-gray-700 bg-white dark:bg-[#1e2b24] text-sm font-semibold text-text-main dark:text-white transition-colors <?= $page <= 1 ? 'pointer-events-none opacity-50' : 'hover:border-primary hover:text-primary' ?>">
                        <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                        Trước
                    </a>

                    <?php $previousPageNumber = null; ?>
                    <?php foreach ($pageNumbers as $pageNumber): ?>
                        <?php if ($previousPageNumber !== null && $pageNumber - $previousPageNumber > 1): ?>
                            <span class="px-2 text-text-secondary">...</span>
                        <?php endif; ?>

                        <a href="<?= clean($buildCatalogUrl(['page' => $pageNumber])) ?>"
                           data-products-ajax-link="true"
                           aria-current="<?= $pageNumber === $page ? 'page' : 'false' ?>"
                           class="inline-flex min-w-11 items-center justify-center rounded-full border px-4 py-2 text-sm font-semibold transition-colors <?= $pageNumber === $page ? 'border-primary bg-primary text-white shadow-lg shadow-primary/20' : 'border-[#e9f2ec] dark:border-gray-700 bg-white dark:bg-[#1e2b24] text-text-main dark:text-white hover:border-primary hover:text-primary' ?>">
                            <?= $pageNumber ?>
                        </a>

                        <?php $previousPageNumber = $pageNumber; ?>
                    <?php endforeach; ?>

                    <a href="<?= clean($buildCatalogUrl(['page' => min($totalPages, $page + 1)])) ?>"
                       data-products-ajax-link="true"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-[#e9f2ec] dark:border-gray-700 bg-white dark:bg-[#1e2b24] text-sm font-semibold text-text-main dark:text-white transition-colors <?= $page >= $totalPages ? 'pointer-events-none opacity-50' : 'hover:border-primary hover:text-primary' ?>">
                        Sau
                        <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                    </a>
                </nav>
            </div>
            <?php endif; ?>
        </section>
    </div>
</div>
