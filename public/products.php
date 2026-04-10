<?php
require_once __DIR__ . '/../config/config.php';

(new ProductController())->index()->send();
