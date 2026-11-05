<?php
declare(strict_types=1);

require __DIR__ . '/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_response(['ok' => false, 'error' => 'Metode tidak diizinkan.'], 405);
}

$input    = request_input();
$username = isset($input['username']) ? trim((string) $input['username']) : '';
$password = isset($input['password']) ? (string) $input['password'] : '';
$confirm  = isset($input['confirm_password']) ? (string) $input['confirm_password'] : '';

if ($username === '' || $password === '') {
    json_response(['ok' => false, 'error' => 'Username dan password wajib diisi.']);
}

if ($password !== $confirm) {
    json_response(['ok' => false, 'error' => 'Password dan konfirmasi password tidak cocok.']);
}

if (strlen($password) < 6) {
    json_response(['ok' => false, 'error' => 'Password minimal 6 karakter.']);
}

if (strlen($username) > 50) {
    json_response(['ok' => false, 'error' => 'Username maksimal 50 karakter.']);
}

$pdo = db();

try {
    $pdo->beginTransaction();

    // Create an empty pelanggan row to link the new account to.
    $stmt = $pdo->query(
        "INSERT INTO pelanggan (nama, no_hp, alamat) VALUES ('', '', '') RETURNING pelanggan_id"
    );
    $pelanggan_id = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        "INSERT INTO akun (username, password_hash, role, pelanggan_id) "
        . "VALUES (:username, crypt(:password, gen_salt('bf')), 'pengguna', :pelanggan_id)"
    );
    $stmt->execute([
        ':username'     => $username,
        ':password'     => $password,
        ':pelanggan_id' => $pelanggan_id,
    ]);

    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($e->getCode() === '23505') {
        json_response(['ok' => false, 'error' => 'Username sudah terdaftar. Silakan gunakan username lain.']);
    }
    error_log('register.php: ' . $e->getMessage());
    json_response(['ok' => false, 'error' => 'Pendaftaran gagal. Silakan coba lagi.'], 500);
}

json_response(['ok' => true]);
