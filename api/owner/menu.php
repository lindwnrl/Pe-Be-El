<?php
declare(strict_types=1);

/**
 * Owner menu management.
 *
 * GET  → catalog (including inactive rows) + categories
 * POST → { action: 'create' | 'update' | 'delete' | 'restore', ... }
 *
 * Deleting a product that appears on a past order would orphan
 * detail_pesanan, so the delete falls back to deactivating it.
 */

require __DIR__ . '/../db.php';
require __DIR__ . '/../../includes/auth.php';

require_owner();

$pdo = db();

/** Shape a menu row for the client. */
function owner_menu_row(array $row): array
{
    $stok   = (int) $row['stok'];
    $aktif  = (bool) $row['aktif'];

    return [
        'menu_id'       => (int) $row['menu_id'],
        'nama_menu'     => (string) $row['nama_menu'],
        'harga'         => (float) $row['harga'],
        'stok'          => $stok,
        'aktif'         => $aktif,
        'status'        => $aktif && $stok > 0 ? 'tersedia' : 'habis',
        'kategori_id'   => $row['kategori_id'] !== null ? (int) $row['kategori_id'] : null,
        'nama_kategori' => $row['nama_kategori'] !== null ? (string) $row['nama_kategori'] : '',
        'gambar'        => $row['gambar'] !== null ? (string) $row['gambar'] : '',
        'deskripsi'     => $row['deskripsi'] !== null ? (string) $row['deskripsi'] : '',
    ];
}

/** Load one row in the client shape. */
function owner_fetch(PDO $pdo, int $menuId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT m.*, k.nama_kategori FROM menu m '
        . 'LEFT JOIN kategori_menu k ON k.kategori_id = m.kategori_id '
        . 'WHERE m.menu_id = :id'
    );
    $stmt->execute([':id' => $menuId]);
    $row = $stmt->fetch();

    return $row === false ? null : owner_menu_row($row);
}

/**
 * Validate the editable fields.
 *
 * @return array{0:array,1:string|null} [values, error]
 */
