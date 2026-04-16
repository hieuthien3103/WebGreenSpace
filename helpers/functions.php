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
    $configuredUrl = rtrim(APP_URL, '/');

    if (!empty($_SERVER['HTTP_HOST'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $configuredPath = (string)parse_url($configuredUrl, PHP_URL_PATH);
        $configuredPath = $configuredPath === '/' ? '' : rtrim($configuredPath, '/');
        $configuredUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $configuredPath;
    }

    return rtrim($configuredUrl, '/') . '/' . ltrim($path, '/');
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
    return base_url('public/images/' . ltrim($path, '/'));
}

/**
 * Get upload URL
 * 
 * @param string $path Upload path
 * @return string Full upload URL
 */
function upload_url(string $path): string {
    return base_url('uploads/' . ltrim($path, '/'));
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
 * Get string length with UTF-8 support even when mbstring is unavailable.
 *
 * @param string $value
 * @return int
 */
function string_length(string $value): int {
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }

    if (preg_match_all('/./us', $value, $matches) === false) {
        return strlen($value);
    }

    return count($matches[0]);
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
        redirect(admin_path('login.php?redirect=' . urlencode($redirectTarget)));
    }

    if (is_admin()) {
        return;
    }

    set_flash('error', 'Bạn không có quyền truy cập khu vực admin.');
    redirect('home.php');
}

/**
 * Lightweight admin permission catalog for sub-admin accounts.
 *
 * @return array<string, array{label: string, description: string}>
 */
function admin_permission_catalog(): array {
    return [
        'admin.full_access' => [
            'label' => 'Toàn quyền quản trị',
            'description' => 'Truy cập toàn bộ module quản trị mà không cần chọn riêng từng quyền.',
        ],
        'orders.manage' => [
            'label' => 'Quản lý đơn hàng',
            'description' => 'Xem đơn, cập nhật trạng thái và duyệt thanh toán mô phỏng.',
        ],
        'products.manage' => [
            'label' => 'Quản lý sản phẩm',
            'description' => 'Tạo, sửa, ẩn/hiện sản phẩm và quản lý luôn ảnh sản phẩm trong cùng màn hình.',
        ],
        'categories.manage' => [
            'label' => 'Quản lý danh mục',
            'description' => 'Tạo và chỉnh sửa cây danh mục sản phẩm.',
        ],
        'users.manage' => [
            'label' => 'Quản lý tài khoản',
            'description' => 'Phân quyền, khóa/mở và cập nhật tài khoản người dùng.',
        ],
        'contacts.manage' => [
            'label' => 'Quản lý liên hệ',
            'description' => 'Đọc tin nhắn từ form liên hệ và theo dõi trạng thái xử lý.',
        ],
        'inventory.manage' => [
            'label' => 'Quản lý kho hàng',
            'description' => 'Nhập kho theo lô, xem lịch sử nhập và tồn kho theo lô (FIFO).',
        ],
    ];
}

/**
 * Normalize stored admin permissions to a clean list of known keys.
 *
 * @param mixed $permissions
 * @return string[]
 */
function normalize_admin_permissions(mixed $permissions): array {
    if (is_string($permissions)) {
        $decoded = json_decode($permissions, true);
        $permissions = is_array($decoded) ? $decoded : [];
    }

    if (!is_array($permissions)) {
        return [];
    }

    $allowed = array_keys(admin_permission_catalog());
    $normalized = [];

    foreach ($permissions as $permission) {
        $permission = trim((string)$permission);
        if ($permission === 'uploads.manage') {
            $permission = 'products.manage';
        }

        if ($permission === '' || !in_array($permission, $allowed, true) || in_array($permission, $normalized, true)) {
            continue;
        }

        $normalized[] = $permission;
    }

    return $normalized;
}

/**
 * Check whether the current admin has unrestricted access.
 */
function admin_has_full_access(?array $user = null): bool {
    if (!is_admin()) {
        return false;
    }

    $user ??= get_user();
    if (!$user) {
        return false;
    }

    return in_array('admin.full_access', normalize_admin_permissions($user['admin_permissions'] ?? []), true);
}

/**
 * Get normalized admin permissions for the current session.
 *
 * @return string[]
 */
function get_admin_permissions(): array {
    $user = get_user();
    return normalize_admin_permissions($user['admin_permissions'] ?? []);
}

/**
 * Check a specific lightweight admin permission.
 */
function admin_has_permission(string $permission): bool {
    if (!is_admin()) {
        return false;
    }

    if (!array_key_exists($permission, admin_permission_catalog())) {
        return false;
    }

    if (admin_has_full_access()) {
        return true;
    }

    return in_array($permission, get_admin_permissions(), true);
}

/**
 * Ensure the current admin can access a specific admin capability.
 */
function require_admin_permission(string $permission, string $redirectTarget = 'dashboard.php'): void {
    require_admin($redirectTarget);

    if (admin_has_permission($permission)) {
        return;
    }

    set_flash('error', 'Bạn không có quyền truy cập chức năng quản trị này.');
    redirect(admin_path('dashboard.php'));
}

/**
 * Build a robust path inside the admin area.
 *
 * @param string $target
 * @return string
 */
