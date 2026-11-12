<?php
declare(strict_types=1);

/**
 * Admin table management.
 *
 *   GET  → every occupied table, with who holds it and since when
 *   POST → { reservasi_id, action: 'release' }
 *
 * Releasing closes the reservation row; the table becomes bookable again and
 * the history is kept.
 */

require __DIR__ . '/../db.php';
require __DIR__ . '/../../includes/auth.php';

require_admin();

$pdo    = db();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $rows = $pdo->query(
        "SELECT r.reservasi_id, r.meja, r.nama_pemesan, r.jumlah_orang, r.tanggal, r.waktu, "
        . "r.dibuat_pada, a.username, p.nama AS nama_pelanggan "
        . "FROM reservasi r "
        . "LEFT JOIN pelanggan p ON p.pelanggan_id = r.pelanggan_id "
        . "LEFT JOIN akun a ON a.pelanggan_id = r.pelanggan_id "
        . "WHERE r.status = 'aktif' ORDER BY r.meja"
    )->fetchAll();

    $items = array_map(static function (array $row): array {
        return [
            'reservasi_id' => (int) $row['reservasi_id'],
            'meja'         => (string) $row['meja'],
            'nama'         => (string) ($row['nama_pemesan'] !== '' ? $row['nama_pemesan'] : ($row['nama_pelanggan'] ?? '')),
            'username'     => $row['username'] !== null ? (string) $row['username'] : '',
            'jumlah_orang' => (int) $row['jumlah_orang'],
            'tanggal'      => (string) $row['tanggal'],
            'waktu'        => substr((string) $row['waktu'], 0, 5),
            'dibuat_pada'  => (string) $row['dibuat_pada'],
        ];
    }, $rows);

    json_response(['ok' => true, 'items' => $items]);
}

if ($method !== 'POST') {
    json_response(['ok' => false, 'error' => 'Metode tidak diizinkan.'], 405);
}

$input  = request_input();
$action = (string) ($input['action'] ?? '');

if ($action !== 'release') {
    json_response(['ok' => false, 'error' => 'Aksi tidak dikenal.'], 400);
}

$id = filter_var($input['reservasi_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($id === false) {
    json_response(['ok' => false, 'error' => 'Reservasi tidak valid.'], 400);
}

try {
    // Only an active row can be released; a repeat call reports 404 rather than
    // silently succeeding, so the console can tell the state changed.
    $stmt = $pdo->prepare(
        "UPDATE reservasi SET status = 'selesai', dirilis_pada = NOW() "
        . "WHERE reservasi_id = :id AND status = 'aktif' "
        . 'RETURNING meja'
    );
    $stmt->execute([':id' => $id]);
    $meja = $stmt->fetchColumn();

    if ($meja === false) {
        json_response(['ok' => false, 'error' => 'Reservasi tidak ditemukan atau sudah aktif kembali.'], 404);
    }
} catch (PDOException $e) {
    error_log('admin/reservasi.php: ' . $e->getMessage());
    json_response(['ok' => false, 'error' => 'Gagal mengaktifkan meja. Silakan coba lagi.'], 500);
}

json_response([
    'ok'   => true,
    'meja' => (string) $meja,
    'note' => 'Meja ' . $meja . ' sudah bisa dipesan lagi.',
]);