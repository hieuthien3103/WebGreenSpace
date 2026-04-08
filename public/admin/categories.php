<?php
require_once __DIR__ . '/bootstrap.php';
require_admin_permission('categories.manage', 'categories.php');

function admin_category_defaults(): array {
    return [
        'name' => '',
        'slug' => '',
        'description' => '',
        'image' => '',
        'parent_id' => '',
        'status' => 'active',
    ];
}

function admin_collect_category_form_data(array $input): array {
    return [
        'name' => trim((string)($input['name'] ?? '')),
        'slug' => trim((string)($input['slug'] ?? '')),
        'description' => trim((string)($input['description'] ?? '')),
        'image' => trim((string)($input['image'] ?? '')),
        'parent_id' => trim((string)($input['parent_id'] ?? '')),
        'status' => trim((string)($input['status'] ?? 'active')),
    ];
}

function admin_generate_unique_category_slug(Category $categoryModel, string $baseSlug, ?int $excludeId = null): string {
    $baseSlug = trim($baseSlug, '-');
    if ($baseSlug === '') {
        $baseSlug = 'danh-muc';
    }

    $slug = $baseSlug;
    $suffix = 2;

    while ($categoryModel->slugExists($slug, $excludeId)) {
        $slug = $baseSlug . '-' . $suffix;
        $suffix++;
    }

    return $slug;
}

function admin_categories_query(array $params): string {
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

$categoryModel = new Category();

$statusOptions = [
    'all' => 'Tất cả',
    'active' => 'Đang hiển thị',
    'inactive' => 'Đang ẩn',
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
$formData = admin_category_defaults();
$editingCategory = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
        redirect('categories.php');
    }

    $action = (string)($_POST['action'] ?? '');

    if ($action === 'delete') {
        $categoryId = max(0, (int)($_POST['category_id'] ?? 0));
        $category = $categoryModel->getAdminById($categoryId);

        if (!$category) {
            set_flash('error', 'Không tìm thấy danh mục cần xóa.');
        } else {
            $result = $categoryModel->deleteAdminCategory($categoryId);

            if (!empty($result['success'])) {
                if (($result['mode'] ?? '') === 'inactivated') {
                    set_flash('success', 'Danh mục đã có sản phẩm nên được chuyển sang trạng thái ẩn để giữ dữ liệu.');
                } else {
                    set_flash('success', 'Đã xóa danh mục khỏi hệ thống.');
                }
            } else {
                set_flash('error', 'Không thể xóa danh mục lúc này.');
            }
        }

        $redirectQuery = admin_categories_query([
            'q' => trim((string)($_POST['return_q'] ?? '')),
            'status' => trim((string)($_POST['return_status'] ?? 'all')),
            'page' => max(1, (int)($_POST['return_page'] ?? 1)),
        ]);
        redirect('categories.php' . $redirectQuery);
    }

    $formData = admin_collect_category_form_data($_POST);
    $formMode = $action === 'update' ? 'edit' : 'create';
    $editId = max(0, (int)($_POST['category_id'] ?? 0));

    if ($action === 'update') {
        $editingCategory = $categoryModel->getAdminById($editId);
        if (!$editingCategory) {
            set_flash('error', 'Không tìm thấy danh mục cần cập nhật.');
            redirect('categories.php');
        }
    }

    $parentOptions = $categoryModel->getAdminParentOptions($action === 'update' ? $editId : null);
    $parentIds = array_map(static fn(array $item): int => (int)$item['id'], $parentOptions);

    if ($formData['name'] === '') {
        $errors['name'] = 'Tên danh mục không được để trống.';
    }

    if ($formData['parent_id'] !== '' && !in_array((int)$formData['parent_id'], $parentIds, true)) {
        $errors['parent_id'] = 'Danh mục cha không hợp lệ.';
    }

    if (!in_array($formData['status'], ['active', 'inactive'], true)) {
        $errors['status'] = 'Trạng thái không hợp lệ.';
    }

    $baseSlug = create_slug($formData['slug'] !== '' ? $formData['slug'] : $formData['name']);
    if ($baseSlug === '') {
        $errors['slug'] = 'Không thể tạo slug từ tên danh mục. Vui lòng nhập slug thủ công.';
    }

    if (empty($errors)) {
        $payload = [
            'name' => $formData['name'],
            'slug' => admin_generate_unique_category_slug($categoryModel, $baseSlug, $action === 'update' ? $editId : null),
            'description' => $formData['description'],
            'image' => $formData['image'],
            'parent_id' => $formData['parent_id'] !== '' ? (int)$formData['parent_id'] : null,
            'status' => $formData['status'],
        ];

        if ($action === 'update') {
            $categoryModel->updateAdminCategory($editId, $payload);
            set_flash('success', 'Đã cập nhật thông tin danh mục.');
            redirect('categories.php' . admin_categories_query([
                'edit' => $editId,
                'q' => $search,
                'status' => $statusFilter,
                'page' => $page,
            ]));
        }

        $newId = $categoryModel->createAdminCategory($payload);
        set_flash('success', 'Đã thêm danh mục mới.');
        redirect('categories.php?edit=' . $newId);
    }
}

