# Refactored Code Structure

## Overview
Dự án đã được refactor để cải thiện chất lượng code, maintainability và scalability.

## Các Thay Đổi Chính

### 1. **Service Layer** - ProductService
- Tách business logic ra khỏi controller/view
- Xử lý tất cả logic liên quan đến products (filtering, sorting, searching)
- File: `app/services/ProductService.php`

**Ví dụ sử dụng:**
```php
$productService = new ProductService();
$result = $productService->getProducts([
    'category' => 'cay-de-ban',
    'search' => 'kim tien',
    'sort' => ProductService::SORT_PRICE_ASC,
    'page' => 1,
    'limit' => 12
]);
```

### 2. **Type Hints và Return Types**
Tất cả functions và methods đều có type hints và return types:

**Before:**
```php
function clean($data) {
    if (is_array($data)) {
        return array_map('clean', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
```

**After:**
```php
function clean(string|array $data): string|array {
    if (is_array($data)) {
        return array_map('clean', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
```

### 3. **Constants cho Magic Strings**
Thay vì dùng string literals, sử dụng constants:

```php
// ProductService constants
ProductService::SORT_NEWEST
ProductService::SORT_PRICE_ASC
ProductService::SORT_PRICE_DESC
ProductService::SORT_BESTSELLER
```

### 4. **Improved Models**
- **Product Model**: Type hints, constants, improved documentation
- **Category Model**: Type hints, improved documentation
- Query reusability với constants: `SELECT_FIELDS`

### 5. **Router System**
Router đơn giản để quản lý URLs tốt hơn:

```php
// File: public/routes.php
$router->get('/products', __DIR__ . '/products.php');
$router->get('/product/{slug}', __DIR__ . '/product-detail.php');
$router->get('/category/{slug}', __DIR__ . '/products.php');
```

### 6. **Enhanced Helper Functions**
Tất cả helper functions đều có:
- Type hints
- Return types
- PHPDoc comments đầy đủ
- Error handling tốt hơn

## Cấu Trúc Thư Mục

```
app/
├── controllers/        # Controllers (điều hướng logic)
├── models/            # Models (database operations)
│   ├── Product.php   # ✅ Refactored với type hints
│   └── Category.php  # ✅ Refactored với type hints
├── services/          # 🆕 Business logic layer
│   └── ProductService.php
├── core/              # 🆕 Core components
│   └── Router.php     # Simple router
└── views/             # Views (presentation)

helpers/
└── functions.php      # ✅ Refactored với type hints

public/
├── routes.php         # 🆕 Route definitions
└── ...                # Public files
```

## Best Practices Được Áp Dụng

### 1. **Separation of Concerns**
- Models: Chỉ xử lý database operations
- Services: Business logic
- Views: Presentation
- Controllers: Routing & coordination

### 2. **DRY (Don't Repeat Yourself)**
- Constants cho reusable values
- Helper functions cho common operations
- Service layer để tránh duplicate logic

### 3. **Type Safety**
```php
// Strict typing
public function getProducts(array $filters = []): array
public function getById(int $id): ?array
public function clean(string|array $data): string|array
```

### 4. **Documentation**
Mỗi function/method có PHPDoc:
```php
/**
 * Get products with filters
 * 
 * @param array $filters Filter parameters
 * @return array Products and metadata
 */
public function getProducts(array $filters = []): array
```

## Migration Guide

### Updating products.php
**Before:**
```php
// Complex logic mixed with view
$products = $productModel->getAll($limit, $offset);
// ... sorting logic
// ... filtering logic
```

**After:**
```php
// Clean service usage
$productService = new ProductService();
$result = $productService->getProducts($filters);
$products = $result['products'];
```

### Using Helper Functions
```php
// All helpers have type hints
$url = base_url('products'); // string -> string
$price = format_currency(150000); // float|int -> string
$slug = create_slug('Cây Kim Tiền'); // string -> string
```

## Performance Benefits

1. **Better Caching Opportunities**: Service layer dễ cache hơn
2. **Query Optimization**: Reusable query patterns
3. **Reduced Code Duplication**: DRY principles
4. **IDE Autocomplete**: Type hints giúp IDE hoạt động tốt hơn

## Security Improvements

1. **Input Sanitization**: `clean()` function with type safety
2. **Prepared Statements**: Tất cả queries sử dụng PDO prepared statements
3. **Type Safety**: Prevent type juggling vulnerabilities

## Testing Benefits

- Service layer dễ test hơn
- Type hints giúp catch bugs sớm
- Separated concerns dễ mock hơn

## Next Steps (Recommendations)

1. **Validation Layer**: Tạo validation classes
2. **Response Standardization**: Standardize API responses
3. **Error Handling**: Centralized error handling
4. **Logging**: Structured logging system
5. **Caching**: Implement caching layer
6. **Database Migrations**: Version control cho database schema

## Coding Standards

- **PSR-12**: Follow PSR-12 coding standard
- **Type Hints**: Always use type hints
- **Documentation**: PHPDoc for all public methods
- **Naming**: Descriptive names (camelCase for methods, snake_case for database)

## Examples

### Creating a New Service

```php
<?php
class OrderService {
    private Order $orderModel;
    
    public function __construct() {
        $this->orderModel = new Order();
    }
    
    public function createOrder(array $items, int $userId): ?array {
        // Business logic here
        return $this->orderModel->create($items, $userId);
    }
}
```

### Adding a New Route

```php
// In public/routes.php
$router->get('/orders', __DIR__ . '/orders.php');
$router->get('/order/{id}', function($id) {
    $orderService = new OrderService();
    $order = $orderService->getOrderById((int)$id);
    // ... render view
});
```

## Changelog

### Version 2.0 (Refactored)
- ✅ Added ProductService layer
- ✅ Type hints for all functions
- ✅ Improved Product & Category models
- ✅ Enhanced helper functions
- ✅ Router system
- ✅ Constants for magic strings
- ✅ Better documentation

---

**Note**: Code refactoring này tập trung vào maintainability, type safety và best practices mà không làm thay đổi functionality hiện tại.
