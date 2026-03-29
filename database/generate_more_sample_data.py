from __future__ import annotations

import argparse
import random
import re
import subprocess
import sys
from dataclasses import dataclass
from datetime import datetime, timedelta
from pathlib import Path
from typing import Iterable


ROOT_DIR = Path(__file__).resolve().parents[1]
DEFAULT_OUTPUT = ROOT_DIR / "database" / "generated_more_sample_data.sql"
PASSWORD_HASH = "$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi"


@dataclass(frozen=True)
class CategoryRow:
    id: int
    slug: str
    name: str


@dataclass(frozen=True)
class ProductSeed:
    category_slug: str
    name: str
    description: str
    size: str
    care_level: str
    light_requirement: str
    water_requirement: str
    price: int
    sale_price: int | None
    stock: int
    featured: int
    image: str


PRODUCT_SEEDS: list[ProductSeed] = [
    ProductSeed("cay-noi-that", "Cay Bach Ma Galaxy", "Cay noi that co tan la sang, hop phong khach va studio.", "Vua", "easy", "medium", "medium", 420000, 389000, 26, 1, "https://images.unsplash.com/photo-1545241047-6083a3684587?w=800&q=80"),
    ProductSeed("cay-noi-that", "Cay Monstera Cot Gach", "Dang la xe dep, phu hop can ho co anh sang tan xa.", "Lon", "medium", "medium", "medium", 690000, None, 18, 1, "https://images.unsplash.com/photo-1614594975525-e45190c55d0b?w=800&q=80"),
    ProductSeed("cay-noi-that", "Cay Philodendron Ring", "Form la mem, de len ke TV va ban console.", "Vua", "easy", "low", "medium", 360000, 329000, 22, 0, "https://images.unsplash.com/photo-1501004318641-b39e6451bec6?w=800&q=80"),
    ProductSeed("cay-van-phong", "Cay Kim Ngan Mini", "Dang than gon, hop ban lam viec va quay le tan.", "Nho", "easy", "medium", "low", 310000, None, 30, 1, "https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?w=800&q=80"),
    ProductSeed("cay-van-phong", "Cay Luoi Ho Tron Chau Xi Mang", "Mau la dam, de cham va phu hop nguoi ban ron.", "Vua", "easy", "low", "low", 290000, None, 35, 1, "https://images.unsplash.com/photo-1593482892290-f54927ae1bb6?w=800&q=80"),
    ProductSeed("cay-van-phong", "Cay Truong Sinh Marble", "Hop cua so nhieu sang, tao diem nhan tren ban hop.", "Nho", "easy", "medium", "medium", 255000, 229000, 28, 0, "https://images.unsplash.com/photo-1512428813834-c702c7702b78?w=800&q=80"),
    ProductSeed("cay-sen-da", "Sen Da Kem Hong", "Chau sen da tong pastel de trung bay tren ke nho.", "Nho", "easy", "high", "low", 175000, None, 40, 0, "https://images.unsplash.com/photo-1542090675-da82b9a9d923?w=800&q=80"),
    ProductSeed("cay-sen-da", "Sen Da Lima Mix", "Set 3 chau mix mau, hop lam qua tang van phong.", "Nho", "easy", "high", "low", 210000, 189000, 34, 1, "https://images.unsplash.com/photo-1459156212016-c812468e2115?w=800&q=80"),
    ProductSeed("cay-sen-da", "Sen Da Vuon Soi", "Phong cach toi gian cho ban hoc va ban trang diem.", "Nho", "easy", "high", "low", 160000, None, 46, 0, "https://images.unsplash.com/photo-1519331379826-f10be5486c6f?w=800&q=80"),
    ProductSeed("cay-thuy-sinh", "Cay Lan Nuoc Mini", "Dang gon dep, co the trung trong binh thuy tinh.", "Nho", "medium", "medium", "high", 240000, None, 20, 0, "https://images.unsplash.com/photo-1520412099551-62b6bafeb5bb?w=800&q=80"),
    ProductSeed("cay-thuy-sinh", "Cay Tram Nuoc Decor", "Tao goc xanh cho ke sach va khong gian cafe.", "Vua", "medium", "medium", "high", 330000, 299000, 16, 0, "https://images.unsplash.com/photo-1509423350716-97f9360b4e09?w=800&q=80"),
    ProductSeed("cay-phong-thuy", "Cay Ngoc Bich Tai Loc", "Tang thong diep may man, hop khai truong va tan gia.", "Vua", "easy", "medium", "low", 350000, None, 25, 1, "https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?w=800&q=80"),
    ProductSeed("cay-phong-thuy", "Cay Van Loc Do", "Tong mau noi bat, hop quay thu ngan va phong tiep khach.", "Vua", "medium", "medium", "medium", 395000, 359000, 18, 1, "https://images.unsplash.com/photo-1632207691143-643e2a9a9361?w=800&q=80"),
    ProductSeed("cay-phong-thuy", "Cay Phat Tai Bup Sen", "Dang than dep, hop sanh nho va goc cau thang.", "Lon", "medium", "medium", "medium", 820000, 759000, 10, 1, "https://images.unsplash.com/photo-1509423350716-97f9360b4e09?w=800&q=80"),
]

