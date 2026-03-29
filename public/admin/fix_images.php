<?php
require_once __DIR__ . '/bootstrap.php';

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->query("SELECT id, name, image FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updateStmt = $conn->prepare("UPDATE products SET image = :image WHERE id = :id");
$results = [];

foreach ($products as $product) {
    $oldPath = (string)($product['image'] ?? '');
    $newPath = str_replace('"', '', $oldPath);
    $newPath = basename($newPath);

    if ($newPath !== '' && strpos($newPath, 'products/') !== 0) {
        $newPath = 'products/' . $newPath;
    }

    try {
        $updateStmt->execute([
            ':image' => $newPath !== '' ? $newPath : null,
            ':id' => (int)$product['id'],
        ]);

        $results[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'old' => $oldPath,
            'new' => $newPath,
            'status' => 'success',
        ];
    } catch (Throwable $e) {
        $results[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'old' => $oldPath,
            'new' => $newPath,
            'status' => 'error',
            'error' => $e->getMessage(),
        ];
    }
}

render_admin_header('Fix đường dẫn ảnh');
?>

<div class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
    <div class="mb-5 flex items-center justify-between gap-3">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Image repair</p>
            <h2 class="mt-2 text-2xl font-extrabold text-[#102118]">Chuẩn hóa đường dẫn image</h2>
        </div>
        <a href="check_images.php" class="text-sm font-semibold text-[#2e9b63] hover:text-[#22784d]">Kiểm tra lại</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-[#edf4ef] text-left text-[#6e8d7b]">
                    <th class="pb-3 pr-4 font-semibold">ID</th>
                    <th class="pb-3 pr-4 font-semibold">Tên</th>
                    <th class="pb-3 pr-4 font-semibold">Cũ</th>
                    <th class="pb-3 pr-4 font-semibold">Mới</th>
                    <th class="pb-3 font-semibold">Kết quả</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                    <tr class="border-b border-[#f4f8f5] last:border-b-0">
                        <td class="py-4 pr-4 font-semibold text-[#102118]"><?= (int)$result['id'] ?></td>
                        <td class="py-4 pr-4 text-[#102118]"><?= clean($result['name']) ?></td>
                        <td class="py-4 pr-4 text-xs text-[#6e8d7b]"><?= clean($result['old']) ?></td>
                        <td class="py-4 pr-4 text-xs text-[#2e9b63]"><?= clean($result['new']) ?></td>
                        <td class="py-4">
                            <?php if ($result['status'] === 'success'): ?>
                                <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">Đã cập nhật</span>
                            <?php else: ?>
                                <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700"><?= clean($result['error'] ?? 'Lỗi') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php render_admin_footer(); ?>
