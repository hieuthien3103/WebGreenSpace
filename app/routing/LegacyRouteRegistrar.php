<?php
/**
 * Register legacy public page handlers that still live under /public.
 */
class LegacyRouteRegistrar implements RouteRegistrar {
    public function register(Router $router): void {
        $router->get('/cart', [CartController::class, 'index']);
        $router->post('/cart', [CartController::class, 'index']);
        $router->get('/checkout', [CheckoutController::class, 'index']);
        $router->post('/checkout', [CheckoutController::class, 'index']);
        $router->get('/qr-pay', [AccountController::class, 'qrPay']);
        $router->post('/qr-pay', [AccountController::class, 'qrPay']);
        $router->get('/profile', [AccountController::class, 'profile']);
        $router->post('/profile', [AccountController::class, 'profile']);
        $router->get('/orders', [AccountController::class, 'orders']);
        $router->get('/order-detail', [AccountController::class, 'orderDetail']);
        $router->post('/order-detail', [AccountController::class, 'orderDetail']);
        $router->get('/profile/orders/{id}', [AccountController::class, 'orderDetail']);
        $router->post('/profile/orders/{id}', [AccountController::class, 'orderDetail']);
        $router->get('/contact', [ContentController::class, 'contact']);
        $router->post('/contact', [ContentController::class, 'contact']);
        $router->get('/care', [ContentController::class, 'care']);
        $router->get('/login', [AuthController::class, 'login']);
        $router->post('/login', [AuthController::class, 'login']);
        $router->get('/signup', [AuthController::class, 'signup']);
        $router->post('/signup', [AuthController::class, 'signup']);
        $router->post('/logout', [AuthController::class, 'logout']);
        $router->get('/cart-api', [CartApiController::class, 'handle']);
        $router->post('/cart-api', [CartApiController::class, 'handle']);
        $router->get('/get-price-range', [UtilityController::class, 'priceRange']);
        $router->get('/debug', [UtilityController::class, 'debug']);
    }
}
