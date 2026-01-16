<?php 
$pageTitle = 'Cây Trầu Bà Nam Mỹ - GreenSpace';
$currentPage = 'products';
include 'includes/header.php'; 
?>

<!-- Main Content -->
<main class="flex-1">
    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="mb-8 text-sm">
            <ol class="flex items-center gap-2 text-text-secondary dark:text-gray-400">
                <li><a class="hover:text-primary transition-colors" href="demo.php">Trang chủ</a></li>
                <li class="material-symbols-outlined text-[16px]">chevron_right</li>
                <li><a class="hover:text-primary transition-colors" href="products.php">Cửa hàng</a></li>
                <li class="material-symbols-outlined text-[16px]">chevron_right</li>
                <li class="text-text-main dark:text-white font-medium">Cây Trầu Bà Nam Mỹ</li>
            </ol>
        </nav>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">
            <!-- Product Images -->
            <div class="flex flex-col gap-4">
                <div class="relative w-full aspect-square bg-white dark:bg-[#1e2b24] rounded-2xl overflow-hidden border border-[#e9f2ec] dark:border-gray-800">
                    <img id="mainImage" alt="Cây Trầu Bà Nam Mỹ" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDeUfh4Ykgwe6D8Dp0aagg4se51MC_-ppr0C7vlMOXkteGnE8wSt6dvFPl3VyypivuGhqq1UlFeaPJdvSRD5P4fmfHG35h5zzghk3NVm4Oit-OttvKT8qUu3uTXukK8K-H2IbBupXx0GGNxNZtejP_s0A8RFs78zuSKb7UQbfn7m83z1SSVWhg2aXIikSoXtOOW0EvUVZ6E6fwBczb2xv9ny29TZWohoukUUZ-XmohkIFuQXy8ROcob02cyCpSBefp0OCHzAPuxgME"/>
                    <div class="absolute top-4 right-4">
                        <span class="px-4 py-1.5 bg-white/90 dark:bg-black/60 backdrop-blur text-xs font-bold rounded-full text-primary uppercase tracking-wider">
                            Mới
                        </span>
                    </div>
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
                    <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-text-main dark:text-white mb-4">Cây Trầu Bà Nam Mỹ</h1>
                    <div class="flex items-center gap-6 mb-4">
                        <div class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-yellow-400 filled">star</span>
                            <span class="material-symbols-outlined text-yellow-400 filled">star</span>
                            <span class="material-symbols-outlined text-yellow-400 filled">star</span>
                            <span class="material-symbols-outlined text-yellow-400 filled">star</span>
                            <span class="material-symbols-outlined text-yellow-400">star</span>
                            <span class="ml-2 text-sm text-text-secondary dark:text-gray-400">(24 đánh giá)</span>
                        </div>
                        <span class="text-sm text-text-secondary dark:text-gray-400">SKU: <span class="font-mono font-bold text-text-main dark:text-white">TB-001</span></span>
                    </div>
                    <div class="flex items-baseline gap-4 mb-6">
                        <span class="text-5xl font-extrabold text-primary">250.000đ</span>
                    </div>
                    <p class="text-text-secondary dark:text-gray-400 text-lg leading-relaxed">
                        Cây Trầu Bà Nam Mỹ (Monstera) là loại cây trang trí nội thất lý tưởng với lá hình trái tim độc đáo. Dễ chăm sóc, phù hợp với văn phòng và gia đình.
                    </p>
                </div>
                
                <!-- Tags -->
                <div class="mb-6 flex flex-wrap gap-2">
                    <span class="px-4 py-2 rounded-full text-xs font-semibold bg-white dark:bg-[#1e2b24] border border-[#e9f2ec] dark:border-gray-700 text-text-secondary flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">check_circle</span> Dễ chăm sóc
                    </span>
                    <span class="px-4 py-2 rounded-full text-xs font-semibold bg-white dark:bg-[#1e2b24] border border-[#e9f2ec] dark:border-gray-700 text-text-secondary flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">wb_sunny</span> Ưa ánh sáng
                    </span>
                    <span class="px-4 py-2 rounded-full text-xs font-semibold bg-white dark:bg-[#1e2b24] border border-[#e9f2ec] dark:border-gray-700 text-text-secondary flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">air</span> Lọc không khí
                    </span>
                </div>
                
                <form class="mb-6 space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-text-main dark:text-white mb-2">Kích thước chậu</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="flex items-center justify-center py-3 px-4 rounded-lg border-2 border-primary bg-primary/5 text-primary font-semibold cursor-pointer transition-all">
                                <input checked class="sr-only" name="size" type="radio" value="small"/>
                                <span>Nhỏ</span>
                            </label>
                            <label class="flex items-center justify-center py-3 px-4 rounded-lg border-2 border-[#e9f2ec] dark:border-gray-700 text-text-main dark:text-gray-400 font-semibold cursor-pointer hover:border-primary hover:text-primary transition-all">
                                <input class="sr-only" name="size" type="radio" value="medium"/>
                                <span>Vừa</span>
                            </label>
                            <label class="flex items-center justify-center py-3 px-4 rounded-lg border-2 border-[#e9f2ec] dark:border-gray-700 text-text-main dark:text-gray-400 font-semibold cursor-pointer hover:border-primary hover:text-primary transition-all">
                                <input class="sr-only" name="size" type="radio" value="large"/>
                                <span>Lớn</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <label class="block text-sm font-bold text-text-main dark:text-white">Số lượng</label>
                        <div class="flex items-center border border-[#e9f2ec] dark:border-gray-700 rounded-lg overflow-hidden">
                            <button class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" type="button">
                                <span class="material-symbols-outlined">remove</span>
                            </button>
                            <input class="w-16 text-center bg-transparent border-x border-[#e9f2ec] dark:border-gray-700 py-2 font-bold text-text-main dark:text-white" min="1" type="number" value="1"/>
                            <button class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" type="button">
                                <span class="material-symbols-outlined">add</span>
                            </button>
                        </div>
                    </div>
                </form>
                
                <div class="flex gap-4 mb-8">
                    <button class="flex-1 px-8 py-4 rounded-full bg-primary text-white font-bold shadow-lg shadow-primary/30 hover:bg-primary-dark active:scale-95 transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">add_shopping_cart</span>
                        Thêm vào giỏ
                    </button>
                    <button class="size-14 rounded-full bg-white dark:bg-[#1e2b24] border border-[#e9f2ec] dark:border-gray-700 flex items-center justify-center hover:bg-primary hover:text-white hover:border-primary transition-all">
                        <span class="material-symbols-outlined">favorite</span>
                    </button>
                </div>
                
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
                <h3 class="text-2xl font-bold mb-4">Về Cây Trầu Bà Nam Mỹ</h3>
                <p class="text-text-secondary dark:text-gray-400 leading-relaxed mb-4">
                    Trầu bà Nam Mỹ (Monstera deliciosa) là một trong những loại cây cảnh phổ biến nhất, nổi tiếng với những chiếc lá hình trái tim có đục lỗ độc đáo. Cây có nguồn gốc từ các khu rừng nhiệt đới ở Trung và Nam Mỹ.
                </p>
                <p class="text-text-secondary dark:text-gray-400 leading-relaxed mb-4">
                    Đây là lựa chọn hoàn hảo cho những người mới bắt đầu trồng cây, vì Monstera rất dễ chăm sóc và có khả năng thích nghi cao với môi trường trong nhà. Cây có thể phát triển mạnh mẽ trong điều kiện ánh sáng gián tiếp và không yêu cầu tưới nước thường xuyên.
                </p>
                
                <h4 class="text-xl font-bold mt-6 mb-3">Đặc điểm nổi bật</h4>
                <ul class="space-y-2 text-text-secondary dark:text-gray-400">
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-primary mt-1">check_circle</span>
                        <span>Lá hình trái tim có đục lỗ độc đáo, tạo điểm nhấn cho không gian</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-primary mt-1">check_circle</span>
                        <span>Khả năng lọc không khí tự nhiên, loại bỏ các chất độc hại</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-primary mt-1">check_circle</span>
                        <span>Dễ dàng nhân giống và chăm sóc</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-primary mt-1">check_circle</span>
                        <span>Phù hợp với nhiều không gian: phòng khách, văn phòng, phòng ngủ</span>
                    </li>
                </ul>
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
                <?php
                // Sample related products
                $relatedProducts = [
                    ['name' => 'Cây Lưỡi Hổ', 'price' => '180.000đ', 'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCiTTx3YnJNis7fQnRSQAKzUBDO1TSsuGlDLIVcdUz7Wu0hM7tQsiWbRTuBcMgx9D4ZQGZIE48ta2L2BWi-Yh2GiiDdQ9Tt1eFm4ax71vpsrNBABwS8TeMtF8jblvP9HBdRUWEoG9UDwOWPJlp_gQJQq7e9N96dffwp8PS8g_3LntjlURPgMyPtia0NifSSDJjePQBK2rk1S5FzDQZ0zXzhcBltb0X3-PeL7BXTUROtJhfLHE_VWppdE2fXVw3TyAvAhiW23wuq9vY'],
                    ['name' => 'Combo Sen Đá', 'price' => '99.000đ', 'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCtKxPjhRgmebHH-iUQF7-uOSw_vcf1RFtLONcUgmP2TGYZqi2quchf1jD7WdmN93eaTLNquaPhcRCX_v11jICTSMAnwrETL1TGTRv2ZWBD-IlAD_7KCHcq201S8lDmq_BOlj7q4iQ6mXYWu-4Vg0p2JhURVcb1qh4P6tkWNU8XipSXwV-bRmAK8TPPQwdnrERghdKhvOKmTsZqEn8bWUGvevQuKm7zuJ0qnD4GuaMaEDlTLKRkyhltjDlrL_m8dKnXZ5Cxy-gEz9Y'],
                    ['name' => 'Cây Đa Búp Đỏ', 'price' => '320.000đ', 'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuBAMVTOVTwmajdmq_VnUwp13SSAqY0ig_BJID1lD0vbihr_kAbaBWOHfLsc_p841FsOy4XFLY7q-epzRTcBxOnceCqj5DfIpHubPVftEkPxde54mT_MvShTO0K1rJAjBX54HYEsW4Dstk2m2Sk0m0dEFQPgcArVbPWrTbXaJEnmofs0vKRCOMHCaZ4SzMU1_HpebhlHqjPlin0dNLxp4eYDUi31XYAxy1hD4X7Aa7wTrB_eK6P0XNX_v4IaPHHsi3S-kVaXd-qaNJs'],
                    ['name' => 'Cây Lan Ý', 'price' => '120.000đ', 'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuB2z2oIEi0w545gFePMLp1mAay91yJ5xQuUFo1sfycYL_IpQOwArSGSw8LpNQ7PFD6MiBLIiKD00B4USllRSShwZ0n6R9oQ6T_hO3odawUWAbc3foOzT3kLN-c6nWm5JarPPmQHgUOrEGH8bRRwBB93EKqI1PTlaWaihGhEXzP2BGYxczb2PVa_wFvrTkXlhm05qJRsg43VK5an6M3fioYHT6C9z2LNADuh-FPfe4GogYsStk7-giZDiVNfyhfHitQfTd9XYmpgNrk'],
                ];
                
                foreach ($relatedProducts as $product): 
                ?>
                <article class="group bg-white dark:bg-[#1e2b24] rounded-2xl overflow-hidden border border-transparent hover:border-[#e9f2ec] dark:hover:border-gray-700 hover:shadow-xl transition-all duration-300 flex flex-col">
                    <a href="product-detail.php" class="relative w-full aspect-[4/5] overflow-hidden bg-gray-100 dark:bg-gray-800">
                        <img alt="<?php echo $product['name']; ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" src="<?php echo $product['image']; ?>"/>
                    </a>
                    <div class="p-5 flex flex-col flex-1">
                        <h3 class="text-lg font-bold text-text-main dark:text-white group-hover:text-primary transition-colors mb-2"><?php echo $product['name']; ?></h3>
                        <p class="text-sm text-text-secondary dark:text-gray-400 mb-4 line-clamp-2">Cây cảnh chất lượng cao</p>
                        <div class="mt-auto flex items-center justify-between">
                            <span class="text-xl font-extrabold text-primary"><?php echo $product['price']; ?></span>
                            <button class="size-10 rounded-full bg-primary text-white flex items-center justify-center shadow-lg shadow-primary/30 hover:bg-primary-dark active:scale-95 transition-all">
                                <span class="material-symbols-outlined text-[20px]">add_shopping_cart</span>
                            </button>
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
</script>

<?php include 'includes/footer.php'; ?>
