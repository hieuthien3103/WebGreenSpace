-- Database: webgreenspace
-- Full setup bundle: schema + sample data
-- Payment is simulated for coursework: COD + online_mock

CREATE DATABASE IF NOT EXISTS webgreenspace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE webgreenspace;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS inventory_logs;
DROP TABLE IF EXISTS coupon_usages;
DROP TABLE IF EXISTS coupons;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS wishlists;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS order_details;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS product_variants;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS addresses;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ========================================
-- Table: users
-- ========================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    admin_permissions TEXT DEFAULT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Table: addresses
-- Tách riêng khỏi users để 1 user có nhiều địa chỉ
-- ========================================
CREATE TABLE addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    receiver_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    province VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    ward VARCHAR(100) DEFAULT NULL,
    address_line VARCHAR(255) NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_addresses_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_addresses_user_id (user_id),
    INDEX idx_addresses_user_default (user_id, is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Table: categories
-- ========================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    parent_id INT DEFAULT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_categories_parent
        FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_categories_parent_id (parent_id),
    INDEX idx_categories_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Table: products
-- Giữ stock ở products để phù hợp đồ án
-- ========================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    price DECIMAL(10, 2) NOT NULL,
    sale_price DECIMAL(10, 2) DEFAULT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    images TEXT DEFAULT NULL, -- JSON string hoặc danh sách ảnh phụ
    size VARCHAR(50) DEFAULT NULL,
    care_level ENUM('easy', 'medium', 'hard') NOT NULL DEFAULT 'medium',
    light_requirement ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    water_requirement ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    featured TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    views INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_products_category_id (category_id),
    INDEX idx_products_status (status),
    INDEX idx_products_featured (featured),
    INDEX idx_products_price (price),
    INDEX idx_products_stock (stock)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Table: product_images
-- Nếu muốn tách ảnh phụ rõ ràng thì dùng bảng này
-- ========================================
CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_images_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_images_product_id (product_id),
    INDEX idx_product_images_product_sort (product_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Table: product_variants
-- Optional cho đồ án; có thể không dùng ở code nếu chưa cần
-- ========================================
CREATE TABLE product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    sku VARCHAR(100) NOT NULL UNIQUE,
    option_name VARCHAR(100) NOT NULL,
    option_value VARCHAR(100) NOT NULL,
    price_override DECIMAL(10, 2) DEFAULT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_variants_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_variants_product_id (product_id),
    INDEX idx_product_variants_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Table: cart
-- Giữ tên cart để tương thích code cũ
-- ========================================
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT DEFAULT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price_snapshot DECIMAL(10, 2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cart_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_variant
        FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_product_variant (user_id, product_id, variant_id),
    INDEX idx_cart_user_id (user_id),
    INDEX idx_cart_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Table: orders
-- Payment for coursework: cod + online_mock
-- ========================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    note TEXT DEFAULT NULL,
    subtotal DECIMAL(10, 2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    shipping_fee DECIMAL(10, 2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(10, 2) NOT NULL,
    coupon_code VARCHAR(50) DEFAULT NULL,
    payment_method ENUM('cod', 'online_mock') NOT NULL DEFAULT 'cod',
    payment_status ENUM('unpaid', 'pending_review', 'paid', 'failed') NOT NULL DEFAULT 'unpaid',
    order_status ENUM('pending', 'confirmed', 'processing', 'shipping', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_orders_user_id (user_id),
    INDEX idx_orders_payment_status (payment_status),
    INDEX idx_orders_order_status (order_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Table: order_details
-- Giữ tên cũ để tương thích code cũ
-- ========================================
CREATE TABLE order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    variant_id INT DEFAULT NULL,
    product_name VARCHAR(200) NOT NULL,
    product_image VARCHAR(255) DEFAULT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_details_order
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_details_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_details_variant
        FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
    INDEX idx_order_details_order_id (order_id),
    INDEX idx_order_details_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Table: payments
-- Mô phỏng thanh toán cho đồ án
-- ========================================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    provider ENUM('cod', 'online_mock') NOT NULL DEFAULT 'cod',
    transaction_code VARCHAR(100) DEFAULT NULL,
    status ENUM('unpaid', 'pending_review', 'paid', 'failed') NOT NULL DEFAULT 'unpaid',
    amount DECIMAL(10, 2) NOT NULL,
    paid_at TIMESTAMP NULL DEFAULT NULL,
    note VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_order
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_payments_order_id (order_id),
    INDEX idx_payments_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Table: coupons
-- ========================================
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('fixed', 'percent') NOT NULL DEFAULT 'fixed',
    discount_value DECIMAL(10, 2) NOT NULL,
    min_order_amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    usage_limit INT DEFAULT NULL,
    used_count INT NOT NULL DEFAULT 0,
    start_date DATETIME DEFAULT NULL,
    end_date DATETIME DEFAULT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_coupons_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Table: coupon_usages
-- ========================================
CREATE TABLE coupon_usages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_coupon_usages_coupon
        FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    CONSTRAINT fk_coupon_usages_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_coupon_usages_order
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    UNIQUE KEY unique_coupon_user_order (coupon_id, user_id, order_id),
    INDEX idx_coupon_usages_coupon_id (coupon_id),
    INDEX idx_coupon_usages_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Table: reviews
-- ========================================
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT chk_reviews_rating CHECK (rating >= 1 AND rating <= 5),
    INDEX idx_reviews_product_id (product_id),
    INDEX idx_reviews_user_id (user_id),
    INDEX idx_reviews_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Table: wishlists
-- ========================================
CREATE TABLE wishlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_wishlists_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlists_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id),
    INDEX idx_wishlists_user_id (user_id),
    INDEX idx_wishlists_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Table: inventory_logs
-- ========================================
CREATE TABLE inventory_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    order_id INT DEFAULT NULL,
    action ENUM('import', 'deduct', 'restore', 'adjust') NOT NULL,
    quantity INT NOT NULL,
    note VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_inventory_logs_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_inventory_logs_order
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_inventory_logs_product_id (product_id),
    INDEX idx_inventory_logs_order_id (order_id),
    INDEX idx_inventory_logs_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Sample data
-- ========================================
INSERT INTO users (username, email, password, full_name, phone, role, status) VALUES
('admin', 'admin@webgreenspace.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '0900000000', 'admin', 'active'),
('user01', 'user01@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A', '0901234567', 'user', 'active'),
('user02', 'user02@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị B', '0912345678', 'user', 'active');

INSERT INTO addresses (user_id, receiver_name, phone, province, district, ward, address_line, is_default) VALUES
(2, 'Nguyễn Văn A', '0901234567', 'TP.HCM', 'Thủ Đức', 'Linh Trung', '123 Đường ABC', 1),
(2, 'Nguyễn Văn A', '0901234567', 'TP.HCM', 'Quận 1', 'Bến Nghé', '45 Đường XYZ', 0),
(3, 'Trần Thị B', '0912345678', 'Hà Nội', 'Cầu Giấy', 'Dịch Vọng', '12 Ngõ Hoa Giấy', 1);

INSERT INTO categories (name, slug, description, status) VALUES
('Cây Nội Thất', 'cay-noi-that', 'Cây trang trí trong nhà', 'active'),
('Cây Văn Phòng', 'cay-van-phong', 'Cây phù hợp cho văn phòng', 'active'),
('Cây Sen Đá', 'cay-sen-da', 'Các loại cây sen đá và cây mọng nước', 'active'),
('Cây Thủy Sinh', 'cay-thuy-sinh', 'Cây trồng trong nước', 'active'),
('Cây Phong Thủy', 'cay-phong-thuy', 'Cây mang ý nghĩa phong thủy', 'active');

INSERT INTO products (
    category_id, name, slug, description, price, sale_price, stock, image, size,
    care_level, light_requirement, water_requirement, featured, status
) VALUES
(1, 'Cây Trầu Bà Nam Mỹ', 'cay-trau-ba-nam-my', 'Cây trầu bà Nam Mỹ với lá xanh tươi, dễ chăm sóc và phù hợp với môi trường trong nhà. Giúp lọc không khí hiệu quả.', 350000, NULL, 50, 'https://images.unsplash.com/photo-1614594975525-e45190c55d0b?w=800&q=80', 'Vừa', 'easy', 'medium', 'medium', 1, 'active'),
(1, 'Cây Lưỡi Hổ', 'cay-luoi-ho', 'Cây lưỡi hổ - loại cây nội thất lý tưởng, chịu hạn tốt, lọc không khí hiệu quả. Phù hợp cho người bận rộn.', 280000, NULL, 40, 'https://images.unsplash.com/photo-1593482892290-f54927ae1bb6?w=800&q=80', 'Vừa', 'easy', 'low', 'low', 1, 'active'),
(2, 'Cây Kim Tiền', 'cay-kim-tien', 'Cây kim tiền mang ý nghĩa phong thủy tốt, thu hút tài lộc. Lá xanh bóng, dễ chăm sóc.', 450000, 399000, 30, 'https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=800&q=80', 'Lớn', 'easy', 'medium', 'low', 1, 'active'),
(3, 'Sen Đá Mix', 'sen-da-mix', 'Bộ sưu tập sen đá mix nhiều màu sắc, hình dáng đa dạng. Dễ trồng và chăm sóc.', 150000, NULL, 100, 'https://images.unsplash.com/photo-1459156212016-c812468e2115?w=800&q=80', 'Nhỏ', 'easy', 'high', 'low', 0, 'active'),
(1, 'Cây Phát Tài', 'cay-phat-tai', 'Cây phát tài - biểu tượng của sự thịnh vượng và may mắn. Thân cây bện đẹp mắt.', 890000, 799000, 15, 'https://images.unsplash.com/photo-1509423350716-97f9360b4e09?w=800&q=80', 'Lớn', 'medium', 'medium', 'medium', 1, 'active'),
(2, 'Cây Tùng La Hán Mini', 'cay-tung-la-han-mini', 'Cây tùng la hán mini - phong cách bonsai, mang lại không gian xanh thanh lịch cho văn phòng.', 650000, NULL, 25, 'https://images.unsplash.com/photo-1512428813834-c702c7702b78?w=800&q=80', 'Nhỏ', 'medium', 'high', 'medium', 0, 'active'),
(5, 'Cây Ngọc Ngân', 'cay-ngoc-ngan', 'Cây ngọc ngân - cây phong thủy hút tài lộc, lá tròn xanh bóng. Phù hợp đặt tại quầy thu ngân.', 320000, NULL, 35, 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?w=800&q=80', 'Vừa', 'easy', 'medium', 'medium', 0, 'active'),
(1, 'Cây Thiết Mộc Lan', 'cay-thiet-moc-lan', 'Cây thiết mộc lan với lá xanh đậm, chịu bóng tốt. Lý tưởng cho không gian trong nhà.', 420000, 379000, 20, 'https://images.unsplash.com/photo-1545241047-6083a3684587?w=800&q=80', 'Vừa', 'easy', 'low', 'medium', 1, 'active'),
(3, 'Sen Đá Hoa Hồng', 'sen-da-hoa-hong', 'Sen đá hình hoa hồng đẹp mắt, màu xanh pastel dễ thương. Chăm sóc đơn giản.', 180000, NULL, 60, 'https://images.unsplash.com/photo-1542090675-da82b9a9d923?w=800&q=80', 'Nhỏ', 'easy', 'high', 'low', 0, 'active'),
(2, 'Cây Vạn Lộc', 'cay-van-loc', 'Cây vạn lộc - cây phong thủy mang lại vận may. Lá xanh mướt, thân thẳng đẹp.', 380000, NULL, 30, 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?w=800&q=80', 'Vừa', 'medium', 'medium', 'medium', 0, 'active');

INSERT INTO product_images (product_id, image_url, sort_order, is_primary) VALUES
(1, 'https://images.unsplash.com/photo-1614594975525-e45190c55d0b?w=800&q=80', 0, 1),
(2, 'https://images.unsplash.com/photo-1593482892290-f54927ae1bb6?w=800&q=80', 0, 1),
(3, 'https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=800&q=80', 0, 1),
(4, 'https://images.unsplash.com/photo-1459156212016-c812468e2115?w=800&q=80', 0, 1),
(5, 'https://images.unsplash.com/photo-1509423350716-97f9360b4e09?w=800&q=80', 0, 1);

INSERT INTO product_variants (product_id, sku, option_name, option_value, price_override, stock_quantity, status) VALUES
(3, 'TREE-KIMTIEN-M', 'size', 'M', 399000, 12, 'active'),
(3, 'TREE-KIMTIEN-L', 'size', 'L', 450000, 18, 'active'),
(4, 'SENDA-MIX-S', 'size', 'S', 150000, 60, 'active'),
(4, 'SENDA-MIX-M', 'size', 'M', 180000, 40, 'active');

INSERT INTO cart (user_id, product_id, variant_id, quantity, price_snapshot) VALUES
(2, 1, NULL, 1, 350000),
(2, 3, 1, 2, 399000),
(3, 2, NULL, 1, 280000);

INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, usage_limit, used_count, start_date, end_date, status) VALUES
('GIAM10', 'percent', 10, 300000, 100, 0, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 'active'),
('FREESHIP50', 'fixed', 50000, 500000, 50, 0, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 'active');

INSERT INTO orders (
    user_id, order_number, full_name, email, phone, address, note,
    subtotal, discount_amount, shipping_fee, total_amount, coupon_code,
    payment_method, payment_status, order_status
) VALUES
(2, 'ORD202603290001', 'Nguyễn Văn A', 'user01@example.com', '0901234567', '123 Đường ABC, Linh Trung, Thủ Đức, TP.HCM', 'Giao giờ hành chính', 798000, 79800, 30000, 748200, 'GIAM10', 'online_mock', 'paid', 'confirmed'),
(3, 'ORD202603290002', 'Trần Thị B', 'user02@example.com', '0912345678', '12 Ngõ Hoa Giấy, Dịch Vọng, Cầu Giấy, Hà Nội', NULL, 280000, 0, 30000, 310000, NULL, 'cod', 'unpaid', 'pending');

INSERT INTO order_details (order_id, product_id, variant_id, product_name, product_image, price, quantity, subtotal) VALUES
(1, 3, 1, 'Cây Kim Tiền', 'https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=800&q=80', 399000, 2, 798000),
(2, 2, NULL, 'Cây Lưỡi Hổ', 'https://images.unsplash.com/photo-1593482892290-f54927ae1bb6?w=800&q=80', 280000, 1, 280000);

INSERT INTO payments (order_id, provider, transaction_code, status, amount, paid_at, note) VALUES
(1, 'online_mock', 'MOCKTXN001', 'paid', 748200, '2026-03-29 10:30:00', 'Thanh toán mô phỏng thành công'),
(2, 'cod', NULL, 'unpaid', 310000, NULL, 'Thanh toán khi nhận hàng');

INSERT INTO coupon_usages (coupon_id, user_id, order_id) VALUES
(1, 2, 1);

UPDATE coupons
SET used_count = 1
WHERE id = 1;

INSERT INTO reviews (product_id, user_id, rating, comment, status) VALUES
(1, 2, 5, 'Cây rất đẹp, đóng gói cẩn thận. Shop tư vấn nhiệt tình!', 'approved'),
(2, 2, 4, 'Cây khỏe, giao hàng nhanh. Sẽ ủng hộ shop tiếp!', 'approved'),
(3, 2, 5, 'Cây kim tiền to và đẹp. Giao hàng tận nơi chu đáo.', 'approved'),
(4, 3, 5, 'Sen đá nhỏ xinh, rất dễ chăm.', 'approved');

INSERT INTO wishlists (user_id, product_id) VALUES
(2, 5),
(2, 7),
(3, 3);

INSERT INTO inventory_logs (product_id, order_id, action, quantity, note) VALUES
(3, 1, 'deduct', 2, 'Trừ kho do đơn hàng ORD202603290001'),
(2, 2, 'deduct', 1, 'Trừ kho do đơn hàng ORD202603290002'),
(1, NULL, 'import', 50, 'Nhập kho ban đầu'),
(4, NULL, 'import', 100, 'Nhập kho ban đầu');
