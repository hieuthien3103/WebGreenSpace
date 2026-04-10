<?php
/**
 * Format small utility payloads.
 */
class UtilityPresenter {
    /**
     * Normalize the public price range payload.
     */
    public function presentPriceRange(array $payload): array {
        return [
            'min_price' => isset($payload['min_price']) ? (float)$payload['min_price'] : null,
            'max_price' => isset($payload['max_price']) ? (float)$payload['max_price'] : null,
        ];
    }

    /**
     * Build the debug page state.
     */
    public function presentDebug(array $snapshot): array {
        return [
            'pageTitle' => 'Debug - GreenSpace',
            'snapshot' => $snapshot,
        ];
    }
}
