<?php
/**
 * Prepare homepage data for the storefront view layer.
 */
class HomePresenter {
    private HomeService $homeService;

    public function __construct(?HomeService $homeService = null) {
        $this->homeService = $homeService ?? new HomeService();
    }

    /**
     * Build the homepage view model.
     */
    public function presentIndex(): array {
        return array_merge([
            'pageTitle' => 'Trang chủ - GreenSpace',
            'currentPage' => 'home',
        ], $this->homeService->getHomepageData());
    }
}
