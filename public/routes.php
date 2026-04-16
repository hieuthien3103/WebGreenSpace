<?php
/**
 * Application Routes
 * Define all application routes here
 */

$router = new Router();
$router
    ->register(new StorefrontRouteRegistrar())
    ->register(new LegacyRouteRegistrar());

return $router;
