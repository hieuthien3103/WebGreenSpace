<?php
// Create test user in database
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "=== Creating Test User ===\n\n";

// Check if user exists
$stmt = $conn->query("SELECT id FROM users WHERE id = 1");
$exists = $stmt->fetch();

if ($exists) {
    echo "✓ Test user (ID: 1) already exists\n";
} else {
    // Create test user
    $query = "INSERT INTO users (id, username, email, password, full_name, role, status) 
              VALUES (1, 'testuser', 'test@greenspace.com', :password, 'Test User', 'user', 'active')";
    
    $stmt = $conn->prepare($query);
    $hashedPassword = password_hash('test123', PASSWORD_DEFAULT);
    $stmt->bindParam(':password', $hashedPassword);
    
    if ($stmt->execute()) {
        echo "✓ Test user created successfully\n";
        echo "  Username: testuser\n";
        echo "  Email: test@greenspace.com\n";
        echo "  Password: test123\n";
    } else {
        echo "✗ Failed to create test user\n";
    }
}

echo "\n=== Done ===\n";
