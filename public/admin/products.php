<?php
require_once __DIR__ . '/bootstrap.php';
require_admin_permission('products.manage', 'products.php');

function admin_product_defaults(): array {
    return [
        'category_id' => '',
        'name' => '',
        'slug' => '',
        'description' => '',
        'price' => '',
        'sale_price' => '',
        'stock' => '0',
        'image' => '',
        'size' => '',
        'care_level' => 'medium',
        'light_requirement' => 'medium',
        'water_requirement' => 'medium',
        'featured' => '0',
        'status' => 'active',
    ];
}

function admin_collect_product_form_data(array $input): array {
    return [
        'category_id' => trim((string)($input['category_id'] ?? '')),
        'name' => trim((string)($input['name'] ?? '')),
        'slug' => trim((string)($input['slug'] ?? '')),
        'description' => trim((string)($input['description'] ?? '')),
        'price' => trim((string)($input['price'] ?? '')),
        'sale_price' => trim((string)($input['sale_price'] ?? '')),
        'stock' => trim((string)($input['stock'] ?? '0')),
        'image' => trim((string)($input['image'] ?? '')),
        'size' => trim((string)($input['size'] ?? '')),
        'care_level' => trim((string)($input['care_level'] ?? 'medium')),
        'light_requirement' => trim((string)($input['light_requirement'] ?? 'medium')),
        'water_requirement' => trim((string)($input['water_requirement'] ?? 'medium')),
        'featured' => !empty($input['featured']) ? '1' : '0',
        'status' => trim((string)($input['status'] ?? 'active')),
    ];
}

function admin_normalize_decimal(string $value): ?string {
    $value = trim(str_replace([' ', ','], ['', ''], $value));
    if ($value === '') {
        return null;
    }

    return is_numeric($value) ? (string)(float)$value : null;
}

function admin_generate_unique_product_slug(Product $productModel, string $baseSlug, ?int $excludeId = null): string {
    $baseSlug = trim($baseSlug, '-');
    if ($baseSlug === '') {
        $baseSlug = 'san-pham';
    }

    $slug = $baseSlug;
    $suffix = 2;

    while ($productModel->slugExists($slug, $excludeId)) {
        $slug = $baseSlug . '-' . $suffix;
        $suffix++;
    }

    return $slug;
}

function admin_products_query(array $params): string {
    $filtered = [];

    foreach ($params as $key => $value) {
        if ($value === null || $value === '') {
            continue;
        }

        $filtered[$key] = $value;
    }

    $query = http_build_query($filtered);
    return $query !== '' ? '?' . $query : '';
}

$productModel = new Product();
$categoryModel = new Category();

$categories = $categoryModel->getAll();
$categoryIds = array_map(static fn(array $category): int => (int)$category['id'], $categories);

$statusOptions = [
    'all' => 'Tất cả',
    'active' => 'Đang bán',
    'inactive' => 'Ngừng bán',
];

$careOptions = [
    'easy' => 'Dễ',
    'medium' => 'Trung bình',
    'hard' => 'Khó',
];

$requirementOptions = [
    'low' => 'Thấp',
    'medium' => 'Vừa',
    'high' => 'Cao',
];

$search = trim((string)($_GET['q'] ?? ''));
$statusFilter = (string)($_GET['status'] ?? 'all');
if (!isset($statusOptions[$statusFilter])) {
    $statusFilter = 'all';
}

$page = max(1, (int)($_GET['page'] ?? 1));
$editId = max(0, (int)($_GET['edit'] ?? 0));
$perPage = ADMIN_ITEMS_PER_PAGE;

