-- Sample data for webgreenspace
-- Compatible with schema_revised.sql
USE webgreenspace;

SET NAMES utf8mb4;

-- ========================================
-- USERS
-- Password hash bên dưới chỉ là mẫu dùng lại từ file cũ
-- ========================================
INSERT INTO users (username, email, password, full_name, phone, role, status) VALUES
('admin', 'admin@webgreenspace.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '0900000000', 'admin', 'active'),
('user01', 'user01@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A', '0901234567', 'user', 'active'),
('user02', 'user02@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị B', '0912345678', 'user', 'active');

-- ========================================
-- ADDRESSES
-- ========================================
INSERT INTO addresses (user_id, receiver_name, phone, province, district, ward, address_line, is_default) VALUES
(2, 'Nguyễn Văn A', '0901234567', 'TP.HCM', 'Thủ Đức', 'Linh Trung', '123 Đường ABC', 1),
(2, 'Nguyễn Văn A', '0901234567', 'TP.HCM', 'Quận 1', 'Bến Nghé', '45 Đường XYZ', 0),
(3, 'Trần Thị B', '0912345678', 'Hà Nội', 'Cầu Giấy', 'Dịch Vọng', '12 Ngõ Hoa Giấy', 1);

-- ========================================
-- CATEGORIES
-- ========================================
INSERT INTO categories (name, slug, description, status) VALUES
('Cây Nội Thất', 'cay-noi-that', 'Cây trang trí trong nhà', 'active'),
('Cây Văn Phòng', 'cay-van-phong', 'Cây phù hợp cho văn phòng', 'active'),
('Cây Sen Đá', 'cay-sen-da', 'Các loại cây sen đá và cây mọng nước', 'active'),
('Cây Thủy Sinh', 'cay-thuy-sinh', 'Cây trồng trong nước', 'active'),
('Cây Phong Thủy', 'cay-phong-thuy', 'Cây mang ý nghĩa phong thủy', 'active');

-- ========================================
-- PRODUCTS
-- ========================================
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

-- ========================================
-- PRODUCT IMAGES
-- ========================================
INSERT INTO product_images (product_id, image_url, sort_order, is_primary) VALUES
(1, 'https://images.unsplash.com/photo-1614594975525-e45190c55d0b?w=800&q=80', 0, 1),
(2, 'https://images.unsplash.com/photo-1593482892290-f54927ae1bb6?w=800&q=80', 0, 1),
(3, 'https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=800&q=80', 0, 1),
(4, 'https://images.unsplash.com/photo-1459156212016-c812468e2115?w=800&q=80', 0, 1),
(5, 'https://images.unsplash.com/photo-1509423350716-97f9360b4e09?w=800&q=80', 0, 1);

-- ========================================
-- PRODUCT VARIANTS
-- Optional cho đồ án; có thể dùng hoặc bỏ qua trong code
-- ========================================
INSERT INTO product_variants (product_id, sku, option_name, option_value, price_override, stock_quantity, status) VALUES
(3, 'TREE-KIMTIEN-M', 'size', 'M', 399000, 12, 'active'),
(3, 'TREE-KIMTIEN-L', 'size', 'L', 450000, 18, 'active'),
(4, 'SENDA-MIX-S', 'size', 'S', 150000, 60, 'active'),
(4, 'SENDA-MIX-M', 'size', 'M', 180000, 40, 'active');

-- ========================================
-- CART
-- ========================================
INSERT INTO cart (user_id, product_id, variant_id, quantity, price_snapshot) VALUES
(2, 1, NULL, 1, 350000),
(2, 3, 1, 2, 399000),
(3, 2, NULL, 1, 280000);

-- ========================================
-- COUPONS
-- ========================================
INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, usage_limit, used_count, start_date, end_date, status) VALUES
('GIAM10', 'percent', 10, 300000, 100, 0, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 'active'),
('FREESHIP50', 'fixed', 50000, 500000, 50, 0, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 'active');

-- ========================================
-- ORDERS
-- payment_method: cod | online_mock
-- payment_status: unpaid | pending_review | paid | failed
-- ========================================
INSERT INTO orders (
    user_id, order_number, full_name, email, phone, address, note,
    subtotal, discount_amount, shipping_fee, total_amount, coupon_code,
    payment_method, payment_status, order_status
) VALUES
(2, 'ORD202603290001', 'Nguyễn Văn A', 'user01@example.com', '0901234567', '123 Đường ABC, Linh Trung, Thủ Đức, TP.HCM', 'Giao giờ hành chính', 798000, 79800, 30000, 748200, 'GIAM10', 'online_mock', 'paid', 'confirmed'),
(3, 'ORD202603290002', 'Trần Thị B', 'user02@example.com', '0912345678', '12 Ngõ Hoa Giấy, Dịch Vọng, Cầu Giấy, Hà Nội', NULL, 280000, 0, 30000, 310000, NULL, 'cod', 'unpaid', 'pending');

-- ========================================
-- ORDER DETAILS
-- ========================================
INSERT INTO order_details (order_id, product_id, variant_id, product_name, product_image, price, quantity, subtotal) VALUES
(1, 3, 1, 'Cây Kim Tiền', 'https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=800&q=80', 399000, 2, 798000),
(2, 2, NULL, 'Cây Lưỡi Hổ', 'https://images.unsplash.com/photo-1593482892290-f54927ae1bb6?w=800&q=80', 280000, 1, 280000);

-- ========================================
-- PAYMENTS
-- Mô phỏng thanh toán cho đồ án
-- ========================================
INSERT INTO payments (order_id, provider, transaction_code, status, amount, paid_at, note) VALUES
(1, 'online_mock', 'MOCKTXN001', 'paid', 748200, '2026-03-29 10:30:00', 'Thanh toán mô phỏng thành công'),
(2, 'cod', NULL, 'unpaid', 310000, NULL, 'Thanh toán khi nhận hàng');

-- ========================================
-- COUPON USAGES
-- ========================================
INSERT INTO coupon_usages (coupon_id, user_id, order_id) VALUES
(1, 2, 1);

UPDATE coupons
SET used_count = 1
WHERE id = 1;

-- ========================================
-- REVIEWS
-- ========================================
INSERT INTO reviews (product_id, user_id, rating, comment, status) VALUES
(1, 2, 5, 'Cây rất đẹp, đóng gói cẩn thận. Shop tư vấn nhiệt tình!', 'approved'),
(2, 2, 4, 'Cây khỏe, giao hàng nhanh. Sẽ ủng hộ shop tiếp!', 'approved'),
(3, 2, 5, 'Cây kim tiền to và đẹp. Giao hàng tận nơi chu đáo.', 'approved'),
(4, 3, 5, 'Sen đá nhỏ xinh, rất dễ chăm.', 'approved');

-- ========================================
-- WISHLISTS
-- ========================================
INSERT INTO wishlists (user_id, product_id) VALUES
(2, 5),
(2, 7),
(3, 3);

-- ========================================
-- INVENTORY LOGS
-- ========================================
INSERT INTO inventory_logs (product_id, order_id, action, quantity, note) VALUES
(3, 1, 'deduct', 2, 'Trừ kho do đơn hàng ORD202603290001'),
(2, 2, 'deduct', 1, 'Trừ kho do đơn hàng ORD202603290002'),
(1, NULL, 'import', 50, 'Nhập kho ban đầu'),
(4, NULL, 'import', 100, 'Nhập kho ban đầu');
