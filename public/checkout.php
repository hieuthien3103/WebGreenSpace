<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!is_logged_in()) {
    redirect('login.php?redirect=' . urlencode('checkout.php'));
}

$cartService = new CartService();
if ($cartService->isEmpty()) {
    set_flash('error', 'Giỏ hàng đang trống. Vui lòng thêm sản phẩm trước khi thanh toán.');
    redirect('cart.php');
}

function checkout_collect_address_payload(array $input): array {
    return [
        'receiver_name' => trim((string)($input['full_name'] ?? '')),
        'phone' => trim((string)($input['phone'] ?? '')),
        'province' => trim((string)($input['province'] ?? '')),
        'district' => trim((string)($input['district'] ?? '')),
        'ward' => trim((string)($input['ward'] ?? '')),
        'address_line' => trim((string)($input['address_line'] ?? '')),
    ];
}

function checkout_validate_quick_address(array $payload): array {
    $errors = [];

    if (($payload['receiver_name'] ?? '') === '') {
        $errors['full_name'] = 'Vui lòng nhập họ tên người nhận.';
    }

    if (($payload['phone'] ?? '') === '') {
        $errors['phone'] = 'Vui lòng nhập số điện thoại.';
    }

    if (($payload['province'] ?? '') === '') {
        $errors['province'] = 'Vui lòng nhập tỉnh/thành.';
    }

    if (($payload['district'] ?? '') === '') {
        $errors['district'] = 'Vui lòng nhập quận/huyện.';
    }

    if (($payload['address_line'] ?? '') === '') {
        $errors['address_line'] = 'Vui lòng nhập địa chỉ cụ thể.';
    }

    return $errors;
}

$userId = (int)get_user_id();
$summary = $cartService->getSummary();
$items = $summary['items'];
$userModel = new User();
$addressModel = new Address();
$currentUser = $userModel->findById($userId) ?? get_user();
$savedAddresses = $addressModel->getAllByUserId($userId);
$defaultAddress = $addressModel->getDefaultByUserId($userId);
$defaultAddressId = (int)($defaultAddress['id'] ?? 0);
$selectedAddressId = $defaultAddressId;
$selectedAddress = $defaultAddress;
$preferredSavedAddressId = max(0, (int)($_GET['saved_address'] ?? 0));

if ($preferredSavedAddressId > 0) {
    $preferredSavedAddress = $addressModel->getByIdForUser($userId, $preferredSavedAddressId);
    if ($preferredSavedAddress) {
        $selectedAddressId = (int)$preferredSavedAddress['id'];
        $selectedAddress = $preferredSavedAddress;
    }
}

$hasSavedAddress = !empty($savedAddresses);

$values = [
    'full_name' => (string)($selectedAddress['receiver_name'] ?? $currentUser['full_name'] ?? ''),
    'email' => (string)($currentUser['email'] ?? ''),
    'phone' => (string)($selectedAddress['phone'] ?? $currentUser['phone'] ?? ''),
    'province' => (string)($selectedAddress['province'] ?? ''),
    'district' => (string)($selectedAddress['district'] ?? ''),
    'ward' => (string)($selectedAddress['ward'] ?? ''),
    'address_line' => (string)($selectedAddress['address_line'] ?? ''),
    'note' => '',
    'payment_method' => 'cod',
    'save_as_default' => $hasSavedAddress ? '0' : '1',
];
$errors = [];
$quickDraft = $_SESSION['checkout_quick_draft'] ?? null;

