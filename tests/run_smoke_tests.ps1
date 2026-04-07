param(
    [string]$BaseUrl = 'http://localhost:8000'
)

$ErrorActionPreference = 'Stop'

function Assert-Step {
    param(
        [bool]$Condition,
        [string]$Message
    )

    if (-not $Condition) {
        throw "FAILED: $Message"
    }

    Write-Host "[PASS] $Message"
}

function Get-Page {
    param(
        [Microsoft.PowerShell.Commands.WebRequestSession]$Session,
        [string]$Path
    )

    return Invoke-WebRequest -UseBasicParsing -WebSession $Session -Uri ($BaseUrl.TrimEnd('/') + $Path)
}

function Post-Form {
    param(
        [Microsoft.PowerShell.Commands.WebRequestSession]$Session,
        [string]$Path,
        [hashtable]$Body
    )

    return Invoke-WebRequest -UseBasicParsing -WebSession $Session -Uri ($BaseUrl.TrimEnd('/') + $Path) -Method Post -Body $Body
}

function Get-MatchValue {
    param(
        [string]$Text,
        [string]$Pattern,
        [int]$Group = 1
    )

    $match = [regex]::Match($Text, $Pattern, [System.Text.RegularExpressions.RegexOptions]::Singleline)
    if (-not $match.Success) {
        return $null
    }

    return $match.Groups[$Group].Value
}

function Get-CsrfToken {
    param([string]$Html)

    $token = Get-MatchValue -Text $Html -Pattern 'name="csrf_token"\s+value="([^"]+)"'
    if ([string]::IsNullOrWhiteSpace($token)) {
        throw 'FAILED: Could not extract CSRF token from HTML response.'
    }

    return $token
}

$timestamp = Get-Date -Format 'yyyyMMddHHmmss'
$username = "smoketest$timestamp"
$email = "$username@example.com"
$password = 'Smoke123!'
$fullName = "Smoke Test $timestamp"
$phone = '0901234567'
$province = 'Ho Chi Minh'
$district = 'Quan 1'
$ward = 'Ben Nghe'
$addressLine = '1 Le Loi'

Write-Host "Running smoke tests against $BaseUrl"
Write-Host "Generated account: $email"

$signupSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$loginSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$adminSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession

# 1. Sign up
$signupPage = Get-Page -Session $signupSession -Path '/signup.php'
$signupCsrf = Get-CsrfToken -Html $signupPage.Content
$signupResponse = Post-Form -Session $signupSession -Path '/signup.php' -Body @{
    csrf_token = $signupCsrf
    redirect = 'home.php'
    full_name = $fullName
    username = $username
    email = $email
    phone = $phone
    password = $password
    confirm_password = $password
}
Assert-Step ($signupResponse.BaseResponse.ResponseUri.AbsoluteUri -match '/home(?:\.php)?$') 'Sign up succeeds for a fresh account'

# 2. Login with a fresh session
$loginPage = Get-Page -Session $loginSession -Path '/login.php'
$loginCsrf = Get-CsrfToken -Html $loginPage.Content
$loginResponse = Post-Form -Session $loginSession -Path '/login.php' -Body @{
    csrf_token = $loginCsrf
    redirect = 'home.php'
    identifier = $email
    password = $password
}
Assert-Step ($loginResponse.BaseResponse.ResponseUri.AbsoluteUri -match '/home(?:\.php)?$') 'Login succeeds with the new account'

# 3. Non-admin must not access admin orders
$blockedAdminResponse = Get-Page -Session $loginSession -Path '/admin/orders'
Assert-Step ($blockedAdminResponse.BaseResponse.ResponseUri.AbsoluteUri -match '/home(?:\.php)?$') 'Regular user is blocked from admin orders'

# 4. Add product to cart
$productsPage = Get-Page -Session $loginSession -Path '/products.php'
$cartCsrf = Get-CsrfToken -Html $productsPage.Content
$productId = Get-MatchValue -Text $productsPage.Content -Pattern 'name="product_id"\s+value="(\d+)"'
Assert-Step (-not [string]::IsNullOrWhiteSpace($productId)) 'Able to extract a product_id for cart testing'

$addCartResponse = Post-Form -Session $loginSession -Path '/cart.php' -Body @{
    csrf_token = $cartCsrf
    action = 'add'
    product_id = $productId
    quantity = '1'
    redirect_to = 'cart.php'
}
Assert-Step ($addCartResponse.BaseResponse.ResponseUri.AbsoluteUri -match '/cart(?:\.php)?$') 'Add to cart succeeds'

$cartPage = Get-Page -Session $loginSession -Path '/cart.php'
Assert-Step ([regex]::IsMatch($cartPage.Content, "name=""quantities\[$productId\]""")) 'Cart contains the added product'

# 5. Update cart quantity
$cartUpdateCsrf = Get-CsrfToken -Html $cartPage.Content
$quantityField = "quantities[$productId]"
$updateCartResponse = Post-Form -Session $loginSession -Path '/cart.php' -Body @{
    csrf_token = $cartUpdateCsrf
    action = 'update'
    $quantityField = '2'
}
Assert-Step ($updateCartResponse.BaseResponse.ResponseUri.AbsoluteUri -match '/cart(?:\.php)?$') 'Cart quantity update succeeds'

$updatedCartPage = Get-Page -Session $loginSession -Path '/cart.php'
$quantityPattern = 'name="quantities\[' + [regex]::Escape($productId) + '\]".*?value="2"'
Assert-Step ([regex]::IsMatch($updatedCartPage.Content, $quantityPattern, [System.Text.RegularExpressions.RegexOptions]::Singleline)) 'Cart quantity changes to 2'

