<?php
/**
 * Contract for modular route registration.
 */
interface RouteRegistrar {
    public function register(Router $router): void;
}
