<?php
/**
 * JSON HTTP response.
 */
class JsonResponse extends Response {
    public function __construct(
        private array $payload,
        int $statusCode = 200,
    ) {
        parent::__construct($statusCode);
    }

    /**
     * Send the JSON response.
     */
    public function send(): void {
        $this->sendStatusCode();
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($this->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
