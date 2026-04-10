<?php
if (empty($GLOBALS['mvc_template_rendering'])) {
    require_once __DIR__ . '/../../config/config.php';
    (new AdminToolController())->createPlaceholder()->send();
    return;
}

require_once __DIR__ . '/bootstrap.php';

render_admin_header('Tạo ảnh placeholder');
?>

<div class="space-y-4 rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
    <h2 class="text-2xl font-extrabold text-[#102118]">Kết quả tạo ảnh placeholder</h2>
    <?php foreach ($results as $result): ?>
        <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
            <?= clean($result['text']) ?>
        </div>
    <?php endforeach; ?>
    <div class="flex flex-wrap gap-3 pt-2">
        <a href="check_images.php" class="inline-flex items-center rounded-full border border-[#d8eadf] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">Kiểm tra ảnh</a>
        <a href="dashboard.php" class="inline-flex items-center rounded-full border border-[#d8eadf] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">Về dashboard</a>
    </div>
</div>

<?php render_admin_footer(); ?>
