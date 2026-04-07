<?php
/**
 * Helper Functions
 */

/**
 * Redirect to URL
 * 
 * @param string $url Target URL
 * @return never
 */
function redirect(string $url): never {
    header("Location: " . $url);
    exit();
}

/**
 * Get base URL
 * 
 * @param string $path Path to append
 * @return string Full URL
 */
function base_url(string $path = ''): string {
    return APP_URL . '/' . ltrim($path, '/');
}

/**
 * Get asset URL
 * 
 * @param string $path Asset path
 * @return string Full asset URL
 */
function asset(string $path): string {
    return base_url('public/' . ltrim($path, '/'));
}

/**
 * Get image URL
 * 
 * @param string $path Image path
 * @return string Full image URL
 */
function image_url(string $path): string {
    return IMG_URL . '/' . ltrim($path, '/');
}

/**
 * Get upload URL
 * 
 * @param string $path Upload path
 * @return string Full upload URL
 */
function upload_url(string $path): string {
    return UPLOAD_URL . '/' . ltrim($path, '/');
}

/**
 * Sanitize input
 * 
 * @param string|array $data Data to sanitize
 * @return string|array Sanitized data
 */
function clean(string|array $data): string|array {
    if (is_array($data)) {
        return array_map('clean', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * 
 * @return bool True if admin
 */
function is_admin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Ensure the current request is authenticated.
 *
 * @param string $redirectTarget
 * @return void
 */
function require_login(string $redirectTarget = 'home.php'): void {
    if (is_logged_in()) {
        return;
    }

    set_flash('error', 'Vui lòng đăng nhập để tiếp tục.');
    redirect('login.php?redirect=' . urlencode($redirectTarget));
}

/**
 * Ensure the current request belongs to an admin user.
 *
 * @param string $redirectTarget
 * @return void
 */
function require_admin(string $redirectTarget = 'admin/dashboard.php'): void {
    if (!is_logged_in()) {
        set_flash('error', 'Vui lòng đăng nhập bằng tài khoản admin.');
        redirect('admin/login.php?redirect=' . urlencode($redirectTarget));
    }

    if (is_admin()) {
        return;
    }

    set_flash('error', 'Bạn không có quyền truy cập khu vực admin.');
    redirect('home.php');
}

/**
 * Get current user ID
 * 
 * @return int|null User ID or null
 */
function get_user_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 * 
 * @return array|null User data or null
 */
function get_user(): ?array {
    return $_SESSION['user_data'] ?? null;
}

/**
 * Get current user display name.
 *
 * @return string|null
 */
function get_user_name(): ?string {
    $user = get_user();

    if (!$user) {
        return null;
    }

    if (!empty($user['full_name'])) {
        return $user['full_name'];
    }

    return $user['username'] ?? null;
}

/**
 * Get total quantity stored in the session cart.
 *
 * @return int
 */
function cart_item_count(): int {
    $cart = $_SESSION['cart'] ?? [];

    if (!is_array($cart)) {
        return 0;
    }

    $total = 0;
    foreach ($cart as $item) {
        $total += max(0, (int)($item['quantity'] ?? 0));
    }

    return $total;
}

/**
 * Set flash message
 * 
 * @param string $type Message type
 * @param string $message Message text
 * @return void
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * 
 * @return array|null Flash message or null
 */
function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Format currency (VND)
 * 
 * @param float|int $amount Amount to format
 * @return string Formatted currency
 */
function format_currency(float|int $amount): string {
    return number_format($amount, 0, ',', '.') . ' đ';
}

/**
 * Format date
 * 
 * @param string $date Date string
 * @param string $format Output format
 * @return string Formatted date
 */
function format_date(string $date, string $format = 'd/m/Y H:i'): string {
    return date($format, strtotime($date));
}

/**
 * Generate slug from string
 * 
 * @param string $string Input string
 * @return string URL-friendly slug
 */
function create_slug(string $string): string {
    $string = trim($string);

    if (function_exists('iconv')) {
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
        if ($transliterated !== false) {
            $string = $transliterated;
        }
    }

    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);

    return trim($string ?? '', '-');
}

/**
 * Validate email
 * 
 * @param string $email Email address
 * @return bool True if valid
 */
function is_valid_email(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate and store a CSRF token.
 *
 * @return string
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Validate a submitted CSRF token.
 *
 * @param string|null $token
 * @return bool
 */
function verify_csrf_token(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Resolve a safe internal redirect target.
 *
 * @param string|null $target
 * @param string $fallback
 * @return string
 */
function safe_redirect_target(?string $target, string $fallback = 'home.php'): string {
    if (empty($target)) {
        return $fallback;
    }

    $target = trim($target);
    if ($target === '') {
        return $fallback;
    }

    if (preg_match('#^(?:https?:)?//#i', $target) === 1) {
        return $fallback;
    }

    if (str_contains($target, "\r") || str_contains($target, "\n")) {
        return $fallback;
    }

    if (!str_starts_with($target, '/')) {
        return $target;
    }

    return ltrim($target, '/');
}

/**
 * Validate an external image URL.
 *
 * @param string $url
 * @return string|null
 */
function validate_image_source_url(string $url): ?string {
    $url = trim($url);

    if ($url === '') {
        return 'Vui lòng nhập URL hình ảnh.';
    }

    if (strlen($url) > 2048) {
        return 'URL hình ảnh quá dài.';
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return 'URL hình ảnh không hợp lệ.';
    }

    $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
    if (!in_array($scheme, ['http', 'https'], true)) {
        return 'Chỉ chấp nhận URL hình ảnh từ http hoặc https.';
    }

    return null;
}

/**
 * Validate an uploaded image file.
 *
 * @param array $file
 * @return array{valid: bool, error: string|null, extension?: string}
 */
function validate_uploaded_image(array $file): array {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'Tải ảnh lên không thành công.'];
    }

    $tmpName = (string)($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        return ['valid' => false, 'error' => 'Không tìm thấy file upload hợp lệ.'];
    }

    $fileSize = (int)($file['size'] ?? 0);
    if ($fileSize <= 0 || $fileSize > MAX_FILE_SIZE) {
        return ['valid' => false, 'error' => 'Kích thước ảnh vượt quá giới hạn cho phép.'];
    }

    $extension = strtolower((string)pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
    if ($extension === '' || !in_array($extension, ALLOWED_IMAGE_EXTENSIONS, true)) {
        return ['valid' => false, 'error' => 'Định dạng ảnh không được hỗ trợ.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = $finfo ? (string)finfo_file($finfo, $tmpName) : '';
    if ($finfo) {
        finfo_close($finfo);
    }

    if ($mimeType === '' || !in_array($mimeType, ALLOWED_IMAGE_TYPES, true)) {
        return ['valid' => false, 'error' => 'File tải lên không phải là ảnh hợp lệ.'];
    }

    if (getimagesize($tmpName) === false) {
        return ['valid' => false, 'error' => 'Không thể đọc metadata của ảnh tải lên.'];
    }

    return [
        'valid' => true,
        'error' => null,
        'extension' => $extension,
    ];
}

/**
 * Generate random string
 * 
 * @param int $length String length
 * @return string Random string
 */
function random_string(int $length = 10): string {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Get client IP
 * 
 * @return string Client IP address
 */
function get_client_ip(): string {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Debug function
 * 
 * @param mixed $data Data to dump
 * @return never
 */
function dd(mixed $data): never {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

/**
 * Truncate text
 * 
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to append
 * @return string Truncated text
 */
function truncate(string $text, int $length = 100, string $suffix = '...'): string {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . $suffix;
    }
    return $text;
}
