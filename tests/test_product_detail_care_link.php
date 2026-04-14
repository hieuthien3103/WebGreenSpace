<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$viewPath = __DIR__ . '/../app/views/storefront/products/detail.php';
$viewContents = file_get_contents($viewPath);

if ($viewContents === false) {
    fwrite(STDERR, "Failed to read product detail view.\n");
    exit(1);
}

$expectedSnippet = "href=\"<?= base_url('care') ?>\"";

if (strpos($viewContents, $expectedSnippet) === false) {
    fwrite(STDERR, "FAIL: Product detail care guidance does not link to the care page.\n");
    exit(1);
}

echo "PASS: Product detail care guidance links to the care page.\n";
