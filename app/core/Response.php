<?php
/**
 * Base HTTP response object.
 */
abstract class Response {
    protected int $statusCode;

    public function __construct(int $statusCode = 200) {
        $this->statusCode = $statusCode;
    }

    /**
     * Send the response to the client.
     */
    abstract public function send(): void;

    /**
     * Apply the configured status code.
     */
    protected function sendStatusCode(): void {
        http_response_code($this->statusCode);
    }
}
