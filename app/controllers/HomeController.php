<?php
/**
 * Home Controller
 */
class HomeController extends Controller {
    private HomePresenter $homePresenter;

    public function __construct(?Request $request = null, ?HomePresenter $homePresenter = null) {
        parent::__construct($request);
        $this->homePresenter = $homePresenter ?? new HomePresenter();
    }

    /**
     * Display homepage
     */
    public function index(): ViewResponse {
        return $this->view('storefront/home/index', $this->homePresenter->presentIndex());
    }
}
