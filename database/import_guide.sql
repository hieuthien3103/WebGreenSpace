-- ========================================
-- HƯỚNG DẪN IMPORT DATABASE TỪ NƠI KHÁC
-- ========================================

-- CÁCH 1: Nếu bạn đã có file .sql từ database cũ
-- ------------------------------------------------
-- 1. Vào phpMyAdmin: http://localhost/phpmyadmin
-- 2. Tạo database mới hoặc xóa database cũ:
--    DROP DATABASE IF EXISTS webgreenspace;
--    CREATE DATABASE webgreenspace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- 3. Click vào database "webgreenspace"
-- 4. Click tab "Import"
-- 5. Chọn file .sql từ database cũ
-- 6. Click "Go"


-- CÁCH 2: Copy từ database khác trên cùng server
-- ------------------------------------------------
-- Nếu database cũ tên là: old_greenspace
-- Database mới tên là: webgreenspace

-- A. Copy toàn bộ structure và data từ 1 table:
INSERT INTO webgreenspace.products 
SELECT * FROM old_greenspace.products;

INSERT INTO webgreenspace.categories 
SELECT * FROM old_greenspace.categories;

-- B. Hoặc copy có chọn lọc:
INSERT INTO webgreenspace.products (name, price, category_id, description, image)
SELECT name, price, category_id, description, image 
FROM old_greenspace.products 
WHERE status = 'active';


-- CÁCH 3: Export từ phpMyAdmin của database cũ
-- ------------------------------------------------
-- 1. Vào phpMyAdmin của database cũ
-- 2. Click vào database
-- 3. Click tab "Export"
-- 4. Chọn "Custom" để tùy chỉnh:
--    - Tables: Chọn tables muốn export
--    - Format: SQL
--    - Object creation options: Chọn "Add DROP TABLE"
-- 5. Click "Go" để download file
-- 6. Import file vào database mới (như Cách 1)


-- CÁCH 4: Xóa database cũ và tạo mới
-- ------------------------------------------------
-- Chạy lần lượt các lệnh sau trong tab SQL của phpMyAdmin:

-- Bước 1: Xóa database cũ (CẨN THẬN!)
DROP DATABASE IF EXISTS webgreenspace;

-- Bước 2: Tạo database mới
CREATE DATABASE webgreenspace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Bước 3: Chọn database
USE webgreenspace;

-- Bước 4: Import file schema.sql và sample_data.sql
-- (Dùng tab Import hoặc copy-paste nội dung file)


-- ========================================
-- LƯU Ý QUAN TRỌNG
-- ========================================

-- 1. Backup database cũ trước khi làm gì:
--    Export → Download file .sql để lưu trữ

-- 2. Kiểm tra tên database trong config/config.php:
--    define('DB_NAME', 'webgreenspace');

-- 3. Nếu tên table khác nhau, cần sửa lại trong code PHP

-- 4. Sau khi import xong, test kết nối tại:
--    http://localhost:8000/test-db.php
