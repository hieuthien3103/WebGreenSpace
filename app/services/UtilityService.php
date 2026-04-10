<?php
/**
 * Handle small utility endpoints that were previously implemented under /public.
 */
class UtilityService {
    /**
     * Return the active product price range.
     */
    public function getPriceRange(): array {
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->query(
            "SELECT
                MIN(CASE WHEN sale_price IS NOT NULL AND sale_price > 0 THEN sale_price ELSE price END) AS min_price,
                MAX(CASE WHEN sale_price IS NOT NULL AND sale_price > 0 THEN sale_price ELSE price END) AS max_price
             FROM products
             WHERE status = 'active'"
        );

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'min_price' => null,
            'max_price' => null,
        ];
    }

    /**
     * Build a small environment snapshot for the debug page.
     */
    public function debugSnapshot(): array {
        return [
            'app_url' => APP_URL,
            'img_url' => IMG_URL,
            'upload_url' => UPLOAD_URL,
            'http_host' => $_SERVER['HTTP_HOST'] ?? 'N/A',
            'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'N/A',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'sample_image_url' => image_url('products/test.jpg'),
        ];
    }
}
