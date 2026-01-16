<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Debug started...<br>";

try {
    require_once __DIR__ . '/../config/config.php';
    echo "Config loaded<br>";
    
    echo "<h3>Debug Info:</h3>";
    echo "<pre>";
    echo "APP_URL: " . APP_URL . "\n";
    echo "IMG_URL: " . IMG_URL . "\n";
    echo "UPLOAD_URL: " . UPLOAD_URL . "\n";
    echo "\n";
    echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";
    echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
    echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
    echo "\n";
    
    // Test image_url function
    require_once __DIR__ . '/../helpers/functions.php';
    echo "Helpers loaded<br>";
    echo "Test image_url('products/test.jpg'): " . image_url('products/test.jpg') . "\n";
    echo "</pre>";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine();
}
