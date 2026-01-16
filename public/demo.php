<?php 
$pageTitle = 'Trang chủ - GreenSpace';
$currentPage = 'home';
include 'includes/header.php'; 
?>

<!-- Main Content -->
<main class="flex-1">
    <!-- Hero Section -->
    <section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-gray-900">
            <!-- Background Image -->
            <div class="absolute inset-0 opacity-70" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuA06hJf5RevgvWw6UsjEXWMmYCqLfvMTborL86eM-XiiSbt5G0celC7nBZKNjS-_dFlUAGntLingCLKDb0WMu5YkKfQzcd6spJmZo-rOgEDRr_jbCtNR5kNUreBfPG51xin5nT89WUWbULNex4GLNEudDCIAgqKV6L0cT-Eig_WDloKAoHLXJro253W_BZAkBBSWkJkxU6xXCTvIPXmdf89BYOOs9ulB4Jyjmcb1sCvH-1A6j1Pm8vwKaBxfBPXBG7hRUtq5AbXoVc'); background-size: cover; background-position: center;">
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
                    <button class="inline-flex h-12 items-center justify-center rounded-full bg-primary px-8 text-base font-bold text-white transition-transform hover:scale-105 hover:bg-primary-dark">
                        Khám phá ngay
                    </button>
                    <button class="inline-flex h-12 items-center justify-center rounded-full bg-white/10 px-8 text-base font-bold text-white backdrop-blur-sm transition-colors hover:bg-white/20">
                        Xem Video
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-end justify-between">
            <h2 class="text-2xl font-bold tracking-tight text-text-main dark:text-white sm:text-3xl">Danh mục sản phẩm</h2>
            <a class="hidden text-sm font-semibold text-primary hover:underline sm:block" href="#">Xem tất cả</a>
        </div>
        <div class="grid grid-cols-2 gap-6 sm:grid-cols-3 md:grid-cols-5">
            <!-- Category Item 1 -->
            <a class="group flex flex-col items-center gap-4 text-center" href="#">
                <div class="aspect-square w-full overflow-hidden rounded-full border-2 border-transparent transition-all group-hover:border-primary">
                    <div class="h-full w-full bg-cover bg-center transition-transform duration-500 group-hover:scale-110" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDoLS4I7Qjt0OFF6yWx4oOLr0h9FyioM-6MRwDDKkD6oGI-EvUcSc34GHGXzpFCTbThoGYX8J82CgkiFPMHGLVyoODGGPcSOAEyG9V_T8wkaPZeJ4tOaVXWTwwkxUV8qke7Sxw3LU64C2B8TS0p7M3NuKMTJsxhFyP-Jcvn-H5soZHjYbUqeNR7N31NP5Ao2pRH4OvBMysVoCIlgUWGoNCA-0s9qxvV9B40Tm0hwfiUnmbs7aDdnflTxCZIE6JvXOQF2E5Gb_UdLUo');">
                    </div>
                </div>
                <div>
                    <h3 class="text-base font-bold text-text-main dark:text-white">Cây để bàn</h3>
                    <p class="text-xs text-text-secondary">Cho văn phòng</p>
                </div>
            </a>
            <!-- Category Item 2 -->
            <a class="group flex flex-col items-center gap-4 text-center" href="#">
                <div class="aspect-square w-full overflow-hidden rounded-full border-2 border-transparent transition-all group-hover:border-primary">
                    <div class="h-full w-full bg-cover bg-center transition-transform duration-500 group-hover:scale-110" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDR_ZHw5AphcJnL5p-1DsSIvtFaDBMtVT0t9cs0QKq58t2UFvKBvJDi35TUPKiyUyLuMOQQv1bLQSmYv2PTBCZo6zc87Ch5dnPkJRIu7o2R1p5zyu9uE6iixdw2MXJtGorhoLSyk3JAw_Cxxifale6IdpkB-pPBUUhgM5TSGaMsNMffMg2gEgNGjOAMoHUgMhIlEzHOY7saJ6iPKUvGl7io3SNl8S44WZR8PWG1FUwU2Zk1BZ0hzhszpihlcZUE4796OOJRKJLan48');">
                    </div>
                </div>
                <div>
                    <h3 class="text-base font-bold text-text-main dark:text-white">Cây phong thủy</h3>
                    <p class="text-xs text-text-secondary">Tài lộc, may mắn</p>
                </div>
            </a>
            <!-- Category Item 3 -->
            <a class="group flex flex-col items-center gap-4 text-center" href="#">
                <div class="aspect-square w-full overflow-hidden rounded-full border-2 border-transparent transition-all group-hover:border-primary">
                    <div class="h-full w-full bg-cover bg-center transition-transform duration-500 group-hover:scale-110" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAjos_ES0bQD4onZCAlvlZFRjT8XibAbt_5Pw6oJT41sdOKmOzt76MuynZm4EU1o_JuxZRNYUmxDdXKDcSrsGIMQgA9ByOwrxC9PiZt72bHKLtmsqpvhAqXN2h-lUdX3mSAuSt-W7opmNy_Ka8suTC8qA1NtzqMpAwLMjvsMUTlBqdZ7z6HF-iPTRhsmH1yCIu74BMMDkxf6dXn6N08v2IJ5S885ps6aKHIXyzGW8lyxlLsgIgryr0f1uPmhLaZHQ1o06uXWQqzpLQ');">
                    </div>
                </div>
                <div>
                    <h3 class="text-base font-bold text-text-main dark:text-white">Sen đá</h3>
                    <p class="text-xs text-text-secondary">Dễ chăm sóc</p>
                </div>
            </a>
            <!-- Category Item 4 -->
            <a class="group flex flex-col items-center gap-4 text-center" href="#">
                <div class="aspect-square w-full overflow-hidden rounded-full border-2 border-transparent transition-all group-hover:border-primary">
                    <div class="h-full w-full bg-cover bg-center transition-transform duration-500 group-hover:scale-110" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuA5wxg823C88zz8Zc5RvxnLGirLTBaqvm2_KqyQFUq0MUbzCwhRdTCb-giToBc9D9MAhC9gicBSDCve0MVq6_yXVyOYC6rxEMqKAyUWX51PIcNoeiJCjib-t8jHBFmba09gOnBpysa44TC2F2wJktmduZcV1f4mQXyRBuD6_Amu95D4E8NqHbujKZQjNoLqSV55SVITzDetLGzQSeNkIDLj23iu-Xqg1MQa5pmPefG5cXXcmro3SEMyu7yK1AlvV021d4wHog8NOpw');">
                    </div>
                </div>
                <div>
                    <h3 class="text-base font-bold text-text-main dark:text-white">Bonsai</h3>
                    <p class="text-xs text-text-secondary">Nghệ thuật</p>
                </div>
            </a>
            <!-- Category Item 5 -->
            <a class="group flex flex-col items-center gap-4 text-center" href="#">
                <div class="aspect-square w-full overflow-hidden rounded-full border-2 border-transparent transition-all group-hover:border-primary">
                    <div class="h-full w-full bg-cover bg-center transition-transform duration-500 group-hover:scale-110" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCoYKI8O2sA4m-RgalsJD8BJWeBcKwN5-yrC0CPIC-r8EDAoBCsyvLiJxt3G1pOAmAuSCFY2SVu5fcMeHtdMNja5P8Kfxg7_Pov5VsxQ3wjA1QlfBz94KQGPB6QbDcTAS1JR9sI8XgjAlaCxkcGrOIpRmWldHuRYBXeVx82h9R8-H-xUvGnCa5au8f970tK0T8grsYTrCS4DlKNSRgIQOFZ43vtk8u4kY-3Fy9M56uDiWpZ5VGdLLYyyLY0piZNzRG4zsWLBW4as18');">
                    </div>
                </div>
                <div>
                    <h3 class="text-base font-bold text-text-main dark:text-white">Cây trong nhà</h3>
                    <p class="text-xs text-text-secondary">Lọc không khí</p>
                </div>
            </a>
        </div>
    </section>

    <!-- Best Sellers Section -->
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 bg-white dark:bg-[#1a2c22] rounded-3xl mb-12">
        <div class="mb-8 flex items-center justify-between">
            <h2 class="text-2xl font-bold tracking-tight text-text-main dark:text-white sm:text-3xl">Sản phẩm bán chạy</h2>
            <div class="flex gap-2">
                <button class="flex size-10 items-center justify-center rounded-full border border-[#e9f2ec] bg-white text-text-main hover:bg-[#e9f2ec] dark:border-[#2a3b30] dark:bg-transparent dark:text-white dark:hover:bg-[#2a3b30]">
                    <span class="material-symbols-outlined">arrow_back</span>
                </button>
                <button class="flex size-10 items-center justify-center rounded-full border border-[#e9f2ec] bg-white text-text-main hover:bg-[#e9f2ec] dark:border-[#2a3b30] dark:bg-transparent dark:text-white dark:hover:bg-[#2a3b30]">
                    <span class="material-symbols-outlined">arrow_forward</span>
                </button>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Product Card 1 -->
            <div class="group relative flex flex-col rounded-2xl bg-background-light p-4 transition-all hover:shadow-lg dark:bg-background-dark">
                <div class="relative mb-4 aspect-[4/5] w-full overflow-hidden rounded-xl bg-gray-100">
                    <div class="h-full w-full bg-cover bg-center transition-transform duration-500 group-hover:scale-110" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBN6iROV5TwbUHJ_cWqNOWYvw8YEocZo4DJRuiWhEUI6LZjl30u9Y7nxT0GK87w9ijlKVcSh2G9eS3yeu424Gbu9KzSd3-5c0UyfWKJ4OWLVbLiXIhTPcS9toptaCkXUBisuo8d2OyUkmG8v2-gXcIOWCHOJ4TzknhRclcV2enxghfhvGZRJHUnt766jt8QNvRvYOlS-gZb_3d8RnFw_E-u1-65yqKXSaDosCrdcmCocfbhpVuKCHUzzvn41Qwa2xqBjBXm9KyU7Zw');">
                    </div>
                    <button class="absolute right-3 top-3 flex size-8 items-center justify-center rounded-full bg-white/80 text-text-main opacity-0 backdrop-blur-sm transition-opacity hover:bg-white group-hover:opacity-100">
                        <span class="material-symbols-outlined text-sm">favorite</span>
                    </button>
                    <span class="absolute left-3 top-3 rounded-lg bg-red-500 px-2 py-1 text-xs font-bold text-white">-15%</span>
                </div>
                <div class="flex flex-1 flex-col">
                    <h3 class="text-lg font-bold text-text-main dark:text-white">Monstera Deliciosa</h3>
                    <p class="mb-3 text-sm text-text-secondary">Cây Trầu Bà Nam Mỹ</p>
                    <div class="mt-auto flex items-center justify-between">
                        <span class="text-lg font-bold text-primary">250.000đ</span>
                        <button class="flex size-9 items-center justify-center rounded-full bg-primary text-white transition-colors hover:bg-primary-dark">
                            <span class="material-symbols-outlined text-sm">add_shopping_cart</span>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Product Card 2 -->
            <div class="group relative flex flex-col rounded-2xl bg-background-light p-4 transition-all hover:shadow-lg dark:bg-background-dark">
                <div class="relative mb-4 aspect-[4/5] w-full overflow-hidden rounded-xl bg-gray-100">
                    <div class="h-full w-full bg-cover bg-center transition-transform duration-500 group-hover:scale-110" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCU25aMqF2vEFHYh25IY-1rVNGh5rChJcjH2zq-Qm3skTgCXl1laefI7hkWnITBDp-5TlkcBCaRfJBalPwqiE3aHmZ8m49VbYyGxYVAzoOMBC0evNZ_FMPhNEQ5Hq0Izbbbnu4Bk1cMh_1kdkYELf_gwhVTPFCaXWY1PercjqL-KPAd5e-kfHrxNCzttD1tqSPpU2y_U0WqzyWQ3PMlSdBZ0itImSlHUZU0k_QkTJ4_wBgefWI8dBAOREXZINlweDxjdqoe560y1SU');">
                    </div>
                    <button class="absolute right-3 top-3 flex size-8 items-center justify-center rounded-full bg-white/80 text-text-main opacity-0 backdrop-blur-sm transition-opacity hover:bg-white group-hover:opacity-100">
                        <span class="material-symbols-outlined text-sm">favorite</span>
                    </button>
                </div>
                <div class="flex flex-1 flex-col">
                    <h3 class="text-lg font-bold text-text-main dark:text-white">Cây Lưỡi Hổ</h3>
                    <p class="mb-3 text-sm text-text-secondary">Lọc không khí tốt</p>
                    <div class="mt-auto flex items-center justify-between">
                        <span class="text-lg font-bold text-primary">120.000đ</span>
                        <button class="flex size-9 items-center justify-center rounded-full bg-primary text-white transition-colors hover:bg-primary-dark">
                            <span class="material-symbols-outlined text-sm">add_shopping_cart</span>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Product Card 3 -->
            <div class="group relative flex flex-col rounded-2xl bg-background-light p-4 transition-all hover:shadow-lg dark:bg-background-dark">
                <div class="relative mb-4 aspect-[4/5] w-full overflow-hidden rounded-xl bg-gray-100">
                    <div class="h-full w-full bg-cover bg-center transition-transform duration-500 group-hover:scale-110" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBEOseQ3gdVvqqHv1vIxJyTzpazPd19M_P25y0g_fDPTzU8CAByUidcH-yDxZJDPNYqTGTWPiNcdpIiHe3FiI7csZJr33shUSTRknuBpORBtIekecK22HQqJalYIZO122GpjaV2TAzIAet9KOAXzrn1qKMUqF-6KvwlwJ0Rh7AqcdrpqwxB9UAkF58NJ8iOTd44Q8m4iJ1P0lazdgjSRIqIlk_pu7I2bi-GcnjHcyNTRNjDoquEwi1Snzj7_UAAG3l7WHcaYfSE8aw');">
                    </div>
                    <button class="absolute right-3 top-3 flex size-8 items-center justify-center rounded-full bg-white/80 text-text-main opacity-0 backdrop-blur-sm transition-opacity hover:bg-white group-hover:opacity-100">
                        <span class="material-symbols-outlined text-sm">favorite</span>
                    </button>
                </div>
                <div class="flex flex-1 flex-col">
                    <h3 class="text-lg font-bold text-text-main dark:text-white">Cây Đa Búp Đỏ</h3>
                    <p class="mb-3 text-sm text-text-secondary">Tạo điểm nhấn</p>
                    <div class="mt-auto flex items-center justify-between">
                        <span class="text-lg font-bold text-primary">350.000đ</span>
                        <button class="flex size-9 items-center justify-center rounded-full bg-primary text-white transition-colors hover:bg-primary-dark">
                            <span class="material-symbols-outlined text-sm">add_shopping_cart</span>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Product Card 4 -->
            <div class="group relative flex flex-col rounded-2xl bg-background-light p-4 transition-all hover:shadow-lg dark:bg-background-dark">
                <div class="relative mb-4 aspect-[4/5] w-full overflow-hidden rounded-xl bg-gray-100">
                    <div class="h-full w-full bg-cover bg-center transition-transform duration-500 group-hover:scale-110" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuA_NsLisnxmkEonLU_mQxkCiI85TBzv2r11I-mTePepgqnBNtjHzjG_sTCxG2hileyp_32aR0Gi5blv2KGHMeHzoeiuFvQKMsGlSz0zzld5Wy2Vr-yFzmuzKXX7hBMmApB20kHZJuhzlfdHvGDlWvPGfai6DIc5KrhsNrH9wMExwjMThxu5CZccOF9PYcpIHXNgN9Sssi9Vb_qmA534o22eHDe1SXyrQKBw7tM2BMC5NiaI7FubJBcxQU-T5ABm6_AFSov7E-eHdXw');">
                    </div>
                    <button class="absolute right-3 top-3 flex size-8 items-center justify-center rounded-full bg-white/80 text-text-main opacity-0 backdrop-blur-sm transition-opacity hover:bg-white group-hover:opacity-100">
                        <span class="material-symbols-outlined text-sm">favorite</span>
                    </button>
                </div>
                <div class="flex flex-1 flex-col">
                    <h3 class="text-lg font-bold text-text-main dark:text-white">Sen Đá Nâu</h3>
                    <p class="mb-3 text-sm text-text-secondary">Nhỏ gọn, đáng yêu</p>
                    <div class="mt-auto flex items-center justify-between">
                        <span class="text-lg font-bold text-primary">50.000đ</span>
                        <button class="flex size-9 items-center justify-center rounded-full bg-primary text-white transition-colors hover:bg-primary-dark">
                            <span class="material-symbols-outlined text-sm">add_shopping_cart</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Value Proposition -->
    <section class="mx-auto mb-20 max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 rounded-3xl bg-[#e9f2ec] p-8 dark:bg-[#1f2e25] md:grid-cols-3">
            <div class="flex items-start gap-4">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-primary text-white">
                    <span class="material-symbols-outlined">local_shipping</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-text-main dark:text-white">Giao hàng nhanh</h3>
                    <p class="text-sm text-text-secondary dark:text-gray-400">Vận chuyển an toàn, đảm bảo cây tươi tốt khi đến tay bạn.</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-primary text-white">
                    <span class="material-symbols-outlined">verified_user</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-text-main dark:text-white">Bảo hành 1 đổi 1</h3>
                    <p class="text-sm text-text-secondary dark:text-gray-400">Nếu cây không đúng mẫu hoặc hư hại trong quá trình vận chuyển.</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-primary text-white">
                    <span class="material-symbols-outlined">support_agent</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-text-main dark:text-white">Tư vấn trọn đời</h3>
                    <p class="text-sm text-text-secondary dark:text-gray-400">Hỗ trợ kỹ thuật chăm sóc cây miễn phí trọn đời cho mọi khách hàng.</p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
