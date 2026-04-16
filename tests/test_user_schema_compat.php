<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../config/config.php';

$db = new Database();
$conn = $db->getConnection();

$email = 'schema-compat-admin@example.com';
$username = 'schema_compat_admin';
$passwordHash = password_hash('password', PASSWORD_DEFAULT);

$conn->prepare('DELETE FROM users WHERE email = :email OR username = :username')
    ->execute([
        ':email' => $email,
        ':username' => $username,
    ]);

$insert = $conn->prepare(
    'INSERT INTO users (username, email, password, full_name, phone, role, status)
     VALUES (:username, :email, :password, :full_name, :phone, :role, :status)'
);

$insert->execute([
    ':username' => $username,
    ':email' => $email,
    ':password' => $passwordHash,
    ':full_name' => 'Schema Compat Admin',
    ':phone' => null,
    ':role' => 'admin',
    ':status' => 'active',
]);

try {
    $user = (new User())->findByLogin($email);
} catch (PDOException $exception) {
    fwrite(STDERR, 'FAIL: User::findByLogin crashed on a users table without admin_permissions: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

if (!$user) {
    fwrite(STDERR, "FAIL: User::findByLogin did not return the inserted admin user.\n");
    exit(1);
}

if (!array_key_exists('admin_permissions', $user) || $user['admin_permissions'] !== []) {
    fwrite(STDERR, "FAIL: Missing-column compatibility should normalize admin_permissions to an empty array.\n");
    exit(1);
}

$conn->prepare('DELETE FROM users WHERE email = :email OR username = :username')
    ->execute([
        ':email' => $email,
        ':username' => $username,
    ]);

echo "PASS: User::findByLogin stays compatible when users.admin_permissions is absent.\n";
