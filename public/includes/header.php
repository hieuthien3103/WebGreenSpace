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

<!-- Header -->
<header class="sticky top-0 z-50 w-full border-b border-[#e9f2ec] dark:border-[#2a3b30] bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-md">
    <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <!-- Logo & Nav -->
        <div class="flex items-center gap-10">
            <a class="flex items-center gap-3" href="demo.php">
                <div class="flex size-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <span class="material-symbols-outlined text-3xl">potted_plant</span>
                </div>
                <h1 class="text-xl font-bold tracking-tight text-text-main dark:text-white">GreenSpace</h1>
            </a>
            <nav class="hidden md:flex items-center gap-8">
                <a class="text-sm font-medium <?php echo (isset($currentPage) && $currentPage == 'home') ? 'text-text-main' : 'text-text-secondary'; ?> hover:text-primary dark:text-gray-200 dark:hover:text-primary transition-colors" href="demo.php">Trang chủ</a>
                <a class="text-sm font-medium <?php echo (isset($currentPage) && $currentPage == 'products') ? 'text-text-main' : 'text-text-secondary'; ?> hover:text-primary dark:text-gray-400 dark:hover:text-primary transition-colors" href="products.php">Sản phẩm</a>
                <a class="text-sm font-medium <?php echo (isset($currentPage) && $currentPage == 'care') ? 'text-text-main' : 'text-text-secondary'; ?> hover:text-primary dark:text-gray-400 dark:hover:text-primary transition-colors" href="care.php">Chăm sóc cây</a>
                <a class="text-sm font-medium <?php echo (isset($currentPage) && $currentPage == 'contact') ? 'text-text-main' : 'text-text-secondary'; ?> hover:text-primary dark:text-gray-400 dark:hover:text-primary transition-colors" href="contact.php">Liên hệ</a>
            </nav>
        </div>
        <!-- Actions -->
        <div class="flex items-center gap-4">
            <!-- Search Bar (Desktop) -->
            <div class="hidden lg:flex w-64 items-center rounded-full bg-[#e9f2ec] dark:bg-[#1f2e25] px-4 py-2">
                <span class="material-symbols-outlined text-text-secondary">search</span>
                <input class="ml-2 w-full bg-transparent border-none p-0 text-sm focus:ring-0 placeholder:text-text-secondary dark:text-white" placeholder="Tìm kiếm..." type="text"/>
            </div>
            <!-- Icons -->
            <div class="flex items-center gap-2">
                <button class="flex size-10 items-center justify-center rounded-full hover:bg-[#e9f2ec] dark:hover:bg-[#1f2e25] transition-colors lg:hidden">
                    <span class="material-symbols-outlined text-text-main dark:text-white">search</span>
                </button>
                <button class="flex size-10 items-center justify-center rounded-full hover:bg-[#e9f2ec] dark:hover:bg-[#1f2e25] transition-colors">
                    <span class="material-symbols-outlined text-text-main dark:text-white">person</span>
                </button>
                <button class="relative flex size-10 items-center justify-center rounded-full hover:bg-[#e9f2ec] dark:hover:bg-[#1f2e25] transition-colors">
                    <span class="material-symbols-outlined text-text-main dark:text-white">shopping_bag</span>
                    <span class="absolute right-1 top-1 flex size-4 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-white">2</span>
                </button>
            </div>
        </div>
    </div>
</header>
