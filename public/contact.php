<?php 
$pageTitle = 'Liên hệ - GreenSpace';
$currentPage = 'contact';
include 'includes/header.php'; 
?>

<!-- Hero Section -->
<section class="relative w-full overflow-hidden min-h-[400px] flex items-center justify-center bg-cover bg-center" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.6)), url('https://lh3.googleusercontent.com/aida-public/AB6AXuAoPqCetkeGNqBqh1-Hw52TE6vkF0eFPai_h7cTZJixtspJ8DEQtDWgajTu2BQZLW-YOg5DEXrXwcjezrrLlvOKLtvbSdyZ6Ud47YV-TP8GN425glLryO-DypMz5U0LqdfBCWPK4pGK9rFO_aY2A1erPqR0DvkGYXWAxUgPQN3XUmHLEhnKM9p3faz1tdFDMd1WKrpzlcJSVE8I-IywXvrJxiMkS_ata3j8khZ-2DkD_sJTRNkggE17PTfGfkR8CflP3duugXa2oWA');">
    <div class="relative z-10 text-center px-4 max-w-3xl">
        <span class="inline-block py-1 px-3 rounded-full bg-white/20 backdrop-blur-sm text-white text-xs font-bold tracking-wider uppercase mb-4 border border-white/30">
            Hỗ trợ 24/7
        </span>
        <h1 class="text-4xl md:text-6xl font-black text-white leading-tight mb-6">
            Kết nối với GreenSpace
        </h1>
        <p class="text-lg md:text-xl text-gray-200 font-medium max-w-2xl mx-auto">
            Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn tìm được người bạn xanh ưng ý cho không gian làm việc.
        </p>
    </div>
</section>

