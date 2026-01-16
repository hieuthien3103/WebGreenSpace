# Hướng dẫn sử dụng trang sản phẩm với dữ liệu thật

## Đã hoàn thành

✅ Cập nhật trang products.php để sử dụng dữ liệu thật từ database
✅ Kết nối với Product Model và Category Model  
✅ Hiển thị danh sách sản phẩm từ database
✅ Thêm chức năng tìm kiếm sản phẩm
✅ Thêm chức năng lọc theo danh mục
✅ Thêm chức năng sắp xếp (mới nhất, bán chạy, giá)
✅ Tạo file test để kiểm tra kết nối database

## Cách sử dụng

### 1. Cài đặt Database

```bash
# Tạo database
CREATE DATABASE webgreenspace;

# Import schema
mysql -u root -p webgreenspace < database/schema.sql

# Import dữ liệu mẫu
mysql -u root -p webgreenspace < database/sample_data.sql
```

### 2. Cấu hình

Kiểm tra file `config/config.php` để đảm bảo thông tin database đúng:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'webgreenspace');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 3. Kiểm tra kết nối

Truy cập: `http://localhost/WebGreenSpace/public/test_products.php`

Trang này sẽ hiển thị:
- Trạng thái kết nối database
- Danh sách sản phẩm từ database
- Danh sách danh mục

### 4. Xem trang sản phẩm

Truy cập: `http://localhost/WebGreenSpace/public/products.php`

## Tính năng

### 1. Hiển thị sản phẩm
- Lấy dữ liệu từ database thông qua Product Model
- Hiển thị hình ảnh, tên, giá, giá sale
- Hiển thị danh mục của sản phẩm
- Badge (Mới, Sale, Best Seller)

### 2. Tìm kiếm
URL: `products.php?search=cây`
- Tìm kiếm theo tên sản phẩm
- Tìm kiếm theo mô tả

### 3. Lọc theo danh mục
URL: `products.php?category=cay-de-ban`
- Click vào danh mục trong sidebar
- Chỉ hiển thị sản phẩm của danh mục đó

### 4. Sắp xếp
- Mới nhất (mặc định)
- Bán chạy
- Giá thấp đến cao
- Giá cao đến thấp

### 5. Phân trang
- Mỗi trang hiển thị 12 sản phẩm
- Nút "Xem thêm" để load trang tiếp theo

## Cấu trúc file

```
public/
  ├── products.php          # Trang danh sách sản phẩm (đã cập nhật)
  └── test_products.php     # Trang test kết nối database (mới)

app/
  ├── models/
  │   ├── Product.php       # Model sản phẩm (đã có)
  │   └── Category.php      # Model danh mục (đã có)
  └── controllers/
      └── ProductController.php  # Controller (có sẵn nhưng chưa dùng)

helpers/
  └── functions.php         # Helper functions (đã có)

config/
  ├── config.php           # Cấu hình ứng dụng
  └── database.php         # Class Database connection
```

## API Methods đã sử dụng

### Product Model
- `getAll($limit, $offset)` - Lấy tất cả sản phẩm
- `search($keyword, $limit, $offset)` - Tìm kiếm sản phẩm
- `getByCategory($categoryId, $limit, $offset)` - Lọc theo danh mục
- `getBestSellers($limit)` - Lấy sản phẩm bán chạy

### Category Model
- `getAll()` - Lấy tất cả danh mục
- `getBySlug($slug)` - Lấy danh mục theo slug

### Helper Functions
- `clean($data)` - Sanitize dữ liệu
- `format_currency($amount)` - Format tiền VND
- `base_url($path)` - Tạo URL
- `image_url($path)` - URL hình ảnh
- `upload_url($path)` - URL file upload

## Troubleshooting

### Lỗi "Connection Error"
- Kiểm tra MySQL đã chạy chưa
- Kiểm tra thông tin database trong config/config.php
- Kiểm tra database đã được tạo chưa

### Không có sản phẩm
- Chạy file schema.sql để tạo bảng
- Chạy file sample_data.sql để import dữ liệu mẫu

### Hình ảnh không hiển thị
- Kiểm tra đường dẫn APP_URL trong config/config.php
- Đảm bảo thư mục public/images có quyền đọc

## Tính năng có thể mở rộng

- [ ] Thêm filter theo giá
- [ ] Thêm filter theo đặc điểm (dễ chăm sóc, lọc không khí...)
- [ ] Thêm AJAX load more
- [ ] Thêm chức năng wishlist
- [ ] Thêm quick view modal
- [ ] Thêm so sánh sản phẩm
