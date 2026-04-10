<?php
if (empty($GLOBALS['mvc_template_rendering'])) {
    require_once __DIR__ . '/../../config/config.php';
    (new AdminPageController())->users()->send();
    return;
}

require_once __DIR__ . '/bootstrap.php';

function admin_user_defaults(): array {
    return [
        'username' => '',
        'email' => '',
        'full_name' => '',
        'phone' => '',
        'role' => 'user',
        'admin_permissions' => [],
        'status' => 'active',
    ];
}

function admin_collect_user_form_data(array $input): array {
    return [
        'username' => trim((string)($input['username'] ?? '')),
        'email' => strtolower(trim((string)($input['email'] ?? ''))),
        'full_name' => trim((string)($input['full_name'] ?? '')),
        'phone' => trim((string)($input['phone'] ?? '')),
        'role' => trim((string)($input['role'] ?? 'user')),
        'admin_permissions' => normalize_admin_permissions($input['admin_permissions'] ?? []),
        'status' => trim((string)($input['status'] ?? 'active')),
    ];
}

function admin_users_query(array $params): string {
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

function admin_user_role_meta(string $role): array {
    return match ($role) {
        'admin' => [
            'label' => 'Admin',
            'class' => 'bg-[#eef6f1] text-[#2e9b63]',
        ],
        default => [
            'label' => 'User',
            'class' => 'bg-[#f4f1ff] text-[#6b4eff]',
        ],
    };
}

function admin_user_status_meta(string $status): array {
    return match ($status) {
        'inactive' => [
            'label' => 'Đã khóa',
            'class' => 'bg-[#fff3e8] text-[#b56a16]',
        ],
        default => [
            'label' => 'Đang hoạt động',
            'class' => 'bg-[#eef6f1] text-[#456a57]',
        ],
    };
}

function admin_user_permission_summary(array $user, array $permissionOptions): ?string {
    if (($user['role'] ?? 'user') !== 'admin') {
        return null;
    }

    if (!empty($user['has_full_admin_access'])) {
        return 'Toàn quyền quản trị';
    }

    $labels = [];
    foreach (normalize_admin_permissions($user['admin_permissions'] ?? []) as $permission) {
        if ($permission === 'admin.full_access') {
            continue;
        }
        $labels[] = $permissionOptions[$permission]['label'] ?? $permission;
    }

    if ($labels === []) {
        return 'Chưa cấp quyền';
    }

    $summary = implode(', ', array_slice($labels, 0, 2));
    if (count($labels) > 2) {
        $summary .= ' +' . (count($labels) - 2) . ' quyền';
    }

    return $summary;
}

render_admin_header('Quản lý user');
?>

<div class="space-y-8">
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[1.5rem] border border-[#d9e9de] bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-[#587766]">Tổng tài khoản</p>
            <p class="mt-3 text-3xl font-extrabold text-[#102118]"><?= clean((string)$stats['total']) ?></p>
            <p class="mt-2 text-sm text-[#6e8d7b]">Bao gồm tất cả admin và user đã đăng ký.</p>
        </article>
        <article class="rounded-[1.5rem] border border-[#d9e9de] bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-[#587766]">Đang hoạt động</p>
            <p class="mt-3 text-3xl font-extrabold text-[#2e9b63]"><?= clean((string)$stats['active']) ?></p>
            <p class="mt-2 text-sm text-[#6e8d7b]">Các tài khoản có thể đăng nhập bình thường.</p>
        </article>
        <article class="rounded-[1.5rem] border border-[#d9e9de] bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-[#587766]">Tài khoản admin</p>
            <p class="mt-3 text-3xl font-extrabold text-[#6b4eff]"><?= clean((string)$stats['admin']) ?></p>
            <p class="mt-2 text-sm text-[#6e8d7b]">Nhóm có quyền truy cập khu vực quản trị.</p>
        </article>
        <article class="rounded-[1.5rem] border border-[#d9e9de] bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-[#587766]">Đã khóa</p>
            <p class="mt-3 text-3xl font-extrabold text-[#b56a16]"><?= clean((string)$stats['inactive']) ?></p>
            <p class="mt-2 text-sm text-[#6e8d7b]">Tài khoản bị vô hiệu hóa tạm thời khỏi hệ thống.</p>
        </article>
    </section>

    <section class="grid items-start gap-8 xl:grid-cols-[minmax(0,1.2fr)_minmax(340px,0.8fr)]">
        <div class="space-y-6">
            <article class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Danh sách user</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-[#102118]">Theo dõi tài khoản trong admin</h2>
                        <p class="mt-2 text-sm text-[#6e8d7b]">Lọc nhanh theo vai trò, trạng thái và mở từng tài khoản để cập nhật thông tin.</p>
                    </div>
                    <a href="users.php" class="inline-flex items-center rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">
                        Xóa bộ lọc
                    </a>
                </div>

                <form method="GET" class="mt-6 grid gap-4 md:grid-cols-[1fr_220px_220px_auto]">
                    <div>
                        <label for="q" class="mb-2 block text-sm font-semibold text-[#102118]">Tìm tài khoản</label>
                        <input id="q" type="text" name="q" value="<?= clean($search) ?>" placeholder="Tên, email, username hoặc số điện thoại..." class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                    </div>
                    <div>
                        <label for="role" class="mb-2 block text-sm font-semibold text-[#102118]">Vai trò</label>
                        <select id="role" name="role" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                            <?php foreach ($roleOptions as $value => $label): ?>
                                <option value="<?= clean($value) ?>" <?= $roleFilter === $value ? 'selected' : '' ?>><?= clean($label) ?></option>
                            <?php endforeach; ?>
                        </select>
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
                                <th class="pb-3 pr-4 font-semibold">Tài khoản</th>
                                <th class="pb-3 pr-4 font-semibold">Vai trò</th>
                                <th class="pb-3 pr-4 font-semibold">Đơn hàng</th>
                                <th class="pb-3 pr-4 font-semibold">Chi tiêu</th>
                                <th class="pb-3 pr-4 font-semibold">Trạng thái</th>
                                <th class="pb-3 font-semibold">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-sm text-[#6e8d7b]">
                                        Không tìm thấy tài khoản nào phù hợp với bộ lọc hiện tại.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <?php
                                    $roleMeta = admin_user_role_meta((string)$user['role']);
                                    $statusMeta = admin_user_status_meta((string)$user['status']);
                                    $permissionSummary = admin_user_permission_summary($user, $permissionOptions);
                                    $editQuery = admin_users_query([
                                        'edit' => $user['id'],
                                        'q' => $search,
                                        'role' => $roleFilter,
                                        'status' => $statusFilter,
                                        'page' => $page,
                                    ]);
                                    $isCurrentRowUser = (int)$user['id'] === $currentUserId;
                                    $userClientPayload = [
                                        'id' => (string)$user['id'],
                                        'username' => (string)($user['username'] ?? ''),
                                        'email' => (string)($user['email'] ?? ''),
                                        'full_name' => (string)($user['full_name'] ?? ''),
                                        'phone' => (string)($user['phone'] ?? ''),
                                        'role' => (string)($user['role'] ?? 'user'),
                                        'admin_permissions' => normalize_admin_permissions($user['admin_permissions'] ?? []),
                                        'status' => (string)($user['status'] ?? 'active'),
                                        'display_name' => (string)(($user['full_name'] ?? '') !== '' ? $user['full_name'] : ($user['username'] ?? '')),
                                        'permission_summary' => (string)($permissionSummary ?? ''),
                                        'created_at' => format_date((string)$user['created_at'], 'd/m/Y H:i'),
                                        'updated_at' => format_date((string)$user['updated_at'], 'd/m/Y H:i'),
                                        'is_current_user' => $isCurrentRowUser,
                                        'profile_url' => '../profile.php',
                                    ];
                                    ?>
                                    <tr class="border-b border-[#f4f8f5] align-top last:border-b-0">
                                        <td class="py-4 pr-4">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <p class="font-semibold text-[#102118]"><?= clean($user['full_name'] ?: $user['username']) ?></p>
                                                    <?php if ($isCurrentRowUser): ?>
                                                        <span class="rounded-full bg-[#102118] px-3 py-1 text-xs font-semibold text-white">Bạn</span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="mt-1 text-xs text-[#6e8d7b]">@<?= clean($user['username']) ?></p>
                                                <p class="mt-1 text-sm text-[#102118]"><?= clean($user['email']) ?></p>
                                                <p class="mt-1 text-xs text-[#6e8d7b]"><?= clean($user['phone'] ?: 'Chưa cập nhật số điện thoại') ?></p>
                                            </div>
                                        </td>
                                        <td class="py-4 pr-4">
                                            <span class="rounded-full px-3 py-1 text-xs font-semibold <?= clean($roleMeta['class']) ?>">
                                                <?= clean($roleMeta['label']) ?>
                                            </span>
                                            <?php if ($permissionSummary !== null): ?>
                                                <p class="mt-2 text-xs text-[#6e8d7b]"><?= clean($permissionSummary) ?></p>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 pr-4 font-semibold text-[#102118]">
                                            <?= clean((string)$user['order_count']) ?>
                                            <p class="mt-1 text-xs font-normal text-[#6e8d7b]">Từ <?= format_date((string)$user['created_at'], 'd/m/Y') ?></p>
                                        </td>
                                        <td class="py-4 pr-4 font-semibold text-[#2e9b63]">
                                            <?= format_currency((float)$user['total_spent']) ?>
                                        </td>
                                        <td class="py-4 pr-4">
                                            <span class="rounded-full px-3 py-1 text-xs font-semibold <?= clean($statusMeta['class']) ?>">
                                                <?= clean($statusMeta['label']) ?>
                                            </span>
                                        </td>
                                        <td class="py-4">
                                            <button
                                                type="button"
                                                class="text-sm font-semibold text-[#2e9b63] hover:text-[#22784d]"
                                                data-edit-user="<?= clean(json_encode($userClientPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) ?>"
                                            >
                                                Chỉnh sửa
                                            </button>
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
                            Trang <?= clean((string)$page) ?> / <?= clean((string)$totalPages) ?>, tổng <?= clean((string)$totalUsers) ?> tài khoản
                        </p>
                        <div class="flex items-center gap-2">
                            <?php if ($page > 1): ?>
                                <a href="users.php<?= clean(admin_users_query(['q' => $search, 'role' => $roleFilter, 'status' => $statusFilter, 'page' => $page - 1])) ?>" class="inline-flex rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">Trang trước</a>
                            <?php endif; ?>
                            <?php if ($page < $totalPages): ?>
                                <a href="users.php<?= clean(admin_users_query(['q' => $search, 'role' => $roleFilter, 'status' => $statusFilter, 'page' => $page + 1])) ?>" class="inline-flex rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">Trang sau</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </article>
        </div>

        <div class="space-y-6">
            <article id="userFormCard" class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm lg:sticky lg:top-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p id="userFormModeLabel" class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]"><?= $editingUser ? 'Chỉnh sửa user' : 'Chi tiết user' ?></p>
                        <h2 id="userFormTitle" class="mt-2 text-2xl font-extrabold text-[#102118]"><?= $editingUser ? clean($editingUser['full_name'] ?: $editingUser['username']) : 'Chọn một tài khoản để chỉnh sửa' ?></h2>
                        <p id="userFormDescription" class="mt-2 text-sm text-[#6e8d7b]">
                            <?= $editingUser ? 'Cập nhật thông tin liên hệ, vai trò và trạng thái truy cập cho tài khoản này.' : 'Từ danh sách bên trái, bấm “Chỉnh sửa” để cập nhật vai trò, trạng thái hoặc thông tin liên hệ của user.' ?>
                        </p>
                    </div>
                    <button type="button" id="resetUserFormButton" class="inline-flex rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63] <?= $editingUser ? '' : 'hidden' ?>">Đóng</button>
                </div>

                <?php if (!empty($errors['general'])): ?>
                    <div id="userGeneralErrorBanner" class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <?= clean($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <div id="currentUserRestrictionsNotice" class="<?= $isEditingCurrentUser ? '' : 'hidden ' ?>mt-6 rounded-2xl border border-[#d9e9de] bg-[#f8fbf9] px-4 py-3 text-sm text-[#4c6a5b]">
                    Bạn đang chỉnh sửa tài khoản admin hiện tại. Hệ thống cho phép cập nhật thông tin cá nhân nhưng không cho tự đổi vai trò, tự khóa tài khoản hoặc tự thu hẹp quyền quản trị của chính mình.
                </div>

                <div id="userFormEmptyState" class="<?= $editingUser ? 'hidden ' : '' ?>mt-6 rounded-[1.5rem] border border-dashed border-[#d9e9de] bg-[#f8fbf9] px-5 py-8 text-sm leading-6 text-[#5f7b6c]">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Panel tài khoản</p>
                    <h3 class="mt-3 text-xl font-extrabold text-[#102118]">Mở một user để xem và chỉnh sửa tập trung</h3>
                    <p class="mt-3">
                        Khu này dành cho cập nhật hồ sơ, vai trò và trạng thái của tài khoản đang có. Để tạo người dùng mới, khách có thể đăng ký ở trang công khai rồi admin quay lại đây để phân quyền hoặc khóa/mở khi cần.
                    </p>
                </div>

                <form id="userAdminForm" method="POST" class="mt-6 space-y-6 <?= $editingUser ? '' : 'hidden' ?>">
                    <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                    <input type="hidden" name="action" value="update">
                    <input id="userIdInput" type="hidden" name="user_id" value="<?= $editingUser ? clean((string)$editingUser['id']) : '' ?>">

                    <section class="rounded-[1.5rem] border border-[#edf4ef] bg-[#fcfefd] p-5">
                        <div class="flex flex-col gap-2">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Thông tin tài khoản</p>
                            <h3 class="text-lg font-extrabold text-[#102118]">Hồ sơ và cách liên hệ</h3>
                            <p class="text-sm text-[#5f7b6c]">Nhóm các trường định danh vào cùng một nơi để dễ đối chiếu trước khi lưu.</p>
                        </div>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="full_name" class="mb-2 block text-sm font-semibold text-[#102118]">Họ tên</label>
                                <input id="full_name" type="text" name="full_name" value="<?= clean($formData['full_name']) ?>" class="w-full rounded-2xl border <?= isset($errors['full_name']) ? 'border-red-300' : 'border-[#d9e9de]' ?> px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="Nhập họ tên người dùng">
                                <?php if (isset($errors['full_name'])): ?>
                                    <p class="mt-2 text-sm text-red-600"><?= clean($errors['full_name']) ?></p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <label for="username" class="mb-2 block text-sm font-semibold text-[#102118]">Tên đăng nhập</label>
                                <input id="username" type="text" name="username" value="<?= clean($formData['username']) ?>" class="w-full rounded-2xl border <?= isset($errors['username']) ? 'border-red-300' : 'border-[#d9e9de]' ?> px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="Ví dụ: nguyenvana">
                                <?php if (isset($errors['username'])): ?>
                                    <p class="mt-2 text-sm text-red-600"><?= clean($errors['username']) ?></p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <label for="email" class="mb-2 block text-sm font-semibold text-[#102118]">Email</label>
                                <input id="email" type="email" name="email" value="<?= clean($formData['email']) ?>" class="w-full rounded-2xl border <?= isset($errors['email']) ? 'border-red-300' : 'border-[#d9e9de]' ?> px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="user@example.com">
                                <?php if (isset($errors['email'])): ?>
                                    <p class="mt-2 text-sm text-red-600"><?= clean($errors['email']) ?></p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <label for="phone" class="mb-2 block text-sm font-semibold text-[#102118]">Số điện thoại</label>
                                <input id="phone" type="text" name="phone" value="<?= clean($formData['phone']) ?>" class="w-full rounded-2xl border <?= isset($errors['phone']) ? 'border-red-300' : 'border-[#d9e9de]' ?> px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="0901234567">
                                <?php if (isset($errors['phone'])): ?>
                                    <p class="mt-2 text-sm text-red-600"><?= clean($errors['phone']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[1.5rem] border border-[#edf4ef] bg-white p-5">
                        <div class="flex flex-col gap-2">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Quyền truy cập</p>
                            <h3 class="text-lg font-extrabold text-[#102118]">Vai trò, trạng thái và phạm vi quản trị</h3>
                            <p class="text-sm text-[#5f7b6c]">Phần này gom toàn bộ quyền hạn vào một chỗ để tránh sửa sót hoặc bỏ quên trạng thái tài khoản.</p>
                        </div>

                        <div class="mt-5 space-y-5">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="role_form" class="mb-2 block text-sm font-semibold text-[#102118]">Vai trò</label>
                                    <input id="userRoleHiddenInput" type="hidden" name="<?= $isEditingCurrentUser ? 'role' : '' ?>" value="<?= $isEditingCurrentUser ? clean($formData['role']) : '' ?>">
                                    <select id="role_form" name="<?= $isEditingCurrentUser ? 'role_display' : 'role' ?>" <?= $isEditingCurrentUser ? 'disabled' : '' ?> class="w-full rounded-2xl border <?= isset($errors['role']) ? 'border-red-300' : 'border-[#d9e9de]' ?> px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63] disabled:cursor-not-allowed disabled:bg-[#f4f7f5] disabled:text-[#6e8d7b]">
                                        <option value="user" <?= $formData['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                        <option value="admin" <?= $formData['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                    <?php if (isset($errors['role'])): ?>
                                        <p class="mt-2 text-sm text-red-600"><?= clean($errors['role']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <label for="status_form" class="mb-2 block text-sm font-semibold text-[#102118]">Trạng thái</label>
                                    <input id="userStatusHiddenInput" type="hidden" name="<?= $isEditingCurrentUser ? 'status' : '' ?>" value="<?= $isEditingCurrentUser ? clean($formData['status']) : '' ?>">
                                    <select id="status_form" name="<?= $isEditingCurrentUser ? 'status_display' : 'status' ?>" <?= $isEditingCurrentUser ? 'disabled' : '' ?> class="w-full rounded-2xl border <?= isset($errors['status']) ? 'border-red-300' : 'border-[#d9e9de]' ?> px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63] disabled:cursor-not-allowed disabled:bg-[#f4f7f5] disabled:text-[#6e8d7b]">
                                        <option value="active" <?= $formData['status'] === 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
                                        <option value="inactive" <?= $formData['status'] === 'inactive' ? 'selected' : '' ?>>Đã khóa</option>
                                    </select>
                                    <?php if (isset($errors['status'])): ?>
                                        <p class="mt-2 text-sm text-red-600"><?= clean($errors['status']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div id="admin_permissions_panel" class="<?= $formData['role'] === 'admin' ? '' : 'hidden ' ?>rounded-[1.5rem] border border-[#d9e9de] bg-[#f8fbf9] p-5">
                                <div class="flex flex-col gap-2">
                                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Admin phụ</p>
                                    <h3 class="text-lg font-extrabold text-[#102118]">Quyền chi tiết theo module</h3>
                                    <p class="text-sm text-[#5f7b6c]">
                                        Chỉ khi tick "Toàn quyền quản trị" thì tài khoản admin này mới có full quyền. Nếu không tick gì thì tài khoản sẽ không có quyền quản trị module nào.
                                    </p>
                                </div>

                                <div class="mt-4 grid gap-3">
                                    <?php foreach ($permissionOptions as $permission => $meta): ?>
                                        <label class="flex items-start gap-3 rounded-2xl border border-[#d9e9de] bg-white px-4 py-4">
                                            <input
                                                type="checkbox"
                                                data-admin-permission-checkbox
                                                name="admin_permissions[]"
                                                value="<?= clean($permission) ?>"
                                                <?= in_array($permission, $formData['admin_permissions'], true) ? 'checked' : '' ?>
                                                <?= $isEditingCurrentUser ? 'disabled' : '' ?>
                                                class="mt-1 rounded border-[#cfe0d5] text-[#2e9b63] focus:ring-[#2e9b63]"
                                            >
                                            <span>
                                                <span class="block text-sm font-semibold text-[#102118]"><?= clean($meta['label']) ?></span>
                                                <span class="mt-1 block text-sm text-[#5f7b6c]"><?= clean($meta['description']) ?></span>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                                <div id="adminPermissionsHiddenInputs">
                                    <?php if ($isEditingCurrentUser): ?>
                                        <?php foreach ($formData['admin_permissions'] as $permission): ?>
                                            <input type="hidden" name="admin_permissions[]" value="<?= clean($permission) ?>">
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <?php if (isset($errors['admin_permissions'])): ?>
                                    <p class="mt-3 text-sm text-red-600"><?= clean($errors['admin_permissions']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>

                    <section id="userMetaInfoBox" class="rounded-[1.5rem] border border-[#edf4ef] bg-[#f8fbf9] p-5 text-sm text-[#4c6a5b]">
                        <div class="flex flex-col gap-2">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Thông tin hệ thống</p>
                            <h3 class="text-lg font-extrabold text-[#102118]">Dấu mốc và tóm tắt quyền</h3>
                        </div>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl border border-[#e2ede5] bg-white px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#6e8d7b]">Tạo lúc</p>
                                <p id="userCreatedAtValue" class="mt-2 text-sm font-semibold text-[#102118]"><?= $editingUser ? clean(format_date((string)$editingUser['created_at'], 'd/m/Y H:i')) : '' ?></p>
                            </div>
                            <div class="rounded-2xl border border-[#e2ede5] bg-white px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#6e8d7b]">Cập nhật gần nhất</p>
                                <p id="userUpdatedAtValue" class="mt-2 text-sm font-semibold text-[#102118]"><?= $editingUser ? clean(format_date((string)$editingUser['updated_at'], 'd/m/Y H:i')) : '' ?></p>
                            </div>
                        </div>
                        <div id="userPermissionSummaryRow" class="mt-4 rounded-2xl border border-[#e2ede5] bg-white px-4 py-4 <?= $editingUser && ($formData['role'] ?? 'user') === 'admin' ? '' : 'hidden' ?>">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#6e8d7b]">Phạm vi admin</p>
                            <p id="userPermissionSummaryValue" class="mt-2 text-sm font-semibold text-[#102118]"><?= clean($initialUserPermissionSummary ?? '') ?></p>
                        </div>
                    </section>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <button id="userFormSubmitButton" type="submit" class="inline-flex items-center rounded-full bg-[#102118] px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#1f3b2d]">
                            Lưu thay đổi
                        </button>
                        <a id="userProfileLink" href="../profile.php" class="inline-flex items-center rounded-full border border-[#d9e9de] px-5 py-3 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63] <?= $isEditingCurrentUser ? '' : 'hidden' ?>">
                            Mở hồ sơ cá nhân
                        </a>
                    </div>
                </form>
            </article>

            <article class="rounded-[1.75rem] border border-[#d9e9de] bg-[#102118] p-6 text-white shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-white/65">Lưu ý quản trị</p>
                <h3 class="mt-2 text-xl font-extrabold">Giữ quyền admin an toàn</h3>
                <p class="mt-3 text-sm leading-6 text-white/80">
                    Hệ thống chặn việc tự khóa tài khoản admin đang đăng nhập và cũng không cho phép xóa quyền của admin cuối cùng đang hoạt động, để tránh mất quyền truy cập khu vực quản trị.
                </p>
            </article>
        </div>
    </section>
</div>

<script>
const userFormCard = document.getElementById('userFormCard');
const userAdminForm = document.getElementById('userAdminForm');
const userFormModeLabel = document.getElementById('userFormModeLabel');
const userFormTitle = document.getElementById('userFormTitle');
const userFormDescription = document.getElementById('userFormDescription');
const userIdInput = document.getElementById('userIdInput');
const resetUserFormButton = document.getElementById('resetUserFormButton');
const userFormEmptyState = document.getElementById('userFormEmptyState');
const userGeneralErrorBanner = document.getElementById('userGeneralErrorBanner');
const currentUserRestrictionsNotice = document.getElementById('currentUserRestrictionsNotice');
const userRoleHiddenInput = document.getElementById('userRoleHiddenInput');
const userStatusHiddenInput = document.getElementById('userStatusHiddenInput');
const userMetaInfoBox = document.getElementById('userMetaInfoBox');
const userCreatedAtValue = document.getElementById('userCreatedAtValue');
const userUpdatedAtValue = document.getElementById('userUpdatedAtValue');
const userPermissionSummaryRow = document.getElementById('userPermissionSummaryRow');
const userPermissionSummaryValue = document.getElementById('userPermissionSummaryValue');
const userProfileLink = document.getElementById('userProfileLink');
const adminRoleSelect = document.getElementById('role_form');
const adminStatusSelect = document.getElementById('status_form');
const adminPermissionsPanel = document.getElementById('admin_permissions_panel');
const adminPermissionCheckboxes = Array.from(document.querySelectorAll('[data-admin-permission-checkbox]'));
const fullAccessCheckbox = adminPermissionCheckboxes.find((input) => input.value === 'admin.full_access') || null;
const granularPermissionCheckboxes = adminPermissionCheckboxes.filter((input) => input.value !== 'admin.full_access');
const adminPermissionsHiddenInputs = document.getElementById('adminPermissionsHiddenInputs');
const defaultUserFormState = <?= json_encode($defaultUserFormState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const initialUserFormState = <?= json_encode($initialUserFormState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const userListStateUrl = <?= json_encode($userListStateUrl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
let userPermissionsLocked = Boolean(initialUserFormState.id && initialUserFormState.is_current_user);

const syncLockedField = (field, hiddenField, locked, fieldName) => {
    if (!field || !hiddenField) {
        return;
    }

    if (locked) {
        field.disabled = true;
        field.name = `${fieldName}_display`;
        hiddenField.name = fieldName;
        hiddenField.value = field.value;
        return;
    }

    field.disabled = false;
    field.name = fieldName;
    hiddenField.name = '';
    hiddenField.value = '';
};

const syncAdminPermissionHiddenInputs = () => {
    if (!adminPermissionsHiddenInputs) {
        return;
    }

    adminPermissionsHiddenInputs.innerHTML = '';

    if (!userPermissionsLocked) {
        return;
    }

    adminPermissionCheckboxes
        .filter((input) => input.checked)
        .forEach((input) => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'admin_permissions[]';
            hiddenInput.value = input.value;
            adminPermissionsHiddenInputs.appendChild(hiddenInput);
        });
};

const syncAdminPermissionsPanel = () => {
    if (!adminRoleSelect || !adminPermissionsPanel) {
        return;
    }

    adminPermissionsPanel.classList.toggle('hidden', adminRoleSelect.value !== 'admin');
};

const syncAdminPermissionCheckboxes = () => {
    if (userPermissionsLocked) {
        adminPermissionCheckboxes.forEach((input) => {
            input.disabled = true;
        });
        syncAdminPermissionHiddenInputs();
        return;
    }

    if (fullAccessCheckbox) {
        const hasFullAccess = fullAccessCheckbox.checked;
        granularPermissionCheckboxes.forEach((input) => {
            input.disabled = hasFullAccess;
            if (hasFullAccess) {
                input.checked = false;
            }
        });
    }

    syncAdminPermissionHiddenInputs();
};

const populateUserForm = (state, { updateHistory = true } = {}) => {
    if (!userAdminForm) {
        return;
    }

    const normalizedState = {
        ...defaultUserFormState,
        ...state,
        admin_permissions: Array.isArray(state?.admin_permissions) ? state.admin_permissions : [],
    };
    const isEditMode = Boolean(normalizedState.id);
    userPermissionsLocked = Boolean(isEditMode && normalizedState.is_current_user);

    if (userGeneralErrorBanner) {
        userGeneralErrorBanner.classList.add('hidden');
    }

    userFormModeLabel.textContent = isEditMode ? 'Chỉnh sửa user' : 'Chi tiết user';
    userFormTitle.textContent = isEditMode
        ? (normalizedState.display_name || normalizedState.username || 'Cập nhật tài khoản')
        : 'Chọn một tài khoản để chỉnh sửa';
    userFormDescription.textContent = isEditMode
        ? 'Cập nhật thông tin liên hệ, vai trò và trạng thái truy cập cho tài khoản này.'
        : 'Từ danh sách bên trái, bấm “Chỉnh sửa” để cập nhật vai trò, trạng thái hoặc thông tin liên hệ của user.';

    resetUserFormButton?.classList.toggle('hidden', !isEditMode);
    userFormEmptyState?.classList.toggle('hidden', isEditMode);
    userAdminForm.classList.toggle('hidden', !isEditMode);
    currentUserRestrictionsNotice?.classList.toggle('hidden', !userPermissionsLocked);
    userMetaInfoBox?.classList.toggle('hidden', !isEditMode);

    if (userIdInput) {
        userIdInput.value = isEditMode ? String(normalizedState.id) : '';
    }

    ['full_name', 'username', 'email', 'phone'].forEach((fieldName) => {
        const field = userAdminForm.elements.namedItem(fieldName);
        if (field) {
            field.value = normalizedState[fieldName] ?? '';
        }
    });

    if (adminRoleSelect) {
        adminRoleSelect.value = normalizedState.role || 'user';
    }

    if (adminStatusSelect) {
        adminStatusSelect.value = normalizedState.status || 'active';
    }

    syncLockedField(adminRoleSelect, userRoleHiddenInput, userPermissionsLocked, 'role');
    syncLockedField(adminStatusSelect, userStatusHiddenInput, userPermissionsLocked, 'status');

    adminPermissionCheckboxes.forEach((input) => {
        input.checked = normalizedState.role === 'admin' && normalizedState.admin_permissions.includes(input.value);
    });

    syncAdminPermissionsPanel();
    syncAdminPermissionCheckboxes();

    if (userCreatedAtValue) {
        userCreatedAtValue.textContent = normalizedState.created_at || '';
    }

    if (userUpdatedAtValue) {
        userUpdatedAtValue.textContent = normalizedState.updated_at || '';
    }

    if (userPermissionSummaryValue) {
        userPermissionSummaryValue.textContent = normalizedState.permission_summary || '';
    }

    userPermissionSummaryRow?.classList.toggle('hidden', !(isEditMode && normalizedState.role === 'admin' && normalizedState.permission_summary));

    if (userProfileLink) {
        userProfileLink.classList.toggle('hidden', !userPermissionsLocked);
        userProfileLink.href = normalizedState.profile_url || '../profile.php';
    }

    if (updateHistory) {
        const separator = userListStateUrl.includes('?') ? '&' : '?';
        const nextUrl = isEditMode
            ? `${userListStateUrl}${separator}edit=${encodeURIComponent(String(normalizedState.id))}`
            : userListStateUrl;
        window.history.pushState({ userState: normalizedState }, '', nextUrl);
    }

    userFormCard?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

document.querySelectorAll('[data-edit-user]').forEach((button) => {
    button.addEventListener('click', () => {
        const userState = JSON.parse(button.dataset.editUser || '{}');
        populateUserForm(userState, { updateHistory: true });
    });
});

adminRoleSelect?.addEventListener('change', syncAdminPermissionsPanel);
fullAccessCheckbox?.addEventListener('change', syncAdminPermissionCheckboxes);
resetUserFormButton?.addEventListener('click', () => {
    populateUserForm(defaultUserFormState, { updateHistory: true });
});
window.addEventListener('popstate', (event) => {
    populateUserForm(event.state?.userState || initialUserFormState || defaultUserFormState, { updateHistory: false });
});
syncAdminPermissionsPanel();
syncAdminPermissionCheckboxes();
</script>

<?php render_admin_footer(); ?>