if (is_array($quickDraft)) {
    unset($_SESSION['checkout_quick_draft']);
    $values['email'] = (string)($quickDraft['email'] ?? $values['email']);
    $values['note'] = (string)($quickDraft['note'] ?? $values['note']);
    $values['payment_method'] = (string)($quickDraft['payment_method'] ?? $values['payment_method']);
    $values['save_as_default'] = !empty($quickDraft['save_as_default']) ? '1' : '0';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $_) {
        $values[$key] = trim((string)($_POST[$key] ?? ''));
    }

    $values['save_as_default'] = !empty($_POST['save_as_default']) ? '1' : '0';

    $action = (string)($_POST['action'] ?? 'place_order');
    $selectedAddressId = max(0, (int)($_POST['selected_address_id'] ?? 0));
    $selectedAddress = $selectedAddressId > 0 ? $addressModel->getByIdForUser($userId, $selectedAddressId) : null;

    if ($selectedAddress) {
        $values['full_name'] = (string)$selectedAddress['receiver_name'];
        $values['phone'] = (string)$selectedAddress['phone'];
        $values['province'] = (string)$selectedAddress['province'];
        $values['district'] = (string)$selectedAddress['district'];
        $values['ward'] = (string)($selectedAddress['ward'] ?? '');
        $values['address_line'] = (string)$selectedAddress['address_line'];
    }

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors['general'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
    } else {
        if ($action === 'quick_save_address') {
            if ($selectedAddress) {
                $errors['address_book'] = 'Địa chỉ này đã có trong sổ địa chỉ. Hãy chọn "Nhập địa chỉ mới" nếu bạn muốn lưu thêm.';
            } else {
                $quickAddressPayload = checkout_collect_address_payload($_POST);
                $errors = array_merge($errors, checkout_validate_quick_address($quickAddressPayload));

                if (empty($errors)) {
                    try {
                        $_SESSION['checkout_quick_draft'] = [
                            'email' => $values['email'],
                            'note' => $values['note'],
                            'payment_method' => $values['payment_method'],
                            'save_as_default' => $values['save_as_default'],
                        ];

                        $makeDefault = $values['save_as_default'] === '1';
                        $newAddressId = $addressModel->createForUser($userId, $quickAddressPayload, $makeDefault);
                        set_flash('success', $makeDefault
                            ? 'Đã lưu nhanh địa chỉ vào hồ sơ và đặt làm mặc định.'
                            : 'Đã lưu nhanh địa chỉ vào hồ sơ. Bạn có thể dùng lại cho các lần mua sau.'
                        );
                        redirect('checkout.php?saved_address=' . urlencode((string)$newAddressId) . '#saved-addresses');
                    } catch (Throwable $e) {
                        unset($_SESSION['checkout_quick_draft']);
                        $errors['address_book'] = 'Không thể lưu nhanh địa chỉ lúc này. Vui lòng thử lại.';
                    }
                }
            }
        } else {
            $checkoutService = new CheckoutService();
            $result = $checkoutService->placeOrder($userId, $_POST);

            if (!empty($result['success'])) {
                set_flash('success', 'Đặt hàng thành công. Đơn của bạn đã được tạo.');
                redirect('profile.php?tab=orders');
            }

            $errors = $result['errors'] ?? ['general' => 'Không thể thanh toán lúc này.'];
        }
    }
}

$addressMap = [];
foreach ($savedAddresses as $address) {
    $addressMap[(int)$address['id']] = [
        'id' => (int)$address['id'],
        'receiver_name' => (string)$address['receiver_name'],
        'phone' => (string)$address['phone'],
        'province' => (string)$address['province'],
        'district' => (string)$address['district'],
        'ward' => (string)($address['ward'] ?? ''),
        'address_line' => (string)$address['address_line'],
    ];
}

$paymentOptions = [
    [
        'value' => 'cod',
        'title' => 'Thanh toán khi nhận hàng',
        'description' => 'Phù hợp với đơn cần xác nhận thủ công và giao tận nơi.',
        'icon' => 'local_shipping',
    ],
    [
        'value' => 'online_mock',
        'title' => 'Online mock',
        'description' => 'Mô phỏng thanh toán online để chạy demo đồ án.',
        'icon' => 'credit_card',
    ],
];

$pageTitle = 'Thanh toán - GreenSpace';
$currentPage = '';

include 'includes/header.php';
?>

