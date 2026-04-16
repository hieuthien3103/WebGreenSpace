<?php
/**
 * Legacy cart API entry point — delegates to CartApiController via the router.
 * New code should use the /cart-api route instead.
 */
require_once __DIR__ . '/../config/config.php';

(new CartApiController())->handle()->send();
