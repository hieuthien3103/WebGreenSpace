<?php
/**
 * Application Routes
 * Define all application routes here
 */

require_once __DIR__ . '/../app/core/Router.php';

$router = new Router();

// Home page
$router->get('/', __DIR__ . '/home.php');
$router->get('/home', __DIR__ . '/home.php');

// Products
$router->get('/products', __DIR__ . '/products.php');
$router->get('/product/{slug}', __DIR__ . '/product-detail.php');

// Categories
$router->get('/category/{slug}', __DIR__ . '/products.php');

// Cart
$router->get('/cart', __DIR__ . '/cart.php');
$router->post('/cart', __DIR__ . '/cart.php');

// Checkout & profile
$router->get('/checkout', __DIR__ . '/checkout.php');
$router->post('/checkout', __DIR__ . '/checkout.php');
$router->get('/profile', __DIR__ . '/profile.php');
$router->post('/profile', __DIR__ . '/profile.php');

// Contact
$router->get('/contact', __DIR__ . '/contact.php');

// Care guide
$router->get('/care', __DIR__ . '/care.php');

// Authentication
$router->get('/login', __DIR__ . '/login.php');
$router->get('/signup', __DIR__ . '/signup.php');
$router->post('/logout', __DIR__ . '/logout.php');

// Admin
$router->get('/admin', __DIR__ . '/admin/dashboard.php');
$router->get('/admin/dashboard', __DIR__ . '/admin/dashboard.php');
$router->get('/admin/products', __DIR__ . '/admin/products.php');
$router->get('/admin/categories', __DIR__ . '/admin/categories.php');

return $router;
