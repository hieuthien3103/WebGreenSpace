# Backend Checklist - Web Ban Cay Xanh

## 1. Moi truong & Docker
- [X] Tao project PHP
- [X] Cau hinh Docker (PHP, MySQL, phpMyAdmin)
- [X] Tao docker-compose.yml
- [X] Tao file .env
- [X] Chay `docker compose up -d` thanh cong

## 2. Database
- [X] Tao database `web_ban_cay`
- [X] Tao bang `users`
- [X] Tao bang `categories`
- [X] Tao bang `products`
- [X] Tao bang `carts` & `cart_items`
- [X] Tao bang `orders` & `order_items`
- [X] Tao bang `payments`
- [X] Thiet lap khoa ngoai
- [X] Seed du lieu mau

## 3. Auth
- [X] Dang ky
- [X] Dang nhap
- [X] Hash password
- [X] Session login
- [X] Phan quyen admin/user

## 4. San pham & Danh muc
- [X] CRUD danh muc (admin)
- [X] CRUD san pham (admin)
- [X] Hien thi danh sach san pham
- [X] Chi tiet san pham
- [X] Tim kiem & loc

## 5. Gio hang
- [X] Them vao gio
- [X] Cap nhat so luong
- [X] Xoa san pham
- [X] Tinh tong tien

## 6. Dat hang
- [X] Form checkout
- [X] Tao order
- [X] Tao order_items
- [X] Tru ton kho
- [X] Xoa gio hang
- [X] Dung transaction

## 7. Thanh toan mo phong
- [X] COD
- [X] Chuyen khoan gia lap
- [X] Nut "Toi da thanh toan"
- [X] Ghi bang `payments`
- [X] Update `payment_status`
- [X] Nut "Gui lai yeu cau thanh toan" khi bi tu choi

## 8. Don hang
- [X] User xem don cua minh
- [X] Xem chi tiet don
- [X] Hien thi trang thai

## 9. Admin
- [X] Quan ly san pham
- [X] Quan ly don hang
- [X] Cap nhat trang thai don
- [X] Quan ly user
- [X] Duyet/Tu choi chuyen khoan gia lap

## 10. Bao mat
- [X] Validate input
- [X] PDO prepared statements
- [X] Escape output
- [X] Check role admin
- [X] Validate upload anh

## 11. Kiem thu
- [X] Test dang ky / dang nhap
- [X] Test gio hang
- [X] Test checkout
- [X] Test thanh toan
- [X] Test phan quyen

## 12. Bao cao
- [X] ERD
- [X] Sequence diagram
- [X] Mo ta luong he thong
- [X] Docker setup

---

## Tong tien do

| Module | Trang thai |
|--------|------------|
| Environment | [X] |
| Database | [X] |
| Auth | [X] |
| Product | [X] |
| Cart | [X] |
| Order | [X] |
| Payment | [X] |
| Admin | [X] |
| Security | [X] |
| Testing | [X] |
| Report | [X] |
