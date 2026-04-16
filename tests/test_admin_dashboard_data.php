<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../config/config.php';

try {
    $data = (new AdminDashboardService())->getDashboardData();
} catch (Throwable $exception) {
    fwrite(STDERR, 'FAIL: Admin dashboard data loading crashed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

if (!is_array($data) || !isset($data['stats']) || !is_array($data['stats'])) {
    fwrite(STDERR, "FAIL: Admin dashboard data should return an array with a stats key.\n");
    exit(1);
}

echo "PASS: Admin dashboard data loads without crashing.\n";
