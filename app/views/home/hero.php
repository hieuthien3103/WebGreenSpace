<!-- Hero Section -->
<section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="relative overflow-hidden rounded-3xl bg-gray-900">
        <!-- Background Image -->
        <div class="absolute inset-0 opacity-70" style="background-image: url('<?= image_url('banners/hero-1.jpg') ?>'); background-size: cover; background-position: center;">
        </div>
        
        <!-- Overlay Gradient -->
        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent"></div>
        
        <div class="relative z-10 flex min-h-[500px] flex-col justify-center px-8 py-12 sm:px-12 md:max-w-2xl">
            <h2 class="mb-4 text-4xl font-black leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">
                Mang thiên nhiên vào không gian sống
            </h2>
            <p class="mb-8 text-lg font-medium text-gray-200 sm:text-xl">
                Cây xanh giúp thư giãn, lọc không khí và tạo cảm hứng làm việc mỗi ngày.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="<?= base_url('products') ?>" class="inline-flex h-12 items-center justify-center rounded-full bg-primary px-8 text-base font-bold text-white transition-transform hover:scale-105 hover:bg-primary-dark">
                    Khám phá ngay
                </a>
                <button class="inline-flex h-12 items-center justify-center rounded-full bg-white/10 px-8 text-base font-bold text-white backdrop-blur-sm transition-colors hover:bg-white/20">
                    Xem Video
                </button>
            </div>
        </div>
    </div>
</section>