$errors = [];
$formMode = 'create';
$formData = admin_product_defaults();
$editingProduct = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
        redirect('products.php');
    }

    $action = (string)($_POST['action'] ?? '');

    if ($action === 'delete') {
        $productId = max(0, (int)($_POST['product_id'] ?? 0));
        $product = $productModel->getAdminById($productId);

        if (!$product) {
            set_flash('error', 'Không tìm thấy sản phẩm cần xóa.');
        } else {
            $result = $productModel->deleteAdminProduct($productId);

            if (!empty($result['success'])) {
                if (($result['mode'] ?? '') === 'inactivated') {
                    set_flash('success', 'Sản phẩm đã có đơn hàng nên được chuyển sang trạng thái ngừng bán để giữ lịch sử.');
                } else {
                    set_flash('success', 'Đã xóa sản phẩm khỏi hệ thống.');
                }
            } else {
                set_flash('error', 'Không thể xóa sản phẩm lúc này.');
            }
        }

        $redirectQuery = admin_products_query([
            'q' => trim((string)($_POST['return_q'] ?? '')),
            'status' => trim((string)($_POST['return_status'] ?? 'all')),
            'page' => max(1, (int)($_POST['return_page'] ?? 1)),
        ]);
        redirect('products.php' . $redirectQuery);
    }

    $formData = admin_collect_product_form_data($_POST);
    $formMode = $action === 'update' ? 'edit' : 'create';
    $editId = max(0, (int)($_POST['product_id'] ?? 0));

    if ($action === 'update') {
        $editingProduct = $productModel->getAdminById($editId);
        if (!$editingProduct) {
            set_flash('error', 'Không tìm thấy sản phẩm cần cập nhật.');
            redirect('products.php');
        }
    }

    if (empty($categories)) {
        $errors['category_id'] = 'Hiện chưa có danh mục hoạt động để gán cho sản phẩm.';
    } elseif (!in_array((int)$formData['category_id'], $categoryIds, true)) {
        $errors['category_id'] = 'Vui lòng chọn danh mục hợp lệ.';
    }

    if ($formData['name'] === '') {
        $errors['name'] = 'Tên sản phẩm không được để trống.';
    }

    $price = admin_normalize_decimal($formData['price']);
    if ($price === null || (float)$price <= 0) {
        $errors['price'] = 'Giá bán phải lớn hơn 0.';
    }

    $salePrice = admin_normalize_decimal($formData['sale_price']);
    if ($formData['sale_price'] !== '' && $salePrice === null) {
        $errors['sale_price'] = 'Giá khuyến mãi không hợp lệ.';
    }

    if ($salePrice !== null && $price !== null && (float)$salePrice >= (float)$price) {
        $errors['sale_price'] = 'Giá khuyến mãi phải nhỏ hơn giá bán.';
    }

    if (!preg_match('/^\d+$/', $formData['stock'])) {
        $errors['stock'] = 'Tồn kho phải là số nguyên không âm.';
    }

    if (!isset($careOptions[$formData['care_level']])) {
        $errors['care_level'] = 'Mức chăm sóc không hợp lệ.';
    }

    if (!isset($requirementOptions[$formData['light_requirement']])) {
        $errors['light_requirement'] = 'Mức ánh sáng không hợp lệ.';
    }

    if (!isset($requirementOptions[$formData['water_requirement']])) {
        $errors['water_requirement'] = 'Mức nước không hợp lệ.';
    }

    if (!in_array($formData['status'], ['active', 'inactive'], true)) {
        $errors['status'] = 'Trạng thái không hợp lệ.';
    }

    $baseSlug = create_slug($formData['slug'] !== '' ? $formData['slug'] : $formData['name']);
    if ($baseSlug === '') {
        $errors['slug'] = 'Không thể tạo đường dẫn từ tên sản phẩm. Vui lòng nhập slug thủ công.';
    }

    if (empty($errors)) {
        $payload = [
            'category_id' => (int)$formData['category_id'],
            'name' => $formData['name'],
            'slug' => admin_generate_unique_product_slug($productModel, $baseSlug, $action === 'update' ? $editId : null),
            'description' => $formData['description'],
            'price' => $price,
            'sale_price' => $salePrice,
            'stock' => (int)$formData['stock'],
            'image' => $formData['image'],
            'size' => $formData['size'],
            'care_level' => $formData['care_level'],
            'light_requirement' => $formData['light_requirement'],
            'water_requirement' => $formData['water_requirement'],
            'featured' => $formData['featured'] === '1',
            'status' => $formData['status'],
        ];

        if ($action === 'update') {
            $productModel->updateAdminProduct($editId, $payload);
            set_flash('success', 'Đã cập nhật thông tin sản phẩm.');
            redirect('products.php' . admin_products_query([
                'edit' => $editId,
                'q' => $search,
                'status' => $statusFilter,
                'page' => $page,
            ]));
        }

        $newId = $productModel->createAdminProduct($payload);
        set_flash('success', 'Đã thêm sản phẩm mới vào hệ thống.');
        redirect('products.php?edit=' . $newId);
    }
}

