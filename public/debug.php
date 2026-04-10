<?php
if (empty($GLOBALS['mvc_template_rendering'])) {
    require_once __DIR__ . '/../config/config.php';
    (new UtilityController())->debug()->send();
    return;
}

require_once __DIR__ . '/../config/config.php';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= clean($pageTitle) ?></title>
    <style>
        body { font-family: sans-serif; margin: 2rem; line-height: 1.5; }
        pre { background: #f4f6f5; padding: 1rem; border-radius: 12px; overflow: auto; }
    </style>
</head>
<body>
    <h1>Debug Info</h1>
    <pre><?php
echo 'APP_URL: ' . $snapshot['app_url'] . PHP_EOL;
echo 'IMG_URL: ' . $snapshot['img_url'] . PHP_EOL;
echo 'UPLOAD_URL: ' . $snapshot['upload_url'] . PHP_EOL;
echo PHP_EOL;
echo 'HTTP_HOST: ' . $snapshot['http_host'] . PHP_EOL;
echo 'SCRIPT_NAME: ' . $snapshot['script_name'] . PHP_EOL;
echo 'REQUEST_URI: ' . $snapshot['request_uri'] . PHP_EOL;
echo PHP_EOL;
echo "Test image_url('products/test.jpg'): " . $snapshot['sample_image_url'] . PHP_EOL;
?></pre>
</body>
</html>
