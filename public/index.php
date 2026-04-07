<?php
/**
 * Front controller for clean URLs.
 */

require_once __DIR__ . '/../config/config.php';

$router = require __DIR__ . '/routes.php';
$router->dispatch();
