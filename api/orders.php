<?php
declare(strict_types=1);

/**
 * Place an order.
 *
 * Writes `pesanan` + `detail_pesanan` and decrements `menu.stok` inside one
 * transaction, so a partial failure can never leave stock and orders out of
 * step. Prices are read from the database, never from the request.
 */

require __DIR__ . '/db.php';
require __DIR__ . '/../includes/session-boot.php';
require __DIR__ . '/../includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_response(['ok' => false, 'error' => 'Metode tidak diizinkan.'], 405);
}

auth_start();

if (auth_user() === null) {
    json_response(['ok' => false, 'error' => 'Login diperlukan.'], 401);
}

$input = request_input();

$nama      = trim((string) ($input['nama'] ?? ''));
$jenis     = trim((string) ($input['jenis'] ?? 'Dine-in'));
$catatan   = trim((string) ($input['catatan'] ?? ''));
$meja      = trim((string) ($input['meja'] ?? ''));
$items     = $input['items'] ?? null;

if ($nama === '') {
    json_response(['ok' => false, 'error' => 'Nama pemesan wajib diisi.'], 400);
}

if (!is_array($items) || $items === []) {
    json_response(['ok' => false, 'error' => 'Pesanan harus memiliki minimal satu item.'], 400);
}

if (mb_strlen($nama) > 100) {
    json_response(['ok' => false, 'error' => 'Nama pemesan terlalu panjang.'], 400);
}

// Normalise the basket into menu_id => jumlah.
$wanted = [];

foreach ($items as $row) {
    $menuId = filter_var($row['menu_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $jumlah = filter_var($row['jumlah'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($menuId === false || $jumlah === false) {
        json_response(['ok' => false, 'error' => 'Item pesanan tidak valid.'], 400);
    }

    $wanted[$menuId] = ($wanted[$menuId] ?? 0) + $jumlah;
}

$pdo = db();

try {
    $pdo->beginTransaction();

    // Lock the rows we are about to decrement.
    $ids = array_keys($wanted);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare(
        'SELECT menu_id, nama_menu, harga, stok, aktif FROM menu '
        . 'WHERE menu_id IN (' . $placeholders . ') FOR UPDATE'
    );
    $stmt->execute($ids);
    $menu = [];

    foreach ($stmt->fetchAll() as $row) {
        $menu[(int) $row['menu_id']] = $row;
    }

    $lines = [];
    $total = 0.0;

    foreach ($wanted as $menuId => $jumlah) {
        if (!isset($menu[$menuId])) {
            throw new RuntimeException('Menu tidak ditemukan.');
        }

        $row = $menu[$menuId];

        if (!$row['aktif']) {
            throw new RuntimeException($row['nama_menu'] . ' sedang tidak tersedia.');
        }

        if ((int) $row['stok'] < $jumlah) {
            throw new RuntimeException('Stok ' . $row['nama_menu'] . ' tidak mencukupi.');
        }

        $harga    = (float) $row['harga'];
        $subtotal = $harga * $jumlah;

        $lines[] = [
            'menu_id'  => (int) $menuId,
            'nama'     => (string) $row['nama_menu'],
            'harga'    => $harga,
            'jumlah'   => $jumlah,
            'subtotal' => $subtotal,
        ];

        $total += $subtotal;
    }

    $pelangganId = (int) ($_SESSION['pelanggan_id'] ?? 0);

    $stmt = $pdo->prepare(
        'INSERT INTO pesanan '
        . '(tanggal_pesanan, total_harga, status_pesanan, pelanggan_id, meja, jenis_pesanan, catatan, nama_pemesan) '
        . 'VALUES (NOW(), :total, :status, :pelanggan, :meja, :jenis, :catatan, :nama) '
        . 'RETURNING pesanan_id'
    );
    $stmt->execute([
        ':total'     => $total,
        ':status'    => 'selesai',
        ':pelanggan' => $pelangganId > 0 ? $pelangganId : null,
        ':meja'      => $meja === '' ? null : $meja,
        ':jenis'     => $jenis === '' ? null : $jenis,
        ':catatan'   => $catatan === '' ? null : $catatan,
        ':nama'      => $nama,
    ]);

    $pesananId = (int) $stmt->fetchColumn();

    $insertLine = $pdo->prepare(
        'INSERT INTO detail_pesanan (jumlah, harga, subtotal, pesanan_id, menu_id) '
        . 'VALUES (:jumlah, :harga, :subtotal, :pesanan, :menu)'
    );
    $decrement = $pdo->prepare(
        "UPDATE menu SET stok = stok - :jumlah, "
        . "status = CASE WHEN stok - :jumlah > 0 THEN 'tersedia' ELSE 'habis' END "
        . 'WHERE menu_id = :menu AND stok >= :jumlah'
    );

    foreach ($lines as $line) {
        $insertLine->execute([
            ':jumlah'   => $line['jumlah'],
            ':harga'    => $line['harga'],
            ':subtotal' => $line['subtotal'],
            ':pesanan'  => $pesananId,
            ':menu'     => $line['menu_id'],
        ]);

        $decrement->execute([
            ':jumlah' => $line['jumlah'],
            ':menu'   => $line['menu_id'],
        ]);

        if ($decrement->rowCount() === 0) {
            throw new RuntimeException('Stok ' . $line['nama'] . ' berubah saat memproses. Coba lagi.');
        }
    }

    $pdo->commit();
} catch (RuntimeException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(['ok' => false, 'error' => $e->getMessage()], 409);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('orders.php: ' . $e->getMessage());
    json_response(['ok' => false, 'error' => 'Pesanan gagal disimpan. Silakan coba lagi.'], 500);
}

json_response([
    'ok'    => true,
    'order' => [
        'pesanan_id' => $pesananId,
        'total'      => $total,
        'items'      => $lines,
    ],
]);