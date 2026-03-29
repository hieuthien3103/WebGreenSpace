<?php
require_once __DIR__ . '/../../config/config.php';
?>
<!DOCTYPE html>
<html class="light" lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($pageTitle) ? $pageTitle : 'GreenSpace'; ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Theme Config -->
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#2ecc70",
                        "primary-dark": "#25a25a",
                        "background-light": "#f9fbfa",
                        "background-dark": "#131f18",
                        "text-main": "#0f1a14",
                        "text-secondary": "#568f6e",
                    },
                    fontFamily: {
                        "display": ["Plus Jakarta Sans", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "1rem", 
                        "lg": "1.5rem", 
                        "xl": "2rem", 
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-text-main dark:text-white antialiased selection:bg-primary selection:text-white">
<div class="relative flex min-h-screen w-full flex-col overflow-x-hidden">
<?php
$flash = get_flash();
$currentUser = get_user();
$currentUserName = get_user_name();
$cartCount = cart_item_count();
?>

<!-- Header -->
<header class="sticky top-0 z-50 w-full border-b border-[#e9f2ec] dark:border-[#2a3b30] bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-md">
    <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <!-- Logo & Nav -->
        <div class="flex items-center gap-10">
            <a class="flex items-center gap-3" href="home.php">
                <div class="flex size-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <span class="material-symbols-outlined text-3xl">potted_plant</span>
                </div>
                <h1 class="text-xl font-bold tracking-tight text-text-main dark:text-white">GreenSpace</h1>
            </a>
            <nav class="hidden md:flex items-center gap-8">
                <a class="text-sm font-medium <?php echo (isset($currentPage) && $currentPage == 'home') ? 'text-text-main' : 'text-text-secondary'; ?> hover:text-primary dark:text-gray-200 dark:hover:text-primary transition-colors" href="home.php">Trang chủ</a>
                <a class="text-sm font-medium <?php echo (isset($currentPage) && $currentPage == 'products') ? 'text-text-main' : 'text-text-secondary'; ?> hover:text-primary dark:text-gray-400 dark:hover:text-primary transition-colors" href="products.php">Sản phẩm</a>
                <a class="text-sm font-medium <?php echo (isset($currentPage) && $currentPage == 'care') ? 'text-text-main' : 'text-text-secondary'; ?> hover:text-primary dark:text-gray-400 dark:hover:text-primary transition-colors" href="care.php">Chăm sóc cây</a>
                <a class="text-sm font-medium <?php echo (isset($currentPage) && $currentPage == 'contact') ? 'text-text-main' : 'text-text-secondary'; ?> hover:text-primary dark:text-gray-400 dark:hover:text-primary transition-colors" href="contact.php">Liên hệ</a>
            </nav>
        </div>
        <!-- Actions -->
        <div class="flex items-center gap-4">
            <!-- Search Bar (Desktop) -->
            <form action="products.php" method="GET" class="hidden lg:flex w-64 items-center rounded-full bg-[#e9f2ec] dark:bg-[#1f2e25] px-4 py-2">
                <span class="material-symbols-outlined text-text-secondary">search</span>
                <input id="headerSearchInput" name="search" class="ml-2 w-full bg-transparent border-none p-0 text-sm focus:ring-0 placeholder:text-text-secondary dark:text-white" placeholder="Tìm kiếm sản phẩm..." type="text" autocomplete="off"/>
            </form>
            <!-- Icons -->
            <div class="flex items-center gap-2">
                <button onclick="toggleMobileSearch()" class="flex size-10 items-center justify-center rounded-full hover:bg-[#e9f2ec] dark:hover:bg-[#1f2e25] transition-colors lg:hidden">
                    <span class="material-symbols-outlined text-text-main dark:text-white">search</span>
                </button>
                <?php if ($currentUser): ?>
                    <a href="<?= is_admin() ? 'admin/dashboard.php' : 'profile.php' ?>" class="flex size-10 items-center justify-center rounded-full hover:bg-[#e9f2ec] dark:hover:bg-[#1f2e25] transition-colors" title="<?= clean($currentUserName ?? 'Tài khoản') ?>">
                        <span class="material-symbols-outlined text-text-main dark:text-white">person</span>
                    </a>
                    <form action="logout.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                        <button type="submit" class="flex size-10 items-center justify-center rounded-full hover:bg-[#e9f2ec] dark:hover:bg-[#1f2e25] transition-colors" title="Đăng xuất">
                            <span class="material-symbols-outlined text-text-main dark:text-white">logout</span>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="flex size-10 items-center justify-center rounded-full hover:bg-[#e9f2ec] dark:hover:bg-[#1f2e25] transition-colors" title="Đăng nhập">
                        <span class="material-symbols-outlined text-text-main dark:text-white">person</span>
                    </a>
                <?php endif; ?>
                <a href="cart.php" class="relative flex size-10 items-center justify-center rounded-full hover:bg-[#e9f2ec] dark:hover:bg-[#1f2e25] transition-colors" title="Giỏ hàng">
                    <span class="material-symbols-outlined text-text-main dark:text-white">shopping_bag</span>
                    <?php if ($cartCount > 0): ?>
                        <span class="absolute right-1 top-1 flex min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-bold text-white"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</header>

<?php if ($flash): ?>
<?php $isSuccessFlash = ($flash['type'] ?? '') === 'success'; ?>
<div id="flashToastWrapper" class="fixed left-1/2 top-24 z-[70] w-full max-w-xl -translate-x-1/2 px-4 transition-all duration-300">
    <div
        id="flashToast"
        data-flash-type="<?= clean((string)($flash['type'] ?? '')) ?>"
        class="pointer-events-auto flex items-start gap-3 rounded-2xl border px-4 py-3 text-sm font-medium shadow-2xl transition-all duration-300 <?= $isSuccessFlash ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800' ?>"
    >
        <span class="material-symbols-outlined text-[20px]"><?= $isSuccessFlash ? 'check_circle' : 'error' ?></span>
        <p class="flex-1 pr-2"><?= clean($flash['message'] ?? '') ?></p>
        <button type="button" id="flashToastClose" class="inline-flex size-7 items-center justify-center rounded-full transition-colors hover:bg-black/5" aria-label="Close notification">
            <span class="material-symbols-outlined text-[18px]">close</span>
        </button>
    </div>
</div>
<?php endif; ?>

<!-- Mobile Search Modal -->
<div id="mobileSearchModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm">
    <div class="flex min-h-full items-start justify-center p-4 pt-20">
        <div class="w-full max-w-2xl bg-white dark:bg-[#1e2b24] rounded-2xl shadow-2xl overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-text-main dark:text-white">Tìm kiếm sản phẩm</h3>
                    <button onclick="toggleMobileSearch()" class="flex size-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <span class="material-symbols-outlined text-text-main dark:text-white">close</span>
                    </button>
                </div>
                <form action="products.php" method="GET" class="relative">
                    <input id="mobileSearchInput" name="search" type="text" placeholder="Nhập tên sản phẩm..." class="w-full pl-12 pr-4 py-4 rounded-xl border-2 border-[#e9f2ec] dark:border-gray-700 bg-white dark:bg-[#131f18] text-text-main dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 text-lg" autocomplete="off" />
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-secondary text-2xl">search</span>
                </form>
                <div class="mt-6">
                    <p class="text-sm text-text-secondary dark:text-gray-400 mb-3">Gợi ý tìm kiếm:</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="products.php?search=cay+canh" class="px-4 py-2 rounded-full bg-[#e9f2ec] dark:bg-[#1f2e25] text-sm text-text-main dark:text-white hover:bg-primary hover:text-white transition-colors">Cây cảnh</a>
                        <a href="products.php?search=sen+da" class="px-4 py-2 rounded-full bg-[#e9f2ec] dark:bg-[#1f2e25] text-sm text-text-main dark:text-white hover:bg-primary hover:text-white transition-colors">Sen đá</a>
                        <a href="products.php?search=trau+ba" class="px-4 py-2 rounded-full bg-[#e9f2ec] dark:bg-[#1f2e25] text-sm text-text-main dark:text-white hover:bg-primary hover:text-white transition-colors">Trầu bà</a>
                        <a href="products.php?search=xương+rồng" class="px-4 py-2 rounded-full bg-[#e9f2ec] dark:bg-[#1f2e25] text-sm text-text-main dark:text-white hover:bg-primary hover:text-white transition-colors">Xương rồng</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle mobile search modal
function toggleMobileSearch() {
    const modal = document.getElementById('mobileSearchModal');
    if (modal.classList.contains('hidden')) {
        modal.classList.remove('hidden');
        document.getElementById('mobileSearchInput').focus();
        document.body.style.overflow = 'hidden';
    } else {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

// Close modal when clicking outside
document.getElementById('mobileSearchModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        toggleMobileSearch();
    }
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('mobileSearchModal');
        if (!modal.classList.contains('hidden')) {
            toggleMobileSearch();
        }
    }
});

const flashToastWrapper = document.getElementById('flashToastWrapper');
const flashToast = document.getElementById('flashToast');
const flashToastClose = document.getElementById('flashToastClose');

function hideFlashToast() {
    if (!flashToastWrapper || !flashToast) {
        return;
    }

    flashToast.classList.add('opacity-0', '-translate-y-3');
    flashToastWrapper.classList.add('pointer-events-none');

    window.setTimeout(() => {
        flashToastWrapper.remove();
    }, 300);
}

if (flashToastWrapper && flashToast) {
    flashToast.classList.add('opacity-0', '-translate-y-3');

    window.requestAnimationFrame(() => {
        flashToast.classList.remove('opacity-0', '-translate-y-3');
    });

    flashToastClose?.addEventListener('click', hideFlashToast);

    if (flashToast.dataset.flashType === 'success') {
        window.setTimeout(hideFlashToast, 3200);
    }
}
</script>
