<?php
$pageTitle = 'Cửa hàng - GreenSpace';
$currentPage = 'products';
include __DIR__ . '/../layouts/header.php';
?>

<style>
    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }
    ::-webkit-scrollbar-track {
        background: transparent; 
    }
    ::-webkit-scrollbar-thumb {
        background: #d2e4da; 
        border-radius: 4px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #b0c4b9; 
    }
</style>

<!-- Main Content -->
<div class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Heading -->
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-text-main dark:text-white">
                Cửa hàng
            </h1>
            <p class="text-text-secondary dark:text-gray-400 text-lg max-w-2xl">
                Mang thiên nhiên vào không gian sống và làm việc của bạn với bộ sưu tập cây xanh được tuyển chọn kỹ lưỡng.
            </p>
        </div>
        
        <!-- Sort dropdown -->
        <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-text-secondary whitespace-nowrap">Sắp xếp theo:</span>
            <select class="bg-transparent border-none text-sm font-bold text-text-main dark:text-white focus:ring-0 cursor-pointer pr-8 py-0">
                <option>Mới nhất</option>
                <option>Bán chạy</option>
                <option>Giá thấp đến cao</option>
                <option>Giá cao đến thấp</option>
            </select>
        </div>
    </div>
    
    <div class="flex flex-col lg:flex-row gap-10 items-start">
        <!-- Sidebar Filters -->
        <?php include __DIR__ . '/filters.php'; ?>
        
        <!-- Product Grid -->
        <section class="flex-1 w-full">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <?php include __DIR__ . '/../components/product-card-grid.php'; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-12">
                        <span class="material-symbols-outlined text-6xl text-gray-300 mb-4">search_off</span>
                        <p class="text-text-secondary text-lg">Không tìm thấy sản phẩm nào.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination / Load More -->
            <?php if (!empty($products) && count($products) >= 12): ?>
                <div class="mt-12 flex justify-center">
                    <button class="px-8 py-3 rounded-full bg-white dark:bg-[#1e2b24] border border-[#e9f2ec] dark:border-gray-700 text-text-main dark:text-white font-bold hover:bg-primary hover:text-white hover:border-primary transition-all duration-300 shadow-sm flex items-center gap-2">
                        Xem thêm 12 sản phẩm
                        <span class="material-symbols-outlined">expand_more</span>
                    </button>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