function owner_validate(array $input): array
{
    $nama = trim((string) ($input['nama_menu'] ?? ''));

    if ($nama === '') {
        return [[], 'Nama menu wajib diisi.'];
    }
    if (mb_strlen($nama) > 100) {
        return [[], 'Nama menu maksimal 100 karakter.'];
    }

    $harga = filter_var($input['harga'] ?? null, FILTER_VALIDATE_FLOAT);
    if ($harga === false || $harga < 0) {
        return [[], 'Harga harus berupa angka dan tidak boleh negatif.'];
    }

    $stok = filter_var($input['stok'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($stok === false) {
        return [[], 'Stok harus berupa angka bulat dan tidak boleh negatif.'];
    }

    $kategoriId = filter_var($input['kategori_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($kategoriId === false) {
        return [[], 'Kategori wajib dipilih.'];
    }

    $gambar    = trim((string) ($input['gambar'] ?? ''));
    $deskripsi = trim((string) ($input['deskripsi'] ?? ''));

    if ($gambar !== '') {
        if (str_starts_with($gambar, 'data:')) {
            // Image dropped in the form: base64 JPEG produced by the browser.
            if (!preg_match('#^data:image/(png|jpe?g|webp|gif);base64,[A-Za-z0-9+/=]+$#', $gambar)) {
                return [[], 'Format gambar tidak dikenali.'];
            }
            if (strlen($gambar) > 2500000) {
                return [[], 'Gambar terlalu besar. Maksimal sekitar 2 MB.'];
            }
        } elseif (mb_strlen($gambar) > 255) {
            return [[], 'Path gambar terlalu panjang.'];
        }
    }
    if (mb_strlen($deskripsi) > 255) {
        return [[], 'Deskripsi maksimal 255 karakter.'];
    }

    return [[
        'nama_menu'   => $nama,
        'harga'       => (float) $harga,
        'stok'        => (int) $stok,
        'kategori_id' => (int) $kategoriId,
        'gambar'      => $gambar === '' ? null : $gambar,
        'deskripsi'   => $deskripsi === '' ? null : $deskripsi,
    ], null];
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $rows = $pdo->query(
        'SELECT m.*, k.nama_kategori FROM menu m '
        . 'LEFT JOIN kategori_menu k ON k.kategori_id = m.kategori_id '
        . 'ORDER BY m.menu_id'
    )->fetchAll();

    $kategori = $pdo->query(
        'SELECT kategori_id, nama_kategori FROM kategori_menu ORDER BY kategori_id'
    )->fetchAll();

    json_response([
        'ok'         => true,
        'items'      => array_map('owner_menu_row', $rows),
        'kategori'   => array_map(static fn (array $k): array => [
            'kategori_id'   => (int) $k['kategori_id'],
            'nama_kategori' => (string) $k['nama_kategori'],
        ], $kategori),
    ]);
}

if ($method !== 'POST') {
    json_response(['ok' => false, 'error' => 'Metode tidak diizinkan.'], 405);
}

$input  = request_input();
$action = (string) ($input['action'] ?? '');

try {
    if ($action === 'create') {
        [$values, $error] = owner_validate($input);

        if ($error !== null) {
            json_response(['ok' => false, 'error' => $error], 400);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO menu (nama_menu, harga, stok, status, kategori_id, gambar, deskripsi, aktif) '
            . 'VALUES (:nama, :harga, :stok, :status, :kategori, :gambar, :deskripsi, TRUE) '
            . 'RETURNING menu_id'
        );
        $stmt->execute([
            ':nama'      => $values['nama_menu'],
            ':harga'     => $values['harga'],
            ':stok'      => $values['stok'],
            ':status'    => $values['stok'] > 0 ? 'tersedia' : 'habis',
            ':kategori'  => $values['kategori_id'],
            ':gambar'    => $values['gambar'],
            ':deskripsi' => $values['deskripsi'],
        ]);

        $id = (int) $stmt->fetchColumn();

        json_response(['ok' => true, 'mode' => 'created', 'item' => owner_fetch($pdo, $id)]);
    }

    if ($action === 'update') {
        $menuId = filter_var($input['menu_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($menuId === false) {
            json_response(['ok' => false, 'error' => 'Menu tidak valid.'], 400);
        }

        [$values, $error] = owner_validate($input);

        if ($error !== null) {
            json_response(['ok' => false, 'error' => $error], 400);
        }

        $stmt = $pdo->prepare(
            'UPDATE menu SET nama_menu = :nama, harga = :harga, stok = :stok, '
            . 'status = CASE WHEN :stok > 0 THEN \'tersedia\' ELSE \'habis\' END, '
            . 'kategori_id = :kategori, gambar = :gambar, deskripsi = :deskripsi '
            . 'WHERE menu_id = :id'
        );
        $stmt->execute([
            ':nama'      => $values['nama_menu'],
            ':harga'     => $values['harga'],
            ':stok'      => $values['stok'],
            ':kategori'  => $values['kategori_id'],
            ':gambar'    => $values['gambar'],
            ':deskripsi' => $values['deskripsi'],
            ':id'        => $menuId,
        ]);

        $item = owner_fetch($pdo, (int) $menuId);

        if ($item === null) {
            json_response(['ok' => false, 'error' => 'Menu tidak ditemukan.'], 404);
        }

        json_response(['ok' => true, 'mode' => 'updated', 'item' => $item]);
    }

    if ($action === 'delete' || $action === 'restore') {
        $menuId = filter_var($input['menu_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($menuId === false) {
            json_response(['ok' => false, 'error' => 'Menu tidak valid.'], 400);
        }

        if ($action === 'restore') {
            $pdo->prepare('UPDATE menu SET aktif = TRUE WHERE menu_id = :id')
                ->execute([':id' => $menuId]);

            $item = owner_fetch($pdo, (int) $menuId);

            if ($item === null) {
                json_response(['ok' => false, 'error' => 'Menu tidak ditemukan.'], 404);
            }

            json_response(['ok' => true, 'mode' => 'restored', 'item' => $item]);
        }

        try {
            $stmt = $pdo->prepare('DELETE FROM menu WHERE menu_id = :id');
            $stmt->execute([':id' => $menuId]);

            if ($stmt->rowCount() === 0) {
                json_response(['ok' => false, 'error' => 'Menu tidak ditemukan.'], 404);
            }

            json_response(['ok' => true, 'mode' => 'deleted']);
        } catch (PDOException $e) {
            // 23503 = foreign key violation: the product has order history.
            if ($e->getCode() !== '23503') {
                throw $e;
            }

            $pdo->prepare('UPDATE menu SET aktif = FALSE, status = \'habis\' WHERE menu_id = :id')
                ->execute([':id' => $menuId]);

            json_response([
                'ok'   => true,
                'mode' => 'archived',
                'item' => owner_fetch($pdo, (int) $menuId),
                'note' => 'Menu ini punya riwayat pesanan, jadi dinonaktifkan alih-alih dihapus.',
            ]);
        }
    }

    json_response(['ok' => false, 'error' => 'Aksi tidak dikenal.'], 400);
} catch (PDOException $e) {
    error_log('owner/menu.php: ' . $e->getMessage());
    json_response(['ok' => false, 'error' => 'Gagal menyimpan menu. Silakan coba lagi.'], 500);
}