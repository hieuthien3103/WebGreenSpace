<?php
if (empty($GLOBALS['mvc_template_rendering'])) {
    require_once __DIR__ . '/../../config/config.php';
    (new AdminPageController())->contacts()->send();
    return;
}

require_once __DIR__ . '/bootstrap.php';

function admin_contacts_query(array $params): string {
    $filtered = [];

    foreach ($params as $key => $value) {
        if ($value === null || $value === '' || $value === 'all') {
            continue;
        }

        if ($key === 'page' && (int)$value <= 1) {
            continue;
        }

        if ($key === 'view' && (int)$value <= 0) {
            continue;
        }

        $filtered[$key] = $value;
    }

    $query = http_build_query($filtered);
    return $query !== '' ? '?' . $query : '';
}

function admin_contact_render_state_fields(array $params): void {
    foreach ($params as $key => $value) {
        if ($value === null || $value === '') {
            continue;
        }

        echo '<input type="hidden" name="' . clean((string)$key) . '" value="' . clean((string)$value) . '">';
    }
}

function admin_contact_status_meta(string $status): array {
    return match ($status) {
        'resolved' => ['label' => 'Đã xử lý', 'class' => 'bg-[#edf8f1] text-[#2e9b63]'],
        'in_progress' => ['label' => 'Đang xử lý', 'class' => 'bg-[#eef4ff] text-[#3758c7]'],
        default => ['label' => 'Mới', 'class' => 'bg-[#fff7e8] text-[#b7791f]'],
    };
}

render_admin_header('Liên hệ khách hàng');
?>

