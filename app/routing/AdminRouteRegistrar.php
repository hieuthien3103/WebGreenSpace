<?php
/**
 * Register legacy admin page handlers.
 */
class AdminRouteRegistrar implements RouteRegistrar {
    public function register(Router $router): void {
        $router->get('/admin/login', [AdminAuthController::class, 'login']);
        $router->post('/admin/login', [AdminAuthController::class, 'login']);
        $router->get('/admin', [AdminPageController::class, 'index']);
        $router->get('/admin/dashboard', [AdminPageController::class, 'dashboard']);
        $router->get('/admin/orders', [AdminPageController::class, 'orders']);
        $router->post('/admin/orders', [AdminPageController::class, 'orders']);
        $router->get('/admin/contacts', [AdminPageController::class, 'contacts']);
        $router->post('/admin/contacts', [AdminPageController::class, 'contacts']);
        $router->get('/admin/products', [AdminPageController::class, 'products']);
        $router->post('/admin/products', [AdminPageController::class, 'products']);
        $router->get('/admin/categories', [AdminPageController::class, 'categories']);
        $router->post('/admin/categories', [AdminPageController::class, 'categories']);
        $router->get('/admin/users', [AdminPageController::class, 'users']);
        $router->post('/admin/users', [AdminPageController::class, 'users']);
        $router->get('/admin/check-images', [AdminToolController::class, 'checkImages']);
        $router->get('/admin/clear-cache', [AdminToolController::class, 'clearCache']);
        $router->get('/admin/create-placeholder', [AdminToolController::class, 'createPlaceholder']);
        $router->get('/admin/fix-images', [AdminToolController::class, 'fixImages']);
        $router->get('/admin/products/catalog', [AdminToolController::class, 'getProducts']);
        $router->post('/admin/products/image-url', [AdminToolController::class, 'updateProductImage']);
        $router->post('/admin/products/upload-image', [AdminToolController::class, 'uploadProductImage']);
        $router->get('/admin/upload-images', [AdminToolController::class, 'adminUploadImages']);
    }
}
