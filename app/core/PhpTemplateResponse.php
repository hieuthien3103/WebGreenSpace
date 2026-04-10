<?php
/**
 * Render an arbitrary PHP template file with extracted data.
 */
class PhpTemplateResponse extends Response {
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $globals
     */
    public function __construct(
        private string $templatePath,
        private array $data = [],
        int $statusCode = 200,
        private array $globals = [],
    ) {
        parent::__construct($statusCode);
    }

    /**
     * Render the PHP template.
     */
    public function send(): void {
        if (!file_exists($this->templatePath)) {
            throw new RuntimeException('Template not found: ' . $this->templatePath);
        }

        $this->sendStatusCode();

        $previousTemplateFlag = $GLOBALS['mvc_template_rendering'] ?? null;
        $GLOBALS['mvc_template_rendering'] = true;

        $previousGlobals = [];
        foreach ($this->globals as $key => $value) {
            $previousGlobals[$key] = $GLOBALS[$key] ?? null;
            $GLOBALS[$key] = $value;
        }

        extract($this->data, EXTR_SKIP);
        include $this->templatePath;

        foreach ($this->globals as $key => $_) {
            if (array_key_exists($key, $previousGlobals) && $previousGlobals[$key] !== null) {
                $GLOBALS[$key] = $previousGlobals[$key];
                continue;
            }

            unset($GLOBALS[$key]);
        }

        if ($previousTemplateFlag === null) {
            unset($GLOBALS['mvc_template_rendering']);
            return;
        }

        $GLOBALS['mvc_template_rendering'] = $previousTemplateFlag;
    }
}
