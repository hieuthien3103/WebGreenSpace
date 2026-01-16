<?php
/**
 * Test Database Connection
 */

require_once 'config/config.php';
require_once 'config/database.php';

echo "<h2>Testing Database Connection...</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "<p style='color: green;'>✓ Kết nối database thành công!</p>";
        
        // Test query
        $stmt = $conn->query("SELECT DATABASE() as db_name");
        $result = $stmt->fetch();
        echo "<p>Database hiện tại: <strong>{$result['db_name']}</strong></p>";
        
        // List all tables
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h3>Các bảng trong database:</h3>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>{$table}</li>";
        }
        echo "</ul>";
        
        // Count records in some tables
        echo "<h3>Số lượng bản ghi:</h3>";
        echo "<ul>";
        foreach ($tables as $table) {
            $stmt = $conn->query("SELECT COUNT(*) as total FROM `{$table}`");
            $count = $stmt->fetch();
            echo "<li>{$table}: {$count['total']} bản ghi</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<p style='color: red;'>✗ Không thể kết nối database!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Lỗi: " . $e->getMessage() . "</p>";
}
?>
