<?php
declare(strict_types=1);

/**
 * Downloadable PDF receipt for one order.
 *
 *   GET /api/receipt.php?id=12
 *
 * Reachable by the customer who placed the order, or by staff.
 */

require __DIR__ . '/db.php';
require __DIR__ . '/../includes/session-boot.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/ReceiptPdf.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    json_response(['ok' => false, 'error' => 'Metode tidak diizinkan.'], 405);
}

auth_start();

$user = auth_user();

if ($user === null) {
    json_response(['ok' => false, 'error' => 'Login diperlukan.'], 401);
}

$orderId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($orderId === false) {
    json_response(['ok' => false, 'error' => 'Pesanan tidak valid.'], 400);
}

$pdo = db();

$stmt = $pdo->prepare(
    'SELECT pesanan_id, tanggal_pesanan, total_harga, status_pesanan, '
    . 'meja, jenis_pesanan, catatan, nama_pemesan, pelanggan_id '
    . 'FROM pesanan WHERE pesanan_id = :id'
);
$stmt->execute([':id' => $orderId]);
$order = $stmt->fetch();

if ($order === false) {
    json_response(['ok' => false, 'error' => 'Pesanan tidak ditemukan.'], 404);
}

// Customers may only download their own receipt.
$isStaff = in_array($user['role'], ['admin', 'owner'], true);
$ownsIt  = (int) ($_SESSION['pelanggan_id'] ?? 0) === (int) ($order['pelanggan_id'] ?? 0);

if (!$isStaff && !$ownsIt) {
    json_response(['ok' => false, 'error' => 'Akses ditolak.'], 403);
}

$stmt = $pdo->prepare(
    'SELECT d.jumlah, d.harga, d.subtotal, m.nama_menu '
    . 'FROM detail_pesanan d '
    . 'LEFT JOIN menu m ON m.menu_id = d.menu_id '
    . 'WHERE d.pesanan_id = :id ORDER BY d.detail_id'
);
$stmt->execute([':id' => $orderId]);
$lines = $stmt->fetchAll();

$rupiah = static fn (float $value): string => 'Rp ' . number_format($value, 0, ',', '.');

$tanggal = new DateTimeImmutable((string) $order['tanggal_pesanan']);
$bulan = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

$pdf = new ReceiptPdf();

$pdf->drawHeading('NINA RASA');
$pdf->drawText('Bakso & Mie Ayam', 10, false, 14);
$pdf->drawText('Jl. Cianjur No.1b, Penanggungan, Kec. Klojen', 9, false, 12);
$pdf->drawText('Kota Malang, Jawa Timur 65113', 9, false, 12);
$pdf->gap(6);
$pdf->rule();
$pdf->gap(4);

$pdf->drawText('STRUK PESANAN', 13, true, 20);

$pdf->drawPair('No. pesanan', '#' . $order['pesanan_id'], 10, true);
$pdf->drawPair('Tanggal', $tanggal->format('d') . ' ' . $bulan[(int) $tanggal->format('n')] . ' ' . $tanggal->format('Y'), 10);
$pdf->drawPair('Jam', $tanggal->format('H:i') . ' WIB', 10);
$pdf->drawPair('Nama pemesan', (string) ($order['nama_pemesan'] ?? '-'), 10);
$pdf->drawPair('Jenis pesanan', (string) ($order['jenis_pesanan'] ?? '-'), 10);
$pdf->drawPair('Meja', (string) ($order['meja'] ?? '-'), 10);

if (!empty($order['catatan'])) {
    $pdf->drawPair('Catatan', (string) $order['catatan'], 10);
}

$pdf->gap(6);
$pdf->rule();
$pdf->gap(4);

$pdf->drawText('RINCIAN', 10, true, 18);

foreach ($lines as $line) {
    $name = (string) ($line['nama_menu'] ?? 'Menu');
    $qty  = (int) $line['jumlah'];

    $pdf->drawPair($name . '  x' . $qty, $rupiah((float) $line['subtotal']), 10, true);
    $pdf->drawText('@ ' . $rupiah((float) $line['harga']) . ' / item', 8.5, false, 12, 10);
}

$pdf->gap(4);
$pdf->rule(1);
$pdf->gap(2);
$pdf->drawPair('TOTAL', $rupiah((float) $order['total_harga']), 14, true);
$pdf->gap(6);
$pdf->rule();
$pdf->gap(4);

$pdf->drawText('Status: ' . ucfirst((string) $order['status_pesanan']), 9, false, 14);
$pdf->gap(8);
$pdf->drawText('Terima kasih telah makan di Nina Rasa.', 9, false, 14);

$binary = $pdf->output();
$filename = 'struk-nina-rasa-' . $order['pesanan_id'] . '.pdf';

// Two transports, same bytes.
//
//   default        → the PDF itself, for direct navigation
//   ?format=json   → base64 in a JSON envelope, for fetch()
//
// Browsers intercept binary/PDF responses (Chromium hands fetch() an empty
// body, which produced zero-byte receipts), so the client asks for JSON and
// assembles the blob itself.
if (($_GET['format'] ?? '') === 'json') {
    json_response([
        'ok'       => true,
        'filename' => $filename,
        'mime'     => 'application/pdf',
        'data'     => base64_encode($binary),
    ]);
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($binary));
header('Cache-Control: private, max-age=0, no-store');

echo $binary;