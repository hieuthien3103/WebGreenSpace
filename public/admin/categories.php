<?php
if (empty($GLOBALS['mvc_template_rendering'])) {
    require_once __DIR__ . '/../../config/config.php';
    (new AdminPageController())->categories()->send();
    return;
}

require_once __DIR__ . '/bootstrap.php';

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
                    <button type="button" id="createCategoryFormButton" class="inline-flex items-center rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">
                        Tạo danh mục mới
                    </button>
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
                                            <div class="flex flex-wrap items-center gap-2">
                                                <?php
                                                $categoryClientPayload = [
                                                    'id' => (string)$category['id'],
                                                    'name' => (string)($category['name'] ?? ''),
                                                    'slug' => (string)($category['slug'] ?? ''),
                                                    'description' => (string)($category['description'] ?? ''),
                                                    'image' => (string)($category['image'] ?? ''),
                                                    'parent_id' => isset($category['parent_id']) && $category['parent_id'] !== null ? (string)$category['parent_id'] : '',
                                                    'status' => (string)($category['status'] ?? 'active'),
                                                    'image_url' => (string)($category['image_url'] ?? image_url('categories/default.jpg')),
                                                    'site_url' => '../products.php?category=' . rawurlencode((string)($category['slug'] ?? '')),
                                                ];
                                                ?>
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center rounded-full border border-[#dce9e0] px-3 py-2 text-sm font-semibold text-[#2e9b63] transition-colors hover:border-[#2e9b63] hover:bg-[#eef6f1]"
                                                    data-edit-category="<?= clean(json_encode($categoryClientPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>"
                                                >Chỉnh sửa</button>
                                                <a href="../products.php?category=<?= clean($category['slug']) ?>" class="inline-flex items-center rounded-full border border-[#dce9e0] px-3 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">Xem ngoài site</a>
                                                <form method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa danh mục này không?');">
                                                    <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="category_id" value="<?= (int)$category['id'] ?>">
                                                    <input type="hidden" name="return_q" value="<?= clean($search) ?>">
                                                    <input type="hidden" name="return_status" value="<?= clean($statusFilter) ?>">
                                                    <input type="hidden" name="return_page" value="<?= clean((string)$page) ?>">
                                                    <button type="submit" class="inline-flex items-center rounded-full border border-[#f1d6d2] px-3 py-2 text-sm font-semibold text-[#b42318] transition-colors hover:border-[#b42318] hover:bg-[#fff6f5]">Xóa</button>
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
            <article id="categoryFormCard" class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm lg:sticky lg:top-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p id="categoryFormModeLabel" class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]"><?= $formMode === 'edit' ? 'Chỉnh sửa' : 'Thêm mới' ?></p>
                        <h2 id="categoryFormTitle" class="mt-2 text-2xl font-extrabold text-[#102118]"><?= $formMode === 'edit' ? 'Cập nhật danh mục' : 'Tạo danh mục mới' ?></h2>
                        <p id="categoryFormDescription" class="mt-2 text-sm text-[#6e8d7b]"><?= $formMode === 'edit' ? 'Điều chỉnh tên, slug, trạng thái và danh mục cha.' : 'Mở panel khi cần tạo mới để bảng danh mục trông thoáng và dễ theo dõi hơn.' ?></p>
                    </div>
                    <button type="button" id="closeCategoryFormButton" class="inline-flex rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63] <?= $showCategoryForm ? '' : 'hidden' ?>">Đóng panel</button>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        Vui lòng kiểm tra lại các trường đang báo lỗi trước khi lưu danh mục.
                    </div>
                <?php endif; ?>

                <div id="categoryFormEmptyState" class="<?= $showCategoryForm ? 'hidden ' : '' ?>mt-6 rounded-[1.5rem] border border-dashed border-[#d9e9de] bg-[#f8fbf9] px-5 py-8 text-sm leading-6 text-[#5f7b6c]">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Panel chỉnh sửa</p>
                    <h3 class="mt-3 text-xl font-extrabold text-[#102118]">Danh mục sẽ mở ở đây khi bạn cần thao tác</h3>
                    <p class="mt-3">
                        Bố cục mới giữ phần danh sách rộng rãi hơn. Bấm “Tạo danh mục mới” hoặc chọn “Chỉnh sửa” ở từng dòng để mở panel bên phải.
                    </p>
                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <button type="button" id="openCategoryFormButton" class="inline-flex items-center rounded-full bg-[#102118] px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#1f3b2d]">
                            Tạo danh mục mới
                        </button>
                        <p class="text-sm text-[#6e8d7b]">Bạn cũng có thể mở trực tiếp form sửa mà không cần tải lại trang.</p>
                    </div>
                </div>

                <form id="categoryAdminForm" method="POST" class="mt-6 space-y-6 <?= $showCategoryForm ? '' : 'hidden' ?>">
                    <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                    <input id="categoryFormAction" type="hidden" name="action" value="<?= $formMode === 'edit' ? 'update' : 'create' ?>">
                    <input id="categoryIdInput" type="hidden" name="category_id" value="<?= $formMode === 'edit' ? clean((string)$editId) : '' ?>">

                    <section class="rounded-[1.5rem] border border-[#edf4ef] bg-[#fcfefd] p-5">
                        <div class="flex flex-col gap-2">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Thông tin chính</p>
                            <h3 class="text-lg font-extrabold text-[#102118]">Tên gọi, cấu trúc và mô tả</h3>
                            <p class="text-sm text-[#5f7b6c]">Thiết lập phần cốt lõi để danh mục được hiển thị rõ ràng trên storefront và trong admin.</p>
                        </div>

                        <div class="mt-5 space-y-5">
                            <div class="grid gap-4 sm:grid-cols-2">
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
                            </div>

                            <div>
                                <label for="description" class="mb-2 block text-sm font-semibold text-[#102118]">Mô tả</label>
                                <textarea id="description" name="description" rows="4" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="Mô tả ngắn gọn về nhóm sản phẩm trong danh mục này..."><?= clean($formData['description']) ?></textarea>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[1.5rem] border border-[#edf4ef] bg-white p-5">
                        <div class="flex flex-col gap-2">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Cấu trúc hiển thị</p>
                            <h3 class="text-lg font-extrabold text-[#102118]">Danh mục cha, ảnh đại diện và trạng thái</h3>
                            <p class="text-sm text-[#5f7b6c]">Giữ các trường cấu trúc trong cùng một nhóm để bạn dễ hình dung cây danh mục hơn.</p>
                        </div>

                        <div class="mt-5 space-y-5">
                            <div>
                                <label for="parent_id" class="mb-2 block text-sm font-semibold text-[#102118]">Danh mục cha</label>
                                <select id="parent_id" name="parent_id" class="w-full rounded-2xl border <?= isset($errors['parent_id']) ? 'border-red-300' : 'border-[#d9e9de]' ?> px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                                    <option value="">Không có danh mục cha</option>
                                    <?php foreach ($allParentOptions as $parent): ?>
                                        <?php if ((string)$parent['id'] === ($formMode === 'edit' ? (string)$editId : '')) { continue; } ?>
                                        <option value="<?= clean((string)$parent['id']) ?>" <?= $formData['parent_id'] === (string)$parent['id'] ? 'selected' : '' ?>>
                                            <?= clean($parent['name']) ?><?= $parent['status'] === 'inactive' ? ' (đang ẩn)' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['parent_id'])): ?>
                                    <p class="mt-2 text-sm text-red-600"><?= clean($errors['parent_id']) ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="grid gap-4 lg:grid-cols-[0.9fr_1.1fr]">
                                <div class="rounded-[1.25rem] border border-[#d9e9de] bg-[#fcfefd] p-4">
                                    <p class="text-sm font-semibold text-[#102118]">Ảnh hiện tại / xem trước</p>
                                    <img
                                        id="categoryImagePreview"
                                        src="<?= clean($currentCategoryImagePreview) ?>"
                                        alt="Ảnh danh mục xem trước"
                                        class="mt-3 h-52 w-full rounded-2xl border border-[#edf4ef] object-cover"
                                    >
                                    <p class="mt-3 text-xs text-[#6e8d7b]">Khi thay URL hoặc đường dẫn ảnh, khung này sẽ cập nhật ngay.</p>
                                </div>

                                <div class="space-y-5">
                                    <div>
                                        <label for="image" class="mb-2 block text-sm font-semibold text-[#102118]">Ảnh danh mục</label>
                                        <input id="image" type="text" name="image" value="<?= clean($formData['image']) ?>" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="Nhập URL ảnh hoặc đường dẫn trong uploads/categories/...">
                                        <p class="mt-2 text-xs text-[#6e8d7b]">Bạn có thể dùng URL ảnh ngoài hoặc đường dẫn ảnh đã có trong `uploads/categories/...`.</p>
                                    </div>

                                    <div>
                                        <label for="status_form" class="mb-2 block text-sm font-semibold text-[#102118]">Trạng thái</label>
                                        <select id="status_form" name="status" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                                            <option value="active" <?= $formData['status'] === 'active' ? 'selected' : '' ?>>Đang hiển thị</option>
                                            <option value="inactive" <?= $formData['status'] === 'inactive' ? 'selected' : '' ?>>Đang ẩn</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="flex flex-wrap items-center gap-3 pt-1">
                        <button id="categoryFormSubmitButton" type="submit" class="inline-flex items-center rounded-full bg-[#102118] px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#1f3b2d]"><?= $formMode === 'edit' ? 'Lưu thay đổi' : 'Thêm danh mục' ?></button>
                        <button type="button" id="resetCategoryFormButton" class="inline-flex items-center rounded-full border border-[#d9e9de] px-5 py-3 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63] <?= $formMode === 'edit' ? '' : 'hidden' ?>">Tạo bản mới</button>
                        <a id="categoryViewLink" href="<?= $formMode === 'edit' ? clean('../products.php?category=' . rawurlencode($formData['slug'])) : '#' ?>" class="inline-flex items-center rounded-full border border-[#d9e9de] px-5 py-3 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63] <?= $formMode === 'edit' ? '' : 'hidden' ?>">Xem ngoài site</a>
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

