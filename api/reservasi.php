<?php
declare(strict_types=1);

/**
 * Table reservations.
 *
 *   GET  → every currently occupied table
 *   POST → book a table
 *
 * A booking stays `aktif` until an admin releases it; nothing expires on its
 * own, which is what keeps a table unavailable for everyone else.
 */

require __DIR__ . '/db.php';
require __DIR__ . '/../includes/session-boot.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/tables.php';

auth_start();

$pdo    = db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $rows = $pdo->query(
        "SELECT r.reservasi_id, r.meja, r.nama_pemesan, r.jumlah_orang, r.tanggal, r.waktu, "
        . "to_char(r.dibuat_pada, 'YYYY-MM-DD\"T\"HH24:MI:SS') AS dibuat_pada, "
        . "p.nama AS nama_pelanggan "
        . "FROM reservasi r LEFT JOIN pelanggan p ON p.pelanggan_id = r.pelanggan_id "
        . "WHERE r.status = 'aktif' ORDER BY r.meja"
    )->fetchAll();

    $tables = [];

    foreach ($rows as $row) {
        $tables[(string) $row['meja']] = [
            'reservasi_id' => (int) $row['reservasi_id'],
            'meja'         => (string) $row['meja'],
            'nama'         => (string) ($row['nama_pemesan'] !== '' ? $row['nama_pemesan'] : ($row['nama_pelanggan'] ?? '')),
            'jumlah_orang' => (int) $row['jumlah_orang'],
            'tanggal'      => (string) $row['tanggal'],
            'waktu'        => substr((string) $row['waktu'], 0, 5),
            'dibuat_pada'  => (string) $row['dibuat_pada'],
        ];
    }

    json_response(['ok' => true, 'tables' => $tables]);
}

if ($method !== 'POST') {
    json_response(['ok' => false, 'error' => 'Metode tidak diizinkan.'], 405);
}

if (auth_user() === null) {
    json_response(['ok' => false, 'error' => 'Login diperlukan.'], 401);
}

$input = request_input();

$meja   = trim((string) ($input['meja'] ?? ''));
$nama   = trim((string) ($input['nama'] ?? ''));
$orang  = filter_var($input['jumlah_orang'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$tanggal = trim((string) ($input['tanggal'] ?? ''));
$waktu  = trim((string) ($input['waktu'] ?? ''));

$kapasitas = nina_tables();

if (!isset($kapasitas[$meja])) {
    json_response(['ok' => false, 'error' => 'Meja tidak dikenal.'], 400);
}

if ($nama === '') {
    $nama = (string) ($_SESSION['nama'] ?? '');
}

if ($nama === '') {
    json_response(['ok' => false, 'error' => 'Nama pemesan wajib diisi.'], 400);
}

if ($orang === false || $orang > $kapasitas[$meja]) {
    json_response(
        ['ok' => false, 'error' => 'Jumlah orang melebihi kapasitas meja ' . $meja . ' (' . $kapasitas[$meja] . ' kursi).'],
        400
    );
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    json_response(['ok' => false, 'error' => 'Tanggal reservasi tidak valid.'], 400);
}

if (!preg_match('/^\d{2}:\d{2}$/', $waktu)) {
    json_response(['ok' => false, 'error' => 'Waktu reservasi tidak valid.'], 400);
}

$pelangganId = (int) ($_SESSION['pelanggan_id'] ?? 0);

try {
    $stmt = $pdo->prepare(
        'INSERT INTO reservasi (meja, pelanggan_id, nama_pemesan, jumlah_orang, tanggal, waktu) '
        . 'VALUES (:meja, :pelanggan, :nama, :orang, :tanggal, :waktu) '
        . 'RETURNING reservasi_id'
    );
    $stmt->execute([
        ':meja'      => $meja,
        ':pelanggan' => $pelangganId > 0 ? $pelangganId : null,
        ':nama'      => $nama,
        ':orang'     => $orang,
        ':tanggal'   => $tanggal,
        ':waktu'     => $waktu,
    ]);

    $id = (int) $stmt->fetchColumn();
} catch (PDOException $e) {
    // 23505 = unique violation on the partial index: the table is already held.
    if ($e->getCode() === '23505') {
        json_response(
            ['ok' => false, 'error' => 'Meja ' . $meja . ' sedang dipakai. Silakan pilih meja lain.'],
            409
        );
    }

    error_log('reservasi.php: ' . $e->getMessage());
    json_response(['ok' => false, 'error' => 'Reservasi gagal disimpan. Silakan coba lagi.'], 500);
}

json_response([
    'ok'          => true,
    'reservation' => [
        'reservasi_id' => $id,
        'meja'         => $meja,
        'nama'         => $nama,
        'jumlah_orang' => (int) $orang,
        'tanggal'      => $tanggal,
        'waktu'        => $waktu,
    ],
]);