if ($formMode !== 'edit' && $editId > 0) {
    $editingProduct = $productModel->getAdminById($editId);

    if ($editingProduct) {
        $formMode = 'edit';
        $formData = [
            'category_id' => (string)($editingProduct['category_id'] ?? ''),
            'name' => (string)($editingProduct['name'] ?? ''),
            'slug' => (string)($editingProduct['slug'] ?? ''),
            'description' => (string)($editingProduct['description'] ?? ''),
            'price' => isset($editingProduct['price']) ? (string)(float)$editingProduct['price'] : '',
            'sale_price' => isset($editingProduct['sale_price']) && $editingProduct['sale_price'] !== null ? (string)(float)$editingProduct['sale_price'] : '',
            'stock' => (string)($editingProduct['stock'] ?? '0'),
            'image' => (string)($editingProduct['image'] ?? ''),
            'size' => (string)($editingProduct['size'] ?? ''),
            'care_level' => (string)($editingProduct['care_level'] ?? 'medium'),
            'light_requirement' => (string)($editingProduct['light_requirement'] ?? 'medium'),
            'water_requirement' => (string)($editingProduct['water_requirement'] ?? 'medium'),
            'featured' => !empty($editingProduct['featured']) ? '1' : '0',
            'status' => (string)($editingProduct['status'] ?? 'active'),
        ];
    } else {
        set_flash('error', 'Không tìm thấy sản phẩm cần chỉnh sửa.');
        redirect('products.php');
    }
}

$stats = [
    'total' => $productModel->getAdminTotal('', 'all'),
    'active' => $productModel->getAdminTotal('', 'active'),
    'inactive' => $productModel->getAdminTotal('', 'inactive'),
];

$totalProducts = $productModel->getAdminTotal($search, $statusFilter);
$totalPages = max(1, (int)ceil($totalProducts / $perPage));

if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $perPage;
$products = $productModel->getAdminList($search, $statusFilter, $perPage, $offset);

render_admin_header('Quản lý sản phẩm');
?>

