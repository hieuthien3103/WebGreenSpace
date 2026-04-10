<?php
/**
 * Application Routes
 * Define all application routes here
 */

require_once __DIR__ . '/../app/core/Router.php';

$router = new Router();

// Home page
$router->get('/', [HomeController::class, 'index']);
$router->get('/home', [HomeController::class, 'index']);

// Products
$router->get('/products', [ProductController::class, 'index']);
$router->get('/product/{slug}', [ProductController::class, 'detail']);

// Categories
$router->get('/category/{slug}', [ProductController::class, 'category']);

// Cart
$router->get('/cart', __DIR__ . '/cart.php');
$router->post('/cart', __DIR__ . '/cart.php');

// Checkout & profile
$router->get('/checkout', __DIR__ . '/checkout.php');
$router->post('/checkout', __DIR__ . '/checkout.php');
$router->get('/qr-pay', __DIR__ . '/qr-pay.php');
$router->post('/qr-pay', __DIR__ . '/qr-pay.php');
$router->get('/profile', __DIR__ . '/profile.php');
$router->post('/profile', __DIR__ . '/profile.php');
$router->get('/orders', __DIR__ . '/orders.php');
$router->get('/order-detail', __DIR__ . '/order-detail.php');
$router->get('/profile/orders/{id}', __DIR__ . '/order-detail.php');

// Contact
$router->get('/contact', __DIR__ . '/contact.php');
$router->post('/contact', __DIR__ . '/contact.php');

// Care guide
$router->get('/care', __DIR__ . '/care.php');

// Authentication
$router->get('/login', __DIR__ . '/login.php');
$router->get('/signup', __DIR__ . '/signup.php');
$router->post('/logout', __DIR__ . '/logout.php');

// Admin
$router->get('/admin/login', __DIR__ . '/admin/login.php');
$router->post('/admin/login', __DIR__ . '/admin/login.php');
$router->get('/admin', __DIR__ . '/admin/dashboard.php');
$router->get('/admin/dashboard', __DIR__ . '/admin/dashboard.php');
$router->get('/admin/orders', __DIR__ . '/admin/orders.php');
$router->post('/admin/orders', __DIR__ . '/admin/orders.php');
$router->get('/admin/contacts', __DIR__ . '/admin/contacts.php');
$router->post('/admin/contacts', __DIR__ . '/admin/contacts.php');
$router->get('/admin/products', __DIR__ . '/admin/products.php');
$router->get('/admin/categories', __DIR__ . '/admin/categories.php');
$router->get('/admin/users', __DIR__ . '/admin/users.php');
$router->post('/admin/users', __DIR__ . '/admin/users.php');

return $router;
