USE webgreenspace;
SET NAMES utf8mb4;

UPDATE categories SET
    name = 'Cây Nội Thất',
    description = 'Cây trang trí trong nhà'
WHERE slug = 'cay-noi-that';

UPDATE categories SET
    name = 'Cây Văn Phòng',
    description = 'Cây phù hợp cho văn phòng'
WHERE slug = 'cay-van-phong';

UPDATE categories SET
    name = 'Cây Sen Đá',
    description = 'Các loại cây sen đá và cây mọng nước'
WHERE slug = 'cay-sen-da';

UPDATE categories SET
    name = 'Cây Thủy Sinh',
    description = 'Cây trồng trong nước'
WHERE slug = 'cay-thuy-sinh';

UPDATE categories SET
    name = 'Cây Phong Thủy',
    description = 'Cây mang ý nghĩa phong thủy'
WHERE slug = 'cay-phong-thuy';

UPDATE products SET
    name = 'Cây Trầu Bà Nam Mỹ',
    description = 'Cây trầu bà Nam Mỹ với lá xanh tươi, dễ chăm sóc và phù hợp với môi trường trong nhà. Giúp lọc không khí hiệu quả.',
    size = 'Vừa'
WHERE slug = 'cay-trau-ba-nam-my';

UPDATE products SET
    name = 'Cây Lưỡi Hổ',
    description = 'Cây lưỡi hổ - loại cây nội thất lý tưởng, chịu hạn tốt, lọc không khí hiệu quả. Phù hợp cho người bận rộn.',
    size = 'Vừa'
WHERE slug = 'cay-luoi-ho';

UPDATE products SET
    name = 'Cây Kim Tiền',
    description = 'Cây kim tiền mang ý nghĩa phong thủy tốt, thu hút tài lộc. Lá xanh bóng, dễ chăm sóc.',
    size = 'Lớn'
WHERE slug = 'cay-kim-tien';

UPDATE products SET
    name = 'Sen Đá Mix',
    description = 'Bộ sưu tập sen đá mix nhiều màu sắc, hình dáng đa dạng. Dễ trồng và chăm sóc.',
    size = 'Nhỏ'
WHERE slug = 'sen-da-mix';

UPDATE products SET
    name = 'Cây Phát Tài',
    description = 'Cây phát tài - biểu tượng của sự thịnh vượng và may mắn. Thân cây bện đẹp mắt.',
    size = 'Lớn'
WHERE slug = 'cay-phat-tai';

UPDATE products SET
    name = 'Cây Tùng La Hán Mini',
    description = 'Cây tùng la hán mini - phong cách bonsai, mang lại không gian xanh thanh lịch cho văn phòng.',
    size = 'Nhỏ'
WHERE slug = 'cay-tung-la-han-mini';

UPDATE products SET
    name = 'Cây Ngọc Ngân',
    description = 'Cây ngọc ngân - cây phong thủy hút tài lộc, lá tròn xanh bóng. Phù hợp đặt tại quầy thu ngân.',
    size = 'Vừa'
WHERE slug = 'cay-ngoc-ngan';

UPDATE products SET
    name = 'Cây Thiết Mộc Lan',
    description = 'Cây thiết mộc lan với lá xanh đậm, chịu bóng tốt. Lý tưởng cho không gian trong nhà.',
    size = 'Vừa'
WHERE slug = 'cay-thiet-moc-lan';

UPDATE products SET
    name = 'Sen Đá Hoa Hồng',
    description = 'Sen đá hình hoa hồng đẹp mắt, màu xanh pastel dễ thương. Chăm sóc đơn giản.',
    size = 'Nhỏ'
WHERE slug = 'sen-da-hoa-hong';

UPDATE products SET
    name = 'Cây Vạn Lộc',
    description = 'Cây vạn lộc - cây phong thủy mang lại vận may. Lá xanh mướt, thân thẳng đẹp.',
    size = 'Vừa'
WHERE slug = 'cay-van-loc';