FIRST_NAMES = ["An", "Binh", "Chi", "Dung", "Giang", "Hanh", "Khanh", "Linh", "Minh", "Nam", "Ngoc", "Phuong", "Quynh", "Son", "Trang", "Vy"]
LAST_NAMES = ["Nguyen", "Tran", "Le", "Pham", "Hoang", "Vu", "Dang", "Do"]
PROVINCES = [
    ("TP.HCM", [("Thu Duc", "Linh Chieu"), ("Quan 1", "Ben Nghe"), ("Quan 7", "Tan Phong")]),
    ("Ha Noi", [("Cau Giay", "Dich Vong"), ("Dong Da", "Lang Thuong"), ("Nam Tu Liem", "My Dinh 1")]),
    ("Da Nang", [("Hai Chau", "Hai Chau 1"), ("Thanh Khe", "Tam Thuan")]),
]
ORDER_NOTES = [
    "Giao gio hanh chinh",
    "Lien he truoc khi giao",
    "De hang tai le tan",
    "Goi ky de tang qua",
]
REVIEW_COMMENTS = [
    "Cay dep, dung mo ta va dong goi chac chan.",
    "La cay xanh khoe, chau gon gang va sach se.",
    "Giao nhanh, cay den tay van rat tuoi.",
    "Mau la dep, rat hop trung bay trong nha.",
    "Chat luong on, minh se quay lai mua tiep.",
]


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Generate extra sample data and optionally import it into MySQL via Docker."
    )
    parser.add_argument("--users", type=int, default=8, help="Number of extra users to create.")
    parser.add_argument("--products", type=int, default=12, help="Number of extra products to create.")
    parser.add_argument("--orders", type=int, default=8, help="Number of extra orders to create.")
    parser.add_argument("--reviews", type=int, default=12, help="Number of extra reviews to create.")
    parser.add_argument("--wishlists", type=int, default=12, help="Number of extra wishlist rows to create.")
    parser.add_argument("--seed", type=int, default=20260329, help="Random seed for deterministic output.")
    parser.add_argument("--output", type=Path, default=DEFAULT_OUTPUT, help="Path to generated SQL file.")
    parser.add_argument("--db-name", default="webgreenspace", help="Database name.")
    parser.add_argument("--docker-service", default="db", help="Docker Compose MySQL service name.")
    parser.add_argument("--mysql-user", default="root", help="MySQL user inside Docker.")
    parser.add_argument("--mysql-password", default="root", help="MySQL password inside Docker.")
    parser.add_argument("--apply", action="store_true", help="Import generated SQL into MySQL after writing the file.")
    return parser.parse_args()


def sql_escape(value: str) -> str:
    return value.replace("\\", "\\\\").replace("'", "''")


def sql_string(value: str) -> str:
    return "'" + sql_escape(value) + "'"


def sql_nullable_string(value: str | None) -> str:
    if value is None:
        return "NULL"
    return sql_string(value)


def slugify(text: str) -> str:
    text = text.lower()
    text = re.sub(r"[^a-z0-9]+", "-", text)
    return text.strip("-")


def mysql_exec(args: argparse.Namespace, sql: str, *, database: str | None = None, capture: bool = True) -> subprocess.CompletedProcess[str]:
    db_name = database or args.db_name
    command = [
        "docker",
        "compose",
        "exec",
        "-T",
        args.docker_service,
        "mysql",
        f"-u{args.mysql_user}",
        f"-p{args.mysql_password}",
        "--default-character-set=utf8mb4",
        "--batch",
        "--raw",
        "--skip-column-names",
        db_name,
        "-e",
        sql,
    ]
    return subprocess.run(
        command,
        cwd=ROOT_DIR,
        check=True,
        capture_output=capture,
        text=True,
        encoding="utf-8",
    )


