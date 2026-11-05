<?php
declare(strict_types=1);

require __DIR__ . '/../db.php';
require __DIR__ . '/../../includes/auth.php';

require_admin();

$pdo = db();

/** Fetch one product row (admin shape). */
function fetch_item(PDO $pdo, int $menuId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT m.menu_id, m.nama_menu, m.harga, m.stok, m.status, '
        . 'k.nama_kategori '
        . 'FROM menu m LEFT JOIN kategori_menu k ON k.kategori_id = m.kategori_id '
        . 'WHERE m.menu_id = :id'
    );
    $stmt->execute([':id' => $menuId]);
    $row = $stmt->fetch();

    if ($row === false) {
        return null;
    }

    return [
        'menu_id'       => (int) $row['menu_id'],
        'nama_menu'     => (string) $row['nama_menu'],
        'harga'         => (float) $row['harga'],
        'stok'          => (int) $row['stok'],
        'status'        => (string) $row['status'],
        'nama_kategori' => $row['nama_kategori'] !== null
            ? (string) $row['nama_kategori']
            : '',
    ];
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $rows = $pdo->query(
        'SELECT m.menu_id, m.nama_menu, m.harga, m.stok, m.status, '
        . 'k.nama_kategori '
        . 'FROM menu m LEFT JOIN kategori_menu k ON k.kategori_id = m.kategori_id '
        . 'ORDER BY m.menu_id'
    )->fetchAll();

    $items = array_map(static function (array $row): array {
        return [
            'menu_id'       => (int) $row['menu_id'],
            'nama_menu'     => (string) $row['nama_menu'],
            'harga'         => (float) $row['harga'],
            'stok'          => (int) $row['stok'],
            'status'        => (string) $row['status'],
            'nama_kategori' => $row['nama_kategori'] !== null
                ? (string) $row['nama_kategori']
                : '',
        ];
    }, $rows);

    json_response(['ok' => true, 'items' => $items]);
}

if ($method !== 'POST') {
    json_response(['ok' => false, 'error' => 'Metode tidak diizinkan.'], 405);
}

$input = request_input();

$menuId = filter_var($input['menu_id'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
if ($menuId === false) {
    json_response(['ok' => false, 'error' => 'Menu tidak valid.'], 400);
}

$hasStok  = array_key_exists('stok', $input);
$hasDelta = array_key_exists('delta', $input);

if ($hasStok === $hasDelta) {
    json_response(
        ['ok' => false, 'error' => 'Sertakan salah satu: stok atau delta.'],
        400
    );
}

try {
    if ($hasStok) {
        $stok = filter_var($input['stok'], FILTER_VALIDATE_INT);

        if ($stok === false) {
            json_response(['ok' => false, 'error' => 'Stok harus berupa angka bulat.'], 400);
        }
        if ($stok < 0) {
            json_response(['ok' => false, 'error' => 'Stok tidak boleh negatif.'], 400);
        }

        $stmt = $pdo->prepare(
            "UPDATE menu "
            . "SET stok = :stok, "
            . "    status = CASE WHEN :stok > 0 THEN 'tersedia' ELSE 'habis' END "
            . "WHERE menu_id = :id "
            . "RETURNING menu_id"
        );
        $stmt->execute([':stok' => $stok, ':id' => $menuId]);
    } else {
        $delta = filter_var($input['delta'], FILTER_VALIDATE_INT);

        if ($delta === false) {
            json_response(['ok' => false, 'error' => 'Delta harus berupa angka bulat.'], 400);
        }

        // Single atomic statement: concurrent updates cannot lose a change or
        // push stock below zero.
        $stmt = $pdo->prepare(
            "UPDATE menu "
            . "SET stok = GREATEST(stok + :delta, 0), "
            . "    status = CASE WHEN GREATEST(stok + :delta, 0) > 0 "
            . "                  THEN 'tersedia' ELSE 'habis' END "
            . "WHERE menu_id = :id "
            . "RETURNING menu_id"
        );
        $stmt->execute([':delta' => $delta, ':id' => $menuId]);
    }

    if ($stmt->fetch() === false) {
        json_response(['ok' => false, 'error' => 'Menu tidak ditemukan.'], 404);
    }
} catch (PDOException $e) {
    error_log('admin/menu-stock.php: ' . $e->getMessage());
    json_response(['ok' => false, 'error' => 'Gagal menyimpan stok. Silakan coba lagi.'], 500);
}

json_response(['ok' => true, 'item' => fetch_item($pdo, $menuId)]);