<div class="space-y-8">
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[1.5rem] border border-[#d9e9de] bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-[#587766]">Tổng liên hệ</p>
            <p class="mt-3 text-3xl font-extrabold text-[#102118]"><?= clean((string)$stats['total_messages']) ?></p>
            <p class="mt-2 text-sm text-[#6e8d7b]">Toàn bộ biểu mẫu khách đã gửi từ trang liên hệ.</p>
        </article>
        <article class="rounded-[1.5rem] border border-[#d9e9de] bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-[#587766]">Chưa đọc</p>
            <p class="mt-3 text-3xl font-extrabold text-[#b7791f]"><?= clean((string)$stats['unread_messages']) ?></p>
            <p class="mt-2 text-sm text-[#6e8d7b]">Ưu tiên mở và phân loại sớm cho đội hỗ trợ.</p>
        </article>
        <article class="rounded-[1.5rem] border border-[#d9e9de] bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-[#587766]">Đang xử lý</p>
            <p class="mt-3 text-3xl font-extrabold text-[#3758c7]"><?= clean((string)$stats['in_progress_messages']) ?></p>
            <p class="mt-2 text-sm text-[#6e8d7b]">Các liên hệ đã nhận và đang follow-up.</p>
        </article>
        <article class="rounded-[1.5rem] border border-[#d9e9de] bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-[#587766]">Đã xử lý</p>
            <p class="mt-3 text-3xl font-extrabold text-[#2e9b63]"><?= clean((string)$stats['resolved_messages']) ?></p>
            <p class="mt-2 text-sm text-[#6e8d7b]">Những liên hệ đã chốt tư vấn hoặc phản hồi xong.</p>
        </article>
    </section>

    <section class="grid gap-8 xl:grid-cols-[0.95fr_1.05fr]">
        <div class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Hộp thư</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-[#102118]">Danh sách liên hệ</h2>
                </div>
                <p class="text-sm text-[#6e8d7b]">Kết quả hiện tại: <strong class="text-[#102118]"><?= clean((string)$totalMessages) ?></strong> liên hệ</p>
            </div>

            <form method="GET" class="mt-6 grid gap-4 lg:grid-cols-[1.2fr_0.8fr_auto]">
                <div class="space-y-2">
                    <label for="q" class="text-sm font-semibold text-[#102118]">Tìm theo tên, email, SĐT, tiêu đề</label>
                    <input id="q" name="q" type="text" value="<?= clean($search) ?>" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="Nguyễn Văn A, contact@...">
                </div>
                <div class="space-y-2">
                    <label for="status" class="text-sm font-semibold text-[#102118]">Trạng thái</label>
                    <select id="status" name="status" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                        <?php foreach ($statusOptions as $value => $label): ?>
                            <option value="<?= clean($value) ?>" <?= $statusFilter === $value ? 'selected' : '' ?>><?= clean($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#102118] px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#1f3b2d]">
                    Lọc
                </button>
            </form>

            <?php if (empty($messages)): ?>
                <div class="mt-6 rounded-[1.5rem] border border-dashed border-[#d9e9de] px-6 py-12 text-center text-sm text-[#6e8d7b]">
                    Chưa có liên hệ nào phù hợp bộ lọc hiện tại.
                </div>
            <?php else: ?>
                <div class="mt-6 space-y-4">
                    <?php foreach ($messages as $message): ?>
                        <?php $statusMeta = admin_contact_status_meta((string)$message['status']); ?>
                        <article class="rounded-[1.5rem] border <?= empty($message['is_read']) ? 'border-[#cde4d4] bg-[#f8fbf9]' : 'border-[#edf4ef] bg-white' ?> p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <p class="text-base font-bold text-[#102118]"><?= clean($message['full_name']) ?></p>
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold <?= clean($statusMeta['class']) ?>"><?= clean($statusMeta['label']) ?></span>
                                        <?php if (empty($message['is_read'])): ?>
                                            <span class="rounded-full bg-[#102118] px-3 py-1 text-xs font-semibold text-white">Chưa đọc</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mt-2 text-sm font-semibold text-[#456a57]"><?= clean($message['subject']) ?></p>
                                    <p class="mt-2 text-sm text-[#6e8d7b]"><?= clean(truncate((string)$message['message'], 140)) ?></p>
                                    <div class="mt-3 flex flex-wrap gap-4 text-xs text-[#6e8d7b]">
                                        <span><?= clean($message['email']) ?></span>
                                        <?php if (!empty($message['phone'])): ?><span><?= clean($message['phone']) ?></span><?php endif; ?>
                                        <span><?= format_date($message['created_at'], 'd/m/Y H:i') ?></span>
                                    </div>
                                </div>
                                <a href="contacts.php<?= clean(admin_contacts_query([
                                    'q' => $search,
                                    'status' => $statusFilter,
                                    'page' => $page,
                                    'view' => (int)$message['id'],
                                ])) ?>#contact-detail" class="inline-flex items-center rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">
                                    Xem chi tiết
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-[#edf4ef] pt-5">
                        <p class="text-sm text-[#6e8d7b]">Trang <?= clean((string)$page) ?> / <?= clean((string)$totalPages) ?></p>
                        <div class="flex flex-wrap gap-3">
                            <?php if ($page > 1): ?>
                                <a href="contacts.php<?= clean(admin_contacts_query(['q' => $search, 'status' => $statusFilter, 'page' => $page - 1])) ?>" class="inline-flex rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">Trang trước</a>
                            <?php endif; ?>
                            <?php if ($page < $totalPages): ?>
                                <a href="contacts.php<?= clean(admin_contacts_query(['q' => $search, 'status' => $statusFilter, 'page' => $page + 1])) ?>" class="inline-flex rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">Trang sau</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div id="contact-detail" class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
            <?php if (!$viewMessage): ?>
                <div class="flex h-full min-h-[420px] items-center justify-center rounded-[1.25rem] border border-dashed border-[#d9e9de] px-6 text-center text-sm text-[#6e8d7b]">
                    Chọn một liên hệ ở danh sách bên trái để xem chi tiết và cập nhật trạng thái xử lý.
                </div>
            <?php else: ?>
                <?php $viewMeta = admin_contact_status_meta((string)$viewMessage['status']); ?>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Chi tiết liên hệ</p>
                        <h2 class="mt-2 text-2xl font-extrabold text-[#102118]"><?= clean($viewMessage['subject']) ?></h2>
                        <p class="mt-2 text-sm text-[#6e8d7b]">Gửi lúc <?= format_date($viewMessage['created_at'], 'd/m/Y H:i') ?></p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full px-3 py-1 text-xs font-semibold <?= clean($viewMeta['class']) ?>"><?= clean($viewMeta['label']) ?></span>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold <?= !empty($viewMessage['is_read']) ? 'bg-[#eef6f1] text-[#456a57]' : 'bg-[#102118] text-white' ?>">
                            <?= !empty($viewMessage['is_read']) ? 'Đã đọc' : 'Chưa đọc' ?>
                        </span>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <article class="rounded-[1.25rem] border border-[#edf4ef] bg-[#f8fbf9] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#6e8d7b]">Người gửi</p>
                        <p class="mt-3 font-bold text-[#102118]"><?= clean($viewMessage['full_name']) ?></p>
                        <p class="mt-2 text-sm text-[#456a57]"><?= clean($viewMessage['email']) ?></p>
                        <p class="mt-1 text-sm text-[#456a57]"><?= clean((string)($viewMessage['phone'] ?: 'Chưa cung cấp số điện thoại')) ?></p>
                    </article>
                    <article class="rounded-[1.25rem] border border-[#edf4ef] bg-[#f8fbf9] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#6e8d7b]">Hành động nhanh</p>
                        <div class="mt-3 flex flex-wrap gap-3 text-sm">
                            <a href="mailto:<?= clean($viewMessage['email']) ?>?subject=<?= rawurlencode('Re: ' . (string)$viewMessage['subject']) ?>" class="inline-flex items-center rounded-full border border-[#d9e9de] px-4 py-2 font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">Soạn email</a>
                            <?php if (!empty($viewMessage['phone'])): ?>
                                <a href="tel:<?= clean((string)$viewMessage['phone']) ?>" class="inline-flex items-center rounded-full border border-[#d9e9de] px-4 py-2 font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">Gọi khách</a>
                            <?php endif; ?>
                        </div>
                    </article>
                </div>

                <section class="mt-6 rounded-[1.25rem] border border-[#edf4ef] p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#6e8d7b]">Nội dung khách gửi</p>
                    <div class="mt-4 rounded-[1rem] bg-[#f8fbf9] p-4 text-sm leading-7 text-[#102118]">
                        <?= nl2br(clean($viewMessage['message'])) ?>
                    </div>
                </section>

                <section class="mt-6 rounded-[1.25rem] border border-[#edf4ef] p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#6e8d7b]">Cập nhật xử lý</p>
                    <form method="POST" class="mt-4 space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                        <input type="hidden" name="action" value="update_contact_status">
                        <input type="hidden" name="contact_id" value="<?= clean((string)$viewMessage['id']) ?>">
                        <?php admin_contact_render_state_fields($currentState); ?>

                        <div class="space-y-2">
                            <label for="next_status" class="text-sm font-semibold text-[#102118]">Trạng thái</label>
                            <select id="next_status" name="next_status" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                                <option value="new" <?= (string)$viewMessage['status'] === 'new' ? 'selected' : '' ?>>Mới</option>
                                <option value="in_progress" <?= (string)$viewMessage['status'] === 'in_progress' ? 'selected' : '' ?>>Đang xử lý</option>
                                <option value="resolved" <?= (string)$viewMessage['status'] === 'resolved' ? 'selected' : '' ?>>Đã xử lý</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label for="admin_note" class="text-sm font-semibold text-[#102118]">Ghi chú nội bộ</label>
                            <textarea id="admin_note" name="admin_note" rows="5" class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="Ví dụ: đã gọi lại, đang chờ báo giá, khách hẹn xem showroom..."><?= clean((string)($viewMessage['admin_note'] ?? '')) ?></textarea>
                        </div>

                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#102118] px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#1f3b2d]">
                            Lưu cập nhật
                        </button>
                    </form>
                </section>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php render_admin_footer(); ?>
