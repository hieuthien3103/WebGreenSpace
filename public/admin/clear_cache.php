<?php
if (empty($GLOBALS['mvc_template_rendering'])) {
    require_once __DIR__ . '/../../config/config.php';
    (new AdminToolController())->clearCache()->send();
    return;
}

require_once __DIR__ . '/bootstrap.php';

render_admin_header('Clear cache');
?>

<div class="space-y-6">
    <?php foreach ($messages as $message): ?>
        <div class="rounded-2xl border px-4 py-3 text-sm font-medium <?= $message['type'] === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800' ?>">
            <?= clean($message['text']) ?>
        </div>
    <?php endforeach; ?>

    <div class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
        <h2 class="text-2xl font-extrabold text-[#102118]">Kiểm tra nhanh Product Model</h2>
        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-[#edf4ef] text-left text-[#6e8d7b]">
                        <th class="pb-3 pr-4 font-semibold">ID</th>
                        <th class="pb-3 pr-4 font-semibold">Tên</th>
                        <th class="pb-3 font-semibold">image_url</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr class="border-b border-[#f4f8f5] last:border-b-0">
                            <td class="py-4 pr-4 font-semibold text-[#102118]"><?= (int)$product['id'] ?></td>
                            <td class="py-4 pr-4 text-[#102118]"><?= clean($product['name']) ?></td>
                            <td class="py-4 text-xs text-[#2e9b63]"><?= clean($product['image_url']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php render_admin_footer(); ?>
