<?php
// Tạo ảnh placeholder cho sản phẩm thiếu ảnh

$missingImages = [
    'combo-sen.png' => 'Combo Sen Đá',
    'bang-singapore.png' => 'Cây Bàng Singapore'
];

$outputDir = __DIR__ . '/images/products/';

foreach ($missingImages as $filename => $productName) {
    $filepath = $outputDir . $filename;
    
    if (file_exists($filepath)) {
        echo "✓ $filename đã tồn tại<br>";
        continue;
    }
    
    // Tạo ảnh 500x500 với màu nền
    $image = imagecreatetruecolor(500, 500);
    
    // Màu nền xanh lá nhạt
    $bgColor = imagecolorallocate($image, 200, 230, 201);
    $textColor = imagecolorallocate($image, 46, 125, 50);
    $borderColor = imagecolorallocate($image, 129, 199, 132);
    
    // Fill nền
    imagefill($image, 0, 0, $bgColor);
    
    // Vẽ viền
    imagerectangle($image, 10, 10, 490, 490, $borderColor);
    imagerectangle($image, 11, 11, 489, 489, $borderColor);
    
    // Thêm text
    $font = 5;
    $text = $productName;
    $textWidth = imagefontwidth($font) * strlen($text);
    $textHeight = imagefontheight($font);
    $x = (500 - $textWidth) / 2;
    $y = (500 - $textHeight) / 2;
    
    imagestring($image, $font, $x, $y, $text, $textColor);
    
    // Lưu file
    imagepng($image, $filepath);
    imagedestroy($image);
    
    echo "✓ Đã tạo $filename<br>";
}

echo "<hr>";
echo "<p><strong>Hoàn tất!</strong> <a href='check_images.php'>Kiểm tra lại</a> | <a href='products.php'>Xem trang sản phẩm</a></p>";
?>
