<?php
$flash = get_flash();
$currentUser = get_user();
$currentUserName = get_user_name();
$cartCount = cart_item_count();
$searchQuery = trim((string)($_GET['search'] ?? ''));
?>
<!DOCTYPE html>
<html class="light" lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= clean($pageTitle ?? 'GreenSpace') ?></title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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

        .fly-to-cart-image {
            position: fixed;
            pointer-events: none;
            z-index: 80;
            border-radius: 0.75rem;
            object-fit: cover;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.22);
            will-change: transform, opacity;
        }

        .fly-to-cart-trail {
            position: fixed;
            pointer-events: none;
            z-index: 79;
            border-radius: 9999px;
            background: radial-gradient(circle at 35% 35%, rgba(46, 204, 112, 0.45), rgba(46, 204, 112, 0.12));
            filter: blur(0.4px);
            will-change: transform, opacity;
        }

        .cart-bump {
            animation: cart-bump 420ms ease;
        }

        @keyframes cart-bump {
            0% { transform: scale(1); }
            35% { transform: scale(1.16); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-text-main dark:text-white antialiased selection:bg-primary selection:text-white">
<div class="relative flex min-h-screen w-full flex-col overflow-x-hidden">
<header class="sticky top-0 z-50 w-full border-b border-[#e9f2ec] dark:border-[#2a3b30] bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-md">
    <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-10">
            <a class="flex items-center gap-3" href="<?= base_url() ?>">
                <div class="flex size-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <span class="material-symbols-outlined text-3xl">potted_plant</span>
                </div>
                <h1 class="text-xl font-bold tracking-tight text-text-main dark:text-white">GreenSpace</h1>
            </a>
            <nav class="hidden md:flex items-center gap-8">
                <a class="text-sm font-medium <?= ($currentPage ?? '') === 'home' ? 'text-text-main dark:text-white' : 'text-text-secondary dark:text-gray-300' ?> hover:text-primary transition-colors" href="<?= base_url() ?>">Trang chủ</a>
                <a class="text-sm font-medium <?= ($currentPage ?? '') === 'products' ? 'text-text-main dark:text-white' : 'text-text-secondary dark:text-gray-300' ?> hover:text-primary transition-colors" href="<?= base_url('products') ?>">Sản phẩm</a>
                <a class="text-sm font-medium <?= ($currentPage ?? '') === 'care' ? 'text-text-main dark:text-white' : 'text-text-secondary dark:text-gray-300' ?> hover:text-primary transition-colors" href="<?= base_url('care') ?>">Chăm sóc cây</a>
                <a class="text-sm font-medium <?= ($currentPage ?? '') === 'contact' ? 'text-text-main dark:text-white' : 'text-text-secondary dark:text-gray-300' ?> hover:text-primary transition-colors" href="<?= base_url('contact') ?>">Liên hệ</a>
            </nav>
        </div>

        <div class="flex items-center gap-4">
            <form action="<?= base_url('products') ?>" method="GET" class="hidden lg:flex w-64 items-center rounded-full bg-[#e9f2ec] dark:bg-[#1f2e25] px-4 py-2" data-products-search-form="true">
                <span class="material-symbols-outlined text-text-secondary">search</span>
                <input id="headerSearchInput" name="search" value="<?= clean($searchQuery) ?>" class="ml-2 w-full bg-transparent border-none p-0 text-sm focus:ring-0 placeholder:text-text-secondary dark:text-white" placeholder="Tìm kiếm sản phẩm..." type="text" autocomplete="off"/>
            </form>

            <div class="flex items-center gap-2">
                <button type="button" onclick="toggleMobileSearch()" class="flex size-10 items-center justify-center rounded-full hover:bg-[#e9f2ec] dark:hover:bg-[#1f2e25] transition-colors lg:hidden">
                    <span class="material-symbols-outlined text-text-main dark:text-white">search</span>
                </button>

                <?php if ($currentUser): ?>
                    <a href="<?= base_url(is_admin() ? 'admin/dashboard' : 'profile') ?>" class="flex size-10 items-center justify-center rounded-full hover:bg-[#e9f2ec] dark:hover:bg-[#1f2e25] transition-colors" title="<?= clean($currentUserName ?? 'Tài khoản') ?>">
                        <span class="material-symbols-outlined text-text-main dark:text-white">person</span>
                    </a>
                    <form action="<?= base_url('logout') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                        <button type="submit" class="flex size-10 items-center justify-center rounded-full hover:bg-[#e9f2ec] dark:hover:bg-[#1f2e25] transition-colors" title="Đăng xuất">
                            <span class="material-symbols-outlined text-text-main dark:text-white">logout</span>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="<?= base_url('login') ?>" class="flex size-10 items-center justify-center rounded-full hover:bg-[#e9f2ec] dark:hover:bg-[#1f2e25] transition-colors" title="Đăng nhập">
                        <span class="material-symbols-outlined text-text-main dark:text-white">person</span>
                    </a>
                <?php endif; ?>

                <a id="headerCartButton" href="<?= base_url('cart') ?>" class="relative flex size-10 items-center justify-center rounded-full hover:bg-[#e9f2ec] dark:hover:bg-[#1f2e25] transition-colors" title="Giỏ hàng">
                    <span class="material-symbols-outlined text-text-main dark:text-white">shopping_bag</span>
                    <span id="headerCartCount" class="absolute right-1 top-1 flex min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-bold text-white <?= $cartCount > 0 ? '' : 'hidden' ?>"><?= $cartCount ?></span>
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

<div id="mobileSearchModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm">
    <div class="flex min-h-full items-start justify-center p-4 pt-20">
        <div class="w-full max-w-2xl bg-white dark:bg-[#1e2b24] rounded-2xl shadow-2xl overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-text-main dark:text-white">Tìm kiếm sản phẩm</h3>
                    <button type="button" onclick="toggleMobileSearch()" class="flex size-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <span class="material-symbols-outlined text-text-main dark:text-white">close</span>
                    </button>
                </div>
                <form action="<?= base_url('products') ?>" method="GET" class="relative" data-products-search-form="true">
                    <input id="mobileSearchInput" name="search" value="<?= clean($searchQuery) ?>" type="text" placeholder="Nhập tên sản phẩm..." class="w-full pl-12 pr-4 py-4 rounded-xl border-2 border-[#e9f2ec] dark:border-gray-700 bg-white dark:bg-[#131f18] text-text-main dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 text-lg" autocomplete="off" />
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-text-secondary text-2xl">search</span>
                </form>
                <div class="mt-6">
                    <p class="text-sm text-text-secondary dark:text-gray-400 mb-3">Gợi ý tìm kiếm:</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="<?= base_url('products?search=cay+canh') ?>" data-products-ajax-link="true" class="px-4 py-2 rounded-full bg-[#e9f2ec] dark:bg-[#1f2e25] text-sm text-text-main dark:text-white hover:bg-primary hover:text-white transition-colors">Cây cảnh</a>
                        <a href="<?= base_url('products?search=sen+da') ?>" data-products-ajax-link="true" class="px-4 py-2 rounded-full bg-[#e9f2ec] dark:bg-[#1f2e25] text-sm text-text-main dark:text-white hover:bg-primary hover:text-white transition-colors">Sen đá</a>
                        <a href="<?= base_url('products?search=trau+ba') ?>" data-products-ajax-link="true" class="px-4 py-2 rounded-full bg-[#e9f2ec] dark:bg-[#1f2e25] text-sm text-text-main dark:text-white hover:bg-primary hover:text-white transition-colors">Trầu bà</a>
                        <a href="<?= base_url('products?search=xuong+rong') ?>" data-products-ajax-link="true" class="px-4 py-2 rounded-full bg-[#e9f2ec] dark:bg-[#1f2e25] text-sm text-text-main dark:text-white hover:bg-primary hover:text-white transition-colors">Xương rồng</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleMobileSearch() {
    const modal = document.getElementById('mobileSearchModal');
    const input = document.getElementById('mobileSearchInput');
    if (!modal) {
        return;
    }

    if (modal.classList.contains('hidden')) {
        modal.classList.remove('hidden');
        input?.focus();
        document.body.style.overflow = 'hidden';
        return;
    }

    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

document.getElementById('mobileSearchModal')?.addEventListener('click', function (event) {
    if (event.target === this) {
        toggleMobileSearch();
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') {
        return;
    }

    const modal = document.getElementById('mobileSearchModal');
    if (modal && !modal.classList.contains('hidden')) {
        toggleMobileSearch();
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

const CART_FLY_PRESETS = {
    snappy: { duration: 520, arcMin: 26, arcMax: 86, arcFactor: 0.16, trailLag: 0.11 },
    balanced: { duration: 720, arcMin: 36, arcMax: 120, arcFactor: 0.22, trailLag: 0.09 },
    cinematic: { duration: 980, arcMin: 52, arcMax: 156, arcFactor: 0.32, trailLag: 0.075 },
};

let activeCartFlyPreset = 'cinematic';

function getCartFlyPreset() {
    return CART_FLY_PRESETS[activeCartFlyPreset] || CART_FLY_PRESETS.balanced;
}

window.setCartFlySpeedPreset = function setCartFlySpeedPreset(presetName) {
    if (!Object.prototype.hasOwnProperty.call(CART_FLY_PRESETS, presetName)) {
        return false;
    }

    activeCartFlyPreset = presetName;
    return true;
};

window.getCartFlySpeedPreset = function getCartFlySpeedPreset() {
    return activeCartFlyPreset;
};

function updateHeaderCartCount(nextCount) {
    const cartCountBadge = document.getElementById('headerCartCount');
    if (!cartCountBadge) {
        return;
    }

    const count = Number.isFinite(nextCount) ? Math.max(0, Math.floor(nextCount)) : 0;
    cartCountBadge.textContent = String(count);
    cartCountBadge.classList.toggle('hidden', count <= 0);
}

function showCartActionToast(message, isSuccess = true) {
    const toast = document.createElement('div');
    toast.className = `fixed right-4 top-24 z-[72] max-w-sm rounded-xl border px-4 py-3 text-sm font-semibold shadow-xl transition-all duration-300 ${isSuccess ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-700'}`;
    toast.textContent = message;
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-8px)';
    document.body.appendChild(toast);

    window.requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });

    window.setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-8px)';
        window.setTimeout(() => toast.remove(), 280);
    }, 2200);
}

async function addToCartAsync(form) {
    const payload = new FormData(form);
    payload.set('ajax', '1');

    const response = await fetch(form.getAttribute('action') || 'cart', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        body: payload,
        credentials: 'same-origin',
    });

    let data;
    try {
        data = await response.json();
    } catch (_error) {
        showCartActionToast('Không thể thêm vào giỏ lúc này. Vui lòng thử lại.', false);
        return;
    }

    const isSuccess = !!data.success;
    updateHeaderCartCount(Number(data.cart_count || 0));
    showCartActionToast(data.message || (isSuccess ? 'Đã thêm sản phẩm vào giỏ.' : 'Không thể thêm vào giỏ.'), isSuccess);
}

