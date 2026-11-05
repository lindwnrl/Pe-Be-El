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
        'SELECT a.akun_id, a.username, a.role, a.pelanggan_id, '
        . 'p.nama, p.no_hp, p.email, p.jenis_kelamin, p.tanggal_lahir '
        . 'FROM akun a '
        . 'LEFT JOIN pelanggan p ON p.pelanggan_id = a.pelanggan_id '
        . 'WHERE a.username = :username AND crypt(:password, a.password_hash) = a.password_hash'
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

// Every account needs a pelanggan row to hold profile data. Accounts created
// before registration linked one (or inserted by hand, e.g. admins) get one now.
if (empty($user['pelanggan_id'])) {
    try {
        $pdo = db();
        $pdo->beginTransaction();

        $stmt = $pdo->query(
            "INSERT INTO pelanggan (nama, no_hp, alamat) VALUES ('', '', '') RETURNING pelanggan_id"
        );
        $pelangganId = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare(
            'UPDATE akun SET pelanggan_id = :pid WHERE akun_id = :aid'
        );
        $stmt->execute([':pid' => $pelangganId, ':aid' => (int) $user['akun_id']]);

        $pdo->commit();

        $user['pelanggan_id'] = (string) $pelangganId;
        $user['nama'] = $user['nama'] ?? '';
        $user['no_hp'] = $user['no_hp'] ?? '';
        $user['email'] = $user['email'] ?? '';
        $user['jenis_kelamin'] = $user['jenis_kelamin'] ?? '';
        $user['tanggal_lahir'] = $user['tanggal_lahir'] ?? '';
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('login.php (profile link): ' . $e->getMessage());
        json_response(['ok' => false, 'error' => 'Layanan autentikasi tidak tersedia. Coba lagi nanti.'], 500);
    }
}

$_SESSION['akun_id']      = (int) $user['akun_id'];
$_SESSION['username']       = (string) $user['username'];
$_SESSION['role']           = (string) $user['role'];
$_SESSION['pelanggan_id']   = (int) $user['pelanggan_id'];
$_SESSION['nama']           = $user['nama'] ?? '';
$_SESSION['no_hp']          = $user['no_hp'] ?? '';
$_SESSION['email']          = $user['email'] ?? '';
$_SESSION['jenis_kelamin']  = $user['jenis_kelamin'] ?? '';
$_SESSION['tanggal_lahir']  = $user['tanggal_lahir'] ?? '';

json_response([
    'ok'   => true,
    'user' => [
        'username'      => $user['username'],
        'role'          => $user['role'],
        'nama'          => $user['nama'] ?? '',
        'no_hp'         => $user['no_hp'] ?? '',
        'email'         => $user['email'] ?? '',
        'jenis_kelamin' => $user['jenis_kelamin'] ?? '',
        'tanggal_lahir' => $user['tanggal_lahir'] ?? '',
    ],
]);
