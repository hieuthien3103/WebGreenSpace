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
        return $this->attemptLogin($identifier, $password, false);
    }

    /**
     * Attempt an admin-only login.
     */
    public function loginAdmin(string $identifier, string $password): array {
        return $this->attemptLogin($identifier, $password, true);
    }

    /**
     * Dummy bcrypt hash used to prevent timing leaks when user is not found.
     */
    private const DUMMY_HASH = '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ012345';

    private function attemptLogin(string $identifier, string $password, bool $adminOnly): array {
        $identifier = trim($identifier);
        $errors = [];

        if ($identifier === '') {
            $errors['identifier'] = 'Vui lòng nhập email hoặc tên đăng nhập.';
        }

        if ($password === '') {
            $errors['password'] = 'Vui lòng nhập mật khẩu.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $user = $this->userModel->findByLogin($identifier);

        if (!$user) {
            password_verify($password, self::DUMMY_HASH);
            return ['success' => false, 'errors' => ['general' => 'Thông tin đăng nhập không đúng.']];
        }

        if (($user['status'] ?? 'inactive') !== 'active') {
            password_verify($password, self::DUMMY_HASH);
            return ['success' => false, 'errors' => ['general' => 'Tài khoản hiện không khả dụng.']];
        }

        if (!password_verify($password, $user['password'] ?? '')) {
            return ['success' => false, 'errors' => ['general' => 'Thông tin đăng nhập không đúng.']];
        }

        if ($adminOnly && (($user['role'] ?? 'user') !== 'admin')) {
            return ['success' => false, 'errors' => ['general' => 'Tài khoản này không có quyền truy cập khu vực admin.']];
        }

        if (password_needs_rehash($user['password'], HASH_ALGO, ['cost' => HASH_COST])) {
            $this->rehashPassword((int)$user['id'], $password);
            $user = $this->userModel->findById((int)$user['id']) ?? $user;
        }

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

        try {
            $userId = $this->userModel->create([
                'username' => $input['username'],
                'email' => $input['email'],
                'password' => password_hash($input['password'], HASH_ALGO, ['cost' => HASH_COST]),
                'full_name' => $input['full_name'],
                'phone' => $input['phone'],
                'role' => 'user',
                'status' => 'active',
            ]);
        } catch (\PDOException $e) {
            if ((int)$e->getCode() === 23000) {
                $msg = $e->getMessage();
                if (stripos($msg, 'username') !== false) {
                    return ['success' => false, 'errors' => ['username' => 'Tên đăng nhập đã tồn tại.']];
                }
                if (stripos($msg, 'email') !== false) {
                    return ['success' => false, 'errors' => ['email' => 'Email đã được sử dụng.']];
                }
                return ['success' => false, 'errors' => ['general' => 'Tên đăng nhập hoặc email đã tồn tại.']];
            }
            throw $e;
        }

        $user = $this->userModel->findById($userId);
        if (!$user) {
            return ['success' => false, 'errors' => ['general' => 'Không thể tạo tài khoản lúc này.']];
        }

        return [
            'success' => true,
            'user' => $this->userModel->withoutPassword($user),
        ];
    }

    /**
     * Validate registration input.
     */
    private function validateRegistration(array $input): array {
        $errors = [];

        if ($input['full_name'] === '') {
            $errors['full_name'] = 'Vui lòng nhập họ tên.';
        } elseif (string_length($input['full_name']) < 2) {
            $errors['full_name'] = 'Họ tên cần ít nhất 2 ký tự.';
        }

        if ($input['username'] === '') {
            $errors['username'] = 'Vui lòng nhập tên đăng nhập.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{4,30}$/', $input['username'])) {
            $errors['username'] = 'Tên đăng nhập gồm 4-30 ký tự, chỉ dùng chữ, số hoặc dấu gạch dưới.';
        } elseif ($this->userModel->findByUsername($input['username'])) {
            $errors['username'] = 'Tên đăng nhập đã tồn tại.';
        }

        if ($input['email'] === '') {
            $errors['email'] = 'Vui lòng nhập email.';
        } elseif (!is_valid_email($input['email'])) {
            $errors['email'] = 'Email không hợp lệ.';
        } elseif ($this->userModel->findByEmail($input['email'])) {
            $errors['email'] = 'Email đã được sử dụng.';
        }

        if ($input['phone'] !== '' && !preg_match('/^[0-9+\s.-]{8,20}$/', $input['phone'])) {
            $errors['phone'] = 'Số điện thoại không hợp lệ.';
        }

        if ($input['password'] === '') {
            $errors['password'] = 'Vui lòng nhập mật khẩu.';
        } elseif (strlen($input['password']) < 6) {
            $errors['password'] = 'Mật khẩu cần ít nhất 6 ký tự.';
        }

        if ($input['confirm_password'] === '') {
            $errors['confirm_password'] = 'Vui lòng nhập lại mật khẩu.';
        } elseif ($input['password'] !== $input['confirm_password']) {
            $errors['confirm_password'] = 'Mật khẩu nhập lại không khớp.';
        }

        return $errors;
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