# 6. Checkout with online mock payment
$checkoutPage = Get-Page -Session $loginSession -Path '/checkout.php'
$checkoutCsrf = Get-CsrfToken -Html $checkoutPage.Content
$checkoutResponse = Post-Form -Session $loginSession -Path '/checkout.php' -Body @{
    csrf_token = $checkoutCsrf
    selected_address_id = ''
    full_name = $fullName
    email = $email
    phone = $phone
    province = $province
    district = $district
    ward = $ward
    address_line = $addressLine
    note = 'Smoke test checkout'
    payment_method = 'online_mock'
    action = 'place_order'
}

$orderDetailUrl = $checkoutResponse.BaseResponse.ResponseUri.AbsoluteUri
$orderId = Get-MatchValue -Text $orderDetailUrl -Pattern '[\?&]id=(\d+)'
Assert-Step (-not [string]::IsNullOrWhiteSpace($orderId)) 'Checkout creates a new order and redirects to order detail'
$orderNumber = Get-MatchValue -Text $checkoutResponse.Content -Pattern '(ORD\d{10,})'
Assert-Step (-not [string]::IsNullOrWhiteSpace($orderNumber)) 'Order detail page shows the new order number'

# 7. Confirm mock payment by user
$orderDetailPage = Get-Page -Session $loginSession -Path "/order-detail.php?id=$orderId"
$confirmCsrf = Get-CsrfToken -Html $orderDetailPage.Content
$confirmPaymentResponse = Post-Form -Session $loginSession -Path "/order-detail.php?id=$orderId" -Body @{
    csrf_token = $confirmCsrf
    action = 'confirm_online_mock_payment'
    qr_scanned = '1'
}
Assert-Step ($confirmPaymentResponse.BaseResponse.ResponseUri.AbsoluteUri -match "order-detail\.php\?id=$orderId") 'User can submit mock payment confirmation'

$afterConfirmPage = Get-Page -Session $loginSession -Path "/order-detail.php?id=$orderId"
Assert-Step (-not ($afterConfirmPage.Content -match 'confirm_online_mock_payment')) 'Payment confirmation form disappears after submit'

# 8. Admin login
$adminLoginPage = Get-Page -Session $adminSession -Path '/admin/login.php'
$adminCsrf = Get-CsrfToken -Html $adminLoginPage.Content
$adminLoginResponse = Post-Form -Session $adminSession -Path '/admin/login.php' -Body @{
    csrf_token = $adminCsrf
    redirect = 'dashboard.php'
    identifier = 'admin@webgreenspace.com'
    password = 'password'
}
Assert-Step ($adminLoginResponse.BaseResponse.ResponseUri.AbsoluteUri -match '/admin/(dashboard|dashboard\.php)$') 'Admin login succeeds'

# 9. Admin approves payment
$adminOrderPage = Get-Page -Session $adminSession -Path ("/admin/orders.php?q=$orderNumber&view=$orderId")
$adminOrderHasNumber = $adminOrderPage.Content -match [regex]::Escape($orderNumber)
if (-not $adminOrderHasNumber) {
    Write-Host "DEBUG admin orders uri: $($adminOrderPage.BaseResponse.ResponseUri.AbsoluteUri)"
    Write-Host "DEBUG admin orders content preview:"
    Write-Host ($adminOrderPage.Content.Substring(0, [Math]::Min(1200, $adminOrderPage.Content.Length)))
}
Assert-Step $adminOrderHasNumber 'Admin can open the target order detail'
$adminOrderCsrf = Get-CsrfToken -Html $adminOrderPage.Content
$approveResponse = Post-Form -Session $adminSession -Path '/admin/orders.php' -Body @{
    csrf_token = $adminOrderCsrf
    action = 'approve_online_mock_payment'
    order_id = $orderId
    q = $orderNumber
    order_status = 'all'
    payment_status = 'all'
    page = '1'
    view = $orderId
}
Assert-Step ($approveResponse.BaseResponse.ResponseUri.AbsoluteUri -match '/admin/orders\.php') 'Admin mock payment approval succeeds'

$afterApprovePage = Get-Page -Session $adminSession -Path ("/admin/orders.php?q=$orderNumber&view=$orderId")
Assert-Step (-not ($afterApprovePage.Content -match 'approve_online_mock_payment')) 'Approval action is no longer shown after approval'

# 10. Admin updates order status
$adminUpdateCsrf = Get-CsrfToken -Html $afterApprovePage.Content
$statusResponse = Post-Form -Session $adminSession -Path '/admin/orders.php' -Body @{
    csrf_token = $adminUpdateCsrf
    action = 'update_order_status'
    order_id = $orderId
    next_status = 'delivered'
    q = $orderNumber
    order_status = 'all'
    payment_status = 'all'
    page = '1'
    view = $orderId
}
Assert-Step ($statusResponse.BaseResponse.ResponseUri.AbsoluteUri -match '/admin/orders\.php') 'Admin can update order status to delivered'

$afterStatusPage = Get-Page -Session $adminSession -Path ("/admin/orders.php?q=$orderNumber&view=$orderId")
Assert-Step ($afterStatusPage.Content -match 'option value="delivered" selected') 'Admin detail reflects delivered status'

# 11. User can still access order detail after admin updates
$finalUserOrderPage = Get-Page -Session $loginSession -Path "/order-detail.php?id=$orderId"
Assert-Step ($finalUserOrderPage.Content -match [regex]::Escape($orderNumber)) 'User can still open the order detail after admin actions'

Write-Host ''
Write-Host 'Smoke tests completed successfully.'
Write-Host "Created test user: $email"
Write-Host "Created order: $orderNumber (ID: $orderId)"
