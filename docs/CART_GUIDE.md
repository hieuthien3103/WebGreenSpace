# Hướng Dẫn Chức Năng Giỏ Hàng 🛒

## 📌 Tổng Quan

Chức năng giỏ hàng cho phép người dùng:
- Thêm sản phẩm vào giỏ với số lượng tùy chỉnh
- Xem danh sách sản phẩm trong giỏ
- Cập nhật số lượng sản phẩm
- Xóa sản phẩm khỏi giỏ
- Hiển thị tổng số lượng và giá trị giỏ hàng

## 🎯 Các Thành Phần

### 1. **Cart Model** (`app/models/Cart.php`)
Xử lý các operations với database:
- `addItem()` - Thêm sản phẩm hoặc tăng số lượng
- `getCartItems()` - Lấy danh sách sản phẩm trong giỏ
- `updateQuantity()` - Cập nhật số lượng
- `removeItem()` - Xóa sản phẩm
- `clearCart()` - Xóa toàn bộ giỏ hàng
- `getItemCount()` - Đếm tổng số lượng items
- `getCartTotal()` - Tính tổng giá trị

### 2. **CartService** (`app/services/CartService.php`)
Business logic layer:
- Validation sản phẩm và số lượng
- Kiểm tra tồn kho
- Xử lý lỗi và return response chuẩn
- Tích hợp với ProductModel

### 3. **Cart API** (`public/cart_api.php`)
RESTful API endpoint:
- `POST` - Thêm vào giỏ
- `GET` - Lấy thông tin giỏ hàng
- `PUT` - Cập nhật số lượng
- `DELETE` - Xóa sản phẩm

### 4. **Frontend Integration**
- Form "Thêm vào giỏ" trên trang chi tiết sản phẩm
- AJAX request không reload trang
- Real-time cập nhật badge trên icon giỏ hàng
- Thông báo toast khi thêm thành công/thất bại

## 🔧 Cách Sử Dụng

### Từ Trang Chi Tiết Sản Phẩm

```javascript
// Người dùng điều chỉnh số lượng và click "Thêm vào giỏ"
1. Chọn số lượng (default: 1)
2. Click button "Thêm vào giỏ"
3. JavaScript gửi AJAX request đến cart_api.php
4. Backend validate và lưu vào database
5. Trả về response với cart_count mới
6. Frontend cập nhật badge trên header
7. Hiển thị toast notification
```

### API Usage

**Thêm vào giỏ:**
```javascript
fetch('cart_api.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `product_id=1&quantity=2`
})
```

**Lấy giỏ hàng:**
```javascript
fetch('cart_api.php', {
    method: 'GET'
})
```

**Cập nhật số lượng:**
```javascript
fetch('cart_api.php', {
    method: 'PUT',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        product_id: 1,
        quantity: 3
    })
})
```

**Xóa sản phẩm:**
```javascript
fetch('cart_api.php', {
    method: 'DELETE',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        product_id: 1
    })
})
```

## 📊 Database Schema

```sql
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);
```

**Key Points:**
- Mỗi user + product chỉ có 1 record (UNIQUE constraint)
- Khi thêm duplicate → tự động tăng quantity
- CASCADE delete khi xóa user hoặc product

## 🎨 UI/UX Features

### 1. **Product Detail Page**
- Form với số lượng input (+ / -)
- Button "Thêm vào giỏ" với loading state
- Hiển thị số lượng còn trong kho
- Disable button nếu hết hàng

### 2. **Header Cart Badge**
- Icon giỏ hàng với badge số lượng
- Badge ẩn khi giỏ trống
- Real-time cập nhật qua AJAX
- Hover effect

### 3. **Toast Notifications**
- Success: Màu xanh, "✓ Đã thêm vào giỏ hàng"
- Error: Màu đỏ với thông báo lỗi cụ thể
- Auto dismiss sau 3 giây
- Smooth animation

## 🧪 Testing

### Unit Tests
```bash
cd C:\Users\Admin\WebGreenSpace\tests
C:\xampp\php\php.exe test_cart.php
```

### Test Cases
1. ✅ Thêm sản phẩm mới vào giỏ
2. ✅ Thêm sản phẩm đã có (tăng quantity)
3. ✅ Lấy danh sách giỏ hàng
4. ✅ Cập nhật số lượng sản phẩm
5. ✅ Xóa sản phẩm khỏi giỏ
6. ✅ Tính tổng giá trị giỏ hàng
7. ✅ Validate sản phẩm không tồn tại
8. ✅ Validate số lượng không hợp lệ

### Manual Testing
1. Truy cập: http://localhost:8000/product-detail.php?id=1
2. Điều chỉnh số lượng
3. Click "Thêm vào giỏ"
4. Kiểm tra:
   - Toast notification hiển thị
   - Badge trên header cập nhật
   - Button chuyển sang trạng thái success
5. Thử thêm cùng sản phẩm lần 2 → quantity tăng

## ⚙️ Configuration

### User Authentication
Hiện tại sử dụng temporary user ID:
```php
// In cart_api.php
$userId = $_SESSION['user_id'] ?? 1; // Default for testing
```

**TODO:** Tích hợp với hệ thống authentication thật

### Session Management
```php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

## 🐛 Troubleshooting

### Lỗi: "Integrity constraint violation"
**Nguyên nhân:** User ID không tồn tại trong bảng users  
**Giải pháp:** Chạy `tests/check_and_create_user.php` để tạo test user

### Lỗi: "Column not found: image_path"
**Nguyên nhân:** Database schema khác với code  
**Giải pháp:** Đã sửa trong Cart model, dùng `thumbnail_url` thay vì `image_path`

### Badge không cập nhật
**Nguyên nhân:** JavaScript không tìm thấy element  
**Giải pháp:** Kiểm tra selector trong function `updateCartCount()`

## 🚀 Cải Tiến Tương Lai

### Phase 1 - Immediate
- [ ] Trang giỏ hàng đầy đủ (`/cart`)
- [ ] Thêm/giảm số lượng trực tiếp trên cart page
- [ ] Áp dụng mã giảm giá

### Phase 2 - Enhanced
- [ ] Quick add từ product listing (không cần vào detail)
- [ ] Mini cart dropdown từ header icon
- [ ] Save cart to localStorage (guest users)
- [ ] Cart recovery (abandoned cart)

### Phase 3 - Advanced
- [ ] Wishlist (yêu thích)
- [ ] Compare products
- [ ] Recently viewed
- [ ] Recommend products dựa trên cart

## 📝 API Response Format

### Success Response
```json
{
    "success": true,
    "message": "Đã thêm vào giỏ hàng",
    "cart_count": 3,
    "cart_total": 500000
}
```

### Error Response
```json
{
    "success": false,
    "message": "Không đủ hàng trong kho"
}
```

### Cart Data Response
```json
{
    "success": true,
    "items": [
        {
            "id": 1,
            "product_id": 1,
            "name": "Cây Trầu Bà Nam Mỹ",
            "price": 250000,
            "quantity": 2,
            "image_url": "..."
        }
    ],
    "total": 500000,
    "count": 2
}
```

## 🔗 Files Liên Quan

- `/app/models/Cart.php` - Database operations
- `/app/services/CartService.php` - Business logic
- `/public/cart_api.php` - API endpoint
- `/public/product-detail.php` - Add to cart UI
- `/public/includes/header.php` - Cart badge
- `/tests/test_cart.php` - Unit tests

---

**Created:** March 8, 2026  
**Version:** 1.0  
**Author:** GreenSpace Team