<!-- Main Content -->
<main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 md:gap-12">
        <!-- Left Column: Contact Form -->
        <div class="lg:col-span-7 flex flex-col gap-8">
            <div class="bg-white dark:bg-[#1e2b24] p-6 md:p-8 rounded-2xl shadow-sm border border-[#e9f2ec] dark:border-gray-800">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-2 text-text-main dark:text-white">Gửi tin nhắn cho chúng mình</h2>
                    <p class="text-text-secondary dark:text-gray-400">Điền thông tin vào mẫu dưới đây, team GreenSpace sẽ phản hồi bạn sớm nhất có thể.</p>
                </div>
                
                <form class="flex flex-col gap-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <label class="flex flex-col gap-2">
                            <span class="text-sm font-bold text-text-main dark:text-white ml-1">Họ và tên</span>
                            <input class="w-full h-12 px-4 rounded-full bg-background-light dark:bg-background-dark border-transparent focus:border-primary focus:ring-primary text-sm transition-all placeholder:text-gray-400 dark:placeholder:text-gray-600" placeholder="Nhập tên của bạn" type="text"/>
                        </label>
                        <label class="flex flex-col gap-2">
                            <span class="text-sm font-bold text-text-main dark:text-white ml-1">Email</span>
                            <input class="w-full h-12 px-4 rounded-full bg-background-light dark:bg-background-dark border-transparent focus:border-primary focus:ring-primary text-sm transition-all placeholder:text-gray-400 dark:placeholder:text-gray-600" placeholder="email@example.com" type="email"/>
                        </label>
                    </div>
                    
                    <label class="flex flex-col gap-2">
                        <span class="text-sm font-bold text-text-main dark:text-white ml-1">Tiêu đề</span>
                        <input class="w-full h-12 px-4 rounded-full bg-background-light dark:bg-background-dark border-transparent focus:border-primary focus:ring-primary text-sm transition-all placeholder:text-gray-400 dark:placeholder:text-gray-600" placeholder="Vấn đề bạn quan tâm" type="text"/>
                    </label>
                    
                    <label class="flex flex-col gap-2">
                        <span class="text-sm font-bold text-text-main dark:text-white ml-1">Nội dung</span>
                        <textarea class="w-full p-4 rounded-xl bg-background-light dark:bg-background-dark border-transparent focus:border-primary focus:ring-primary text-sm transition-all resize-none placeholder:text-gray-400 dark:placeholder:text-gray-600" placeholder="Chia sẻ với chúng mình..." rows="5"></textarea>
                    </label>
                    
                    <button class="mt-2 h-12 w-full md:w-auto px-8 bg-primary hover:bg-primary-dark text-white font-bold rounded-full transition-all shadow-lg shadow-primary/30 flex items-center justify-center gap-2 self-start" type="button">
                        <span>Gửi tin nhắn</span>
                        <span class="material-symbols-outlined text-sm">send</span>
                    </button>
                </form>
            </div>
            
            <!-- FAQ Quick Link -->
            <div class="bg-primary/10 rounded-2xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4 border border-primary/20">
                <div class="flex items-center gap-4">
                    <div class="size-12 rounded-full bg-white flex items-center justify-center text-primary shrink-0 shadow-sm">
                        <span class="material-symbols-outlined">help</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-text-main dark:text-white">Câu hỏi thường gặp</h3>
                        <p class="text-sm text-text-secondary dark:text-gray-400">Tìm câu trả lời nhanh cho các vấn đề phổ biến.</p>
                    </div>
                </div>
                <button class="text-primary font-bold text-sm hover:underline whitespace-nowrap">Xem FAQ →</button>
            </div>
        </div>
        
        <!-- Right Column: Contact Info & Map -->
        <div class="lg:col-span-5 flex flex-col gap-8">
            <!-- Contact Cards -->
            <div class="bg-white dark:bg-[#1e2b24] rounded-2xl p-6 md:p-8 shadow-sm border border-[#e9f2ec] dark:border-gray-800 flex flex-col gap-6">
                <h2 class="text-xl font-bold mb-2 text-text-main dark:text-white">Thông tin liên hệ</h2>
                
                <div class="flex items-start gap-4">
                    <div class="size-10 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-xl">call</span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-text-secondary uppercase tracking-wide mb-1">Hotline</p>
                        <p class="font-semibold text-lg hover:text-primary transition-colors cursor-pointer text-text-main dark:text-white">1900 123 456</p>
                        <p class="text-sm text-text-secondary dark:text-gray-400">8:00 - 21:00 (Thứ 2 - CN)</p>
                    </div>
                </div>
                
                <div class="w-full h-px bg-gray-100 dark:bg-gray-800"></div>
                
                <div class="flex items-start gap-4">
                    <div class="size-10 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-xl">mail</span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-text-secondary uppercase tracking-wide mb-1">Email</p>
                        <p class="font-semibold text-lg hover:text-primary transition-colors cursor-pointer text-text-main dark:text-white">contact@greenspace.vn</p>
                        <p class="text-sm text-text-secondary dark:text-gray-400">Hỗ trợ đối tác &amp; báo giá</p>
                    </div>
                </div>
                
                <div class="w-full h-px bg-gray-100 dark:bg-gray-800"></div>
                
                <div class="flex items-start gap-4">
                    <div class="size-10 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-xl">location_on</span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-text-secondary uppercase tracking-wide mb-1">Showroom</p>
                        <p class="font-semibold text-base leading-relaxed text-text-main dark:text-white">123 Đường Cây Xanh, Quận 1,<br/>TP. Hồ Chí Minh</p>
                    </div>
                </div>
                
                <!-- Social Links -->
                <div class="mt-4 flex gap-3">
                    <a class="size-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center hover:bg-primary hover:text-white transition-colors" href="#">
                        <span class="font-bold text-sm">FB</span>
                    </a>
                    <a class="size-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center hover:bg-primary hover:text-white transition-colors" href="#">
                        <span class="font-bold text-sm">IG</span>
                    </a>
                    <a class="size-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center hover:bg-primary hover:text-white transition-colors" href="#">
                        <span class="font-bold text-sm">TT</span>
                    </a>
                </div>
            </div>
            
            <!-- Map -->
            <div class="bg-white dark:bg-[#1e2b24] rounded-2xl overflow-hidden shadow-sm border border-[#e9f2ec] dark:border-gray-800 h-80 relative group">
                <iframe allowfullscreen="" class="group-hover:filter-none transition-all duration-500" height="100%" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.4942918451845!2d106.6991196148007!3d10.773403392323577!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f40a3b49e59%3A0xa1bd14e483a602db!2sBen%20Thanh%20Market!5e0!3m2!1sen!2s!4v1647851234567!5m2!1sen!2s" style="border:0; filter: grayscale(0.3) opacity(0.9);" width="100%"></iframe>
                
                <!-- Overlay Badge -->
                <div class="absolute bottom-4 left-4 bg-white dark:bg-[#1e2b24] px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
                    <span class="block size-2 rounded-full bg-green-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-text-main dark:text-white">Đang mở cửa</span>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
