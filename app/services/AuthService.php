<?php
/**
 * Authentication service
 */

class AuthService {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Attempt a login.
     */
    public function login(string $identifier, string $password): array {
        $identifier = trim($identifier);
        $errors = [];

        if ($identifier === '') {
            $errors['identifier'] = 'Vui long nhap email hoac ten dang nhap.';
        }

        if ($password === '') {
            $errors['password'] = 'Vui long nhap mat khau.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $user = $this->userModel->findByLogin($identifier);
        if (!$user) {
            return ['success' => false, 'errors' => ['general' => 'Thong tin dang nhap khong dung.']];
        }

        if (($user['status'] ?? 'inactive') !== 'active') {
            return ['success' => false, 'errors' => ['general' => 'Tai khoan hien khong kha dung.']];
        }

        if (!password_verify($password, $user['password'] ?? '')) {
            return ['success' => false, 'errors' => ['general' => 'Thong tin dang nhap khong dung.']];
        }

        if (password_needs_rehash($user['password'], HASH_ALGO, ['cost' => HASH_COST])) {
            $this->rehashPassword((int)$user['id'], $password);
            $user = $this->userModel->findById((int)$user['id']) ?? $user;
        }

        $this->storeSession($user);

        return [
            'success' => true,
            'user' => $this->userModel->withoutPassword($user),
        ];
    }

    /**
     * Register a new account.
     */
    public function register(array $data): array {
        $input = [
            'full_name' => trim((string)($data['full_name'] ?? '')),
            'username' => trim((string)($data['username'] ?? '')),
            'email' => strtolower(trim((string)($data['email'] ?? ''))),
            'phone' => trim((string)($data['phone'] ?? '')),
            'password' => (string)($data['password'] ?? ''),
            'confirm_password' => (string)($data['confirm_password'] ?? ''),
        ];

        $errors = $this->validateRegistration($input);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $userId = $this->userModel->create([
            'username' => $input['username'],
            'email' => $input['email'],
            'password' => password_hash($input['password'], HASH_ALGO, ['cost' => HASH_COST]),
            'full_name' => $input['full_name'],
            'phone' => $input['phone'],
            'role' => 'user',
            'status' => 'active',
        ]);

        $user = $this->userModel->findById($userId);
        if (!$user) {
            return ['success' => false, 'errors' => ['general' => 'Khong the tao tai khoan luc nay.']];
        }

        $this->storeSession($user);

        return [
            'success' => true,
            'user' => $this->userModel->withoutPassword($user),
        ];
    }

    /**
     * Log the current user out.
     */
    public function logout(): void {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_regenerate_id(true);
    }

    /**
     * Validate registration input.
     */
    private function validateRegistration(array $input): array {
        $errors = [];

        if ($input['full_name'] === '') {
            $errors['full_name'] = 'Vui long nhap ho ten.';
        } elseif (mb_strlen($input['full_name']) < 2) {
            $errors['full_name'] = 'Ho ten can it nhat 2 ky tu.';
        }

        if ($input['username'] === '') {
            $errors['username'] = 'Vui long nhap ten dang nhap.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{4,30}$/', $input['username'])) {
            $errors['username'] = 'Ten dang nhap gom 4-30 ky tu, chi dung chu, so hoac dau gach duoi.';
        } elseif ($this->userModel->findByUsername($input['username'])) {
            $errors['username'] = 'Ten dang nhap da ton tai.';
        }

        if ($input['email'] === '') {
            $errors['email'] = 'Vui long nhap email.';
        } elseif (!is_valid_email($input['email'])) {
            $errors['email'] = 'Email khong hop le.';
        } elseif ($this->userModel->findByEmail($input['email'])) {
            $errors['email'] = 'Email da duoc su dung.';
        }

        if ($input['phone'] !== '' && !preg_match('/^[0-9+\s.-]{8,20}$/', $input['phone'])) {
            $errors['phone'] = 'So dien thoai khong hop le.';
        }

        if ($input['password'] === '') {
            $errors['password'] = 'Vui long nhap mat khau.';
        } elseif (strlen($input['password']) < 6) {
            $errors['password'] = 'Mat khau can it nhat 6 ky tu.';
        }

        if ($input['confirm_password'] === '') {
            $errors['confirm_password'] = 'Vui long nhap lai mat khau.';
        } elseif ($input['password'] !== $input['confirm_password']) {
            $errors['confirm_password'] = 'Mat khau nhap lai khong khop.';
        }

        return $errors;
    }

    /**
     * Save authenticated user into session.
     */
    private function storeSession(array $user): void {
        session_regenerate_id(true);

        $safeUser = $this->userModel->withoutPassword($user);

        $_SESSION['user_id'] = (int)$safeUser['id'];
        $_SESSION['user_role'] = $safeUser['role'] ?? 'user';
        $_SESSION['user_data'] = $safeUser;
    }

    /**
     * Refresh password hash when needed.
     */
    private function rehashPassword(int $userId, string $plainPassword): void {
        $db = new Database();
        $conn = $db->getConnection();
        $query = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':password', password_hash($plainPassword, HASH_ALGO, ['cost' => HASH_COST]), PDO::PARAM_STR);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }
}
