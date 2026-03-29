-- ========================================
-- HƯỚNG DẪN IMPORT DATABASE (BẢN ĐÃ CHỈNH)
-- Dùng cho project PHP + MySQL + Docker
-- ========================================

-- File nên dùng:
-- 1. schema_revised.sql
-- 2. sample_data_revised.sql
--
-- Không nên import schema.sql cũ nữa nếu bạn muốn đồng bộ
-- với system_design.md, api_contract_php.md và flow thanh toán mô phỏng.

-- ========================================
-- CÁCH 1: IMPORT BẰNG phpMyAdmin
-- ========================================
-- 1. Mở phpMyAdmin
-- 2. Xóa database cũ nếu muốn làm sạch hoàn toàn:
--    DROP DATABASE IF EXISTS webgreenspace;
-- 3. Tạo lại database:
--    CREATE DATABASE webgreenspace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- 4. Chọn database "webgreenspace"
-- 5. Import file `schema_revised.sql`
-- 6. Import tiếp file `sample_data_revised.sql`

-- ========================================
-- CÁCH 2: IMPORT BẰNG MYSQL CLI
-- ========================================
-- Nếu đang dùng XAMPP / Laragon / terminal local:

-- Bước 1: Tạo database
DROP DATABASE IF EXISTS webgreenspace;
CREATE DATABASE webgreenspace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Bước 2: Import bằng command line
-- mysql -u root -p webgreenspace < schema_revised.sql
-- mysql -u root -p webgreenspace < sample_data_revised.sql

-- ========================================
-- CÁCH 3: IMPORT KHI CHẠY BẰNG DOCKER
-- ========================================
-- Giả sử service MySQL trong docker-compose tên là `db`

-- Bước 1: Chạy docker
-- docker compose up -d

-- Bước 2: Copy file vào container nếu cần
-- docker cp schema_revised.sql <container_mysql_name>:/schema_revised.sql
-- docker cp sample_data_revised.sql <container_mysql_name>:/sample_data_revised.sql

-- Bước 3: Import trong container
-- docker compose exec db mysql -u root -proot webgreenspace < /schema_revised.sql
-- docker compose exec db mysql -u root -proot webgreenspace < /sample_data_revised.sql

-- Hoặc nếu user/password khác thì sửa lại cho đúng:
-- docker compose exec db mysql -u cayxanh_user -psecret webgreenspace < /schema_revised.sql

-- ========================================
-- CÁCH 4: CHẠY TỰ ĐỘNG KHI CONTAINER MYSQL KHỞI ĐỘNG
-- ========================================
-- Nếu muốn MySQL tự import file lúc khởi tạo database lần đầu,
-- đặt file vào thư mục:
--
-- docker/mysql/init/
--
-- Ví dụ:
-- docker/mysql/init/01_schema.sql
-- docker/mysql/init/02_sample_data.sql
--
-- rồi mount vào:
-- /docker-entrypoint-initdb.d

-- Ví dụ docker-compose:
--
-- db:
--   image: mysql:8.0
--   environment:
--     MYSQL_DATABASE: webgreenspace
--     MYSQL_ROOT_PASSWORD: root
--   volumes:
--     - mysql_data:/var/lib/mysql
--     - ./docker/mysql/init:/docker-entrypoint-initdb.d
--
-- Lưu ý:
-- Cơ chế này chỉ chạy khi volume database còn trống.
-- Nếu volume đã có data rồi thì MySQL sẽ không import lại.

-- ========================================
-- THỨ TỰ IMPORT KHUYẾN NGHỊ
-- ========================================
-- 1. schema_revised.sql
-- 2. sample_data_revised.sql

-- Không cần import:
-- - schema.sql cũ
-- - sample_data.sql cũ
--
-- trừ khi bạn chỉ muốn tham khảo dữ liệu cũ.

-- ========================================
-- KIỂM TRA SAU KHI IMPORT
-- ========================================
USE webgreenspace;

SHOW TABLES;

SELECT COUNT(*) AS total_users FROM users;
SELECT COUNT(*) AS total_categories FROM categories;
SELECT COUNT(*) AS total_products FROM products;
SELECT COUNT(*) AS total_orders FROM orders;
SELECT COUNT(*) AS total_payments FROM payments;

-- Kiểm tra user admin
SELECT id, username, email, role, status
FROM users
WHERE role = 'admin';

-- Kiểm tra đơn hàng mẫu
SELECT id, order_number, payment_method, payment_status, order_status
FROM orders;

-- Kiểm tra payment mô phỏng
SELECT id, order_id, provider, status, amount
FROM payments;

-- ========================================
-- THÔNG TIN DỮ LIỆU MẪU
-- ========================================
-- Có sẵn:
-- - 1 admin
-- - 2 user thường
-- - nhiều category
-- - nhiều sản phẩm mẫu
-- - địa chỉ mẫu
-- - cart mẫu
-- - đơn hàng mẫu
-- - payment mô phỏng
-- - coupon mẫu
-- - review mẫu
-- - wishlist mẫu

-- ========================================
-- LƯU Ý QUAN TRỌNG
-- ========================================
-- 1. File mới đã đổi flow thanh toán thành:
--    - cod
--    - online_mock
--
-- 2. Không còn dùng payment gateway thật
-- 3. Không có webhook thật
-- 4. Phù hợp hơn với đồ án PHP + MySQL
--
-- 5. Nếu code PHP của bạn đang dùng bảng cũ:
--    - cart
--    - order_details
-- thì bản revised vẫn giữ các tên này để hạn chế phải sửa code quá nhiều

-- ========================================
-- NẾU IMPORT BỊ LỖI
-- ========================================
-- Hãy kiểm tra:
-- 1. MySQL version có phải 8.0 không
-- 2. Database đã chọn đúng là webgreenspace chưa
-- 3. Có import đúng thứ tự schema trước, sample data sau không
-- 4. Database cũ có dữ liệu gây trùng unique không
-- 5. File SQL có đang bị lỗi encoding không

-- Nếu cần reset hoàn toàn:
DROP DATABASE IF EXISTS webgreenspace;
CREATE DATABASE webgreenspace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Sau đó import lại:
-- schema_revised.sql
-- sample_data_revised.sql
