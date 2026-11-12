<?php
declare(strict_types=1);

/**
 * Staff account management. Owner only.
 *
 *   GET  → staff accounts (admin + owner) with their profile name
 *   POST → { action: 'create', username, password, role } | { action: 'delete', akun_id }
 *
 * Only `admin` and `owner` can be created here: customers register themselves.
 * The owner cannot delete or demote their own account, so the console can never
 * be locked out by accident.
 */

require __DIR__ . '/../db.php';
require __DIR__ . '/../../includes/auth.php';

require_owner();

$pdo    = db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$me     = auth_user();

if ($method === 'GET') {
    $rows = $pdo->query(
        "SELECT a.akun_id, a.username, a.role, a.pelanggan_id, p.nama "
        . "FROM akun a LEFT JOIN pelanggan p ON p.pelanggan_id = a.pelanggan_id "
        . "WHERE a.role IN ('admin', 'owner') ORDER BY a.role DESC, a.username"
    )->fetchAll();

    $items = array_map(static function (array $row): array {
        return [
            'akun_id'  => (int) $row['akun_id'],
            'username' => (string) $row['username'],
            'role'     => (string) $row['role'],
            'nama'     => $row['nama'] !== null ? (string) $row['nama'] : '',
        ];
    }, $rows);

    json_response(['ok' => true, 'items' => $items]);
}

if ($method !== 'POST') {
    json_response(['ok' => false, 'error' => 'Metode tidak diizinkan.'], 405);
}

$input  = request_input();
$action = (string) ($input['action'] ?? '');

if ($action === 'create') {
    $username = trim((string) ($input['username'] ?? ''));
    $password = (string) ($input['password'] ?? '');
    $role     = trim((string) ($input['role'] ?? ''));

    if ($username === '' || $password === '') {
        json_response(['ok' => false, 'error' => 'Username dan password wajib diisi.'], 400);
    }

    if (mb_strlen($username) < 3 || mb_strlen($username) > 50) {
        json_response(['ok' => false, 'error' => 'Username harus 3–50 karakter.'], 400);
    }

    if (!preg_match('/^[A-Za-z0-9._-]+$/', $username)) {
        json_response(['ok' => false, 'error' => 'Username hanya boleh huruf, angka, titik, garis bawah, dan strip.'], 400);
    }

    if (strlen($password) < 8) {
        json_response(['ok' => false, 'error' => 'Password minimal 8 karakter untuk akun staf.'], 400);
    }

    if (!in_array($role, ['admin', 'owner'], true)) {
        json_response(['ok' => false, 'error' => 'Role harus admin atau owner.'], 400);
    }

    try {
        $pdo->beginTransaction();

        // Staff get a profile row too, so the profile gate behaves the same.
        $stmt = $pdo->query(
            "INSERT INTO pelanggan (nama, no_hp, alamat) VALUES ('', '', '') RETURNING pelanggan_id"
        );
        $pelangganId = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare(
            'INSERT INTO akun (username, password_hash, role, pelanggan_id) '
            . "VALUES (:username, crypt(:password, gen_salt('bf')), :role, :pelanggan) "
            . 'RETURNING akun_id'
        );
        $stmt->execute([
            ':username'  => $username,
            ':password'  => $password,
            ':role'      => $role,
            ':pelanggan' => $pelangganId,
        ]);

        $akunId = (int) $stmt->fetchColumn();
        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        if ($e->getCode() === '23505') {
            json_response(['ok' => false, 'error' => 'Username sudah dipakai.'], 409);
        }

        error_log('owner/accounts.php: ' . $e->getMessage());
        json_response(['ok' => false, 'error' => 'Akun gagal dibuat. Silakan coba lagi.'], 500);
    }

    json_response([
        'ok'   => true,
        'item' => [
            'akun_id'  => $akunId,
            'username' => $username,
            'role'     => $role,
            'nama'     => '',
        ],
        'note' => 'Akun ' . $role . ' "' . $username . '" dibuat. Pemiliknya perlu mengisi profil saat pertama masuk.',
    ]);
}

if ($action === 'delete') {
    $akunId = filter_var($input['akun_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($akunId === false) {
        json_response(['ok' => false, 'error' => 'Akun tidak valid.'], 400);
    }

    if ($akunId === $me['akun_id']) {
        json_response(['ok' => false, 'error' => 'Anda tidak bisa menghapus akun sendiri.'], 400);
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            "SELECT akun_id, role, pelanggan_id FROM akun WHERE akun_id = :id AND role IN ('admin', 'owner')"
        );
        $stmt->execute([':id' => $akunId]);
        $target = $stmt->fetch();

        if ($target === false) {
            $pdo->rollBack();
            json_response(['ok' => false, 'error' => 'Akun staf tidak ditemukan.'], 404);
        }

        $pdo->prepare('DELETE FROM akun WHERE akun_id = :id')->execute([':id' => $akunId]);

        // Drop the profile row only when nothing else points at it.
        if ($target['pelanggan_id'] !== null) {
            $pdo->prepare(
                'DELETE FROM pelanggan WHERE pelanggan_id = :pid '
                . 'AND NOT EXISTS (SELECT 1 FROM akun a WHERE a.pelanggan_id = :pid)'
            )->execute([':pid' => $target['pelanggan_id']]);
        }

        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('owner/accounts.php (delete): ' . $e->getMessage());
        json_response(['ok' => false, 'error' => 'Akun gagal dihapus.'], 500);
    }

    json_response(['ok' => true, 'note' => 'Akun staf dihapus.']);
}

json_response(['ok' => false, 'error' => 'Aksi tidak dikenal.'], 400);