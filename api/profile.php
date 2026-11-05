<?php
declare(strict_types=1);

require __DIR__ . '/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function require_login(): void
{
    if (empty($_SESSION['akun_id'])) {
        json_response(['ok' => false, 'error' => 'Login diperlukan.'], 401);
    }
}

function get_pelanggan_id(): int
{
    $id = $_SESSION['pelanggan_id'] ?? 0;
    if ($id <= 0) {
        json_response(['ok' => false, 'error' => 'Profil tidak ditemukan.'], 400);
    }
    return (int) $id;
}

function fetch_profile(int $pelanggan_id): ?array
{
    $stmt = db()->prepare(
        'SELECT pelanggan_id, nama, no_hp, email, jenis_kelamin, tanggal_lahir '
        . 'FROM pelanggan WHERE pelanggan_id = :id'
    );
    $stmt->execute([':id' => $pelanggan_id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function validate_profile(array $input): array
{
    $errors = [];

    $nama          = isset($input['nama']) ? trim((string) $input['nama']) : '';
    $no_hp         = isset($input['no_hp']) ? trim((string) $input['no_hp']) : '';
    $email         = isset($input['email']) ? trim((string) $input['email']) : '';
    $jenis_kelamin = isset($input['jenis_kelamin']) ? trim((string) $input['jenis_kelamin']) : '';
    $tanggal_lahir = isset($input['tanggal_lahir']) ? trim((string) $input['tanggal_lahir']) : '';

    if ($nama === '') {
        $errors[] = 'Nama wajib diisi.';
    }
    if ($no_hp === '') {
        $errors[] = 'No. telepon wajib diisi.';
    }
    if ($email === '') {
        $errors[] = 'Email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if ($jenis_kelamin === '') {
        $errors[] = 'Jenis kelamin wajib dipilih.';
    } elseif (!in_array($jenis_kelamin, ['laki-laki', 'perempuan'], true)) {
        $errors[] = 'Jenis kelamin tidak valid.';
    }
    if ($tanggal_lahir === '') {
        $errors[] = 'Tanggal lahir wajib diisi.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_lahir)) {
        $errors[] = 'Format tanggal lahir tidak valid (YYYY-MM-DD).';
    }

    return [
        'errors'        => $errors,
        'nama'          => $nama,
        'no_hp'         => $no_hp,
        'email'         => $email,
        'jenis_kelamin' => $jenis_kelamin,
        'tanggal_lahir' => $tanggal_lahir,
    ];
}

require_login();
$pelanggan_id = get_pelanggan_id();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $profile = fetch_profile($pelanggan_id);
    if ($profile === null) {
        json_response(['ok' => false, 'error' => 'Profil tidak ditemukan.'], 404);
    }
    json_response(['ok' => true, 'profile' => $profile]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input   = request_input();
    $validated = validate_profile($input);

    if ($validated['errors'] !== []) {
        json_response(['ok' => false, 'error' => implode(' ', $validated['errors'])], 400);
    }

    try {
        $stmt = db()->prepare(
            'UPDATE pelanggan '
            . 'SET nama = :nama, no_hp = :no_hp, email = :email, '
            . 'jenis_kelamin = :jenis_kelamin, tanggal_lahir = :tanggal_lahir '
            . 'WHERE pelanggan_id = :id'
        );
        $stmt->execute([
            ':nama'          => $validated['nama'],
            ':no_hp'         => $validated['no_hp'],
            ':email'         => $validated['email'],
            ':jenis_kelamin' => $validated['jenis_kelamin'],
            ':tanggal_lahir' => $validated['tanggal_lahir'],
            ':id'            => $pelanggan_id,
        ]);
    } catch (PDOException $e) {
        error_log('profile.php: ' . $e->getMessage());
        json_response(['ok' => false, 'error' => 'Gagal menyimpan profil. Silakan coba lagi.'], 500);
    }

    json_response([
        'ok'      => true,
        'profile' => [
            'pelanggan_id'  => $pelanggan_id,
            'nama'          => $validated['nama'],
            'no_hp'         => $validated['no_hp'],
            'email'         => $validated['email'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
        ],
    ]);
}

json_response(['ok' => false, 'error' => 'Metode tidak diizinkan.'], 405);
