<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$bootstrapPath = __DIR__ . '/../public/admin/bootstrap.php';
$contents = file_get_contents($bootstrapPath);

if ($contents === false) {
    fwrite(STDERR, "FAIL: Could not read admin bootstrap template.\n");
    exit(1);
}

$legacyTargets = [
    "base_url('public/home.php')",
    "base_url('public/profile.php')",
    "base_url('public/logout.php')",
];

foreach ($legacyTargets as $target) {
    if (strpos($contents, $target) !== false) {
        fwrite(STDERR, "FAIL: Admin bootstrap still contains legacy target {$target}.\n");
        exit(1);
    }
}

$requiredTargets = [
    "base_url()",
    "base_url('profile')",
    "base_url('logout')",
    'name="redirect" value="<?= clean(\'admin/login.php\') ?>"',
];

foreach ($requiredTargets as $target) {
    if (strpos($contents, $target) === false) {
        fwrite(STDERR, "FAIL: Admin bootstrap is missing expected target {$target}.\n");
        exit(1);
    }
}

echo "PASS: Admin bootstrap uses current MVC storefront/logout links.\n";
