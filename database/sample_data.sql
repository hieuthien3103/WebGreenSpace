-- Sample Products Data for WebGreenSpace
USE webgreenspace;

-- Insert sample products
INSERT INTO products (category_id, name, slug, description, price, sale_price, stock, image, size, care_level, light_requirement, water_requirement, featured) VALUES 
(1, 'Cây Trầu Bà Nam Mỹ', 'cay-trau-ba-nam-my', 'Cây trầu bà Nam Mỹ với lá xanh tươi, dễ chăm sóc và phù hợp với môi trường trong nhà. Giúp lọc không khí hiệu quả.', 350000, NULL, 50, 'https://images.unsplash.com/photo-1614594975525-e45190c55d0b?w=800&q=80', 'Vừa', 'easy', 'medium', 'medium', TRUE),

(1, 'Cây Lưỡi Hổ', 'cay-luoi-ho', 'Cây lưỡi hổ - loại cây nội thất lý tưởng, chịu hạn tốt, lọc không khí hiệu quả. Phù hợp cho người bận rộn.', 280000, NULL, 40, 'https://images.unsplash.com/photo-1593482892290-f54927ae1bb6?w=800&q=80', 'Vừa', 'easy', 'low', 'low', TRUE),

(2, 'Cây Kim Tiền', 'cay-kim-tien', 'Cây kim tiền mang ý nghĩa phong thủy tốt, thu hút tài lộc. Lá xanh bóng, dễ chăm sóc.', 450000, 399000, 30, 'https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=800&q=80', 'Lớn', 'easy', 'medium', 'low', TRUE),

(3, 'Sen Đá Mix', 'sen-da-mix', 'Bộ sưu tập sen đá mix nhiều màu sắc, hình dáng đa dạng. Dễ trồng và chăm sóc.', 150000, NULL, 100, 'https://images.unsplash.com/photo-1459156212016-c812468e2115?w=800&q=80', 'Nhỏ', 'easy', 'high', 'low', FALSE),

(1, 'Cây Phát Tài', 'cay-phat-tai', 'Cây phát tài - biểu tượng của sự thịnh vượng và may mắn. Thân cây bện đẹp mắt.', 890000, 799000, 15, 'https://images.unsplash.com/photo-1509423350716-97f9360b4e09?w=800&q=80', 'Lớn', 'medium', 'medium', 'medium', TRUE),

(2, 'Cây Tùng La Hán Mini', 'cay-tung-la-han-mini', 'Cây tùng la hán mini - phong cách bonsai, mang lại không gian xanh thanh lịch cho văn phòng.', 650000, NULL, 25, 'https://images.unsplash.com/photo-1512428813834-c702c7702b78?w=800&q=80', 'Nhỏ', 'medium', 'high', 'medium', FALSE),

(5, 'Cây Ngọc Ngân', 'cay-ngoc-ngan', 'Cây ngọc ngân - cây phong thủy hút tài lộc, lá tròn xanh bóng. Phù hợp đặt tại quầy thu ngân.', 320000, NULL, 35, 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?w=800&q=80', 'Vừa', 'easy', 'medium', 'medium', FALSE),

(1, 'Cây Thiết Mộc Lan', 'cay-thiet-moc-lan', 'Cây thiết mộc lan với lá xanh đậm, chịu bóng tốt. Lý tưởng cho không gian trong nhà.', 420000, 379000, 20, 'https://images.unsplash.com/photo-1545241047-6083a3684587?w=800&q=80', 'Vừa', 'easy', 'low', 'medium', TRUE),

(3, 'Sen Đá Hoa Hồng', 'sen-da-hoa-hong', 'Sen đá hình hoa hồng đẹp mắt, màu xanh pastel dễ thương. Chăm sóc đơn giản.', 180000, NULL, 60, 'https://images.unsplash.com/photo-1542090675-da82b9a9d923?w=800&q=80', 'Nhỏ', 'easy', 'high', 'low', FALSE),

(2, 'Cây Vạn Lộc', 'cay-van-loc', 'Cây vạn lộc - cây phong thủy mang lại vận may. Lá xanh mướt, thân thẳng đẹp.', 380000, NULL, 30, 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?w=800&q=80', 'Vừa', 'medium', 'medium', 'medium', FALSE);

-- Insert sample reviews
INSERT INTO reviews (product_id, user_id, rating, comment, status) VALUES 
(1, 1, 5, 'Cây rất đẹp, đóng gói cẩn thận. Shop tư vấn nhiệt tình!', 'approved'),
(1, 1, 4, 'Cây khỏe, giao hàng nhanh. Sẽ ủng hộ shop tiếp!', 'approved'),
(2, 1, 5, 'Cây lưỡi hổ đẹp, đúng như hình. Rất hài lòng!', 'approved'),
(3, 1, 5, 'Cây kim tiền to và đẹp. Giao hàng tận nơi chu đáo.', 'approved');
