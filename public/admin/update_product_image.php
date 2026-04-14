<?php
require_once __DIR__ . '/../../config/config.php';

(new AdminToolController())->updateProductImage()->send();
