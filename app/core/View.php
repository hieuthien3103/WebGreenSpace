<?php
/**
 * Simple view renderer.
 */
class View {
    /**
     * Render a PHP view with extracted data.
     */
    public static function render(string $view, array $data = []): void {
        $path = self::resolvePath($view);
        extract($data, EXTR_SKIP);
        include $path;
    }

    /**
     * Resolve a logical view name to a real file path.
     */
    public static function resolvePath(string $view): string {
        $path = VIEW_PATH . '/' . trim($view, '/') . '.php';
        if (!file_exists($path)) {
            throw new RuntimeException("View not found: {$view}");
        }

        return $path;
    }
}
