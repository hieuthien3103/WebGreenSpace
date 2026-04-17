<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$templates = [
    __DIR__ . '/../public/admin/categories.php',
    __DIR__ . '/../public/admin/orders.php',
    __DIR__ . '/../public/admin/products.php',
    __DIR__ . '/../public/admin/users.php',
];

foreach ($templates as $templatePath) {
    $contents = file_get_contents($templatePath);

    if ($contents === false) {
        fwrite(STDERR, "FAIL: Could not read template {$templatePath}.\n");
        exit(1);
    }

    preg_match_all('/^\s*function\s+([a-zA-Z0-9_]+)\s*\(/m', $contents, $matches, PREG_OFFSET_CAPTURE);

    foreach ($matches[1] as [$functionName, $offset]) {
        $windowStart = max(0, $offset - 200);
        $window = substr($contents, $windowStart, $offset - $windowStart);

        if (strpos($window, "if (!function_exists('{$functionName}'))") === false) {
            fwrite(STDERR, "FAIL: {$templatePath} declares {$functionName}() without a function_exists guard.\n");
            exit(1);
        }
    }
}

echo "PASS: Admin self-rendered templates guard helper declarations against double include.\n";