function admin_path(string $target = 'dashboard.php'): string {
    $target = ltrim(trim($target), '/');
    if (str_starts_with($target, 'admin/')) {
        $target = substr($target, 6);
    }

    $requestPath = (string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if ($requestPath !== '') {
        $adminPosition = strpos($requestPath, '/admin');
        if ($adminPosition !== false) {
            $prefix = rtrim(substr($requestPath, 0, $adminPosition), '/');
            return ($prefix !== '' ? $prefix : '') . '/admin/' . $target;
        }
    }

    return 'admin/' . $target;
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
 * Resolve the application secret used for HMAC-style tokens.
 */
function app_secret(): string {
    if (APP_SECRET !== '') {
        return APP_SECRET;
    }

    if (DB_PASS !== '') {
        return DB_PASS;
    }

    return hash('sha256', BASE_PATH . '|' . APP_URL . '|qr-payment-fallback');
}

/**
 * Build a signed QR payment token for one order.
 */
function build_qr_payment_token(int $orderId, int $userId, string $orderNumber): string {
    return hash_hmac('sha256', $orderId . '|' . $userId . '|' . $orderNumber, app_secret());
}

/**
 * Payment method catalog shared across checkout, user screens, and admin.
 *
 * @return array<string, array<string, mixed>>
 */
function payment_method_catalog(): array {
    return [
        'cod' => [
            'label' => 'Thanh toán khi nhận hàng',
            'description' => 'Phù hợp với đơn cần xác nhận thủ công và giao tận nơi.',
            'icon' => 'local_shipping',
            'is_online' => false,
            'is_manual_review' => false,
        ],
        'online_mock' => [
            'label' => 'Chuyển khoản giả lập',
            'description' => 'Nhận thông tin tài khoản mô phỏng và bấm "Tôi đã thanh toán" sau khi chuyển khoản.',
            'icon' => 'credit_card',
            'is_online' => true,
            'is_manual_review' => true,
        ],
    ];
}

/**
 * Get one payment method's metadata.
 *
 * @return array<string, mixed>
 */
function payment_method_meta(string $method): array {
    $catalog = payment_method_catalog();

    return $catalog[$method] ?? [
        'label' => $method,
        'description' => '',
        'icon' => 'payments',
        'is_online' => false,
        'is_manual_review' => false,
    ];
}

/**
 * Get payment methods ready for checkout rendering.
 *
 * @return array<int, array<string, mixed>>
 */
function payment_checkout_options(): array {
    $options = [];

    foreach (payment_method_catalog() as $value => $meta) {
        $options[] = array_merge(['value' => $value], $meta);
    }

    return $options;
}

/**
 * Human-friendly payment method label.
 */
function payment_method_label(string $method): string {
    return (string)(payment_method_meta($method)['label'] ?? $method);
}

/**
 * Whether the method is an online payment flow.
 */
function payment_method_is_online(string $method): bool {
    return !empty(payment_method_meta($method)['is_online']);
}

/**
 * Whether the method still needs manual review after buyer confirms payment.
 */
function payment_method_requires_manual_review(string $method): bool {
    return !empty(payment_method_meta($method)['is_manual_review']);
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

    if (preg_match('/^[a-z][a-z0-9+\-.]*:/i', $target) === 1) {
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
 * Store authenticated user data into the session.
 * Must be called by controllers after a successful login or registration.
 *
 * @param array $user User data already stripped of sensitive fields (e.g. password).
 * @return void
 */
function store_auth_session(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']   = (int)$user['id'];
    $_SESSION['user_role'] = $user['role'] ?? 'user';
    $_SESSION['user_data'] = $user;
}

/**
 * Destroy the current authentication session and invalidate the cookie.
 * Must be called by controllers on logout.
 *
 * @return void
 */
function clear_auth_session(): void {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_regenerate_id(true);
}

/**
 * Check whether login attempts are rate-limited for the current session.
 *
 * @param int $maxAttempts Maximum failed attempts before lockout.
 * @param int $windowSeconds Window in which failures are counted.
 * @param int $lockoutSeconds Cooldown after exceeding limit.
 * @return array{allowed: bool, retry_after?: int}
 */
function check_login_rate_limit(int $maxAttempts = 5, int $windowSeconds = 300, int $lockoutSeconds = 60): array {
    $now = time();
    $key = 'login_attempts';
    $lockKey = 'login_locked_until';

    if (!empty($_SESSION[$lockKey]) && $now < (int)$_SESSION[$lockKey]) {
        return ['allowed' => false, 'retry_after' => (int)$_SESSION[$lockKey] - $now];
    }

    if (!empty($_SESSION[$lockKey]) && $now >= (int)$_SESSION[$lockKey]) {
        unset($_SESSION[$lockKey], $_SESSION[$key]);
    }

    $attempts = $_SESSION[$key] ?? [];
    $attempts = array_values(array_filter($attempts, static fn(int $t): bool => ($now - $t) < $windowSeconds));
    $_SESSION[$key] = $attempts;

    if (count($attempts) >= $maxAttempts) {
        $_SESSION[$lockKey] = $now + $lockoutSeconds;
        $_SESSION[$key] = [];
        return ['allowed' => false, 'retry_after' => $lockoutSeconds];
    }

    return ['allowed' => true];
}

/**
 * Record one failed login attempt for rate limiting.
 */
function record_failed_login(): void {
    $_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? [];
    $_SESSION['login_attempts'][] = time();
}

/**
 * Clear login rate limit state after successful login.
 */
function clear_login_rate_limit(): void {
    unset($_SESSION['login_attempts'], $_SESSION['login_locked_until']);
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
