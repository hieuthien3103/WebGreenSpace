<?php
require_once __DIR__ . '/../../config/config.php';
(new AdminAuthController())->logout()->send();
