<?php
/**
 * Test Database Connection
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Database - GreenSpace</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 40px; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        h1 { color: #2ecc70; margin-bottom: 20px; font-size: 28px; }
        h2 { color: #333; margin-bottom: 15px; font-size: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745; margin-bottom: 15px; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; border-left: 4px solid #dc3545; margin-bottom: 15px; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 8px; border-left: 4px solid #17a2b8; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #2ecc70; color: white; font-weight: 600; }
        tr:hover { background: #f9f9f9; }
        .code { background: #f4f4f4; padding: 3px 8px; border-radius: 4px; font-family: monospace; font-size: 13px; }
        .btn { display: inline-block; padding: 12px 24px; background: #2ecc70; color: white; text-decoration: none; border-radius: 8px; margin-top: 15px; font-weight: 600; }
        .btn:hover { background: #25a25a; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>🌿 Test Kết Nối Database - GreenSpace</h1>
            
            <?php
            try {
                $database = new Database();
                $conn = $database->getConnection();
                
                if ($conn) {
                    echo '<div class="success">✅ <strong>Kết nối database thành công!</strong></div>';
                    
                    echo '<div class="info">';
                    echo '<strong>Thông tin kết nối:</strong><br>';
                    echo 'Host: <span class="code">' . DB_HOST . '</span><br>';
                    echo 'Database: <span class="code">' . DB_NAME . '</span><br>';
                    echo 'User: <span class="code">' . DB_USER . '</span><br>';
                    echo 'Charset: <span class="code">' . DB_CHARSET . '</span>';
                    echo '</div>';
                    
                    // Test query - Get tables
                    echo '<h2>📋 Danh sách Tables</h2>';
                    $stmt = $conn->query("SHOW TABLES");
                    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    if (count($tables) > 0) {
                        echo '<table>';
                        echo '<thead><tr><th>STT</th><th>Tên Table</th><th>Số Records</th></tr></thead>';
                        echo '<tbody>';
                        
                        foreach ($tables as $index => $table) {
                            $countStmt = $conn->query("SELECT COUNT(*) FROM `$table`");
                            $count = $countStmt->fetchColumn();
                            
                            echo '<tr>';
                            echo '<td>' . ($index + 1) . '</td>';
                            echo '<td><span class="code">' . $table . '</span></td>';
                            echo '<td>' . $count . ' records</td>';
                            echo '</tr>';
                        }
                        
                        echo '</tbody></table>';
                    } else {
                        echo '<div class="error">⚠️ Không tìm thấy table nào. Vui lòng import file full_setup_revised.sql</div>';
                    }
                    
                    // Test query - Get sample products
                    echo '<h2>🌱 Sample Products (5 sản phẩm đầu tiên)</h2>';
                    $stmt = $conn->query("SELECT id, name, price, stock, featured FROM products LIMIT 5");
                    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($products) > 0) {
                        echo '<table>';
                        echo '<thead><tr><th>ID</th><th>Tên sản phẩm</th><th>Giá</th><th>Tồn kho</th><th>Nổi bật</th></tr></thead>';
                        echo '<tbody>';
                        
                        foreach ($products as $product) {
                            echo '<tr>';
                            echo '<td>' . $product['id'] . '</td>';
                            echo '<td>' . $product['name'] . '</td>';
                            echo '<td>' . number_format($product['price'], 0, ',', '.') . 'đ</td>';
                            echo '<td>' . $product['stock'] . '</td>';
                            echo '<td>' . ($product['featured'] ? '⭐ Yes' : 'No') . '</td>';
                            echo '</tr>';
                        }
                        
                        echo '</tbody></table>';
                    } else {
                        echo '<div class="error">⚠️ Chưa có sản phẩm nào. Vui lòng import file full_setup_revised.sql để nạp dữ liệu mẫu</div>';
                    }
                    
                } else {
                    echo '<div class="error">❌ <strong>Không thể kết nối database!</strong></div>';
                }
                
            } catch (PDOException $e) {
                echo '<div class="error">';
                echo '<strong>❌ Lỗi kết nối database:</strong><br>';
                echo $e->getMessage();
                echo '</div>';
                
                echo '<div class="info">';
                echo '<strong>💡 Hướng dẫn khắc phục:</strong><br>';
                echo '1. Kiểm tra XAMPP đã bật MySQL chưa<br>';
                echo '2. Kiểm tra thông tin kết nối trong <span class="code">config/config.php</span><br>';
                echo '3. Import file <span class="code">database/full_setup_revised.sql</span> vao phpMyAdmin hoac MySQL CLI<br>';
                echo '4. Neu CSDL da cu, hay doi chieu schema hien tai voi file <span class="code">database/full_setup_revised.sql</span> va cap nhat cac cot payment_status/status cho phu hop';
                echo '</div>';
            }
            ?>
            
            <a href="demo.php" class="btn">← Quay lại trang chủ</a>
            <a href="products.php" class="btn">Xem sản phẩm</a>
        </div>
    </div>
</body>
</html>
