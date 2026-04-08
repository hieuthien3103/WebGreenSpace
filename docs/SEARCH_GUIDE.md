# Hướng Dẫn Sử Dụng Chức Năng Tìm Kiếm 🔍

## 📌 Tổng Quan

Chức năng tìm kiếm cho phép người dùng dễ dàng tìm sản phẩm theo tên, mô tả, kết hợp với bộ lọc danh mục và giá.

## 🎯 Các Cách Tìm Kiếm

### 1. **Search Box Trên Header (Desktop)**
- Hiển thị ở góc phải header trên màn hình desktop (≥1024px)
- Gõ từ khóa và nhấn Enter để tìm kiếm
- Tự động chuyển đến trang sản phẩm với kết quả tìm kiếm

### 2. **Mobile Search Modal**
- Click icon 🔍 trên header (mobile/tablet)
- Popup mở ra với:
  - Ô tìm kiếm lớn
  - Gợi ý tìm kiếm phổ biến
- Nhấn ngoài modal hoặc ESC để đóng

### 3. **Tìm Kiếm Trên Trang Sản Phẩm**
- Ô tìm kiếm bên phải tiêu đề "Cửa hàng"
- Có thể kết hợp với:
  - Bộ lọc danh mục
  - Bộ lọc giá
  - Sắp xếp

## 🔧 Cách Hoạt Động

### Backend Logic

**1. Model Layer** - `Product.php`
```php
// Method tìm kiếm đơn giản
public function search(string $keyword, int $limit, int $offset): array

// Method tìm kiếm nâng cao với nhiều filter
public function getFilteredProducts(array $filters, int $limit, int $offset): array
```

**2. Service Layer** - `ProductService.php`
```php
// Xử lý business logic tìm kiếm
public function getProducts(array $filters): array
```

**3. Controller/View** - `products.php`
```php
// Nhận parameters
$filters = [
    'search' => $_GET['search'] ?? '',
    'category' => $_GET['category'] ?? '',
    'min_price' => $_GET['min_price'] ?? null,
    'max_price' => $_GET['max_price'] ?? null,
];

// Gọi service
$result = $productService->getProducts($filters);
```

### Tìm Kiếm Trong Database

Tìm kiếm trong 3 trường:
- `name` - Tên sản phẩm
- `short_description` - Mô tả ngắn
- `long_description` - Mô tả chi tiết

```sql
WHERE (
    p.name LIKE '%keyword%' OR 
    p.short_description LIKE '%keyword%' OR 
    p.long_description LIKE '%keyword%'
)
```

## 💡 Ví Dụ Sử Dụng

### Ví dụ 1: Tìm kiếm đơn giản
```
URL: /products.php?search=trầu+bà
Kết quả: Tất cả sản phẩm có chứa "trầu bà"
```

### Ví dụ 2: Tìm trong danh mục
```
URL: /products.php?search=cây&category=cay-de-ban
Kết quả: Sản phẩm chứa "cây" trong danh mục "Cây để bàn"
```

### Ví dụ 3: Tìm với giá
```
URL: /products.php?search=sen&max_price=200000
Kết quả: Sản phẩm chứa "sen" có giá ≤ 200,000đ
```

### Ví dụ 4: Tìm và sắp xếp
```
URL: /products.php?search=cây&sort=price_asc
Kết quả: Sản phẩm chứa "cây", sắp xếp giá tăng dần
```

## 🎨 Giao Diện

### Desktop Search Box
- Vị trí: Header góc phải
- Kích thước: 256px (w-64)
- Hiệu ứng: Rounded-full, dark mode support

### Mobile Search Modal
- Full-screen overlay với backdrop blur
- Card trắng/tối tùy theme
- Gợi ý tìm kiếm dạng tag pills
- Animation mượt mà

## 🧪 Testing

### Unit Tests
Chạy file test:
```bash
cd C:\Users\Admin\WebGreenSpace\tests
C:\xampp\php\php.exe test_search.php
```

### Test Cases
1. ✅ Tìm kiếm với từ khóa phổ biến
2. ✅ Tìm kiếm không có kết quả
3. ✅ Tìm kiếm + filter danh mục
4. ✅ Tìm kiếm + filter giá
5. ✅ Tìm kiếm trực tiếp từ Product model
6. ✅ Tìm kiếm qua ProductService

## 🐛 Các Lỗi Đã Sửa

### Lỗi: Invalid parameter number
**Nguyên nhân:** Sử dụng cùng placeholder nhiều lần trong LIKE
```php
// ❌ SAI
WHERE (name LIKE :keyword OR description LIKE :keyword)

// ✅ ĐÚNG  
WHERE (name LIKE :keyword1 OR description LIKE :keyword2)
```

**Đã sửa trong:**
- `Product::search()` - Line ~145
- `Product::getFilteredProducts()` - Line ~196

## 🚀 Cải Tiến Tương Lai

### Gợi ý
1. **Autocomplete/Suggestions**
   - AJAX live search
   - Hiển thị gợi ý khi gõ

2. **Search History**
   - Lưu từ khóa đã tìm
   - LocalStorage hoặc Session

3. **Advanced Filters**
   - Tìm theo tag
   - Tìm theo độ khó chăm sóc
   - Tìm theo nhu cầu ánh sáng/nước

4. **Full-text Search**
   - MySQL FULLTEXT index
   - Elastic Search integration

5. **Search Analytics**
   - Track từ khóa phổ biến
   - No-result searches

## 📝 API Reference

### GET /products.php

**Parameters:**
| Param | Type | Description |
|-------|------|-------------|
| `search` | string | Từ khóa tìm kiếm |
| `category` | string | Slug danh mục |
| `min_price` | float | Giá tối thiểu |
| `max_price` | float | Giá tối đa |
| `sort` | string | newest, price_asc, price_desc, bestseller |
| `page` | int | Số trang (pagination) |

**Example Response:**
```php
[
    'products' => [...],  // Array of products
    'category' => [...],  // Category data (if filtered)
    'total' => 15        // Total count
]
```

## 🔗 Files Liên Quan

- `/public/includes/header.php` - Search UI trong header
- `/public/products.php` - Main search page
- `/app/models/Product.php` - Database queries
- `/app/services/ProductService.php` - Business logic
- `/tests/test_search.php` - Unit tests

---

**Created:** March 8, 2026  
**Version:** 1.0  
**Author:** GreenSpace Team