def fetch_categories(args: argparse.Namespace) -> list[CategoryRow]:
    result = mysql_exec(
        args,
        "SELECT id, slug, name FROM categories WHERE status = 'active' ORDER BY id;",
    )
    categories: list[CategoryRow] = []
    for line in result.stdout.splitlines():
        if not line.strip():
            continue
        parts = line.split("\t")
        if len(parts) != 3:
            continue
        categories.append(CategoryRow(id=int(parts[0]), slug=parts[1], name=parts[2]))
    return categories


def build_users(run_key: str, count: int, rng: random.Random) -> list[dict[str, str]]:
    users: list[dict[str, str]] = []
    for index in range(1, count + 1):
        last_name = rng.choice(LAST_NAMES)
        first_name = rng.choice(FIRST_NAMES)
        username = f"seed_{run_key}_u{index:02d}"
        email = f"{username}@example.com"
        phone = f"09{rng.randint(10000000, 99999999)}"
        province, districts = rng.choice(PROVINCES)
        district, ward = rng.choice(districts)
        street_number = rng.randint(10, 299)
        users.append(
            {
                "username": username,
                "email": email,
                "full_name": f"{last_name} {first_name}",
                "phone": phone,
                "province": province,
                "district": district,
                "ward": ward,
                "address_line": f"{street_number} duong seed {index}",
            }
        )
    return users


def build_products(run_key: str, count: int, categories: list[CategoryRow], rng: random.Random) -> list[dict[str, object]]:
    categories_by_slug = {category.slug: category for category in categories}
    seeds = PRODUCT_SEEDS.copy()
    rng.shuffle(seeds)

    products: list[dict[str, object]] = []
    for index in range(count):
        seed = seeds[index % len(seeds)]
        category = categories_by_slug.get(seed.category_slug)
        if category is None:
            continue

        slug = f"{slugify(seed.name)}-{run_key}-{index + 1:02d}"
        products.append(
            {
                "category_id": category.id,
                "category_slug": category.slug,
                "name": seed.name,
                "slug": slug,
                "description": seed.description,
                "price": seed.price,
                "sale_price": seed.sale_price,
                "stock": seed.stock,
                "image": seed.image,
                "size": seed.size,
                "care_level": seed.care_level,
                "light_requirement": seed.light_requirement,
                "water_requirement": seed.water_requirement,
                "featured": seed.featured,
            }
        )
    return products


def effective_price(product: dict[str, object]) -> int:
    sale_price = product["sale_price"]
    if isinstance(sale_price, int) and sale_price > 0:
        return sale_price
    return int(product["price"])


def build_orders(
    run_key: str,
    count: int,
    users: list[dict[str, str]],
    products: list[dict[str, object]],
    rng: random.Random,
) -> list[dict[str, object]]:
    orders: list[dict[str, object]] = []
    statuses = [
        ("pending", "unpaid", "cod"),
        ("confirmed", "paid", "online_mock"),
        ("processing", "paid", "online_mock"),
        ("delivered", "paid", "cod"),
    ]
    base_time = datetime.now().replace(second=0, microsecond=0)

    for index in range(1, count + 1):
        user = rng.choice(users)
        item_count = rng.randint(1, min(3, len(products)))
        chosen_products = rng.sample(products, k=item_count)
        status, payment_status, payment_method = rng.choice(statuses)
        shipping_fee = 30000 if item_count < 3 else 20000
        discount_amount = 0
        lines: list[dict[str, object]] = []
        subtotal = 0

        for product in chosen_products:
            quantity = rng.randint(1, 3)
            unit_price = effective_price(product)
            line_subtotal = unit_price * quantity
            subtotal += line_subtotal
            lines.append(
                {
                    "product_slug": product["slug"],
                    "product_name": product["name"],
                    "product_image": product["image"],
                    "price": unit_price,
                    "quantity": quantity,
                    "subtotal": line_subtotal,
                }
            )

        if subtotal >= 900000 and rng.random() < 0.35:
            discount_amount = 50000

        total_amount = subtotal - discount_amount + shipping_fee
        order_time = base_time - timedelta(hours=index * 4)
        order_number = f"SEED{run_key.upper()}{index:04d}"
        address = ", ".join(
            [
                user["address_line"],
                user["ward"],
                user["district"],
                user["province"],
            ]
        )
        orders.append(
            {
                "order_number": order_number,
                "username": user["username"],
                "full_name": user["full_name"],
                "email": user["email"],
                "phone": user["phone"],
                "address": address,
                "note": rng.choice(ORDER_NOTES),
                "subtotal": subtotal,
                "discount_amount": discount_amount,
                "shipping_fee": shipping_fee,
                "total_amount": total_amount,
                "coupon_code": "FREESHIP50" if discount_amount else None,
                "payment_method": payment_method,
                "payment_status": payment_status,
                "order_status": status,
                "created_at": order_time.strftime("%Y-%m-%d %H:%M:%S"),
                "paid_at": (order_time + timedelta(minutes=15)).strftime("%Y-%m-%d %H:%M:%S") if payment_status == "paid" else None,
                "transaction_code": f"SEEDTXN{run_key.upper()}{index:04d}" if payment_status == "paid" else None,
                "lines": lines,
            }
        )
    return orders


