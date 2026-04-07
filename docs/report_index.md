# Report Index

Tai lieu bao cao cho phan backend cua WebGreenSpace duoc tach thanh 4 phan de de nop, de review va de dung lai khi demo:

1. [ERD](./report_erd.md)
2. [Sequence Diagram](./report_sequence.md)
3. [System Flow](./report_system_flow.md)
4. [Docker Setup](./report_docker_setup.md)

## Tong quan nhanh

- Kien truc: monolith PHP + MySQL + Apache trong Docker.
- Entry point: `public/index.php`, router o `public/routes.php`.
- Nhom chuc nang chinh:
  - Auth va phan quyen user/admin
  - Product, category, image, inventory
  - Cart, checkout, order, payment mo phong
  - Admin dashboard, user management, order management
- CSDL runtime: `webgreenspace`
- Payment scope: `cod` va `online_mock`, khong tich hop cong thanh toan that.

## Cac URL chinh

- User site: `http://localhost:8000`
- Admin login: `http://localhost:8000/admin/login`
- phpMyAdmin: `http://localhost:8080`

## Tai lieu lien quan

- Checklist backend: [backend_checklist.md](./backend_checklist.md)
