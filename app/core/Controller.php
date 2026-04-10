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
        View::render($view, $data);
    }

    /**
     * Render a 404 response.
     */
    protected function notFound(string $message = 'Không tìm thấy nội dung bạn đang tìm kiếm.'): void {
        http_response_code(404);
        View::render('errors/404', [
            'pageTitle' => '404 - Không tìm thấy trang',
            'message' => $message,
        ]);
    }
}
