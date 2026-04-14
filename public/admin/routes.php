<?php
/**
 * Admin Routes
 * Loaded only by the admin front controller.
 */

$router = new Router();
$router->register(new AdminRouteRegistrar());

return $router;
