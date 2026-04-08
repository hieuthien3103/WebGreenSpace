<?php
require_once __DIR__ . '/bootstrap.php';
require_admin_permission('users.manage', 'users.php');

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

$userModel = new User();
$permissionOptions = admin_permission_catalog();

$roleOptions = [
    'all' => 'Tất cả vai trò',
    'admin' => 'Admin',
    'user' => 'User',
];

$statusOptions = [
    'all' => 'Tất cả trạng thái',
    'active' => 'Đang hoạt động',
    'inactive' => 'Đã khóa',
];

$search = trim((string)($_GET['q'] ?? ''));
$roleFilter = (string)($_GET['role'] ?? 'all');
$statusFilter = (string)($_GET['status'] ?? 'all');
$page = max(1, (int)($_GET['page'] ?? 1));
$editId = max(0, (int)($_GET['edit'] ?? 0));
$perPage = ADMIN_ITEMS_PER_PAGE;
$currentUserId = (int)(get_user_id() ?? 0);

if (!isset($roleOptions[$roleFilter])) {
    $roleFilter = 'all';
}

if (!isset($statusOptions[$statusFilter])) {
    $statusFilter = 'all';
}

$errors = [];
$formData = admin_user_defaults();
$editingUser = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        set_flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
        redirect('users.php');
    }

    $action = (string)($_POST['action'] ?? '');

    if ($action === 'update') {
        $editId = max(0, (int)($_POST['user_id'] ?? 0));
        $editingUser = $userModel->getAdminById($editId);

        if (!$editingUser) {
            set_flash('error', 'Không tìm thấy tài khoản cần cập nhật.');
            redirect('users.php');
        }

        $formData = admin_collect_user_form_data($_POST);

        if ($formData['full_name'] === '') {
            $errors['full_name'] = 'Họ tên không được để trống.';
        } elseif (string_length($formData['full_name']) < 2) {
            $errors['full_name'] = 'Họ tên cần ít nhất 2 ký tự.';
        }

        if ($formData['username'] === '') {
            $errors['username'] = 'Vui lòng nhập tên đăng nhập.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{4,30}$/', $formData['username'])) {
            $errors['username'] = 'Tên đăng nhập gồm 4-30 ký tự, chỉ dùng chữ, số hoặc dấu gạch dưới.';
        } elseif ($userModel->usernameExists($formData['username'], $editId)) {
            $errors['username'] = 'Tên đăng nhập này đã tồn tại.';
        }

        if ($formData['email'] === '') {
            $errors['email'] = 'Vui lòng nhập email.';
        } elseif (!is_valid_email($formData['email'])) {
            $errors['email'] = 'Email không hợp lệ.';
        } elseif ($userModel->emailExists($formData['email'], $editId)) {
            $errors['email'] = 'Email này đã được sử dụng.';
        }

        if ($formData['phone'] !== '' && !preg_match('/^[0-9+\s.-]{8,20}$/', $formData['phone'])) {
            $errors['phone'] = 'Số điện thoại không hợp lệ.';
        }

        if (!in_array($formData['role'], ['admin', 'user'], true)) {
            $errors['role'] = 'Vai trò không hợp lệ.';
        }

        if ($formData['role'] !== 'admin') {
            $formData['admin_permissions'] = [];
        }

        foreach ($formData['admin_permissions'] as $permission) {
            if (!array_key_exists($permission, $permissionOptions)) {
                $errors['admin_permissions'] = 'Danh sách quyền admin phụ không hợp lệ.';
                break;
            }
        }

        if (in_array('admin.full_access', $formData['admin_permissions'], true)) {
            $formData['admin_permissions'] = ['admin.full_access'];
        }

        if (!in_array($formData['status'], ['active', 'inactive'], true)) {
            $errors['status'] = 'Trạng thái không hợp lệ.';
        }

        if ($editId === $currentUserId) {
            if ($formData['role'] !== (string)$editingUser['role']) {
                $errors['role'] = 'Bạn không thể tự đổi vai trò của chính mình tại trang này.';
            }

            if ($formData['status'] !== (string)$editingUser['status']) {
                $errors['status'] = 'Bạn không thể tự khóa tài khoản admin đang đăng nhập.';
            }

            if ($formData['admin_permissions'] !== normalize_admin_permissions($editingUser['admin_permissions'] ?? [])) {
                $errors['admin_permissions'] = 'Bạn không thể tự thay đổi phạm vi quyền admin của chính mình tại trang này.';
            }
        }

        $removingAdminAccess = ($editingUser['role'] ?? 'user') === 'admin'
            && ($editingUser['status'] ?? 'inactive') === 'active'
            && ($formData['role'] !== 'admin' || $formData['status'] !== 'active');

        if ($removingAdminAccess && $userModel->countByRoleAndStatus('admin', 'active', $editId) === 0) {
            $errors['general'] = 'Hệ thống cần ít nhất một tài khoản admin đang hoạt động.';
        }

        if (empty($errors)) {
            $updated = $userModel->updateAdminUser($editId, [
                'username' => $formData['username'],
                'email' => $formData['email'],
                'full_name' => $formData['full_name'],
                'phone' => $formData['phone'],
                'role' => $formData['role'],
                'admin_permissions' => $formData['admin_permissions'],
                'status' => $formData['status'],
            ]);

            if ($updated) {
                if ($editId === $currentUserId) {
                    $freshUser = $userModel->findById($editId);
                    if ($freshUser) {
                        $_SESSION['user_role'] = $freshUser['role'] ?? 'user';
                        $_SESSION['user_data'] = $userModel->withoutPassword($freshUser);
                    }
                }

                set_flash('success', 'Đã cập nhật thông tin tài khoản.');
                redirect('users.php' . admin_users_query([
                    'edit' => $editId,
                    'q' => $search,
                    'role' => $roleFilter,
                    'status' => $statusFilter,
                    'page' => $page,
                ]));
            }

            $errors['general'] = 'Không thể cập nhật tài khoản lúc này.';
        }
    }
}