def build_reviews(users: list[dict[str, str]], products: list[dict[str, object]], count: int, rng: random.Random) -> list[dict[str, object]]:
    rows: list[dict[str, object]] = []
    pairs: set[tuple[str, str]] = set()

    while len(rows) < count and len(pairs) < len(users) * len(products):
        user = rng.choice(users)
        product = rng.choice(products)
        pair = (user["username"], str(product["slug"]))
        if pair in pairs:
            continue
        pairs.add(pair)
        rows.append(
            {
                "username": user["username"],
                "product_slug": product["slug"],
                "rating": rng.randint(4, 5),
                "comment": rng.choice(REVIEW_COMMENTS),
            }
        )
    return rows


def build_wishlists(users: list[dict[str, str]], products: list[dict[str, object]], count: int, rng: random.Random) -> list[dict[str, str]]:
    rows: list[dict[str, str]] = []
    pairs: set[tuple[str, str]] = set()
    while len(rows) < count and len(pairs) < len(users) * len(products):
        user = rng.choice(users)
        product = rng.choice(products)
        pair = (user["username"], str(product["slug"]))
        if pair in pairs:
            continue
        pairs.add(pair)
        rows.append({"username": user["username"], "product_slug": str(product["slug"])})
    return rows


def render_user_sql(user: dict[str, str]) -> str:
    return (
        "INSERT INTO users (username, email, password, full_name, phone, role, status) VALUES "
        f"({sql_string(user['username'])}, {sql_string(user['email'])}, {sql_string(PASSWORD_HASH)}, "
        f"{sql_string(user['full_name'])}, {sql_string(user['phone'])}, 'user', 'active');"
    )


def render_address_sql(user: dict[str, str]) -> str:
    return (
        "INSERT INTO addresses (user_id, receiver_name, phone, province, district, ward, address_line, is_default) "
        "SELECT id, "
        f"{sql_string(user['full_name'])}, {sql_string(user['phone'])}, {sql_string(user['province'])}, "
        f"{sql_string(user['district'])}, {sql_string(user['ward'])}, {sql_string(user['address_line'])}, 1 "
        f"FROM users WHERE username = {sql_string(user['username'])} LIMIT 1;"
    )


def render_product_sql(product: dict[str, object]) -> str:
    sale_price = "NULL" if product["sale_price"] is None else str(product["sale_price"])
    return (
        "INSERT INTO products (category_id, name, slug, description, price, sale_price, stock, image, size, care_level, light_requirement, water_requirement, featured, status) VALUES "
        f"({product['category_id']}, {sql_string(str(product['name']))}, {sql_string(str(product['slug']))}, "
        f"{sql_string(str(product['description']))}, {product['price']}, {sale_price}, {product['stock']}, "
        f"{sql_string(str(product['image']))}, {sql_string(str(product['size']))}, {sql_string(str(product['care_level']))}, "
        f"{sql_string(str(product['light_requirement']))}, {sql_string(str(product['water_requirement']))}, {product['featured']}, 'active');"
    )


