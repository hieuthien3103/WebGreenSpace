<?php
require_once __DIR__ . '/bootstrap.php';
require_admin_permission('uploads.manage', 'create_placeholder.php');

$missingImages = [
    'combo-sen.png' => 'Combo Sen Đá',
    'bang-singapore.png' => 'Cây Bàng Singapore',
];

$outputDir = __DIR__ . '/../images/products/';
$results = [];

foreach ($missingImages as $filename => $productName) {
    $filepath = $outputDir . $filename;

    if (file_exists($filepath)) {
        $results[] = ['status' => 'success', 'text' => $filename . ' đã tồn tại.'];
        continue;
    }

    $image = imagecreatetruecolor(500, 500);
    $bgColor = imagecolorallocate($image, 200, 230, 201);
    $textColor = imagecolorallocate($image, 46, 125, 50);
    $borderColor = imagecolorallocate($image, 129, 199, 132);

    imagefill($image, 0, 0, $bgColor);
    imagerectangle($image, 10, 10, 490, 490, $borderColor);
    imagerectangle($image, 11, 11, 489, 489, $borderColor);

    $font = 5;
    $textWidth = imagefontwidth($font) * strlen($productName);
    $textHeight = imagefontheight($font);
    $x = (int)((500 - $textWidth) / 2);
    $y = (int)((500 - $textHeight) / 2);

    imagestring($image, $font, $x, $y, $productName, $textColor);
    imagepng($image, $filepath);
    imagedestroy($image);

    $results[] = ['status' => 'success', 'text' => 'Đã tạo ' . $filename];
}

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
