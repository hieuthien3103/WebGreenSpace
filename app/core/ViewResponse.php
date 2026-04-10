<?php
/**
 * Response wrapper for rendered PHP views.
 */
class ViewResponse extends Response {
    private string $view;
    private array $data;

    public function __construct(string $view, array $data = [], int $statusCode = 200) {
        parent::__construct($statusCode);
        $this->view = $view;
        $this->data = $data;
    }

    /**
     * Render the prepared view.
     */
    public function send(): void {
        $this->sendStatusCode();
        View::render($this->view, $this->data);
    }
}