<div class="space-y-8">
    <section class="grid gap-4 md:grid-cols-3">
        <article class="rounded-[1.5rem] border border-[#d9e9de] bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-[#587766]">Tổng sản phẩm</p>
            <p class="mt-3 text-3xl font-extrabold text-[#102118]"><?= clean((string)$stats['total']) ?></p>
            <p class="mt-2 text-sm text-[#6e8d7b]">Bao gồm cả đang bán và ngừng bán.</p>
        </article>
        <article class="rounded-[1.5rem] border border-[#d9e9de] bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-[#587766]">Đang bán</p>
            <p class="mt-3 text-3xl font-extrabold text-[#2e9b63]"><?= clean((string)$stats['active']) ?></p>
            <p class="mt-2 text-sm text-[#6e8d7b]">Hiển thị cho khách trên cửa hàng.</p>
        </article>
        <article class="rounded-[1.5rem] border border-[#d9e9de] bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-[#587766]">Ngừng bán</p>
            <p class="mt-3 text-3xl font-extrabold text-[#b56a16]"><?= clean((string)$stats['inactive']) ?></p>
            <p class="mt-2 text-sm text-[#6e8d7b]">Ẩn khỏi cửa hàng nhưng vẫn giữ dữ liệu.</p>
        </article>
    </section>

    <section class="grid items-start gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(340px,0.8fr)]">
        <div class="order-2 space-y-6 lg:order-1">
            <article class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Danh sách sản phẩm</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-[#102118]">Quản trị sản phẩm trong admin</h2>
                        <p class="mt-2 text-sm text-[#6e8d7b]">Bạn có thể thêm, sửa, ẩn hoặc xóa sản phẩm trực tiếp tại đây.</p>
                    </div>
                    <a href="products.php" class="inline-flex items-center rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">
                        Tạo form mới
                    </a>
                </div>

                <form method="GET" class="mt-6 grid gap-4 md:grid-cols-[1fr_220px_auto]">
                    <div>
                        <label for="q" class="mb-2 block text-sm font-semibold text-[#102118]">Tìm sản phẩm</label>
                        <input id="q" type="text" name="q" value="<?= clean($search) ?>" placeholder="Nhập tên, slug hoặc danh mục..." class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                    </div>
                    <div>
                        <label for="status" class="mb-2 block text-sm font-semibold text-[#102118]">Trạng thái</label>
                        <select id="status" name="status" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                            <?php foreach ($statusOptions as $value => $label): ?>
                                <option value="<?= clean($value) ?>" <?= $statusFilter === $value ? 'selected' : '' ?>><?= clean($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center self-end rounded-2xl bg-[#102118] px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#1f3b2d]">
                        Lọc dữ liệu
                    </button>
                </form>

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-[#edf4ef] text-left text-[#6e8d7b]">
                                <th class="pb-3 pr-4 font-semibold">Sản phẩm</th>
                                <th class="pb-3 pr-4 font-semibold">Danh mục</th>
                                <th class="pb-3 pr-4 font-semibold">Giá</th>
                                <th class="pb-3 pr-4 font-semibold">Tồn kho</th>
                                <th class="pb-3 pr-4 font-semibold">Trạng thái</th>
                                <th class="pb-3 font-semibold">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-sm text-[#6e8d7b]">
                                        Không tìm thấy sản phẩm nào phù hợp bộ lọc hiện tại.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <?php
                                    $productPrice = !empty($product['sale_price']) && (float)$product['sale_price'] > 0
                                        ? (float)$product['sale_price']
                                        : (float)$product['price'];
                                    $editQuery = admin_products_query([
                                        'edit' => $product['id'],
                                        'q' => $search,
                                        'status' => $statusFilter,
                                        'page' => $page,
                                    ]);
                                    ?>
                                    <tr class="border-b border-[#f4f8f5] align-top last:border-b-0">
                                        <td class="py-4 pr-4">
                                            <div class="flex items-start gap-3">
                                                <img src="<?= clean($product['image_url']) ?>" alt="<?= clean($product['name']) ?>" class="h-14 w-14 rounded-2xl border border-[#edf4ef] object-cover">
                                                <div>
                                                    <p class="font-semibold text-[#102118]"><?= clean($product['name']) ?></p>
                                                    <p class="mt-1 text-xs text-[#6e8d7b]">Slug: <?= clean($product['slug']) ?></p>
                                                    <?php if (!empty($product['featured'])): ?>
                                                        <span class="mt-2 inline-flex rounded-full bg-[#e9f5ee] px-3 py-1 text-xs font-semibold text-[#2e9b63]">Nổi bật</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 pr-4 text-[#102118]"><?= clean($product['category_name'] ?? 'Chưa có') ?></td>
                                        <td class="py-4 pr-4">
                                            <p class="font-semibold text-[#102118]"><?= format_currency($productPrice) ?></p>
                                            <?php if (!empty($product['sale_price']) && (float)$product['sale_price'] > 0): ?>
                                                <p class="text-xs text-[#6e8d7b] line-through"><?= format_currency((float)$product['price']) ?></p>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 pr-4 font-semibold <?= (int)$product['stock'] <= 5 ? 'text-[#b56a16]' : 'text-[#102118]' ?>">
                                            <?= clean((string)$product['stock']) ?>
                                        </td>
                                        <td class="py-4 pr-4">
                                            <span class="rounded-full px-3 py-1 text-xs font-semibold <?= $product['status'] === 'active' ? 'bg-[#eef6f1] text-[#456a57]' : 'bg-[#fff3e8] text-[#b56a16]' ?>">
                                                <?= $product['status'] === 'active' ? 'Đang bán' : 'Ngừng bán' ?>
                                            </span>
                                            <?php if (!empty($product['order_count'])): ?>
                                                <p class="mt-2 text-xs text-[#6e8d7b]">Đã có <?= clean((string)$product['order_count']) ?> dòng đơn hàng</p>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4">
                                            <div class="flex flex-col items-start gap-2">
                                                <a href="products.php<?= clean($editQuery) ?>" class="text-sm font-semibold text-[#2e9b63] hover:text-[#22784d]">Chỉnh sửa</a>
                                                <a href="../product-detail.php?slug=<?= clean($product['slug']) ?>" class="text-sm font-semibold text-[#102118] hover:text-[#2e9b63]">Xem ngoài site</a>
                                                <form method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này không?');">
                                                    <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                                                    <input type="hidden" name="return_q" value="<?= clean($search) ?>">
                                                    <input type="hidden" name="return_status" value="<?= clean($statusFilter) ?>">
                                                    <input type="hidden" name="return_page" value="<?= clean((string)$page) ?>">
                                                    <button type="submit" class="text-sm font-semibold text-[#b42318] hover:text-[#8f1d14]">Xóa</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-[#edf4ef] pt-4">
                        <p class="text-sm text-[#6e8d7b]">
                            Trang <?= clean((string)$page) ?> / <?= clean((string)$totalPages) ?>, tổng <?= clean((string)$totalProducts) ?> sản phẩm
                        </p>
                        <div class="flex items-center gap-2">
                            <?php if ($page > 1): ?>
                                <a href="products.php<?= clean(admin_products_query(['q' => $search, 'status' => $statusFilter, 'page' => $page - 1])) ?>" class="inline-flex rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">Trang trước</a>
                            <?php endif; ?>
                            <?php if ($page < $totalPages): ?>
                                <a href="products.php<?= clean(admin_products_query(['q' => $search, 'status' => $statusFilter, 'page' => $page + 1])) ?>" class="inline-flex rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">Trang sau</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </article>
        </div>

        <div class="order-1 space-y-6 lg:order-2">
            <article class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm lg:sticky lg:top-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]"><?= $formMode === 'edit' ? 'Chỉnh sửa' : 'Thêm mới' ?></p>
                        <h2 class="mt-2 text-2xl font-extrabold text-[#102118]"><?= $formMode === 'edit' ? 'Cập nhật sản phẩm' : 'Tạo sản phẩm mới' ?></h2>
                        <p class="mt-2 text-sm text-[#6e8d7b]"><?= $formMode === 'edit' ? 'Điều chỉnh thông tin hiển thị, giá bán và tồn kho.' : 'Điền đủ thông tin để sản phẩm xuất hiện trên cửa hàng.' ?></p>
                    </div>
                    <?php if ($formMode === 'edit'): ?>
                        <a href="products.php" class="inline-flex rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">Thêm mới</a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        Vui lòng kiểm tra lại các trường đang báo lỗi trước khi lưu sản phẩm.
                    </div>
                <?php endif; ?>

                <form method="POST" class="mt-6 space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                    <input type="hidden" name="action" value="<?= $formMode === 'edit' ? 'update' : 'create' ?>">
                    <?php if ($formMode === 'edit'): ?>
                        <input type="hidden" name="product_id" value="<?= clean((string)$editId) ?>">
                    <?php endif; ?>
                    <div>
                        <label for="category_id" class="mb-2 block text-sm font-semibold text-[#102118]">Danh mục</label>
                        <select id="category_id" name="category_id" class="w-full rounded-2xl border <?= isset($errors['category_id']) ? 'border-red-300' : 'border-[#d9e9de]' ?> px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                            <option value="">Chọn danh mục</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= clean((string)$category['id']) ?>" <?= $formData['category_id'] === (string)$category['id'] ? 'selected' : '' ?>><?= clean($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['category_id'])): ?>
                            <p class="mt-2 text-sm text-red-600"><?= clean($errors['category_id']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="name" class="mb-2 block text-sm font-semibold text-[#102118]">Tên sản phẩm</label>
                        <input id="name" type="text" name="name" value="<?= clean($formData['name']) ?>" class="w-full rounded-2xl border <?= isset($errors['name']) ? 'border-red-300' : 'border-[#d9e9de]' ?> px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="Ví dụ: Cây Bạch Mã Galaxy">
                        <?php if (isset($errors['name'])): ?>
                            <p class="mt-2 text-sm text-red-600"><?= clean($errors['name']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="slug" class="mb-2 block text-sm font-semibold text-[#102118]">Slug</label>
                        <input id="slug" type="text" name="slug" value="<?= clean($formData['slug']) ?>" class="w-full rounded-2xl border <?= isset($errors['slug']) ? 'border-red-300' : 'border-[#d9e9de]' ?> px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="Để trống để tạo tự động từ tên sản phẩm">
                        <p class="mt-2 text-xs text-[#6e8d7b]">Nếu trùng, hệ thống sẽ tự thêm hậu tố để slug luôn duy nhất.</p>
                        <?php if (isset($errors['slug'])): ?>
                            <p class="mt-2 text-sm text-red-600"><?= clean($errors['slug']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="price" class="mb-2 block text-sm font-semibold text-[#102118]">Giá bán</label>
                            <input id="price" type="number" min="1000" step="1000" name="price" value="<?= clean($formData['price']) ?>" class="w-full rounded-2xl border <?= isset($errors['price']) ? 'border-red-300' : 'border-[#d9e9de]' ?> px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="350000">
                            <?php if (isset($errors['price'])): ?>
                                <p class="mt-2 text-sm text-red-600"><?= clean($errors['price']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label for="sale_price" class="mb-2 block text-sm font-semibold text-[#102118]">Giá khuyến mãi</label>
                            <input id="sale_price" type="number" min="0" step="1000" name="sale_price" value="<?= clean($formData['sale_price']) ?>" class="w-full rounded-2xl border <?= isset($errors['sale_price']) ? 'border-red-300' : 'border-[#d9e9de]' ?> px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="Bỏ trống nếu không giảm giá">
                            <?php if (isset($errors['sale_price'])): ?>
                                <p class="mt-2 text-sm text-red-600"><?= clean($errors['sale_price']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="stock" class="mb-2 block text-sm font-semibold text-[#102118]">Tồn kho</label>
                            <input id="stock" type="number" min="0" step="1" name="stock" value="<?= clean($formData['stock']) ?>" class="w-full rounded-2xl border <?= isset($errors['stock']) ? 'border-red-300' : 'border-[#d9e9de]' ?> px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                            <?php if (isset($errors['stock'])): ?>
                                <p class="mt-2 text-sm text-red-600"><?= clean($errors['stock']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label for="size" class="mb-2 block text-sm font-semibold text-[#102118]">Kích thước</label>
                            <input id="size" type="text" name="size" value="<?= clean($formData['size']) ?>" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="Ví dụ: Nhỏ, Vừa, Lớn">
                        </div>
                    </div>

                    <div>
                        <label for="image" class="mb-2 block text-sm font-semibold text-[#102118]">Ảnh đại diện</label>
                        <input id="image" type="text" name="image" value="<?= clean($formData['image']) ?>" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="Nhập URL ảnh hoặc đường dẫn trong uploads/products/...">
                        <p class="mt-2 text-xs text-[#6e8d7b]">Bạn vẫn có thể dùng công cụ upload ảnh riêng nếu muốn tải ảnh từ máy tính.</p>
                    </div>

                    <div>
                        <label for="description" class="mb-2 block text-sm font-semibold text-[#102118]">Mô tả</label>
                        <textarea id="description" name="description" rows="4" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="Mô tả ngắn gọn về sản phẩm, cách dùng và điểm nổi bật..."><?= clean($formData['description']) ?></textarea>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label for="care_level" class="mb-2 block text-sm font-semibold text-[#102118]">Chăm sóc</label>
                            <select id="care_level" name="care_level" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                                <?php foreach ($careOptions as $value => $label): ?>
                                    <option value="<?= clean($value) ?>" <?= $formData['care_level'] === $value ? 'selected' : '' ?>><?= clean($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="light_requirement" class="mb-2 block text-sm font-semibold text-[#102118]">Ánh sáng</label>
                            <select id="light_requirement" name="light_requirement" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                                <?php foreach ($requirementOptions as $value => $label): ?>
                                    <option value="<?= clean($value) ?>" <?= $formData['light_requirement'] === $value ? 'selected' : '' ?>><?= clean($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="water_requirement" class="mb-2 block text-sm font-semibold text-[#102118]">Nước</label>
                            <select id="water_requirement" name="water_requirement" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                                <?php foreach ($requirementOptions as $value => $label): ?>
                                    <option value="<?= clean($value) ?>" <?= $formData['water_requirement'] === $value ? 'selected' : '' ?>><?= clean($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="flex items-start gap-3 rounded-2xl border border-[#d9e9de] px-4 py-3">
                            <input type="checkbox" name="featured" value="1" <?= $formData['featured'] === '1' ? 'checked' : '' ?> class="mt-1 rounded border-[#d9e9de] text-[#2e9b63] focus:ring-[#2e9b63]">
                            <span>
                                <span class="block text-sm font-semibold text-[#102118]">Đánh dấu nổi bật</span>
                                <span class="mt-1 block text-xs text-[#6e8d7b]">Ưu tiên hiển thị ở đầu danh sách và khu nổi bật.</span>
                            </span>
                        </label>
                        <div>
                            <label for="status_form" class="mb-2 block text-sm font-semibold text-[#102118]">Trạng thái</label>
                            <select id="status_form" name="status" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                                <option value="active" <?= $formData['status'] === 'active' ? 'selected' : '' ?>>Đang bán</option>
                                <option value="inactive" <?= $formData['status'] === 'inactive' ? 'selected' : '' ?>>Ngừng bán</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <button type="submit" class="inline-flex items-center rounded-full bg-[#102118] px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#1f3b2d]"><?= $formMode === 'edit' ? 'Lưu thay đổi' : 'Thêm sản phẩm' ?></button>
                        <?php if ($formMode === 'edit'): ?>
                            <a href="../product-detail.php?slug=<?= clean($formData['slug']) ?>" class="inline-flex items-center rounded-full border border-[#d9e9de] px-5 py-3 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">Xem sản phẩm</a>
                        <?php endif; ?>
                    </div>
                </form>
            </article>

            <article class="rounded-[1.75rem] border border-[#d9e9de] bg-[#102118] p-6 text-white shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-white/65">Lưu ý khi xóa</p>
                <h3 class="mt-2 text-xl font-extrabold">Xóa thông minh để giữ lịch sử</h3>
                <p class="mt-3 text-sm leading-6 text-white/80">
                    Nếu sản phẩm chưa từng phát sinh đơn hàng, hệ thống sẽ xóa hẳn. Nếu sản phẩm đã có trong đơn hàng,
                    hệ thống sẽ chuyển sang trạng thái ngừng bán để bảo toàn dữ liệu thống kê và lịch sử giao dịch.
                </p>
            </article>
        </div>
    </section>
</div>

<?php render_admin_footer(); ?>