if ($formMode !== 'edit' && $editId > 0) {
    $editingCategory = $categoryModel->getAdminById($editId);

    if ($editingCategory) {
        $formMode = 'edit';
        $formData = [
            'name' => (string)($editingCategory['name'] ?? ''),
            'slug' => (string)($editingCategory['slug'] ?? ''),
            'description' => (string)($editingCategory['description'] ?? ''),
            'image' => (string)($editingCategory['image'] ?? ''),
            'parent_id' => isset($editingCategory['parent_id']) && $editingCategory['parent_id'] !== null ? (string)$editingCategory['parent_id'] : '',
            'status' => (string)($editingCategory['status'] ?? 'active'),
        ];
    } else {
        set_flash('error', 'Không tìm thấy danh mục cần chỉnh sửa.');
        redirect('categories.php');
    }
}

$parentOptions = $categoryModel->getAdminParentOptions($formMode === 'edit' ? $editId : null);

$stats = [
    'total' => $categoryModel->getAdminTotal('', 'all'),
    'active' => $categoryModel->getAdminTotal('', 'active'),
    'inactive' => $categoryModel->getAdminTotal('', 'inactive'),
];

$totalCategories = $categoryModel->getAdminTotal($search, $statusFilter);
$totalPages = max(1, (int)ceil($totalCategories / $perPage));

if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $perPage;
$categories = $categoryModel->getAdminList($search, $statusFilter, $perPage, $offset);

render_admin_header('Quản lý danh mục');
?>

