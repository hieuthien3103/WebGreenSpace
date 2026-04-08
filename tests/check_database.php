<?php
// Check database structure
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "=== Checking products table structure ===\n\n";

// Get column information
$stmt = $conn->query("DESCRIBE products");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Columns in 'products' table:\n";
foreach ($columns as $column) {
    echo "  - {$column['Field']} ({$column['Type']})\n";
}

echo "\n";

// Check if views column exists
$hasViews = false;
foreach ($columns as $column) {
    if ($column['Field'] === 'views') {
        $hasViews = true;
        break;
    }
}

if ($hasViews) {
    echo "✓ Column 'views' EXISTS\n";
} else {
    echo "✗ Column 'views' DOES NOT EXIST - Need to add it!\n";
    echo "\nSQL to add views column:\n";
    echo "ALTER TABLE products ADD COLUMN views INT DEFAULT 0 AFTER status;\n";
}
