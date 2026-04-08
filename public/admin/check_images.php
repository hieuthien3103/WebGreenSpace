<?php
require_once __DIR__ . '/bootstrap.php';
require_admin_permission('products.manage', 'check_images.php');
require_once __DIR__ . '/../../app/models/Product.php';

$productModel = new Product();
$products = $productModel->getAll(8, 0);

render_admin_header('Kiểm tra ảnh sản phẩm');
?>

<div class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-center justify-between gap-3">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Image audit</p>
            <h2 class="mt-2 text-2xl font-extrabold text-[#102118]">Preview dữ liệu ảnh</h2>
        </div>
        <a href="dashboard.php" class="text-sm font-semibold text-[#2e9b63] hover:text-[#22784d]">Về dashboard</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-[#edf4ef] text-left text-[#6e8d7b]">
                    <th class="pb-3 pr-4 font-semibold">ID</th>
                    <th class="pb-3 pr-4 font-semibold">Tên</th>
                    <th class="pb-3 pr-4 font-semibold">image (DB)</th>
                    <th class="pb-3 pr-4 font-semibold">image_url</th>
                    <th class="pb-3 font-semibold">Preview</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr class="border-b border-[#f4f8f5] last:border-b-0">
                        <td class="py-4 pr-4 font-semibold text-[#102118]"><?= (int)$product['id'] ?></td>
                        <td class="py-4 pr-4 text-[#102118]"><?= clean($product['name']) ?></td>
                        <td class="py-4 pr-4 text-xs text-[#6e8d7b]"><?= clean($product['image'] ?? 'N/A') ?></td>
                        <td class="py-4 pr-4 text-xs text-[#2e9b63]"><?= clean($product['image_url']) ?></td>
                        <td class="py-4">
                            <img src="<?= clean($product['image_url']) ?>" alt="<?= clean($product['name']) ?>" class="h-20 w-20 rounded-2xl object-cover">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php render_admin_footer(); ?>
