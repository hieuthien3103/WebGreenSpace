<?php
/**
 * HTTP redirect response.
 */
class RedirectResponse extends Response {
    private string $targetUrl;

    public function __construct(string $targetUrl, int $statusCode = 302) {
        parent::__construct($statusCode);
        $this->targetUrl = $targetUrl;
    }

    /**
     * Send the redirect response.
     */
    public function send(): void {
        $this->sendStatusCode();
        header('Location: ' . $this->targetUrl);
    }
}
