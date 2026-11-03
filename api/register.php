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

if (strlen($password) < 6) {
    json_response(['ok' => false, 'error' => 'Password minimal 6 karakter.']);
}

if (strlen($username) > 50) {
    json_response(['ok' => false, 'error' => 'Username maksimal 50 karakter.']);
}

try {
    $stmt = db()->prepare(
        "INSERT INTO akun (username, password_hash, role) "
        . "VALUES (:username, crypt(:password, gen_salt('bf')), 'pengguna')"
    );
    $stmt->execute([':username' => $username, ':password' => $password]);
} catch (PDOException $e) {
    if ($e->getCode() === '23505') {
        json_response(['ok' => false, 'error' => 'Username sudah terdaftar. Silakan gunakan username lain.']);
    }
    error_log('register.php: ' . $e->getMessage());
    json_response(['ok' => false, 'error' => 'Pendaftaran gagal. Silakan coba lagi.'], 500);
}

json_response(['ok' => true]);
