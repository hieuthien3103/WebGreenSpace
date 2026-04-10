<?php
/**
 * Base controller with rendering helpers.
 */
abstract class Controller {
    protected Request $request;

    public function __construct(?Request $request = null) {
        $this->request = $request ?? new Request();
    }

    /**
     * Render a view.
     */
    protected function render(string $view, array $data = []): void {
        $this->view($view, $data)->send();
    }

    /**
     * Build a view response.
     */
    protected function view(string $view, array $data = [], int $statusCode = 200): ViewResponse {
        return new ViewResponse($view, $data, $statusCode);
    }

    /**
     * Build a PHP template response.
     *
     * @param array<string, mixed> $globals
     */
    protected function template(string $templatePath, array $data = [], int $statusCode = 200, array $globals = []): PhpTemplateResponse {
        return new PhpTemplateResponse($templatePath, $data, $statusCode, $globals);
    }

    /**
     * Build a redirect response.
     */
    protected function redirect(string $targetUrl, int $statusCode = 302): RedirectResponse {
        return new RedirectResponse($targetUrl, $statusCode);
    }

    /**
     * Build a JSON response.
     */
    protected function json(array $payload, int $statusCode = 200): JsonResponse {
        return new JsonResponse($payload, $statusCode);
    }

    /**
     * Send a prepared response.
     */
    protected function respond(Response $response): void {
        $response->send();
    }

    /**
     * Render a 404 response.
     */
    protected function notFound(string $message = 'Không tìm thấy nội dung bạn đang tìm kiếm.'): void {
        $this->notFoundResponse($message)->send();
    }

    /**
     * Build a 404 response.
     */
    protected function notFoundResponse(string $message = 'Không tìm thấy nội dung bạn đang tìm kiếm.'): ViewResponse {
        return $this->view('errors/404', [
            'pageTitle' => '404 - Không tìm thấy trang',
            'message' => $message,
        ], 404);
    }
}
