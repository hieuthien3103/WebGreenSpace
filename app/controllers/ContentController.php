<?php
/**
 * Handle static and informational storefront pages.
 */
class ContentController extends Controller {
    public function __construct(
        ?Request $request = null,
        private ?StorefrontPageService $pageService = null,
        private ?StorefrontPagePresenter $pagePresenter = null,
    ) {
        parent::__construct($request);
        $this->pageService ??= new StorefrontPageService();
        $this->pagePresenter ??= new StorefrontPagePresenter();
    }

    /**
     * Show the care guide page.
     */
    public function care(): Response {
        return $this->template(PUBLIC_PATH . '/care.php', $this->pagePresenter->presentCare());
    }

    /**
     * Show or submit the contact page.
     */
    public function contact(): Response {
        $values = [];
        $errors = [];

        if ($this->request->method() === 'POST') {
            $result = $this->pageService->submitContact($_POST);
            if (!empty($result['success'])) {
                set_flash('success', 'GreenSpace đã nhận được tin nhắn của bạn. Admin sẽ xem và phản hồi sớm nhất.');
                return $this->redirect('contact.php');
            }

            $values = $result['values'] ?? [];
            $errors = $result['errors'] ?? [];
        }

        return $this->template(PUBLIC_PATH . '/contact.php', $this->pagePresenter->presentContact($values, $errors));
    }
}
