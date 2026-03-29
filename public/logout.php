<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$redirectTarget = safe_redirect_target($_POST['redirect'] ?? $_GET['redirect'] ?? 'home.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Không thể đăng xuất từ yêu cầu này.');
    redirect($redirectTarget);
}

if (is_logged_in()) {
    $authService = new AuthService();
    $authService->logout();
    set_flash('success', 'Bạn đã đăng xuất khỏi hệ thống.');
}

redirect($redirectTarget);
