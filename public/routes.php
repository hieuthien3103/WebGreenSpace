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

// Contact
$router->get('/contact', __DIR__ . '/contact.php');

// Care guide
$router->get('/care', __DIR__ . '/care.php');

return $router;
