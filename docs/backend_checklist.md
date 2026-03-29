# Backend Checklist – Web Bán Cây Xanh

## 1. Môi trường & Docker
- [X] Tạo project PHP
- [X] Cấu hình Docker (PHP, MySQL, phpMyAdmin)
- [X] Tạo docker-compose.yml
- [X] Tạo file .env
- [X] Chạy docker compose up -d thành công

## 2. Database
- [X] Tạo database web_ban_cay
- [X] Tạo bảng users
- [X] Tạo bảng categories
- [X] Tạo bảng products
- [X] Tạo bảng carts & cart_items
- [X] Tạo bảng orders & order_items
- [X] Tạo bảng payments
- [X] Thiết lập khóa ngoại
- [X] Seed dữ liệu mẫu

## 3. Auth
- [X] Đăng ký
- [X] Đăng nhập
- [X] Hash password
- [X] Session login
- [X] Phân quyền admin/user

## 4. Sản phẩm & Danh mục
- [ ] CRUD danh mục (admin)
- [ ] CRUD sản phẩm (admin)
- [X] Hiển thị danh sách sản phẩm
- [X] Chi tiết sản phẩm
- [X] Tìm kiếm & lọc

## 5. Giỏ hàng
- [X] Thêm vào giỏ
- [X] Cập nhật số lượng
- [X] Xóa sản phẩm
- [X] Tính tổng tiền

## 6. Đặt hàng
- [ ] Form checkout
- [ ] Tạo order
- [ ] Tạo order_items
- [ ] Trừ tồn kho
- [ ] Xóa giỏ hàng
- [ ] Dùng transaction

## 7. Thanh toán mô phỏng
- [ ] COD
- [ ] Chuyển khoản giả lập
- [ ] Nút “Tôi đã thanh toán”
- [ ] Ghi bảng payments
- [ ] Update payment_status

## 8. Đơn hàng
- [ ] User xem đơn của mình
- [ ] Xem chi tiết đơn
- [ ] Hiển thị trạng thái

## 9. Admin
- [ ] Quản lý sản phẩm
- [ ] Quản lý đơn hàng
- [ ] Cập nhật trạng thái đơn
- [ ] Quản lý user

## 10. Bảo mật
- [ ] Validate input
- [ ] PDO prepared statements
- [ ] Escape output
- [ ] Check role admin
- [ ] Validate upload ảnh

## 11. Kiểm thử
- [ ] Test đăng ký / đăng nhập
- [ ] Test giỏ hàng
- [ ] Test checkout
- [ ] Test thanh toán
- [ ] Test phân quyền

## 12. Báo cáo
- [ ] ERD
- [ ] Sequence diagram
- [ ] Mô tả luồng hệ thống
- [ ] Docker setup

---

## Tổng tiến độ

| Module | Trạng thái |
|--------|----------|
| Environment | [ ] |
| Database | [ ] |
| Auth | [ ] |
| Product | [ ] |
| Cart | [ ] |
| Order | [ ] |
| Payment | [ ] |
| Admin | [ ] |
| Security | [ ] |
| Testing | [ ] |
| Report | [ ] |
