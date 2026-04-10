<?php
/**
 * Register storefront controller routes.
 */
class StorefrontRouteRegistrar implements RouteRegistrar {
    public function register(Router $router): void {
        $router->get('/', [HomeController::class, 'index']);
        $router->get('/home', [HomeController::class, 'index']);
        $router->get('/products', [ProductController::class, 'index']);
        $router->get('/product/{slug}', [ProductController::class, 'detail']);
        $router->get('/category/{slug}', [ProductController::class, 'category']);
    }
}
