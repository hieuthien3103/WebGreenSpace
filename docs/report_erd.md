# ERD

Tai lieu nay mo ta cau truc CSDL hien tai cua `webgreenspace` theo `database/schema_revised.sql` va migration `database/alter_payment_status_pending_review.sql`.

## Nhom bang chinh

- Nhom tai khoan: `users`, `addresses`
- Nhom catalog: `categories`, `products`, `product_images`, `product_variants`
- Nhom mua hang: `cart`, `orders`, `order_details`, `payments`
- Nhom marketing va social: `coupons`, `coupon_usages`, `reviews`, `wishlists`
- Nhom kho: `inventory_logs`

## ER Diagram

```mermaid
erDiagram
    USERS {
        int id PK
        varchar username UK
        varchar email UK
        varchar password
        varchar full_name
        varchar phone
        enum role
        enum status
    }

    ADDRESSES {
        int id PK
        int user_id FK
        varchar receiver_name
        varchar phone
        varchar province
        varchar district
        varchar ward
        varchar address_line
        bool is_default
    }

    CATEGORIES {
        int id PK
        varchar name
        varchar slug UK
        int parent_id FK
        enum status
    }

    PRODUCTS {
        int id PK
        int category_id FK
        varchar name
        varchar slug UK
        decimal price
        decimal sale_price
        int stock
        varchar image
        enum status
    }

    PRODUCT_IMAGES {
        int id PK
        int product_id FK
        varchar image_url
        int sort_order
        bool is_primary
    }

    PRODUCT_VARIANTS {
        int id PK
        int product_id FK
        varchar sku UK
        varchar option_name
        varchar option_value
        decimal price_override
        int stock_quantity
        enum status
    }

    CART {
        int id PK
        int user_id FK
        int product_id FK
        int variant_id FK
        int quantity
        decimal price_snapshot
    }

    ORDERS {
        int id PK
        int user_id FK
        varchar order_number UK
        decimal subtotal
        decimal discount_amount
        decimal shipping_fee
        decimal total_amount
        enum payment_method
        enum payment_status
        enum order_status
    }

    ORDER_DETAILS {
        int id PK
        int order_id FK
        int product_id FK
        int variant_id FK
        varchar product_name
        decimal price
        int quantity
        decimal subtotal
    }

    PAYMENTS {
        int id PK
        int order_id FK
        enum provider
        varchar transaction_code
        enum status
        decimal amount
        datetime paid_at
        varchar note
    }

    COUPONS {
        int id PK
        varchar code UK
        enum discount_type
        decimal discount_value
        decimal min_order_amount
        int usage_limit
        int used_count
        enum status
    }

    COUPON_USAGES {
        int id PK
        int coupon_id FK
        int user_id FK
        int order_id FK
    }

    REVIEWS {
        int id PK
        int product_id FK
        int user_id FK
        int rating
        enum status
    }

    WISHLISTS {
        int id PK
        int user_id FK
        int product_id FK
    }

    INVENTORY_LOGS {
        int id PK
        int product_id FK
        int order_id FK
        enum action
        int quantity
    }

    USERS ||--o{ ADDRESSES : has
    USERS ||--o{ CART : owns
    USERS ||--o{ ORDERS : places
    USERS ||--o{ COUPON_USAGES : uses
    USERS ||--o{ REVIEWS : writes
    USERS ||--o{ WISHLISTS : saves

    CATEGORIES ||--o{ PRODUCTS : groups
    CATEGORIES ||--o{ CATEGORIES : parent_of

    PRODUCTS ||--o{ PRODUCT_IMAGES : has
    PRODUCTS ||--o{ PRODUCT_VARIANTS : has
    PRODUCTS ||--o{ CART : added_to
    PRODUCTS ||--o{ ORDER_DETAILS : snapshot_from
    PRODUCTS ||--o{ REVIEWS : receives
    PRODUCTS ||--o{ WISHLISTS : appears_in
    PRODUCTS ||--o{ INVENTORY_LOGS : tracked_by

    PRODUCT_VARIANTS ||--o{ CART : selected_in
    PRODUCT_VARIANTS ||--o{ ORDER_DETAILS : selected_in

    ORDERS ||--o{ ORDER_DETAILS : contains
    ORDERS ||--o{ PAYMENTS : records
    ORDERS ||--o{ COUPON_USAGES : linked_to
    ORDERS ||--o{ INVENTORY_LOGS : affects

    COUPONS ||--o{ COUPON_USAGES : tracked_by
```

## Giai thich nghiep vu

- `users` chua role `admin` va `user`; bang nay la diem vao cho auth va phan quyen.
- `addresses` tach rieng de mot user co nhieu dia chi va co `is_default`.
- `products.stock` duoc giu truc tiep tren bang san pham de phu hop scope do an; bien dong kho duoc luu them o `inventory_logs`.
- `cart` la bang gio hang runtime cua user dang dang nhap.
- `orders` luu snapshot giao hang, tong tien, `payment_method`, `payment_status`, `order_status`.
- `order_details` dong vai tro snapshot san pham tai thoi diem dat hang.
- `payments` luu lich su xac nhan thanh toan mo phong, bao gom `pending_review`.
- `coupon_usages`, `reviews`, `wishlists` la cac bang phu tro cho mo rong he thong.

## Rang buoc quan trong

- `users.email`, `users.username`, `products.slug`, `categories.slug`, `orders.order_number`, `product_variants.sku`, `coupons.code` la duy nhat.
- `cart` dung unique key `(user_id, product_id, variant_id)` de tranh trung dong.
- `reviews.rating` co `CHECK (rating >= 1 AND rating <= 5)`.
- `payments.status` va `orders.payment_status` phai ho tro: `unpaid`, `pending_review`, `paid`, `failed`.

## Ket luan

ERD hien tai dap ung duoc cac module da hoan thanh trong checklist: auth, product, cart, checkout, payment mo phong, admin va security.
