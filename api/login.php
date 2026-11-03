<?php
declare(strict_types=1);

require __DIR__ . '/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_response(['ok' => false, 'error' => 'Metode tidak diizinkan.'], 405);
}

$input    = request_input();
$username = isset($input['username']) ? trim((string) $input['username']) : '';
$password = isset($input['password']) ? (string) $input['password'] : '';

if ($username === '' || $password === '') {
    json_response(['ok' => false, 'error' => 'Username dan password wajib diisi.']);
}

try {
    $stmt = db()->prepare(
        'SELECT akun_id, username, role FROM akun '
        . 'WHERE username = :username AND crypt(:password, password_hash) = password_hash'
    );
    $stmt->execute([':username' => $username, ':password' => $password]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log('login.php: ' . $e->getMessage());
    json_response(['ok' => false, 'error' => 'Layanan autentikasi tidak tersedia. Coba lagi nanti.'], 500);
}

if ($user === false) {
    json_response(['ok' => false, 'error' => 'Username atau password salah.']);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
session_regenerate_id(true);
$_SESSION['akun_id'] = (int) $user['akun_id'];
$_SESSION['username'] = (string) $user['username'];
$_SESSION['role']     = (string) $user['role'];

json_response([
    'ok'   => true,
    'user' => [
        'username' => $user['username'],
        'role'     => $user['role'],
    ],
]);
