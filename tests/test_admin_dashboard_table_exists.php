<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../config/config.php';

$service = new AdminDashboardService();
$reflection = new ReflectionClass($service);
$method = $reflection->getMethod('tableExists');
$method->setAccessible(true);

try {
    $result = $method->invoke($service, 'contact_messages');
} catch (PDOException $exception) {
    fwrite(STDERR, 'FAIL: AdminDashboardService::tableExists crashed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

if (!is_bool($result)) {
    fwrite(STDERR, "FAIL: AdminDashboardService::tableExists should return a boolean.\n");
    exit(1);
}

echo "PASS: AdminDashboardService::tableExists returns a boolean without SQL errors.\n";