def render_product_image_sql(product: dict[str, object]) -> str:
    return (
        "INSERT INTO product_images (product_id, image_url, sort_order, is_primary) "
        "SELECT id, "
        f"{sql_string(str(product['image']))}, 0, 1 FROM products WHERE slug = {sql_string(str(product['slug']))} LIMIT 1;"
    )


def render_inventory_import_sql(product: dict[str, object]) -> str:
    return (
        "INSERT INTO inventory_logs (product_id, order_id, action, quantity, note) "
        "SELECT id, NULL, 'import', "
        f"{product['stock']}, {sql_string('Seed stock import for ' + str(product['name']))} "
        f"FROM products WHERE slug = {sql_string(str(product['slug']))} LIMIT 1;"
    )


def render_order_sql(order: dict[str, object]) -> str:
    return (
        "INSERT INTO orders (user_id, order_number, full_name, email, phone, address, note, subtotal, discount_amount, shipping_fee, total_amount, coupon_code, payment_method, payment_status, order_status, created_at, updated_at) "
        "SELECT id, "
        f"{sql_string(str(order['order_number']))}, {sql_string(str(order['full_name']))}, {sql_string(str(order['email']))}, "
        f"{sql_string(str(order['phone']))}, {sql_string(str(order['address']))}, {sql_string(str(order['note']))}, "
        f"{order['subtotal']}, {order['discount_amount']}, {order['shipping_fee']}, {order['total_amount']}, "
        f"{sql_nullable_string(order['coupon_code'])}, {sql_string(str(order['payment_method']))}, "
        f"{sql_string(str(order['payment_status']))}, {sql_string(str(order['order_status']))}, "
        f"{sql_string(str(order['created_at']))}, {sql_string(str(order['created_at']))} "
        f"FROM users WHERE username = {sql_string(str(order['username']))} LIMIT 1;"
    )


def render_order_line_sql(order_number: str, line: dict[str, object]) -> str:
    return (
        "INSERT INTO order_details (order_id, product_id, variant_id, product_name, product_image, price, quantity, subtotal) "
        "SELECT o.id, p.id, NULL, "
        f"{sql_string(str(line['product_name']))}, {sql_string(str(line['product_image']))}, {line['price']}, {line['quantity']}, {line['subtotal']} "
        "FROM orders o "
        "JOIN products p ON p.slug = "
        f"{sql_string(str(line['product_slug']))} "
        f"WHERE o.order_number = {sql_string(order_number)} LIMIT 1;"
    )


def render_payment_sql(order: dict[str, object]) -> str:
    note = "Seed payment imported" if order["payment_status"] == "paid" else "Seed COD payment pending"
    return (
        "INSERT INTO payments (order_id, provider, transaction_code, status, amount, paid_at, note, created_at, updated_at) "
        "SELECT id, "
        f"{sql_string(str(order['payment_method']))}, {sql_nullable_string(order['transaction_code'])}, "
        f"{sql_string(str(order['payment_status']))}, {order['total_amount']}, {sql_nullable_string(order['paid_at'])}, "
        f"{sql_string(note)}, {sql_string(str(order['created_at']))}, {sql_string(str(order['created_at']))} "
        f"FROM orders WHERE order_number = {sql_string(str(order['order_number']))} LIMIT 1;"
    )


def render_inventory_deduct_sql(order_number: str, line: dict[str, object]) -> str:
    return (
        "INSERT INTO inventory_logs (product_id, order_id, action, quantity, note) "
        "SELECT p.id, o.id, 'deduct', "
        f"{line['quantity']}, {sql_string('Seed order ' + order_number + ' stock deduct')} "
        "FROM products p "
        "JOIN orders o ON o.order_number = "
        f"{sql_string(order_number)} "
        f"WHERE p.slug = {sql_string(str(line['product_slug']))} LIMIT 1;"
    )


def render_review_sql(review: dict[str, object]) -> str:
    return (
        "INSERT INTO reviews (product_id, user_id, rating, comment, status) "
        "SELECT p.id, u.id, "
        f"{review['rating']}, {sql_string(str(review['comment']))}, 'approved' "
        "FROM products p "
        "JOIN users u ON u.username = "
        f"{sql_string(str(review['username']))} "
        f"WHERE p.slug = {sql_string(str(review['product_slug']))} LIMIT 1;"
    )


