<?php
/**
 * Handle utility pages and JSON endpoints that still need MVC wrappers.
 */
class UtilityController extends Controller {
    public function __construct(
        ?Request $request = null,
        private ?UtilityService $utilityService = null,
        private ?UtilityPresenter $utilityPresenter = null,
    ) {
        parent::__construct($request);
        $this->utilityService ??= new UtilityService();
        $this->utilityPresenter ??= new UtilityPresenter();
    }

    /**
     * Return the current product price range.
     */
    public function priceRange(): Response {
        return $this->json(
            $this->utilityPresenter->presentPriceRange($this->utilityService->getPriceRange())
        );
    }

    /**
     * Show the internal debug page.
     */
    public function debug(): Response {
        return $this->template(
            PUBLIC_PATH . '/debug.php',
            $this->utilityPresenter->presentDebug($this->utilityService->debugSnapshot())
        );
    }
}