function animateAddToCart(form) {
    const cartButton = document.getElementById('headerCartButton');
    if (!cartButton) {
        addToCartAsync(form).finally(() => {
            delete form.dataset.animating;
        });
        return;
    }

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        addToCartAsync(form).finally(() => {
            delete form.dataset.animating;
        });
        return;
    }

    const imageSrc = form.dataset.productImage || '';
    if (!imageSrc) {
        addToCartAsync(form).finally(() => {
            delete form.dataset.animating;
        });
        return;
    }

    const sourceImage = document.createElement('img');
    sourceImage.src = imageSrc;
    sourceImage.alt = '';
    sourceImage.className = 'fly-to-cart-image';

    const clickedButton = document.activeElement instanceof HTMLElement ? document.activeElement : null;
    const startRectSource = clickedButton && form.contains(clickedButton)
        ? clickedButton.getBoundingClientRect()
        : form.getBoundingClientRect();
    const startSize = Math.max(44, Math.min(76, Math.min(startRectSource.width, startRectSource.height) || 56));
    const startX = startRectSource.left + (startRectSource.width / 2) - (startSize / 2);
    const startY = startRectSource.top + (startRectSource.height / 2) - (startSize / 2);

    const cartRect = cartButton.getBoundingClientRect();
    const endSize = 20;
    const endX = cartRect.left + (cartRect.width / 2) - (endSize / 2);
    const endY = cartRect.top + (cartRect.height / 2) - (endSize / 2);

    sourceImage.style.left = `${startX}px`;
    sourceImage.style.top = `${startY}px`;
    sourceImage.style.width = `${startSize}px`;
    sourceImage.style.height = `${startSize}px`;
    sourceImage.style.opacity = '0.96';
    sourceImage.style.transform = 'translate3d(0, 0, 0) scale(1)';
    document.body.appendChild(sourceImage);

    const trailDots = Array.from({ length: 3 }, (_, index) => {
        const dot = document.createElement('span');
        dot.className = 'fly-to-cart-trail';
        const size = Math.max(8, 14 - index * 2);
        dot.style.width = `${size}px`;
        dot.style.height = `${size}px`;
        dot.style.left = `${startX + (startSize / 2) - (size / 2)}px`;
        dot.style.top = `${startY + (startSize / 2) - (size / 2)}px`;
        dot.style.opacity = '0.55';
        document.body.appendChild(dot);
        return dot;
    });

    const deltaX = endX - startX;
    const deltaY = endY - startY;
    const preset = getCartFlyPreset();
    const duration = preset.duration;
    const arcHeight = Math.max(preset.arcMin, Math.min(preset.arcMax, Math.abs(deltaX) * preset.arcFactor + preset.arcMin));
    const startTime = performance.now();
    const easeOutQuint = (value) => 1 - Math.pow(1 - value, 5);

    const runFrame = (now) => {
        const elapsed = now - startTime;
        const progress = Math.min(1, elapsed / duration);
        const eased = easeOutQuint(progress);

        const pathX = startX + deltaX * eased;
        const pathY = startY + deltaY * eased - (Math.sin(progress * Math.PI) * arcHeight);
        const scale = 1 - (0.76 * progress);
        const opacity = 0.95 - (0.82 * progress);

        sourceImage.style.opacity = `${Math.max(0.1, opacity)}`;
        sourceImage.style.transform = `translate3d(${pathX - startX}px, ${pathY - startY}px, 0) scale(${Math.max(0.22, scale)}) rotate(${12 * progress}deg)`;

        trailDots.forEach((dot, index) => {
            const lag = (index + 1) * preset.trailLag;
            const trailProgress = Math.max(0, progress - lag);
            const trailEased = easeOutQuint(trailProgress);
            const trailX = startX + deltaX * trailEased;
            const trailY = startY + deltaY * trailEased - (Math.sin(trailProgress * Math.PI) * arcHeight);

            dot.style.transform = `translate3d(${trailX - startX}px, ${trailY - startY}px, 0) scale(${Math.max(0.25, 1 - trailProgress)})`;
            dot.style.opacity = `${Math.max(0, 0.45 - trailProgress * 0.55)}`;
        });

        if (progress < 1) {
            window.requestAnimationFrame(runFrame);
            return;
        }

        sourceImage.remove();
        trailDots.forEach((dot) => dot.remove());
        cartButton.classList.add('cart-bump');
        window.setTimeout(() => cartButton.classList.remove('cart-bump'), 420);
        addToCartAsync(form).finally(() => {
            delete form.dataset.animating;
        });
    };

    window.requestAnimationFrame(runFrame);
}

document.addEventListener('submit', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLFormElement)) {
        return;
    }

    const actionInput = target.querySelector('input[name="action"]');
    const isAddAction = actionInput instanceof HTMLInputElement && actionInput.value === 'add';
    const actionUrl = target.getAttribute('action') || '';
    const isCartAction = actionUrl.endsWith('/cart') || actionUrl === 'cart';

    if (!isAddAction || !isCartAction || target.dataset.animating === '1') {
        return;
    }

    event.preventDefault();
    target.dataset.animating = '1';
    animateAddToCart(target);
});
</script>

<main class="flex-1">