<main class="flex-1">
    <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <section class="rounded-[2rem] border border-[#dcecdf] bg-[linear-gradient(135deg,#f7fbf8_0%,#eef7f1_55%,#f8fbf9_100%)] px-6 py-8 shadow-sm sm:px-8">
            <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-end">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-primary">Checkout</p>
                    <h1 class="mt-3 text-4xl font-extrabold tracking-tight text-text-main dark:text-white">Hoàn tất đơn hàng của bạn</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-text-secondary">
                        Điền thông tin nhận hàng, chọn phương thức thanh toán và kiểm tra lại đơn trước khi xác nhận.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-[1.5rem] bg-white/80 px-4 py-4 shadow-sm ring-1 ring-white/70">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#6b8a78]">Bước 1</p>
                        <p class="mt-2 font-bold text-[#102118]">Giỏ hàng</p>
                        <p class="mt-1 text-sm text-[#6e8d7b]"><?= clean((string)count($items)) ?> sản phẩm đã chọn</p>
                    </div>
                    <div class="rounded-[1.5rem] bg-[#102118] px-4 py-4 text-white shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Bước 2</p>
                        <p class="mt-2 font-bold">Thanh toán</p>
                        <p class="mt-1 text-sm text-white/75">Xác nhận thông tin giao nhận</p>
                    </div>
                    <div class="rounded-[1.5rem] bg-white/80 px-4 py-4 shadow-sm ring-1 ring-white/70">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#6b8a78]">Bước 3</p>
                        <p class="mt-2 font-bold text-[#102118]">Hoàn tất</p>
                        <p class="mt-1 text-sm text-[#6e8d7b]">Đơn sẽ xuất hiện ở hồ sơ</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="mt-8 grid items-start gap-8 lg:grid-cols-[minmax(0,1.08fr)_minmax(340px,0.92fr)]">
            <section class="space-y-6">
                <?php if (!empty($errors['general'])): ?>
                    <div class="rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-medium text-red-700">
                        <?= clean($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6" id="checkoutForm">
                    <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                    <input type="hidden" name="selected_address_id" id="selectedAddressId" value="<?= $selectedAddressId > 0 ? clean((string)$selectedAddressId) : '' ?>">

                    <?php if ($hasSavedAddress): ?>
                        <article id="saved-addresses" class="rounded-[2rem] border border-[#dcecdf] bg-white px-6 py-6 shadow-sm">
                            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-primary">Sổ địa chỉ</p>
                                    <h2 class="mt-2 text-2xl font-extrabold text-text-main dark:text-white">Chọn địa chỉ có sẵn từ hồ sơ</h2>
                                    <p class="mt-2 text-sm text-text-secondary">Bấm vào một địa chỉ để tự điền form, hoặc chuyển sang nhập địa chỉ mới.</p>
                                </div>
                                <a href="profile.php" class="inline-flex items-center rounded-full border border-[#d8eadf] px-4 py-2 text-sm font-semibold text-text-main transition-colors hover:border-primary hover:text-primary dark:border-[#32483b] dark:text-white">
                                    Quản lý trong hồ sơ
                                </a>
                            </div>

                            <div class="grid gap-4">
                                <?php foreach ($savedAddresses as $address): ?>
                                    <?php $addressId = (int)$address['id']; ?>
                                    <button
                                        type="button"
                                        class="address-card rounded-[1.5rem] border px-5 py-4 text-left transition-all <?= $selectedAddressId === $addressId ? 'border-primary bg-[#f3fbf6]' : 'border-[#d8eadf] hover:border-primary' ?>"
                                        data-address-id="<?= clean((string)$addressId) ?>"
                                    >
                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                            <div>
                                                <p class="font-bold text-text-main dark:text-white"><?= clean($address['receiver_name']) ?></p>
                                                <p class="mt-1 text-sm text-text-secondary"><?= clean($address['phone']) ?></p>
                                            </div>
                                            <?php if (!empty($address['is_default'])): ?>
                                                <span class="rounded-full bg-[#e9f5ee] px-3 py-1 text-xs font-semibold text-[#2e9b63]">Mặc định</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="mt-3 text-sm leading-6 text-text-secondary">
                                            <?= clean(implode(', ', array_filter([
                                                (string)$address['address_line'],
                                                (string)($address['ward'] ?? ''),
                                                (string)$address['district'],
                                                (string)$address['province'],
                                            ]))) ?>
                                        </p>
                                    </button>
                                <?php endforeach; ?>

                                <button
                                    type="button"
                                    id="customAddressButton"
                                    class="rounded-[1.5rem] border border-dashed px-5 py-4 text-left transition-all <?= $selectedAddressId === 0 ? 'border-primary bg-[#f9fcfa]' : 'border-[#d8eadf] hover:border-primary' ?>"
                                >
                                    <p class="font-bold text-text-main dark:text-white">Nhập địa chỉ mới</p>
                                    <p class="mt-2 text-sm text-text-secondary">Dùng khi bạn muốn giao đến nơi khác ngoài các địa chỉ đã lưu.</p>
                                </button>
                            </div>
                        </article>
                    <?php endif; ?>

                    <article class="rounded-[2rem] border border-[#dcecdf] bg-white px-6 py-6 shadow-sm">
                        <div class="mb-6">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-primary">Người nhận</p>
                            <h2 class="mt-2 text-2xl font-extrabold text-text-main dark:text-white">Thông tin giao hàng</h2>
                            <p class="mt-2 text-sm text-text-secondary">Thông tin này sẽ được dùng để tạo đơn, và bạn cũng có thể lưu nhanh vào sổ địa chỉ ngay tại đây.</p>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2 md:col-span-2">
                                <label for="full_name" class="text-sm font-semibold text-text-main dark:text-white">Họ tên người nhận</label>
                                <input id="full_name" name="full_name" type="text" value="<?= clean($values['full_name']) ?>" class="address-field w-full rounded-2xl border <?= !empty($errors['full_name']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#32483b]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white">
                                <?php if (!empty($errors['full_name'])): ?><p class="text-sm text-red-600"><?= clean($errors['full_name']) ?></p><?php endif; ?>
                            </div>

                            <div class="space-y-2">
                                <label for="email" class="text-sm font-semibold text-text-main dark:text-white">Email</label>
                                <input id="email" name="email" type="email" value="<?= clean($values['email']) ?>" class="w-full rounded-2xl border <?= !empty($errors['email']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#32483b]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white">
                                <?php if (!empty($errors['email'])): ?><p class="text-sm text-red-600"><?= clean($errors['email']) ?></p><?php endif; ?>
                            </div>

                            <div class="space-y-2">
                                <label for="phone" class="text-sm font-semibold text-text-main dark:text-white">Số điện thoại</label>
                                <input id="phone" name="phone" type="text" value="<?= clean($values['phone']) ?>" class="address-field w-full rounded-2xl border <?= !empty($errors['phone']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#32483b]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white">
                                <?php if (!empty($errors['phone'])): ?><p class="text-sm text-red-600"><?= clean($errors['phone']) ?></p><?php endif; ?>
                            </div>
                        </div>
                    </article>

                    <article class="rounded-[2rem] border border-[#dcecdf] bg-white px-6 py-6 shadow-sm">
                        <div class="mb-6">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-primary">Địa chỉ</p>
                            <h2 class="mt-2 text-2xl font-extrabold text-text-main dark:text-white">Nơi nhận hàng</h2>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="province" class="text-sm font-semibold text-text-main dark:text-white">Tỉnh / Thành</label>
                                <input id="province" name="province" type="text" value="<?= clean($values['province']) ?>" class="address-field w-full rounded-2xl border <?= !empty($errors['province']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#32483b]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white">
                                <?php if (!empty($errors['province'])): ?><p class="text-sm text-red-600"><?= clean($errors['province']) ?></p><?php endif; ?>
                            </div>

                            <div class="space-y-2">
                                <label for="district" class="text-sm font-semibold text-text-main dark:text-white">Quận / Huyện</label>
                                <input id="district" name="district" type="text" value="<?= clean($values['district']) ?>" class="address-field w-full rounded-2xl border <?= !empty($errors['district']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#32483b]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white">
                                <?php if (!empty($errors['district'])): ?><p class="text-sm text-red-600"><?= clean($errors['district']) ?></p><?php endif; ?>
                            </div>

                            <div class="space-y-2">
                                <label for="ward" class="text-sm font-semibold text-text-main dark:text-white">Phường / Xã</label>
                                <input id="ward" name="ward" type="text" value="<?= clean($values['ward']) ?>" class="address-field w-full rounded-2xl border border-[#d8eadf] bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:border-[#32483b] dark:bg-[#101914] dark:text-white">
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <label for="address_line" class="text-sm font-semibold text-text-main dark:text-white">Địa chỉ cụ thể</label>
                                <textarea id="address_line" name="address_line" rows="3" class="address-field w-full rounded-2xl border <?= !empty($errors['address_line']) ? 'border-red-300' : 'border-[#d8eadf] dark:border-[#32483b]' ?> bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:bg-[#101914] dark:text-white"><?= clean($values['address_line']) ?></textarea>
                                <?php if (!empty($errors['address_line'])): ?><p class="text-sm text-red-600"><?= clean($errors['address_line']) ?></p><?php endif; ?>
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <label for="note" class="text-sm font-semibold text-text-main dark:text-white">Ghi chú giao hàng</label>
                                <textarea id="note" name="note" rows="3" class="w-full rounded-2xl border border-[#d8eadf] bg-white px-4 py-3 text-text-main focus:border-primary focus:ring-primary/20 dark:border-[#32483b] dark:bg-[#101914] dark:text-white" placeholder="Ví dụ: giao giờ hành chính, gọi trước khi giao..."><?= clean($values['note']) ?></textarea>
                            </div>

                            <div class="md:col-span-2">
                                <label class="flex items-start gap-3 rounded-[1.5rem] border border-[#d8eadf] bg-[#fbfdfb] px-4 py-4">
                                    <input
                                        type="checkbox"
                                        name="save_as_default"
                                        value="1"
                                        <?= $values['save_as_default'] === '1' ? 'checked' : '' ?>
                                        id="saveAsDefaultCheckbox"
                                        class="mt-1 rounded border-[#d8eadf] text-primary focus:ring-primary"
                                    >
                                    <span>
                                        <span class="block font-semibold text-text-main dark:text-white">Đặt địa chỉ này làm mặc định</span>
                                        <span id="saveAsDefaultHint" class="mt-1 block text-sm leading-6 text-text-secondary">
                                            Khi tick, địa chỉ bạn đang nhập hoặc đang chọn sẽ trở thành địa chỉ mặc định cho các lần mua sau.
                                        </span>
                                    </span>
                                </label>
                            </div>

                            <div class="md:col-span-2">
                                <div class="rounded-[1.5rem] border border-dashed border-[#d8eadf] bg-[#f8fbf9] px-5 py-4">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="font-bold text-text-main dark:text-white">Lưu nhanh vào sổ địa chỉ</p>
                                            <p id="quickSaveAddressHint" class="mt-1 text-sm text-text-secondary">
                                                Đang nhập địa chỉ mới? Bấm nút này để lưu lại cho những lần thanh toán sau.
                                            </p>
                                        </div>
                                        <button
                                            type="submit"
                                            name="action"
                                            value="quick_save_address"
                                            id="quickSaveAddressButton"
                                            class="inline-flex items-center justify-center rounded-full border border-[#d8eadf] bg-white px-5 py-3 text-sm font-semibold text-text-main transition-colors hover:border-primary hover:text-primary disabled:cursor-not-allowed disabled:opacity-60"
                                        >
                                            Thêm nhanh vào hồ sơ
                                        </button>
                                    </div>
                                    <?php if (!empty($errors['address_book'])): ?>
                                        <p class="mt-3 text-sm text-red-600"><?= clean($errors['address_book']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="rounded-[2rem] border border-[#dcecdf] bg-white px-6 py-6 shadow-sm">
                        <div class="mb-6">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-primary">Thanh toán</p>
                            <h2 class="mt-2 text-2xl font-extrabold text-text-main dark:text-white">Chọn phương thức thanh toán</h2>
                        </div>

                        <div class="grid gap-4">
                            <?php foreach ($paymentOptions as $option): ?>
                                <label class="block">
                                    <input type="radio" name="payment_method" value="<?= clean($option['value']) ?>" class="peer sr-only" <?= $values['payment_method'] === $option['value'] ? 'checked' : '' ?>>
                                    <div class="rounded-[1.5rem] border border-[#d8eadf] px-5 py-4 transition-all peer-checked:border-primary peer-checked:bg-[#f3fbf6] hover:border-primary dark:border-[#32483b] dark:peer-checked:bg-[#112018]">
                                        <div class="flex items-start gap-4">
                                            <span class="flex size-11 items-center justify-center rounded-2xl bg-[#eef6f1] text-primary">
                                                <span class="material-symbols-outlined"><?= clean($option['icon']) ?></span>
                                            </span>
                                            <div class="flex-1">
                                                <div class="flex flex-wrap items-center justify-between gap-2">
                                                    <span class="font-bold text-text-main dark:text-white"><?= clean($option['title']) ?></span>
                                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-[#456a57] shadow-sm">Khả dụng</span>
                                                </div>
                                                <p class="mt-2 text-sm leading-6 text-text-secondary"><?= clean($option['description']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                            <?php if (!empty($errors['payment_method'])): ?><p class="text-sm text-red-600"><?= clean($errors['payment_method']) ?></p><?php endif; ?>
                        </div>

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                            <button type="submit" name="action" value="place_order" class="inline-flex w-full items-center justify-center rounded-full bg-primary px-5 py-3 text-sm font-bold text-white shadow-lg shadow-primary/20 transition-colors hover:bg-primary-dark">
                                Xác nhận đặt hàng
                            </button>
                            <a href="cart.php" class="inline-flex w-full items-center justify-center rounded-full border border-[#d8eadf] px-5 py-3 text-sm font-semibold text-text-main transition-colors hover:border-primary hover:text-primary dark:border-[#32483b] dark:text-white">
                                Quay lại giỏ hàng
                            </a>
                        </div>
                    </article>
                </form>
            </section>

            <aside class="lg:sticky lg:top-24">
                <article class="overflow-hidden rounded-[2rem] border border-[#dcecdf] bg-white shadow-sm">
                    <div class="bg-[#102118] px-6 py-6 text-white">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-white/65">Tóm tắt đơn hàng</p>
                        <div class="mt-3 flex items-end justify-between gap-4">
                            <div>
                                <h2 class="text-2xl font-extrabold">Đơn hàng của bạn</h2>
                                <p class="mt-2 text-sm text-white/75"><?= clean((string)count($items)) ?> sản phẩm đang chờ xác nhận</p>
                            </div>
                            <span class="rounded-full bg-white/10 px-4 py-2 text-sm font-semibold"><?= format_currency($summary['total']) ?></span>
                        </div>
                    </div>

                    <div class="space-y-4 px-6 py-6">
                        <?php foreach ($items as $item): ?>
                            <div class="flex items-center gap-4 rounded-[1.5rem] border border-[#edf5ef] p-3">
                                <img src="<?= clean($item['image_url']) ?>" alt="<?= clean($item['name']) ?>" class="size-16 rounded-2xl object-cover">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold text-text-main dark:text-white"><?= clean($item['name']) ?></p>
                                    <p class="mt-1 text-sm text-text-secondary"><?= (int)$item['quantity'] ?> x <?= format_currency($item['price']) ?></p>
                                </div>
                                <p class="text-sm font-bold text-primary"><?= format_currency($item['subtotal']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="border-t border-dashed border-[#d8eadf] px-6 py-6 text-sm">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between text-text-secondary">
                                <span>Tạm tính</span>
                                <span class="font-semibold text-text-main dark:text-white"><?= format_currency($summary['subtotal']) ?></span>
                            </div>
                            <div class="flex items-center justify-between text-text-secondary">
                                <span>Phí vận chuyển</span>
                                <span class="font-semibold text-text-main dark:text-white"><?= format_currency($summary['shipping_fee']) ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-base font-bold text-text-main dark:text-white">Tổng cộng</span>
                                <span class="text-2xl font-extrabold text-primary"><?= format_currency($summary['total']) ?></span>
                            </div>
                        </div>

                        <div class="mt-6 rounded-[1.5rem] bg-[#f6fbf7] px-4 py-4 text-sm text-[#456a57]">
                            <p class="font-semibold text-[#102118]">Lưu ý thanh toán</p>
                            <p class="mt-2 leading-6">
                                Đơn dưới 500.000đ sẽ cộng thêm 30.000đ phí vận chuyển. Nếu chọn <strong>Online mock</strong>,
                                hệ thống sẽ tự ghi nhận thanh toán để phục vụ demo.
                            </p>
                        </div>
                    </div>
                </article>
            </aside>
        </div>
    </div>
</main>

<script>
const addressMap = <?= json_encode($addressMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const selectedAddressInput = document.getElementById('selectedAddressId');
const addressCards = Array.from(document.querySelectorAll('.address-card'));
const customAddressButton = document.getElementById('customAddressButton');
const addressFields = Array.from(document.querySelectorAll('.address-field'));
const quickSaveAddressButton = document.getElementById('quickSaveAddressButton');
const quickSaveAddressHint = document.getElementById('quickSaveAddressHint');
const saveAsDefaultHint = document.getElementById('saveAsDefaultHint');

function updateQuickSaveState() {
    const isCustomAddress = !selectedAddressInput || selectedAddressInput.value === '';

    if (quickSaveAddressButton) {
        quickSaveAddressButton.disabled = !isCustomAddress;
    }

    if (quickSaveAddressHint) {
        quickSaveAddressHint.textContent = isCustomAddress
            ? 'Đang nhập địa chỉ mới? Bấm nút này để lưu lại cho những lần thanh toán sau.'
            : 'Bạn đang dùng địa chỉ đã lưu. Chọn "Nhập địa chỉ mới" nếu muốn thêm một địa chỉ khác.';
    }

    if (saveAsDefaultHint) {
        saveAsDefaultHint.textContent = isCustomAddress
            ? 'Khi tick, địa chỉ mới này sẽ trở thành địa chỉ mặc định cho các lần mua sau.'
            : 'Khi tick, địa chỉ đang chọn trong sổ địa chỉ sẽ được đặt làm mặc định.';
    }
}

function updateAddressCardStyles() {
    const selectedId = selectedAddressInput ? selectedAddressInput.value : '';

    addressCards.forEach((card) => {
        const isActive = card.dataset.addressId === selectedId;
        card.classList.toggle('border-primary', isActive);
        card.classList.toggle('bg-[#f3fbf6]', isActive);
        card.classList.toggle('border-[#d8eadf]', !isActive);
    });

    if (customAddressButton) {
        const isCustom = selectedId === '';
        customAddressButton.classList.toggle('border-primary', isCustom);
        customAddressButton.classList.toggle('bg-[#f9fcfa]', isCustom);
        customAddressButton.classList.toggle('border-[#d8eadf]', !isCustom);
    }

    updateQuickSaveState();
}

function fillAddressFields(address) {
    if (!address) {
        return;
    }

    const mapping = {
        full_name: address.receiver_name || '',
        phone: address.phone || '',
        province: address.province || '',
        district: address.district || '',
        ward: address.ward || '',
        address_line: address.address_line || '',
    };

    Object.entries(mapping).forEach(([field, value]) => {
        const input = document.getElementById(field);
        if (input) {
            input.value = value;
        }
    });
}

function clearAddressFields() {
    ['full_name', 'phone', 'province', 'district', 'ward', 'address_line'].forEach((field) => {
        const input = document.getElementById(field);
        if (input) {
            input.value = '';
        }
    });
}

addressCards.forEach((card) => {
    card.addEventListener('click', () => {
        const addressId = card.dataset.addressId || '';
        if (!selectedAddressInput) {
            return;
        }

        selectedAddressInput.value = addressId;
        fillAddressFields(addressMap[addressId] || null);
        updateAddressCardStyles();
    });
});

customAddressButton?.addEventListener('click', () => {
    if (!selectedAddressInput) {
        return;
    }

    selectedAddressInput.value = '';
    clearAddressFields();
    updateAddressCardStyles();
});

addressFields.forEach((field) => {
    field.addEventListener('input', () => {
        if (!selectedAddressInput || selectedAddressInput.value === '') {
            return;
        }

        selectedAddressInput.value = '';
        updateAddressCardStyles();
    });
});

updateAddressCardStyles();
</script>

<?php include 'includes/footer.php'; ?>
