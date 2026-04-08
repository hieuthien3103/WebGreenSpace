<?php
// Check users table structure
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "=== Checking users table ===\n\n";

$stmt = $conn->query("DESCRIBE users");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Columns:\n";
foreach ($columns as $col) {
    echo "  - {$col['Field']} ({$col['Type']})\n";
}

echo "\n=== Creating test user ===\n";

// Check if any user exists
$stmt = $conn->query("SELECT * FROM users LIMIT 1");
$user = $stmt->fetch();

if ($user) {
    echo "✓ Found existing user (ID: {$user['id']})\n";
    echo "Using this user for testing\n";
} else {
    echo "No users found. Creating new user...\n";
    // Insert with correct column name
    $query = "INSERT INTO users (email, password_hash, full_name, role) 
              VALUES ('test@greenspace.com', :password_hash, 'Test User', 'customer')";
    
    $stmt = $conn->prepare($query);
    $hashedPassword = password_hash('test123', PASSWORD_DEFAULT);
    $stmt->bindParam(':password_hash', $hashedPassword);
    
    if ($stmt->execute()) {
        $userId = $conn->lastInsertId();
        echo "✓ Test user created (ID: $userId)\n";
    } else {
        echo "✗ Failed to create user\n";
    }
}