def render_wishlist_sql(wishlist: dict[str, str]) -> str:
    return (
        "INSERT IGNORE INTO wishlists (user_id, product_id) "
        "SELECT u.id, p.id FROM users u "
        "JOIN products p ON p.slug = "
        f"{sql_string(wishlist['product_slug'])} "
        f"WHERE u.username = {sql_string(wishlist['username'])} LIMIT 1;"
    )


def build_sql(
    args: argparse.Namespace,
    users: list[dict[str, str]],
    products: list[dict[str, object]],
    orders: list[dict[str, object]],
    reviews: list[dict[str, object]],
    wishlists: list[dict[str, str]],
) -> str:
    lines = [
        f"-- Generated sample data at {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}",
        f"-- Target database: {args.db_name}",
        f"USE {args.db_name};",
        "SET NAMES utf8mb4;",
        "SET FOREIGN_KEY_CHECKS = 1;",
        "START TRANSACTION;",
        "",
    ]

    for user in users:
        lines.append(render_user_sql(user))
    lines.append("")
    for user in users:
        lines.append(render_address_sql(user))
    lines.append("")
    for product in products:
        lines.append(render_product_sql(product))
    lines.append("")
    for product in products:
        lines.append(render_product_image_sql(product))
        lines.append(render_inventory_import_sql(product))
    lines.append("")
    for order in orders:
        lines.append(render_order_sql(order))
        for line in order["lines"]:
            lines.append(render_order_line_sql(str(order["order_number"]), line))
        lines.append(render_payment_sql(order))
        for line in order["lines"]:
            lines.append(render_inventory_deduct_sql(str(order["order_number"]), line))
        lines.append("")
    for review in reviews:
        lines.append(render_review_sql(review))
    lines.append("")
    for wishlist in wishlists:
        lines.append(render_wishlist_sql(wishlist))
    lines.extend(["", "COMMIT;", ""])
    return "\n".join(lines)


def ensure_categories(categories: Iterable[CategoryRow]) -> None:
    category_list = list(categories)
    if not category_list:
        raise RuntimeError(
            "No active categories found. Import schema_revised.sql and sample_data_revised.sql first."
        )


def write_sql_file(path: Path, sql: str) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(sql, encoding="utf-8")


def apply_sql(args: argparse.Namespace, sql: str) -> None:
    command = [
        "docker",
        "compose",
        "exec",
        "-T",
        args.docker_service,
        "mysql",
        f"-u{args.mysql_user}",
        f"-p{args.mysql_password}",
        "--default-character-set=utf8mb4",
        args.db_name,
    ]
    subprocess.run(
        command,
        cwd=ROOT_DIR,
        check=True,
        input=sql,
        text=True,
        encoding="utf-8",
    )


def main() -> int:
    args = parse_args()
    rng = random.Random(args.seed)
    run_key = datetime.now().strftime("%Y%m%d%H%M%S")

    try:
        categories = fetch_categories(args)
        ensure_categories(categories)
    except subprocess.CalledProcessError as exc:
        print(exc.stderr or exc.stdout, file=sys.stderr)
        print("Unable to query MySQL through Docker. Check `docker compose ps` first.", file=sys.stderr)
        return 1
    except RuntimeError as exc:
        print(str(exc), file=sys.stderr)
        return 1

    users = build_users(run_key, max(0, args.users), rng)
    products = build_products(run_key, max(0, args.products), categories, rng)
    orders = build_orders(run_key, max(0, args.orders), users, products, rng) if users and products else []
    reviews = build_reviews(users, products, max(0, args.reviews), rng) if users and products else []
    wishlists = build_wishlists(users, products, max(0, args.wishlists), rng) if users and products else []

    sql = build_sql(args, users, products, orders, reviews, wishlists)
    write_sql_file(args.output, sql)

    print(f"Generated SQL: {args.output}")
    print(f"Users: {len(users)} | Products: {len(products)} | Orders: {len(orders)} | Reviews: {len(reviews)} | Wishlists: {len(wishlists)}")

    if not args.apply:
        print("Dry run complete. Use --apply to import into MySQL.")
        return 0

    try:
        apply_sql(args, sql)
    except subprocess.CalledProcessError as exc:
        print(exc.stderr or exc.stdout, file=sys.stderr)
        print("Generated SQL file is still available above for manual import.", file=sys.stderr)
        return 1

    print("Sample data imported into MySQL successfully.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
