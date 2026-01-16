<?php 
$pageTitle = 'Chăm sóc cây - GreenSpace';
$currentPage = 'care';
include 'includes/header.php'; 
?>

<!-- Hero Section -->
<section class="relative h-[500px] flex items-center justify-center bg-cover bg-center" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?w=1920&q=80');">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl relative z-10 text-center">
        <h1 class="text-4xl md:text-5xl font-black text-white mb-4 drop-shadow-md">Bí kíp cho khu vườn nhỏ của bạn</h1>
        <p class="text-lg md:text-xl mb-8 text-white max-w-2xl mx-auto drop-shadow-sm">
            Khám phá các hướng dẫn chăm sóc cây cảnh từ cơ bản đến nâng cao để không gian xanh luôn tươi tốt.
        </p>
        
        <!-- Search Bar -->
        <div class="max-w-3xl mx-auto bg-white p-2 rounded-full flex shadow-lg mb-8">
            <div class="flex-grow relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">search</span>
                <input class="w-full bg-transparent border-none text-gray-800 placeholder-gray-500 pl-12 pr-4 py-3 focus:ring-0 text-lg outline-none" placeholder="Tìm kiếm mẹo chăm sóc, loại cây..." type="text"/>
            </div>
            <button class="bg-primary hover:bg-primary-dark text-white px-8 rounded-full font-medium transition-colors">
                Tìm kiếm
            </button>
        </div>
        
        <!-- Filter Chips -->
        <div class="flex flex-wrap justify-center gap-3">
            <span class="text-sm font-medium pt-2 mr-2 text-white">Lọc theo:</span>
            <button class="bg-white text-primary border border-white px-5 py-2 rounded-full text-sm font-medium cursor-pointer shadow-sm">Tất cả</button>
            <button class="bg-white/90 text-gray-600 hover:bg-primary hover:text-white border border-transparent px-5 py-2 rounded-full text-sm font-medium cursor-pointer shadow-sm transition-all">Ánh sáng</button>
            <button class="bg-white/90 text-gray-600 hover:bg-primary hover:text-white border border-transparent px-5 py-2 rounded-full text-sm font-medium cursor-pointer shadow-sm transition-all">Tưới nước</button>
            <button class="bg-white/90 text-gray-600 hover:bg-primary hover:text-white border border-transparent px-5 py-2 rounded-full text-sm font-medium cursor-pointer shadow-sm transition-all">Phân bón</button>
            <button class="bg-white/90 text-gray-600 hover:bg-primary hover:text-white border border-transparent px-5 py-2 rounded-full text-sm font-medium cursor-pointer shadow-sm transition-all">Sâu bệnh</button>
        </div>
    </div>
</section>

