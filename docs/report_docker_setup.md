# Docker Setup

Tai lieu nay tong hop cach chay WebGreenSpace bang Docker trong moi truong local.

## 1. Thanh phan Docker

File chinh: `docker-compose.yml`

Services hien tai:

- `web`
  - Build tu `Dockerfile`
  - Chay Apache + PHP 8.2
  - Map port `8000:80`
  - Mount source code vao `/var/www/html`
- `db`
  - Image `mysql:8.0`
  - Tao CSDL `webgreenspace`
  - Map port `3306:3306`
  - Mount `./database` vao `/docker-entrypoint-initdb.d`
- `phpmyadmin`
  - Image `phpmyadmin/phpmyadmin`
  - Map port `8080:80`

## 2. Dockerfile

`Dockerfile` dang lam 4 viec chinh:

1. Dung base image `php:8.2-apache`
2. Bat `mod_rewrite`
3. Cai `mysqli`, `pdo`, `pdo_mysql`
4. Doi `DocumentRoot` cua Apache sang `/var/www/html/public`

## 3. Lenh khoi dong

Khoi dong toan bo stack:

```bash
docker compose up -d --build
```

Khoi dong rieng web sau khi sua code:

```bash
docker compose up -d web
```

Kiem tra container:

```bash
docker compose ps
```

## 4. Cac dia chi truy cap

- User site: `http://localhost:8000`
- Admin login: `http://localhost:8000/admin/login`
- phpMyAdmin: `http://localhost:8080`

## 5. Khoi tao database

Lan dau khoi dong:

- MySQL se tao DB `webgreenspace`
- SQL trong `database/` duoc mount vao thu muc init cua container DB
- Co the import toan bo schema va sample data bang `database/full_setup_revised.sql` qua phpMyAdmin hoac `mysql` CLI neu can
- Trang thai `pending_review` da nam san trong `database/full_setup_revised.sql`

## 6. Kiem tra ket noi va schema

Repo da co script test DB:

```bash
docker compose exec -T web php tests/test_db.php
```

Script nay giup kiem tra:

- ket noi DB
- ten database dang dung
- danh sach bang
- so ban ghi
- schema `orders.payment_status`
- schema `payments.status`

## 7. Kiem tra smoke test

Sau khi stack san sang, co the chay smoke test:

```bash
powershell -ExecutionPolicy Bypass -File tests/run_smoke_tests.ps1
```

Smoke test cover:

- signup/login
- cart
- checkout
- payment `online_mock`
- admin approve payment
- admin update order status

## 8. Xu ly su co nhanh

### 8.1 `Internal Server Error`

- Kiem tra `public/.htaccess`
- Kiem tra `public/index.php` co dispatch router
- Xem log web:

```bash
docker compose logs web --no-color --tail 100
```

### 8.2 Khong luu duoc `pending_review`

- Xac nhan migration enum da ap dung tren dung DB Docker
- Chay lai:

```bash
docker compose exec -T web php tests/test_db.php
```

### 8.3 Web thay doi code nhung chua phan anh

- Thu refresh manh `Ctrl + F5`
- Hoac rebuild:

```bash
docker compose up -d --build web
```

## 9. Ket luan

Docker setup hien tai phu hop cho:

- phat trien local
- demo do an
- kiem tra nhanh luong user/admin
- chia se moi truong thong nhat giua cac may
