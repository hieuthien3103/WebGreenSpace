<?php
/**
 * Simple Router for cleaner URLs
 */

class Router {
    private array $routes = [];
    private string $basePath;
    
    public function __construct(string $basePath = '') {
        $this->basePath = rtrim($basePath, '/');
    }
    
    /**
     * Add a GET route
     * 
     * @param string $path URL path
     * @param callable|string $handler Handler function or file path
     * @return self
     */
    public function get(string $path, callable|string $handler): self {
        $this->addRoute('GET', $path, $handler);
        return $this;
    }
    
    /**
     * Add a POST route
     * 
     * @param string $path URL path
     * @param callable|string $handler Handler function or file path
     * @return self
     */
    public function post(string $path, callable|string $handler): self {
        $this->addRoute('POST', $path, $handler);
        return $this;
    }
    
    /**
     * Add a route
     * 
     * @param string $method HTTP method
     * @param string $path URL path
     * @param callable|string $handler Handler function or file path
     * @return void
     */
    private function addRoute(string $method, string $path, callable|string $handler): void {
        $pattern = $this->pathToPattern($path);
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'path' => $path,
            'handler' => $handler
        ];
    }
    
    /**
     * Convert path to regex pattern
     * 
     * @param string $path URL path
     * @return string Regex pattern
     */
    private function pathToPattern(string $path): string {
        // Convert :param to named capture groups
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $this->basePath . $pattern . '$#';
    }
    
    /**
     * Dispatch the request
     * 
     * @return mixed Handler result
     */
    public function dispatch(): mixed {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                return $this->executeHandler($route['handler'], $params);
            }
        }
        
        // No route matched - 404
        http_response_code(404);
        $this->handle404();
        return null;
    }
    
    /**
     * Execute the route handler
     * 
     * @param callable|string $handler Handler function or file path
     * @param array $params Route parameters
     * @return mixed Handler result
     */
    private function executeHandler(callable|string $handler, array $params = []): mixed {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }
        
        if (is_string($handler)) {
            // Extract params to variables
            extract($params);
            
            // Include the file
            if (file_exists($handler)) {
                return require $handler;
            } else {
                error_log("Router: Handler file not found: {$handler}");
                http_response_code(500);
                echo "Internal Server Error";
                return null;
            }
        }
        
        return null;
    }
    
    /**
     * Handle 404 errors
     * 
     * @return void
     */
    private function handle404(): void {
        echo '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Không tìm thấy trang</title>
    <style>
        body { font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #f5f5f5; }
        .error { text-align: center; }
        h1 { font-size: 72px; margin: 0; color: #2d7a4e; }
        p { font-size: 18px; color: #666; }
        a { color: #2d7a4e; text-decoration: none; }
    </style>
</head>
<body>
    <div class="error">
        <h1>404</h1>
        <p>Không tìm thấy trang bạn đang tìm kiếm</p>
        <a href="/">← Về trang chủ</a>
    </div>
</body>
</html>';
    }
    
    /**
     * Get all registered routes
     * 
     * @return array Routes list
     */
    public function getRoutes(): array {
        return $this->routes;
    }
}