if ($editId > 0 && !$editingUser) {
    $editingUser = $userModel->getAdminById($editId);

    if (!$editingUser) {
        set_flash('error', 'Không tìm thấy tài khoản cần chỉnh sửa.');
        redirect('users.php');
    }

    $formData = [
        'username' => (string)($editingUser['username'] ?? ''),
        'email' => (string)($editingUser['email'] ?? ''),
        'full_name' => (string)($editingUser['full_name'] ?? ''),
        'phone' => (string)($editingUser['phone'] ?? ''),
        'role' => (string)($editingUser['role'] ?? 'user'),
        'admin_permissions' => normalize_admin_permissions($editingUser['admin_permissions'] ?? []),
        'status' => (string)($editingUser['status'] ?? 'active'),
    ];
}

$stats = [
    'total' => $userModel->getAdminTotal('', 'all', 'all'),
    'active' => $userModel->getAdminTotal('', 'all', 'active'),
    'admin' => $userModel->getAdminTotal('', 'admin', 'all'),
    'inactive' => $userModel->getAdminTotal('', 'all', 'inactive'),
];

$totalUsers = $userModel->getAdminTotal($search, $roleFilter, $statusFilter);
$totalPages = max(1, (int)ceil($totalUsers / $perPage));

if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $perPage;
$users = $userModel->getAdminList($search, $roleFilter, $statusFilter, $perPage, $offset);
$isEditingCurrentUser = $editingUser && (int)$editingUser['id'] === $currentUserId;

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
                                            <a href="users.php<?= clean($editQuery) ?>" class="text-sm font-semibold text-[#2e9b63] hover:text-[#22784d]">
                                                Chỉnh sửa
                                            </a>
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
            <article class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm lg:sticky lg:top-8">
                <?php if ($editingUser): ?>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Chỉnh sửa user</p>
                            <h2 class="mt-2 text-2xl font-extrabold text-[#102118]"><?= clean($editingUser['full_name'] ?: $editingUser['username']) ?></h2>
                            <p class="mt-2 text-sm text-[#6e8d7b]">
                                Cập nhật thông tin liên hệ, vai trò và trạng thái truy cập cho tài khoản này.
                            </p>
                        </div>
                        <a href="users.php" class="inline-flex rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">Đóng</a>
                    </div>

                    <?php if (!empty($errors['general'])): ?>
                        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                            <?= clean($errors['general']) ?>
                        </div>
                    <?php elseif ($isEditingCurrentUser): ?>
                        <div class="mt-6 rounded-2xl border border-[#d9e9de] bg-[#f8fbf9] px-4 py-3 text-sm text-[#4c6a5b]">
                            Bạn đang chỉnh sửa tài khoản admin hiện tại. Hệ thống cho phép cập nhật thông tin cá nhân nhưng không cho tự đổi vai trò, tự khóa tài khoản hoặc tự thu hẹp quyền quản trị của chính mình.
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="mt-6 space-y-5">
                        <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="user_id" value="<?= clean((string)$editingUser['id']) ?>">

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

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="role_form" class="mb-2 block text-sm font-semibold text-[#102118]">Vai trò</label>
                                <?php if ($isEditingCurrentUser): ?>
                                    <input type="hidden" name="role" value="<?= clean($formData['role']) ?>">
                                <?php endif; ?>
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
                                <?php if ($isEditingCurrentUser): ?>
                                    <input type="hidden" name="status" value="<?= clean($formData['status']) ?>">
                                <?php endif; ?>
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

                            <?php if ($isEditingCurrentUser): ?>
                                <div class="mt-4 rounded-2xl border border-[#d9e9de] bg-white px-4 py-3 text-sm text-[#4c6a5b]">
                                    Bạn không thể tự thay đổi phạm vi quyền admin của mình tại màn này.
                                </div>
                                <?php foreach ($formData['admin_permissions'] as $permission): ?>
                                    <input type="hidden" name="admin_permissions[]" value="<?= clean($permission) ?>">
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="mt-4 grid gap-3">
                                    <?php foreach ($permissionOptions as $permission => $meta): ?>
                                        <label class="flex items-start gap-3 rounded-2xl border border-[#d9e9de] bg-white px-4 py-4">
                                            <input type="checkbox" name="admin_permissions[]" value="<?= clean($permission) ?>" <?= in_array($permission, $formData['admin_permissions'], true) ? 'checked' : '' ?> class="mt-1 rounded border-[#cfe0d5] text-[#2e9b63] focus:ring-[#2e9b63]">
                                            <span>
                                                <span class="block text-sm font-semibold text-[#102118]"><?= clean($meta['label']) ?></span>
                                                <span class="mt-1 block text-sm text-[#5f7b6c]"><?= clean($meta['description']) ?></span>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($errors['admin_permissions'])): ?>
                                <p class="mt-3 text-sm text-red-600"><?= clean($errors['admin_permissions']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="rounded-[1.25rem] border border-[#edf4ef] bg-[#f8fbf9] px-4 py-4 text-sm text-[#4c6a5b]">
                            <p class="font-semibold text-[#102118]">Thông tin thêm</p>
                            <p class="mt-2">Tạo lúc: <?= format_date((string)$editingUser['created_at'], 'd/m/Y H:i') ?></p>
                            <p class="mt-1">Cập nhật gần nhất: <?= format_date((string)$editingUser['updated_at'], 'd/m/Y H:i') ?></p>
                            <?php if (($editingUser['role'] ?? 'user') === 'admin'): ?>
                                <p class="mt-1">Phạm vi admin: <?= clean(admin_user_permission_summary($editingUser, $permissionOptions) ?? 'Toàn quyền quản trị') ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <button type="submit" class="inline-flex items-center rounded-full bg-[#102118] px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#1f3b2d]">
                                Lưu thay đổi
                            </button>
                            <?php if ($isEditingCurrentUser): ?>
                                <a href="../profile.php" class="inline-flex items-center rounded-full border border-[#d9e9de] px-5 py-3 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">
                                    Mở hồ sơ cá nhân
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Chi tiết user</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-[#102118]">Chọn một tài khoản để chỉnh sửa</h2>
                    <p class="mt-2 text-sm text-[#6e8d7b]">
                        Từ danh sách bên trái, bấm “Chỉnh sửa” để cập nhật vai trò, trạng thái hoặc thông tin liên hệ của user.
                    </p>

                    <div class="mt-6 rounded-[1.5rem] border border-dashed border-[#d9e9de] bg-[#f8fbf9] px-5 py-8 text-sm leading-6 text-[#5f7b6c]">
                        Trang này ưu tiên quản lý tài khoản hiện có trong hệ thống. Để tạo user mới, người dùng có thể đăng ký ở trang công khai, sau đó admin quay lại đây để phân quyền hoặc khóa/mở tài khoản khi cần.
                    </div>
                <?php endif; ?>
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
const adminRoleSelect = document.getElementById('role_form');
const adminPermissionsPanel = document.getElementById('admin_permissions_panel');
const fullAccessCheckbox = document.querySelector('input[name="admin_permissions[]"][value="admin.full_access"]');
const granularPermissionCheckboxes = Array.from(document.querySelectorAll('input[name="admin_permissions[]"]')).filter((input) => input.value !== 'admin.full_access');

const syncAdminPermissionsPanel = () => {
    if (!adminRoleSelect || !adminPermissionsPanel) {
        return;
    }

    adminPermissionsPanel.classList.toggle('hidden', adminRoleSelect.value !== 'admin');
};

const syncAdminPermissionCheckboxes = () => {
    if (!fullAccessCheckbox) {
        return;
    }

    const hasFullAccess = fullAccessCheckbox.checked;
    granularPermissionCheckboxes.forEach((input) => {
        input.disabled = hasFullAccess;
        if (hasFullAccess) {
            input.checked = false;
        }
    });
};

adminRoleSelect?.addEventListener('change', syncAdminPermissionsPanel);
fullAccessCheckbox?.addEventListener('change', syncAdminPermissionCheckboxes);
syncAdminPermissionsPanel();
syncAdminPermissionCheckboxes();
</script>

<?php render_admin_footer(); ?>
