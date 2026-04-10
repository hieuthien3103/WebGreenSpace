<?php
/**
 * Lightweight HTTP request wrapper for controllers.
 */
class Request {
    /**
     * Get the current HTTP method.
     */
    public function method(): string {
        return strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    }

    /**
     * Get the normalized request path.
     */
    public function path(): string {
        $path = (string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if ($path === '') {
            return '/';
        }

        $normalized = '/' . trim($path, '/');
        return $normalized === '//' ? '/' : $normalized;
    }

    /**
     * Retrieve a query parameter.
     */
    public function query(string $key, mixed $default = null): mixed {
        return $_GET[$key] ?? $default;
    }

    /**
     * Retrieve a request input value from POST first, then GET.
     */
    public function input(string $key, mixed $default = null): mixed {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Retrieve a trimmed string query value.
     */
    public function string(string $key, string $default = ''): string {
        $value = $this->query($key, $default);
        return is_scalar($value) ? trim((string)$value) : $default;
    }

    /**
     * Retrieve an integer query value.
     */
    public function integer(string $key, int $default = 0): int {
        $value = $this->query($key, $default);
        return is_numeric($value) ? (int)$value : $default;
    }

    /**
     * Retrieve a float query value.
     */
    public function float(string $key, ?float $default = null): ?float {
        $value = $this->query($key, $default);
        return is_numeric($value) ? (float)$value : $default;
    }

    /**
     * Detect an AJAX request.
     */
    public function isAjax(): bool {
        return (string)$this->query('ajax', '0') === '1'
            || strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
    }
}