<script>
const categoryFormCard = document.getElementById('categoryFormCard');
const categoryAdminForm = document.getElementById('categoryAdminForm');
const categoryFormModeLabel = document.getElementById('categoryFormModeLabel');
const categoryFormTitle = document.getElementById('categoryFormTitle');
const categoryFormDescription = document.getElementById('categoryFormDescription');
const categoryFormEmptyState = document.getElementById('categoryFormEmptyState');
const categoryFormAction = document.getElementById('categoryFormAction');
const categoryIdInput = document.getElementById('categoryIdInput');
const categoryFormSubmitButton = document.getElementById('categoryFormSubmitButton');
const categoryViewLink = document.getElementById('categoryViewLink');
const createCategoryFormButton = document.getElementById('createCategoryFormButton');
const openCategoryFormButton = document.getElementById('openCategoryFormButton');
const closeCategoryFormButton = document.getElementById('closeCategoryFormButton');
const resetCategoryFormButton = document.getElementById('resetCategoryFormButton');
const categoryImageInputField = document.getElementById('image');
const categoryImagePreview = document.getElementById('categoryImagePreview');
const categoryParentSelect = document.getElementById('parent_id');
const uploadBaseUrl = <?= json_encode(rtrim(UPLOAD_URL, '/'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const defaultCategoryImageUrl = <?= json_encode(image_url('categories/default.jpg'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const defaultCategoryFormState = <?= json_encode($defaultCategoryFormState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const initialCategoryFormState = <?= json_encode($initialCategoryFormState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const categoryListStateUrl = <?= json_encode($categoryListStateUrl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const allCategoryParentOptions = <?= json_encode($allParentOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const initialCategoryFormVisible = <?= json_encode($showCategoryForm) ?>;
let categoryFormVisible = initialCategoryFormVisible;

const resolveCategoryImageUrl = (value, fallback = defaultCategoryImageUrl) => {
    const normalized = String(value || '').trim();
    if (!normalized) {
        return fallback;
    }

    if (/^https?:\/\//i.test(normalized)) {
        return normalized;
    }

    return `${uploadBaseUrl}/${normalized.replace(/^\/+/, '')}`;
};

const syncCategoryFormVisibility = () => {
    categoryAdminForm?.classList.toggle('hidden', !categoryFormVisible);
    categoryFormEmptyState?.classList.toggle('hidden', categoryFormVisible);
    closeCategoryFormButton?.classList.toggle('hidden', !categoryFormVisible);
};

const pushCategoryHistoryState = (state, visible) => {
    const nextUrl = visible && state.id
        ? `categories.php<?= clean(admin_categories_query(['q' => $search, 'status' => $statusFilter, 'page' => $page])) ?>&edit=${encodeURIComponent(String(state.id))}`
        : categoryListStateUrl;

    window.history.pushState({ categoryState: state, categoryVisible: visible }, '', nextUrl);
};

const renderCategoryParentOptions = (selectedValue = '', currentCategoryId = '') => {
    if (!categoryParentSelect) {
        return;
    }

    const selected = String(selectedValue || '');
    const currentId = String(currentCategoryId || '');
    categoryParentSelect.innerHTML = '';

    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = 'Không có danh mục cha';
    defaultOption.selected = selected === '';
    categoryParentSelect.appendChild(defaultOption);

    allCategoryParentOptions
        .filter((option) => String(option.id) !== currentId)
        .forEach((option) => {
            const element = document.createElement('option');
            element.value = String(option.id);
            element.selected = String(option.id) === selected;
            element.textContent = `${String(option.name)}${option.status === 'inactive' ? ' (đang ẩn)' : ''}`;
            categoryParentSelect.appendChild(element);
        });
};

const populateCategoryForm = (state, { updateHistory = true, forceVisible = true } = {}) => {
    if (!categoryAdminForm) {
        return;
    }

    const normalizedState = {
        ...defaultCategoryFormState,
        ...state,
    };
    const isEditMode = Boolean(normalizedState.id);
    categoryFormVisible = forceVisible;
    syncCategoryFormVisibility();

    categoryFormModeLabel.textContent = isEditMode ? 'Chỉnh sửa' : 'Thêm mới';
    categoryFormTitle.textContent = isEditMode ? 'Cập nhật danh mục' : 'Tạo danh mục mới';
    categoryFormDescription.textContent = isEditMode
        ? 'Điều chỉnh tên, slug, trạng thái và danh mục cha.'
        : 'Điền đủ thông tin để tạo danh mục mới cho cửa hàng.';
    categoryFormAction.value = isEditMode ? 'update' : 'create';
    categoryIdInput.value = isEditMode ? String(normalizedState.id) : '';
    categoryFormSubmitButton.textContent = isEditMode ? 'Lưu thay đổi' : 'Thêm danh mục';
    resetCategoryFormButton?.classList.toggle('hidden', !isEditMode);

    ['name', 'slug', 'description', 'image', 'status'].forEach((fieldName) => {
        const field = categoryAdminForm.elements.namedItem(fieldName);
        if (field) {
            field.value = normalizedState[fieldName] ?? '';
        }
    });

    renderCategoryParentOptions(normalizedState.parent_id || '', normalizedState.id || '');

    if (categoryImagePreview) {
        categoryImagePreview.src = normalizedState.image_url || resolveCategoryImageUrl(normalizedState.image, defaultCategoryImageUrl);
    }

    if (categoryViewLink) {
        categoryViewLink.classList.toggle('hidden', !isEditMode);
        categoryViewLink.href = normalizedState.site_url || '#';
    }

    if (updateHistory) {
        pushCategoryHistoryState(normalizedState, categoryFormVisible);
    }

    if (categoryFormVisible) {
        categoryFormCard?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};

const resetCategoryForm = (event) => {
    event.preventDefault();
    populateCategoryForm(defaultCategoryFormState, { updateHistory: true, forceVisible: true });
};

const closeCategoryForm = ({ updateHistory = true } = {}) => {
    categoryFormVisible = false;
    syncCategoryFormVisibility();

    if (updateHistory) {
        pushCategoryHistoryState(defaultCategoryFormState, false);
    }
};

document.querySelectorAll('[data-edit-category]').forEach((button) => {
    button.addEventListener('click', () => {
        const categoryState = JSON.parse(button.dataset.editCategory || '{}');
        populateCategoryForm(categoryState, { updateHistory: true, forceVisible: true });
    });
});

createCategoryFormButton?.addEventListener('click', resetCategoryForm);
openCategoryFormButton?.addEventListener('click', resetCategoryForm);
resetCategoryFormButton?.addEventListener('click', resetCategoryForm);
closeCategoryFormButton?.addEventListener('click', () => {
    closeCategoryForm({ updateHistory: true });
});

categoryImageInputField?.addEventListener('input', () => {
    if (!categoryImagePreview || !categoryImageInputField) {
        return;
    }

    categoryImagePreview.src = resolveCategoryImageUrl(categoryImageInputField.value, defaultCategoryImageUrl);
});

window.addEventListener('popstate', (event) => {
    const categoryState = event.state?.categoryState || initialCategoryFormState || defaultCategoryFormState;
    const visible = typeof event.state?.categoryVisible === 'boolean' ? event.state.categoryVisible : initialCategoryFormVisible;
    populateCategoryForm(categoryState, { updateHistory: false, forceVisible: visible });
});

populateCategoryForm(initialCategoryFormState, { updateHistory: false, forceVisible: initialCategoryFormVisible });
</script>

<?php render_admin_footer(); ?>
