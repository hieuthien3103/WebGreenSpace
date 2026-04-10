<?php
/**
 * Router with support for controller actions and request injection.
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
     * @param callable|string|array $handler Handler function, controller action, or file path
     * @return self
     */
    public function get(string $path, callable|string|array $handler): self {
        $this->addRoute('GET', $path, $handler);
        return $this;
    }

    /**
     * Add a POST route
     *
     * @param string $path URL path
     * @param callable|string|array $handler Handler function, controller action, or file path
     * @return self
     */
    public function post(string $path, callable|string|array $handler): self {
        $this->addRoute('POST', $path, $handler);
        return $this;
    }

    /**
     * Add a route
     *
     * @param string $method HTTP method
     * @param string $path URL path
     * @param callable|string|array $handler Handler function, controller action, or file path
     * @return void
     */
    private function addRoute(string $method, string $path, callable|string|array $handler): void {
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
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $this->basePath . rtrim($pattern, '/') . '/?$#';
    }

    /**
     * Dispatch the request
     *
     * @return mixed Handler result
     */
    public function dispatch(): mixed {
        $method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $uri = $this->normalizeUri((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                return $this->executeHandler($route['handler'], $params);
            }
        }

        http_response_code(404);
        $this->handle404();
        return null;
    }

    /**
     * Execute the route handler
     *
     * @param callable|string|array $handler Handler function, controller action, or file path
     * @param array $params Route parameters
     * @return mixed Handler result
     */
    private function executeHandler(callable|string|array $handler, array $params = []): mixed {
        if (is_array($handler) && count($handler) === 2) {
            [$controllerClass, $method] = $handler;

            if (is_string($controllerClass)) {
                if (!class_exists($controllerClass)) {
                    throw new RuntimeException("Router: Controller not found: {$controllerClass}");
                }

                $controller = new $controllerClass();
                return $this->invokeCallable([$controller, (string)$method], $params);
            }

            if (is_object($controllerClass)) {
                return $this->invokeCallable([$controllerClass, (string)$method], $params);
            }
        }

        if (is_callable($handler)) {
            return $this->invokeCallable($handler, $params);
        }

        if (is_string($handler)) {
            extract($params);
            if (file_exists($handler)) {
                return require $handler;
            }

            error_log("Router: Handler file not found: {$handler}");
            http_response_code(500);
            echo "Internal Server Error";
            return null;
        }

        return null;
    }

    /**
     * Invoke a callable while injecting route params and request objects.
     */
    private function invokeCallable(callable $handler, array $params = []): mixed {
        $reflection = is_array($handler)
            ? new ReflectionMethod($handler[0], $handler[1])
            : new ReflectionFunction($handler);

        $arguments = [];
        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin() && $type->getName() === Request::class) {
                $arguments[] = new Request();
                continue;
            }

            $name = $parameter->getName();
            if (array_key_exists($name, $params)) {
                $arguments[] = $params[$name];
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
                continue;
            }

            $arguments[] = null;
        }

        if ($reflection instanceof ReflectionMethod) {
            return $reflection->invokeArgs($handler[0], $arguments);
        }

        return $reflection->invokeArgs($arguments);
    }

    /**
     * Normalize a URI for matching.
     */
    private function normalizeUri(string $uri): string {
        $uri = '/' . trim($uri, '/');
        return $uri === '//' ? '/' : $uri;
    }

    /**
     * Handle 404 errors
     *
     * @return void
     */
    private function handle404(): void {
        View::render('errors/404', [
            'pageTitle' => '404 - Không tìm thấy trang',
            'message' => 'Không tìm thấy trang bạn đang tìm kiếm.',
        ]);
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
