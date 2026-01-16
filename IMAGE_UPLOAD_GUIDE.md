# 📸 Hướng Dẫn Quản Lý Ảnh Sản Phẩm

## 🎯 Tổng quan

Bạn có **2 cách** để đưa ảnh vào database:

### ✅ Cách 1: Dùng URL từ Internet (Khuyến nghị - Nhanh & Miễn phí)
- Lấy ảnh từ Unsplash, Pexels
- Không tốn dung lượng server
- Tốc độ load nhanh

### ✅ Cách 2: Upload ảnh từ máy tính
- Upload ảnh từ máy lên server
- Lưu vào folder `/uploads/products/`
- Phù hợp khi bạn có ảnh riêng

---

## 🚀 Cách 1: Cập nhật ảnh bằng URL

### Bước 1: Tìm ảnh miễn phí

**Unsplash.com** (Recommended)
```
1. Truy cập: https://unsplash.com
2. Tìm kiếm: "plants", "cactus", "succulent", "indoor plants"
3. Click vào ảnh bạn thích
4. Click nút "Download" (bên phải)
5. Copy URL ảnh (có dạng: https://images.unsplash.com/photo-...)
```

**Pexels.com**
```
1. Truy cập: https://pexels.com
2. Tìm kiếm cây cảnh
3. Click ảnh > Copy URL
```

### Bước 2: Cập nhật vào database

**Truy cập trang admin:**
```
http://localhost/WebGreenSpace/public/admin_upload_images.php
```

**Làm theo:**
1. Chọn sản phẩm từ dropdown
2. Dán URL ảnh vào ô "URL hình ảnh"
3. Click "Cập nhật URL ảnh"
4. Xong! ✅

---

## 📤 Cách 2: Upload ảnh từ máy tính

### Bước 1: Chuẩn bị ảnh

**Yêu cầu:**
- Format: JPG, PNG, WEBP, GIF
- Kích thước: Tối đa 5MB
- Khuyến nghị: 800x800px đến 1200x1200px

### Bước 2: Upload

**Truy cập:**
```
http://localhost/WebGreenSpace/public/admin_upload_images.php
```

**Cách upload:**
1. Chọn sản phẩm
2. Kéo thả ảnh vào vùng "Upload" HOẶC click để chọn file
3. Xem preview ảnh
4. Click "Upload và cập nhật"
5. Ảnh sẽ lưu vào: `/uploads/products/product_ID_timestamp.jpg`

---

## 🔧 Cập nhật hàng loạt bằng SQL

Nếu bạn muốn cập nhật nhiều ảnh cùng lúc:

### Script 1: Dùng URL Unsplash

```sql
-- Cập nhật ảnh cho từng sản phẩm
UPDATE products SET thumbnail_url = 'https://images.unsplash.com/photo-1614594975525-e45190c55d0b?w=800' WHERE id = 1;
UPDATE products SET thumbnail_url = 'https://images.unsplash.com/photo-1593482892290-f54927ae1bb6?w=800' WHERE id = 2;
UPDATE products SET thumbnail_url = 'https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=800' WHERE id = 3;
-- ... tiếp tục cho các sản phẩm khác
```

### Script 2: Nếu bạn đã upload ảnh vào folder

```sql
-- Nếu ảnh trong folder uploads/products/
UPDATE products SET thumbnail_url = 'products/cay-trau-ba-nam-my.jpg' WHERE slug = 'cay-trau-ba-nam-my';
UPDATE products SET thumbnail_url = 'products/cay-luoi-ho.jpg' WHERE slug = 'cay-luoi-ho';
-- ... 
```

---

## 📝 Ví dụ thực tế

### Ví dụ 1: Cập nhật 1 sản phẩm bằng URL

```
Sản phẩm: Cây Trầu Bà Nam Mỹ (ID: 1)
URL ảnh: https://images.unsplash.com/photo-1614594975525-e45190c55d0b?w=800&q=80

SQL:
UPDATE products 
SET thumbnail_url = 'https://images.unsplash.com/photo-1614594975525-e45190c55d0b?w=800&q=80' 
WHERE id = 1;
```

### Ví dụ 2: Upload ảnh từ máy

```
1. Bạn có file: trau-ba-nam-my.jpg trên máy
2. Vào trang admin_upload_images.php
3. Chọn sản phẩm "Cây Trầu Bà Nam Mỹ"
4. Kéo thả file trau-ba-nam-my.jpg
5. Click Upload

=> Ảnh sẽ lưu thành: uploads/products/product_1_1673344567.jpg
=> Database tự động update: thumbnail_url = 'products/product_1_1673344567.jpg'
```

---

## 🛠️ Các file đã tạo

```
public/
├── admin_upload_images.php      # Trang admin quản lý ảnh (giao diện)
├── get_products.php             # API lấy danh sách sản phẩm
├── update_product_image.php     # Xử lý cập nhật URL
└── upload_product_image.php     # Xử lý upload file

uploads/
└── products/                    # Thư mục lưu ảnh upload
```

---

## 🎨 Gợi ý tìm ảnh cây cảnh miễn phí

### Keywords tìm kiếm:
- "potted plant"
- "indoor plants"
- "succulent"
- "cactus"
- "houseplant"
- "monstera"
- "snake plant"
- "fiddle leaf fig"

### Nguồn ảnh miễn phí:
1. **Unsplash.com** ⭐ (Recommend)
2. **Pexels.com**
3. **Pixabay.com**
4. **Freepik.com** (cần credit)

---

## ⚡ Quick Start

### Nhanh nhất - Cập nhật bằng URL:

1. Mở: `http://localhost/WebGreenSpace/public/admin_upload_images.php`
2. Tìm ảnh tại: `https://unsplash.com/s/photos/plants`
3. Copy URL ảnh
4. Dán vào form và submit
5. Done! 🎉

---

## 🐛 Troubleshooting

### Lỗi: Upload failed
- Kiểm tra file size < 5MB
- Đảm bảo format đúng (JPG, PNG, WEBP)
- Kiểm tra quyền write folder `/uploads/products/`

### Lỗi: Database không update
- Kiểm tra kết nối database
- Xem console log error

### Ảnh không hiển thị
- Nếu dùng URL: Kiểm tra URL có hợp lệ không
- Nếu upload: Kiểm tra file có trong folder uploads chưa
- Clear cache trình duyệt (Ctrl + F5)

---

## 📞 Hỗ trợ

Nếu cần thêm tính năng:
- Upload nhiều ảnh cho 1 sản phẩm
- Crop/resize ảnh tự động
- Image optimization
- Gallery manager

Liên hệ để được hỗ trợ!
