<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

function qr_pay_build_token(int $orderId, int $userId, string $orderNumber): string {
    return build_qr_payment_token($orderId, $userId, $orderNumber);
}

$orderModel = new Order();
$orderId = max(0, (int)($_GET['order_id'] ?? $_POST['order_id'] ?? 0));
$token = trim((string)($_GET['token'] ?? $_POST['token'] ?? ''));

$order = $orderId > 0 ? $orderModel->getOnlineMockOrderForPortal($orderId) : null;
$isValidToken = $order
    ? hash_equals(qr_pay_build_token((int)$order['id'], (int)$order['user_id'], (string)$order['order_number']), $token)
    : false;

$portalError = null;
$portalSuccess = null;

if (!$order || !$isValidToken) {
    $portalError = 'Lien ket thanh toan khong hop le hoac da het han.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$portalError) {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'confirm_qr_payment') {
        if ((string)($order['order_status'] ?? '') === 'cancelled') {
            $portalError = 'Don hang da bi huy nen khong the xac nhan thanh toan QR nua.';
        } elseif ((string)$order['payment_status'] === 'pending_review') {
            $portalSuccess = 'Yeu cau thanh toan da duoc gui truoc do. Admin dang xu ly.';
        } elseif ((string)$order['payment_status'] === 'paid') {
            $portalSuccess = 'Don hang da duoc admin duyet thanh toan.';
        } elseif ((string)$order['payment_status'] !== 'unpaid') {
            $portalError = 'Don hang hien khong o trang thai cho thanh toan QR.';
        } else {
            $submitted = $orderModel->confirmOnlineMockPaymentByPortal((int)$order['id']);

            if ($submitted) {
                $portalSuccess = 'Da gui yeu cau thanh toan den admin ngay lap tuc. Vui long cho duyet.';
                $order = $orderModel->getOnlineMockOrderForPortal((int)$order['id']);
            } else {
                $portalError = $orderModel->getLastErrorMessage() ?? 'Khong the gui yeu cau thanh toan luc nay. Vui long thu lai.';
            }
        }
    }
}

$pageTitle = 'Thanh toan QR mo phong - GreenSpace';
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= clean($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-[#eef6f1] font-['Plus_Jakarta_Sans'] text-[#102118]">
    <main class="mx-auto flex min-h-screen w-full max-w-md items-center px-4 py-8">
        <section class="w-full rounded-[2rem] border border-[#d8eadf] bg-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">GreenSpace QR Payment</p>
            <h1 class="mt-2 text-2xl font-extrabold">Thanh toan chuyen khoan mo phong</h1>

            <?php if ($portalError): ?>
                <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                    <?= clean($portalError) ?>
                </div>
            <?php endif; ?>

            <?php if ($portalSuccess): ?>
                <div class="mt-5 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
                    <?= clean($portalSuccess) ?>
                </div>
            <?php endif; ?>

            <?php if ($order && !$portalError): ?>
                <div class="mt-5 space-y-3 rounded-2xl border border-[#e7f1ea] bg-[#f8fbf9] p-4 text-sm">
                    <p><strong>Ma don:</strong> <?= clean((string)$order['order_number']) ?></p>
                    <p><strong>So tien:</strong> <?= format_currency((float)$order['total_amount']) ?></p>
                    <p><strong>Trang thai:</strong> <?= clean((string)$order['payment_status']) ?></p>
                </div>

                <?php if ((string)($order['order_status'] ?? '') === 'cancelled'): ?>
                    <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                        Don hang nay da bi huy, lien ket thanh toan QR khong con hieu luc.
                    </div>
                <?php elseif ((string)$order['payment_status'] === 'unpaid'): ?>
                    <form method="POST" class="mt-5">
                        <input type="hidden" name="order_id" value="<?= clean((string)$order['id']) ?>">
                        <input type="hidden" name="token" value="<?= clean($token) ?>">
                        <input type="hidden" name="action" value="confirm_qr_payment">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-[#2ecc70] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-[#2ecc70]/30 transition-colors hover:bg-[#25a25a]">
                            Thanh toan ngay
                        </button>
                    </form>
                <?php endif; ?>

                <p class="mt-4 text-center text-xs leading-6 text-[#5d6d63]">Sau khi bấm thanh toan, admin se nhan yeu cau duyet ngay lap tuc.</p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
