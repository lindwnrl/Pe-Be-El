<?php
declare(strict_types=1);

/**
 * Daily sales aggregate for the admin and owner dashboards.
 *
 * GET ?from=YYYY-MM-DD&to=YYYY-MM-DD  (defaults to the last 7 days)
 * Returns one row per day that had sales, plus a range total.
 */

require __DIR__ . '/db.php';
require __DIR__ . '/../includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    json_response(['ok' => false, 'error' => 'Metode tidak diizinkan.'], 405);
}

require_staff();

$today = new DateTimeImmutable('today');
$to    = trim((string) ($_GET['to'] ?? ''));
$from  = trim((string) ($_GET['from'] ?? ''));

$isDate = static fn (string $v): bool => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $v);

if ($to === '' || !$isDate($to)) {
    $to = $today->format('Y-m-d');
}

if ($from === '' || !$isDate($from)) {
    $from = $today->modify('-6 days')->format('Y-m-d');
}

if ($from > $to) {
    json_response(['ok' => false, 'error' => 'Rentang tanggal tidak valid.'], 400);
}

try {
    $stmt = db()->prepare(
        "SELECT to_char(tanggal_pesanan, 'YYYY-MM-DD') AS tanggal, "
        . 'COUNT(*) AS pesanan, '
        . 'COALESCE(SUM(total_harga), 0) AS total '
        . 'FROM pesanan '
        . "WHERE status_pesanan = 'selesai' "
        . 'AND tanggal_pesanan >= :from::date '
        . 'AND tanggal_pesanan < (:to::date + INTERVAL \'1 day\') '
        . 'GROUP BY 1 ORDER BY 1'
    );
    $stmt->execute([':from' => $from, ':to' => $to]);
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('sales-daily.php: ' . $e->getMessage());
    json_response(['ok' => false, 'error' => 'Laporan tidak dapat dimuat.'], 500);
}

// Fill gaps so the chart has one bar per day in the range.
$byDate = [];
foreach ($rows as $row) {
    $byDate[$row['tanggal']] = [
        'pesanan' => (int) $row['pesanan'],
        'total'   => (float) $row['total'],
    ];
}

$days = [];
$cursor = new DateTimeImmutable($from);
$end    = new DateTimeImmutable($to);

while ($cursor <= $end) {
    $key = $cursor->format('Y-m-d');
    $days[] = [
        'tanggal' => $key,
        'pesanan' => $byDate[$key]['pesanan'] ?? 0,
        'total'   => $byDate[$key]['total'] ?? 0.0,
    ];
    $cursor = $cursor->modify('+1 day');
}

$totalRevenue = array_sum(array_column($days, 'total'));
$totalOrders  = array_sum(array_column($days, 'pesanan'));

json_response([
    'ok'    => true,
    'from'  => $from,
    'to'    => $to,
    'days'  => $days,
    'total' => [
        'pendapatan' => $totalRevenue,
        'pesanan'    => $totalOrders,
        'rata_rata'  => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0,
    ],
]);