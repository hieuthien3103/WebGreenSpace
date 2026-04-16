<?php
/**
 * Admin Front Controller
 * Separate entry point for the admin area.
 */

require_once __DIR__ . '/../../config/config.php';

$router = require __DIR__ . '/routes.php';
$router->dispatch();
