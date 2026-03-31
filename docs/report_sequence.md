# Sequence Diagram

Tai lieu nay chon luong nghiep vu quan trong nhat cua do an: user dat hang bang `online_mock`, gui xac nhan thanh toan, sau do admin vao duyet va cap nhat trang thai don.

## Actors

- User
- Browser
- PHP App
- CheckoutService
- Order model
- MySQL
- Admin

## Sequence: Checkout + Payment Review

```mermaid
sequenceDiagram
    actor User
    participant Browser
    participant App as PHP App
    participant Checkout as CheckoutService
    participant Order as Order Model
    participant DB as MySQL
    actor Admin

    User->>Browser: Mo /products va them san pham vao gio
    Browser->>App: POST /cart
    App->>DB: insert/update cart
    DB-->>App: OK
    App-->>Browser: Redirect /cart

    User->>Browser: Mo /checkout va gui form
    Browser->>App: POST /checkout
    App->>Checkout: placeOrder(userId, payload)
    Checkout->>DB: begin transaction
    Checkout->>Order: create(order)
    Order->>DB: insert orders
    DB-->>Order: order_id
    loop tung san pham trong gio
        Checkout->>Order: addItem(...)
        Order->>DB: insert order_details
    end
    Checkout->>Order: addPayment(provider=online_mock, status=unpaid)
    Order->>DB: insert payments
    Checkout->>DB: commit
    Checkout-->>App: success + order_id
    App-->>Browser: Redirect /order-detail?id={id}#payment-confirmation

    User->>Browser: Bam "Toi da thanh toan"
    Browser->>App: POST /order-detail?action=confirm_online_mock_payment
    App->>Order: confirmOnlineMockPaymentByUser(userId, orderId)
    Order->>DB: begin transaction
    Order->>DB: lock order
    Order->>DB: update payments note/transaction_code
    Order->>DB: update orders.payment_status = pending_review
    Order->>DB: commit
    App-->>Browser: Redirect /order-detail?id={id}

    Admin->>Browser: Dang nhap /admin/login
    Browser->>App: POST /admin/login
    App->>DB: verify admin account
    DB-->>App: admin session
    App-->>Browser: Redirect /admin/dashboard

    Admin->>Browser: Mo /admin/orders?q=order_number&view=order_id
    Browser->>App: GET /admin/orders
    App->>Order: getAdminList + getAdminDetailById
    Order->>DB: select orders/payments/order_details
    DB-->>Order: order detail
    App-->>Browser: Render admin order detail

    Admin->>Browser: Bam "Duyet thanh toan"
    Browser->>App: POST /admin/orders?action=approve_online_mock_payment
    App->>Order: approveOnlineMockPaymentByAdmin(orderId)
    Order->>DB: begin transaction
    Order->>DB: update payments.status = paid
    Order->>DB: update orders.payment_status = paid
    Order->>DB: commit
    App-->>Browser: Redirect /admin/orders?view={id}

    Admin->>Browser: Chuyen order_status sang delivered
    Browser->>App: POST /admin/orders?action=update_order_status
    App->>Order: updateAdminOrderStatus(orderId, delivered)
    Order->>DB: update orders.order_status = delivered
    DB-->>Order: OK
    App-->>Browser: Redirect /admin/orders?view={id}
```

## Diem nghiep vu can nho

- Checkout duoc bao boi transaction de tranh tao don dang do.
- `order_details` luu snapshot gia va thong tin san pham tai thoi diem mua.
- User khong tu chuyen `payment_status` sang `paid`; user chi dua don sang `pending_review`.
- Chi admin moi co quyen approve/reject thanh toan `online_mock`.
- Sau khi thanh toan duoc duyet, admin moi tiep tuc xu ly `order_status`.

## Trang thai duoc su dung

- `payment_status`: `unpaid -> pending_review -> paid` hoac `failed`
- `order_status`: `pending -> confirmed -> processing -> shipping -> delivered`

## Gia tri cua sequence nay

Sequence nay bao phu 3 module quan trong nhat cua do an:

- Dat hang
- Thanh toan mo phong
- Quan tri don hang ben admin