<div class="space-y-8">
    <section class="grid gap-4 md:grid-cols-3">
        <article class="rounded-[1.5rem] border border-[#d9e9de] bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-[#587766]">Tổng danh mục</p>
            <p class="mt-3 text-3xl font-extrabold text-[#102118]"><?= clean((string)$stats['total']) ?></p>
            <p class="mt-2 text-sm text-[#6e8d7b]">Bao gồm danh mục đang hiển thị và đang ẩn.</p>
        </article>
        <article class="rounded-[1.5rem] border border-[#d9e9de] bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-[#587766]">Đang hiển thị</p>
            <p class="mt-3 text-3xl font-extrabold text-[#2e9b63]"><?= clean((string)$stats['active']) ?></p>
            <p class="mt-2 text-sm text-[#6e8d7b]">Khách có thể nhìn thấy ngoài cửa hàng.</p>
        </article>
        <article class="rounded-[1.5rem] border border-[#d9e9de] bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-[#587766]">Đang ẩn</p>
            <p class="mt-3 text-3xl font-extrabold text-[#b56a16]"><?= clean((string)$stats['inactive']) ?></p>
            <p class="mt-2 text-sm text-[#6e8d7b]">Dùng để lưu trữ hoặc tạm ngưng hiển thị.</p>
        </article>
    </section>

    <section class="grid items-start gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(340px,0.8fr)]">
        <div class="order-2 space-y-6 lg:order-1">
            <article class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Danh sách danh mục</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-[#102118]">Quản trị danh mục trong admin</h2>
                        <p class="mt-2 text-sm text-[#6e8d7b]">Thêm, sửa, ẩn hoặc xóa danh mục ngay trong khu quản trị.</p>
                    </div>
                    <a href="categories.php" class="inline-flex items-center rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">
                        Tạo form mới
                    </a>
                </div>

                <form method="GET" class="mt-6 grid gap-4 md:grid-cols-[1fr_220px_auto]">
                    <div>
                        <label for="q" class="mb-2 block text-sm font-semibold text-[#102118]">Tìm danh mục</label>
                        <input id="q" type="text" name="q" value="<?= clean($search) ?>" placeholder="Nhập tên, slug hoặc danh mục cha..." class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
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
                                <th class="pb-3 pr-4 font-semibold">Danh mục</th>
                                <th class="pb-3 pr-4 font-semibold">Danh mục cha</th>
                                <th class="pb-3 pr-4 font-semibold">Sản phẩm</th>
                                <th class="pb-3 pr-4 font-semibold">Trạng thái</th>
                                <th class="pb-3 font-semibold">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-sm text-[#6e8d7b]">
                                        Không tìm thấy danh mục nào phù hợp bộ lọc hiện tại.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categories as $category): ?>
                                    <?php
                                    $editQuery = admin_categories_query([
                                        'edit' => $category['id'],
                                        'q' => $search,
                                        'status' => $statusFilter,
                                        'page' => $page,
                                    ]);
                                    ?>
                                    <tr class="border-b border-[#f4f8f5] align-top last:border-b-0">
                                        <td class="py-4 pr-4">
                                            <div class="flex items-start gap-3">
                                                <img src="<?= clean($category['image_url']) ?>" alt="<?= clean($category['name']) ?>" class="h-14 w-14 rounded-2xl border border-[#edf4ef] object-cover">
                                                <div>
                                                    <p class="font-semibold text-[#102118]"><?= clean($category['name']) ?></p>
                                                    <p class="mt-1 text-xs text-[#6e8d7b]">Slug: <?= clean($category['slug']) ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 pr-4 text-[#102118]"><?= clean($category['parent_name'] ?? 'Không có') ?></td>
                                        <td class="py-4 pr-4">
                                            <p class="font-semibold text-[#102118]"><?= clean((string)($category['product_count'] ?? 0)) ?></p>
                                            <p class="text-xs text-[#6e8d7b]">Đang bán: <?= clean((string)($category['active_product_count'] ?? 0)) ?></p>
                                        </td>
                                        <td class="py-4 pr-4">
                                            <span class="rounded-full px-3 py-1 text-xs font-semibold <?= $category['status'] === 'active' ? 'bg-[#eef6f1] text-[#456a57]' : 'bg-[#fff3e8] text-[#b56a16]' ?>">
                                                <?= $category['status'] === 'active' ? 'Đang hiển thị' : 'Đang ẩn' ?>
                                            </span>
                                        </td>
                                        <td class="py-4">
                                            <div class="flex flex-col items-start gap-2">
                                                <a href="categories.php<?= clean($editQuery) ?>" class="text-sm font-semibold text-[#2e9b63] hover:text-[#22784d]">Chỉnh sửa</a>
                                                <a href="../products.php?category=<?= clean($category['slug']) ?>" class="text-sm font-semibold text-[#102118] hover:text-[#2e9b63]">Xem ngoài site</a>
                                                <form method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa danh mục này không?');">
                                                    <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="category_id" value="<?= (int)$category['id'] ?>">
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
                            Trang <?= clean((string)$page) ?> / <?= clean((string)$totalPages) ?>, tổng <?= clean((string)$totalCategories) ?> danh mục
                        </p>
                        <div class="flex items-center gap-2">
                            <?php if ($page > 1): ?>
                                <a href="categories.php<?= clean(admin_categories_query(['q' => $search, 'status' => $statusFilter, 'page' => $page - 1])) ?>" class="inline-flex rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">Trang trước</a>
                            <?php endif; ?>
                            <?php if ($page < $totalPages): ?>
                                <a href="categories.php<?= clean(admin_categories_query(['q' => $search, 'status' => $statusFilter, 'page' => $page + 1])) ?>" class="inline-flex rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">Trang sau</a>
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
                        <h2 class="mt-2 text-2xl font-extrabold text-[#102118]"><?= $formMode === 'edit' ? 'Cập nhật danh mục' : 'Tạo danh mục mới' ?></h2>
                        <p class="mt-2 text-sm text-[#6e8d7b]"><?= $formMode === 'edit' ? 'Điều chỉnh tên, slug, trạng thái và danh mục cha.' : 'Điền đủ thông tin để tạo danh mục mới cho cửa hàng.' ?></p>
                    </div>
                    <?php if ($formMode === 'edit'): ?>
                        <a href="categories.php" class="inline-flex rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">Thêm mới</a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        Vui lòng kiểm tra lại các trường đang báo lỗi trước khi lưu danh mục.
                    </div>
                <?php endif; ?>

                <form method="POST" class="mt-6 space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                    <input type="hidden" name="action" value="<?= $formMode === 'edit' ? 'update' : 'create' ?>">
                    <?php if ($formMode === 'edit'): ?>
                        <input type="hidden" name="category_id" value="<?= clean((string)$editId) ?>">
                    <?php endif; ?>
                    <div>
                        <label for="name" class="mb-2 block text-sm font-semibold text-[#102118]">Tên danh mục</label>
                        <input id="name" type="text" name="name" value="<?= clean($formData['name']) ?>" class="w-full rounded-2xl border <?= isset($errors['name']) ? 'border-red-300' : 'border-[#d9e9de]' ?> px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="Ví dụ: Cây nội thất">
                        <?php if (isset($errors['name'])): ?>
                            <p class="mt-2 text-sm text-red-600"><?= clean($errors['name']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="slug" class="mb-2 block text-sm font-semibold text-[#102118]">Slug</label>
                        <input id="slug" type="text" name="slug" value="<?= clean($formData['slug']) ?>" class="w-full rounded-2xl border <?= isset($errors['slug']) ? 'border-red-300' : 'border-[#d9e9de]' ?> px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="Để trống để tạo tự động từ tên danh mục">
                        <p class="mt-2 text-xs text-[#6e8d7b]">Nếu trùng, hệ thống sẽ tự thêm hậu tố để slug luôn duy nhất.</p>
                        <?php if (isset($errors['slug'])): ?>
                            <p class="mt-2 text-sm text-red-600"><?= clean($errors['slug']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="parent_id" class="mb-2 block text-sm font-semibold text-[#102118]">Danh mục cha</label>
                        <select id="parent_id" name="parent_id" class="w-full rounded-2xl border <?= isset($errors['parent_id']) ? 'border-red-300' : 'border-[#d9e9de]' ?> px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                            <option value="">Không có danh mục cha</option>
                            <?php foreach ($parentOptions as $parent): ?>
                                <option value="<?= clean((string)$parent['id']) ?>" <?= $formData['parent_id'] === (string)$parent['id'] ? 'selected' : '' ?>>
                                    <?= clean($parent['name']) ?><?= $parent['status'] === 'inactive' ? ' (đang ẩn)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['parent_id'])): ?>
                            <p class="mt-2 text-sm text-red-600"><?= clean($errors['parent_id']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="image" class="mb-2 block text-sm font-semibold text-[#102118]">Ảnh danh mục</label>
                        <input id="image" type="text" name="image" value="<?= clean($formData['image']) ?>" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="Nhập URL ảnh hoặc đường dẫn trong uploads/categories/...">
                    </div>

                    <div>
                        <label for="description" class="mb-2 block text-sm font-semibold text-[#102118]">Mô tả</label>
                        <textarea id="description" name="description" rows="4" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="Mô tả ngắn gọn về nhóm sản phẩm trong danh mục này..."><?= clean($formData['description']) ?></textarea>
                    </div>

                    <div>
                        <label for="status_form" class="mb-2 block text-sm font-semibold text-[#102118]">Trạng thái</label>
                        <select id="status_form" name="status" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                            <option value="active" <?= $formData['status'] === 'active' ? 'selected' : '' ?>>Đang hiển thị</option>
                            <option value="inactive" <?= $formData['status'] === 'inactive' ? 'selected' : '' ?>>Đang ẩn</option>
                        </select>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <button type="submit" class="inline-flex items-center rounded-full bg-[#102118] px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#1f3b2d]"><?= $formMode === 'edit' ? 'Lưu thay đổi' : 'Thêm danh mục' ?></button>
                        <?php if ($formMode === 'edit'): ?>
                            <a href="../products.php?category=<?= clean($formData['slug']) ?>" class="inline-flex items-center rounded-full border border-[#d9e9de] px-5 py-3 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">Xem ngoài site</a>
                        <?php endif; ?>
                    </div>
                </form>
            </article>

            <article class="rounded-[1.75rem] border border-[#d9e9de] bg-[#102118] p-6 text-white shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-white/65">Lưu ý khi xóa</p>
                <h3 class="mt-2 text-xl font-extrabold">Giữ danh mục nếu đã có sản phẩm</h3>
                <p class="mt-3 text-sm leading-6 text-white/80">
                    Nếu danh mục chưa chứa sản phẩm, hệ thống sẽ xóa hẳn. Nếu danh mục đã có sản phẩm,
                    hệ thống sẽ chuyển sang trạng thái ẩn để tránh làm mất liên kết và dữ liệu đang dùng.
                </p>
            </article>
        </div>
    </section>
</div>

<?php render_admin_footer(); ?>
