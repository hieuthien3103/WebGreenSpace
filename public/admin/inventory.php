<?php
if (empty($GLOBALS['mvc_template_rendering'])) {
    require_once __DIR__ . '/../../config/config.php';
    (new AdminPageController())->inventory()->send();
    return;
}

require_once __DIR__ . '/bootstrap.php';

render_admin_header('Nhập kho theo lô');
?>

<?php if (empty($tableExists)): ?>
    <div class="rounded-[1.75rem] border border-red-200 bg-red-50 p-6 shadow-sm">
        <h2 class="text-xl font-extrabold text-red-800">Chưa có bảng inventory_batches</h2>
        <p class="mt-2 text-sm text-red-700">Vui lòng chạy migration <code class="rounded bg-red-100 px-1.5 py-0.5 text-xs font-mono">database/migrations/20260415_create_inventory_batches.sql</code> trước khi sử dụng chức năng này.</p>
    </div>
<?php else: ?>

<div class="space-y-6">
    <!-- Receive form -->
    <div class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
        <h2 class="text-xl font-extrabold text-[#102118]">Nhập lô hàng mới</h2>
        <form method="POST" action="inventory.php" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="sm:col-span-2 lg:col-span-1">
                <label class="mb-1 block text-sm font-semibold text-[#102118]">Sản phẩm <span class="text-red-500">*</span></label>
                <select name="product_id" required class="w-full rounded-xl border border-[#d9e9de] bg-white px-4 py-2.5 text-sm text-[#102118] focus:border-[#2e9b63] focus:outline-none focus:ring-1 focus:ring-[#2e9b63]">
                    <option value="">-- Chọn sản phẩm --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= (int)$p['id'] === $productFilter ? 'selected' : '' ?>>
                            <?= clean($p['name']) ?> (Tồn: <?= (int)$p['stock'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-[#102118]">Số lượng nhập <span class="text-red-500">*</span></label>
                <input type="number" name="quantity" min="1" required placeholder="VD: 50" class="w-full rounded-xl border border-[#d9e9de] px-4 py-2.5 text-sm text-[#102118] focus:border-[#2e9b63] focus:outline-none focus:ring-1 focus:ring-[#2e9b63]">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-[#102118]">Mã lô (tự sinh nếu để trống)</label>
                <input type="text" name="batch_code" placeholder="VD: BATCH-001" class="w-full rounded-xl border border-[#d9e9de] px-4 py-2.5 text-sm text-[#102118] focus:border-[#2e9b63] focus:outline-none focus:ring-1 focus:ring-[#2e9b63]">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-[#102118]">Giá vốn / đơn vị</label>
                <input type="text" name="cost_price" placeholder="VD: 150000" class="w-full rounded-xl border border-[#d9e9de] px-4 py-2.5 text-sm text-[#102118] focus:border-[#2e9b63] focus:outline-none focus:ring-1 focus:ring-[#2e9b63]">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-[#102118]">Nhà cung cấp</label>
                <input type="text" name="supplier" placeholder="VD: Vườn Xanh Co." class="w-full rounded-xl border border-[#d9e9de] px-4 py-2.5 text-sm text-[#102118] focus:border-[#2e9b63] focus:outline-none focus:ring-1 focus:ring-[#2e9b63]">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-[#102118]">Ghi chú</label>
                <input type="text" name="note" placeholder="VD: Nhập bổ sung tháng 4" class="w-full rounded-xl border border-[#d9e9de] px-4 py-2.5 text-sm text-[#102118] focus:border-[#2e9b63] focus:outline-none focus:ring-1 focus:ring-[#2e9b63]">
            </div>

            <div class="flex items-end sm:col-span-2 lg:col-span-3">
                <button type="submit" class="inline-flex items-center rounded-full bg-[#2e9b63] px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#22784d]">
                    <span class="material-symbols-outlined mr-2 text-[18px]">inventory_2</span>
                    Nhập lô hàng
                </button>
            </div>
        </form>
    </div>

    <!-- Filter by product -->
    <div class="flex flex-wrap items-center gap-3">
        <span class="text-sm font-semibold text-[#6e8d7b]">Lọc theo sản phẩm:</span>
        <a href="inventory.php" class="rounded-full px-3 py-1.5 text-sm font-semibold transition-colors <?= $productFilter === 0 ? 'bg-[#102118] text-white' : 'border border-[#d9e9de] text-[#102118] hover:border-[#2e9b63]' ?>">Tất cả</a>
        <?php if ($selectedProduct): ?>
            <span class="rounded-full bg-[#102118] px-3 py-1.5 text-sm font-semibold text-white">
                <?= clean($selectedProduct['name'] ?? '') ?>
            </span>
        <?php endif; ?>
    </div>

    <!-- Batch list -->
    <div class="rounded-[1.75rem] border border-[#d9e9de] bg-white shadow-sm">
        <div class="border-b border-[#edf4ef] px-6 py-4">
            <h2 class="text-lg font-extrabold text-[#102118]">Lịch sử nhập lô (<?= (int)$totalBatches ?> lô)</h2>
        </div>

        <?php if (empty($batches)): ?>
            <div class="px-6 py-12 text-center text-sm text-[#6e8d7b]">
                Chưa có lô hàng nào được nhập<?= $productFilter > 0 ? ' cho sản phẩm này' : '' ?>.
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-[#edf4ef] text-left text-[#6e8d7b]">
                            <th class="px-6 pb-3 pt-4 font-semibold">Mã lô</th>
                            <th class="px-4 pb-3 pt-4 font-semibold">Sản phẩm</th>
                            <th class="px-4 pb-3 pt-4 font-semibold text-right">Nhập</th>
                            <th class="px-4 pb-3 pt-4 font-semibold text-right">Còn lại</th>
                            <th class="px-4 pb-3 pt-4 font-semibold text-right">Giá vốn</th>
                            <th class="px-4 pb-3 pt-4 font-semibold">NCC</th>
                            <th class="px-4 pb-3 pt-4 font-semibold">Ghi chú</th>
                            <th class="px-6 pb-3 pt-4 font-semibold">Ngày nhập</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($batches as $batch): ?>
                            <?php
                            $qtyReceived = (int)$batch['quantity_received'];
                            $qtyRemaining = (int)$batch['quantity_remaining'];
                            $depleted = $qtyRemaining === 0;
                            ?>
                            <tr class="border-b border-[#f4f8f5] last:border-b-0 <?= $depleted ? 'opacity-50' : '' ?>">
                                <td class="px-6 py-4 font-mono text-xs font-semibold text-[#102118]">
                                    <?= clean($batch['batch_code']) ?>
                                </td>
                                <td class="px-4 py-4">
                                    <a href="inventory.php?product_id=<?= (int)$batch['product_id'] ?>" class="font-semibold text-[#2e9b63] hover:underline">
                                        <?= clean($batch['product_name'] ?? 'SP #' . $batch['product_id']) ?>
                                    </a>
                                </td>
                                <td class="px-4 py-4 text-right font-semibold text-[#102118]"><?= number_format($qtyReceived) ?></td>
                                <td class="px-4 py-4 text-right">
                                    <?php if ($depleted): ?>
                                        <span class="rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-600">Hết</span>
                                    <?php elseif ($qtyRemaining < $qtyReceived): ?>
                                        <span class="font-semibold text-amber-600"><?= number_format($qtyRemaining) ?></span>
                                    <?php else: ?>
                                        <span class="font-semibold text-green-700"><?= number_format($qtyRemaining) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 text-right text-[#6e8d7b]">
                                    <?= $batch['cost_price'] !== null ? format_currency((float)$batch['cost_price']) : '—' ?>
                                </td>
                                <td class="max-w-[120px] truncate px-4 py-4 text-[#6e8d7b]"><?= clean($batch['supplier'] ?? '—') ?></td>
                                <td class="max-w-[150px] truncate px-4 py-4 text-[#6e8d7b]"><?= clean($batch['note'] ?? '—') ?></td>
                                <td class="px-6 py-4 text-[#6e8d7b]"><?= format_date($batch['received_at'], 'd/m/Y H:i') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="flex items-center justify-center gap-2 border-t border-[#edf4ef] px-6 py-4">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <a href="inventory.php?<?= http_build_query(array_filter(['product_id' => $productFilter ?: null, 'page' => $p > 1 ? $p : null])) ?>"
                           class="rounded-full px-3 py-1.5 text-sm font-semibold transition-colors <?= $p === $page ? 'bg-[#102118] text-white' : 'border border-[#d9e9de] text-[#102118] hover:border-[#2e9b63]' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>

<?php render_admin_footer(); ?>