<!-- Main Content -->
<main class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Left Column: Articles -->
        <div class="w-full lg:w-[68%]">
            <!-- Section Header -->
            <div class="flex items-center justify-between mb-8 border-b pb-4">
                <div class="flex items-center">
                    <div class="w-1.5 h-8 bg-primary rounded-full mr-3"></div>
                    <h2 class="font-bold text-gray-800 text-xl">Hướng dẫn phổ biến</h2>
                </div>
                <a class="text-primary font-medium hover:underline flex items-center" href="#">
                    Xem tất cả <span class="material-symbols-outlined ml-2 text-sm">arrow_forward</span>
                </a>
            </div>
            
            <!-- Article Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
                <!-- Card 1 -->
                <article class="bg-white rounded-2xl overflow-hidden flex flex-col h-full border border-[#e9f2ec] dark:border-gray-800 hover:shadow-xl transition-all duration-300">
                    <div class="relative h-56 overflow-hidden">
                        <img alt="Monstera plant" class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1614594975525-e45190c55d0b?w=800&q=80"/>
                        <div class="absolute top-4 left-4">
                            <span class="bg-white/90 backdrop-blur-sm text-xs font-semibold px-3 py-1 rounded-full text-gray-700 shadow-sm">Trong nhà</span>
                        </div>
                    </div>
                    <div class="p-6 flex-grow flex flex-col">
                        <h3 class="text-xl font-bold mb-2 text-text-main dark:text-white hover:text-primary cursor-pointer transition-colors">Cách chăm sóc Cây Trầu Bà Nam Mỹ (Monstera) đúng cách</h3>
                        <p class="text-text-secondary dark:text-gray-400 text-sm mb-4">
                            Monstera là loại cây dễ trồng nhưng cần chú ý đến lượng nước và ánh sáng để lá xẻ đẹp.
                        </p>
                        <div class="mt-auto flex items-center gap-4 text-sm text-text-secondary pt-4 border-t border-gray-100">
                            <div class="flex items-center"><span class="material-symbols-outlined text-blue-400 mr-1 text-lg">water_drop</span> Vừa phải</div>
                            <div class="flex items-center"><span class="material-symbols-outlined text-yellow-500 mr-1 text-lg">light_mode</span> Tán xạ</div>
                        </div>
                    </div>
                </article>
                
                <!-- Card 2 -->
                <article class="bg-white rounded-2xl overflow-hidden flex flex-col h-full border border-[#e9f2ec] dark:border-gray-800 hover:shadow-xl transition-all duration-300">
                    <div class="relative h-56 overflow-hidden">
                        <img alt="Snake plant" class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1593482892290-f54927ae1bb6?w=800&q=80"/>
                        <div class="absolute top-4 left-4 flex gap-2">
                            <span class="bg-white/90 backdrop-blur-sm text-xs font-semibold px-3 py-1 rounded-full text-gray-700 shadow-sm">Văn phòng</span>
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full shadow-sm">Dễ trồng</span>
                        </div>
                    </div>
                    <div class="p-6 flex-grow flex flex-col">
                        <h3 class="text-xl font-bold mb-2 text-text-main dark:text-white hover:text-primary cursor-pointer transition-colors">Cây Lưỡi Hổ: Lọc không khí và ý nghĩa phong thủy</h3>
                        <p class="text-text-secondary dark:text-gray-400 text-sm mb-4">
                            Loại cây "bất tử" dành cho người bận rộn, giúp thanh lọc không khí cực tốt vào ban đêm.
                        </p>
                        <div class="mt-auto flex items-center gap-4 text-sm text-text-secondary pt-4 border-t border-gray-100">
                            <div class="flex items-center"><span class="material-symbols-outlined text-blue-400 mr-1 text-lg">water_drop</span> Ít nước</div>
                            <div class="flex items-center"><span class="material-symbols-outlined text-yellow-500 mr-1 text-lg">light_mode</span> Mọi đk</div>
                        </div>
                    </div>
                </article>
                
                <!-- Card 3 -->
                <article class="bg-white rounded-2xl overflow-hidden flex flex-col h-full border border-[#e9f2ec] dark:border-gray-800 hover:shadow-xl transition-all duration-300">
                    <div class="relative h-56 overflow-hidden">
                        <img alt="Watering plants" class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800&q=80"/>
                        <div class="absolute top-4 left-4">
                            <span class="bg-white/90 backdrop-blur-sm text-xs font-semibold px-3 py-1 rounded-full text-gray-700 shadow-sm">Kỹ thuật</span>
                        </div>
                    </div>
                    <div class="p-6 flex-grow flex flex-col">
                        <h3 class="text-xl font-bold mb-2 text-text-main dark:text-white hover:text-primary cursor-pointer transition-colors">Khi nào thì nên tưới cây? Dấu hiệu nhận biết</h3>
                        <p class="text-text-secondary dark:text-gray-400 text-sm mb-4">
                            Đừng tưới theo lịch cố định! Hãy quan sát đất và lá cây để biết chính xác khi nào chúng khát nước.
                        </p>
                        <div class="mt-auto flex items-center gap-4 text-sm text-text-secondary pt-4 border-t border-gray-100">
                            <div class="flex items-center"><span class="material-symbols-outlined text-primary mr-1 text-lg">menu_book</span> Cơ bản</div>
                        </div>
                    </div>
                </article>
                
                <!-- Card 4 -->
                <article class="bg-white rounded-2xl overflow-hidden flex flex-col h-full border border-[#e9f2ec] dark:border-gray-800 hover:shadow-xl transition-all duration-300">
                    <div class="relative h-56 overflow-hidden">
                        <img alt="Fertilizer" class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1530836369250-ef72a3f5cda8?w=800&q=80"/>
                        <div class="absolute top-4 left-4">
                            <span class="bg-white/90 backdrop-blur-sm text-xs font-semibold px-3 py-1 rounded-full text-gray-700 shadow-sm">Phân bón</span>
                        </div>
                    </div>
                    <div class="p-6 flex-grow flex flex-col">
                        <h3 class="text-xl font-bold mb-2 text-text-main dark:text-white hover:text-primary cursor-pointer transition-colors">Các loại phân bón hữu cơ tự làm tại nhà</h3>
                        <p class="text-text-secondary dark:text-gray-400 text-sm mb-4">
                            Tận dụng rác thải nhà bếp để tạo ra nguồn dinh dưỡng tuyệt vời và an toàn cho vườn cây của bạn.
                        </p>
                        <div class="mt-auto flex items-center gap-4 text-sm text-text-secondary pt-4 border-t border-gray-100">
                            <div class="flex items-center"><span class="material-symbols-outlined text-green-600 mr-1 text-lg">recycling</span> Tái chế</div>
                        </div>
                    </div>
                </article>
            </div>
            
            <!-- Promo Banner -->
            <section class="rounded-2xl overflow-hidden shadow-lg relative h-auto md:h-64 flex flex-col md:flex-row" style="background: linear-gradient(to right, #3E2723, #5D4037);">
                <div class="p-8 md:w-3/5 flex flex-col justify-center z-10">
                    <span class="text-primary font-bold text-sm tracking-wider uppercase mb-2">Mẹo hay mỗi ngày</span>
                    <h3 class="text-2xl md:text-3xl font-bold mb-4 leading-tight text-white">Nước vo gạo - "Thần dược" miễn phí cho cây cảnh</h3>
                    <p class="text-gray-300 mb-6 text-sm md:text-base">
                        Giàu vitamin B và khoáng chất, nước vo gạo giúp cây phát triển rễ mạnh và lá xanh mướt.
                    </p>
                    <div>
                        <button class="bg-primary hover:bg-primary-dark text-white px-6 py-2.5 rounded-full font-medium transition-colors inline-flex items-center">
                            Xem chi tiết <span class="material-symbols-outlined ml-2 text-sm">arrow_forward</span>
                        </button>
                    </div>
                </div>
                <div class="md:w-2/5 relative h-48 md:h-auto overflow-hidden">
                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-48 h-48 bg-white/10 rounded-full blur-xl"></div>
                    <img alt="Rice water for plants" class="absolute inset-0 w-full h-full object-cover object-center" src="https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?w=800&q=80"/>
                </div>
            </section>
        </div>
        
        <!-- Right Sidebar -->
        <aside class="w-full lg:w-[32%]">
            <!-- Widget: Tools -->
            <div class="bg-white dark:bg-[#1e2b24] p-6 rounded-2xl shadow-sm border border-[#e9f2ec] dark:border-gray-800 mb-8">
                <h3 class="text-lg font-bold text-text-main dark:text-white mb-6 pb-2 border-b border-gray-100 dark:border-gray-800">Dụng cụ cần thiết</h3>
                <ul class="mb-6 space-y-4">
                    <li class="flex items-center group cursor-pointer">
                        <div class="bg-[#f5f5f5] dark:bg-gray-800 p-3 rounded-xl w-16 h-16 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary text-2xl">mist</span>
                        </div>
                        <div class="ml-4 flex-1">
                            <h4 class="font-medium text-text-main dark:text-white group-hover:text-primary transition-colors">Bình xịt phun sương</h4>
                            <p class="text-xs text-text-secondary">Giữ ẩm lá cây</p>
                        </div>
                        <span class="material-symbols-outlined text-gray-300 text-sm">chevron_right</span>
                    </li>
                    <li class="flex items-center group cursor-pointer">
                        <div class="bg-[#f5f5f5] dark:bg-gray-800 p-3 rounded-xl w-16 h-16 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary text-2xl">content_cut</span>
                        </div>
                        <div class="ml-4 flex-1">
                            <h4 class="font-medium text-text-main dark:text-white group-hover:text-primary transition-colors">Kéo tỉa cành</h4>
                            <p class="text-xs text-text-secondary">Thép không gỉ sắc bén</p>
                        </div>
                        <span class="material-symbols-outlined text-gray-300 text-sm">chevron_right</span>
                    </li>
                    <li class="flex items-center group cursor-pointer">
                        <div class="bg-[#f5f5f5] dark:bg-gray-800 p-3 rounded-xl w-16 h-16 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary text-2xl">water_drop</span>
                        </div>
                        <div class="ml-4 flex-1">
                            <h4 class="font-medium text-text-main dark:text-white group-hover:text-primary transition-colors">Dung dịch dinh dưỡng</h4>
                            <p class="text-xs text-text-secondary">Bổ sung vi lượng</p>
                        </div>
                        <span class="material-symbols-outlined text-gray-300 text-sm">chevron_right</span>
                    </li>
                </ul>
                <button class="w-full border border-primary text-primary hover:bg-primary hover:text-white font-medium py-2 rounded-full transition-colors text-sm">
                    Tham khảo dụng cụ
                </button>
            </div>
            
            <!-- Widget: Newsletter -->
            <div class="bg-[#f5f5f5] dark:bg-[#1e2b24] p-6 rounded-2xl mb-8 relative overflow-hidden">
                <div class="absolute -top-6 -right-6 w-24 h-24 bg-primary/10 rounded-full"></div>
                <h3 class="text-lg font-bold text-text-main dark:text-white mb-2 relative z-10">Nhận mẹo chăm sóc</h3>
                <p class="text-text-secondary dark:text-gray-400 text-sm mb-4 relative z-10">Đăng ký để nhận bài viết mới nhất và mã giảm giá hàng tuần.</p>
                <form class="flex flex-col gap-3 relative z-10">
                    <input class="w-full border-gray-200 dark:border-gray-700 dark:bg-gray-800 rounded-full px-4 py-2 text-sm focus:border-primary focus:ring-primary" placeholder="Email của bạn..." type="email"/>
                    <button class="bg-primary hover:bg-primary-dark text-white py-2 rounded-full font-medium transition-colors text-sm shadow-sm" type="button">
                        Đăng ký ngay
                    </button>
                </form>
            </div>
            
            <!-- Widget: Tags -->
            <div class="bg-white dark:bg-[#1e2b24] p-6 rounded-2xl shadow-sm border border-[#e9f2ec] dark:border-gray-800">
                <h3 class="text-lg font-bold text-text-main dark:text-white mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">Chủ đề hot</h3>
                <div class="flex flex-wrap gap-2">
                    <a class="px-3 py-1 hover:bg-primary hover:text-white text-text-secondary text-xs rounded-full border border-gray-200 dark:border-gray-700 transition-colors" href="#">#Sen đá</a>
                    <a class="px-3 py-1 hover:bg-primary hover:text-white text-text-secondary text-xs rounded-full border border-gray-200 dark:border-gray-700 transition-colors" href="#">#Cây văn phòng</a>
                    <a class="px-3 py-1 hover:bg-primary hover:text-white text-text-secondary text-xs rounded-full border border-gray-200 dark:border-gray-700 transition-colors" href="#">#Bệnh vàng lá</a>
                    <a class="px-3 py-1 hover:bg-primary hover:text-white text-text-secondary text-xs rounded-full border border-gray-200 dark:border-gray-700 transition-colors" href="#">#Decor</a>
                    <a class="px-3 py-1 hover:bg-primary hover:text-white text-text-secondary text-xs rounded-full border border-gray-200 dark:border-gray-700 transition-colors" href="#">#Tưới tự động</a>
                    <a class="px-3 py-1 hover:bg-primary hover:text-white text-text-secondary text-xs rounded-full border border-gray-200 dark:border-gray-700 transition-colors" href="#">#Đất trồng</a>
                </div>
            </div>
        </aside>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
