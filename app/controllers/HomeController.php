<?php
/**
 * Home Controller
 */
class HomeController extends Controller {
    private HomeService $homeService;

    public function __construct(?Request $request = null, ?HomeService $homeService = null) {
        parent::__construct($request);
        $this->homeService = $homeService ?? new HomeService();
    }

    /**
     * Display homepage
     */
    public function index(): void {
        $this->render('storefront/home/index', array_merge(
            [
                'pageTitle' => 'Trang chủ - GreenSpace',
                'currentPage' => 'home',
            ],
            $this->homeService->getHomepageData()
        ));
    }
}
