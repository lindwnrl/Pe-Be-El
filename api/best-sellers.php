<?php
declare(strict_types=1);

/**
 * Best-selling products.
 *
 *   GET ?from=YYYY-MM-DD&to=YYYY-MM-DD&limit=3
 *
 * Ranks products by units sold (not revenue) over orders marked `selesai`.
 * Shared by the owner console; admin may read it too.
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
    $from = $today->modify('-29 days')->format('Y-m-d');
}

if ($from > $to) {
    json_response(['ok' => false, 'error' => 'Rentang tanggal tidak valid.'], 400);
}

$limit = filter_var($_GET['limit'] ?? 3, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 20],
]);

if ($limit === false) {
    $limit = 3;
}

try {
    $stmt = db()->prepare(
        'SELECT d.menu_id, '
        . 'COALESCE(m.nama_menu, \'Menu dihapus\') AS nama_menu, '
        . 'COALESCE(m.gambar, \'\') AS gambar, '
        . 'SUM(d.jumlah) AS terjual, '
        . 'SUM(d.subtotal) AS pendapatan '
        . 'FROM detail_pesanan d '
        . 'JOIN pesanan p ON p.pesanan_id = d.pesanan_id '
        . 'LEFT JOIN menu m ON m.menu_id = d.menu_id '
        . "WHERE p.status_pesanan = 'selesai' "
        . 'AND p.tanggal_pesanan >= :from::date '
        . 'AND p.tanggal_pesanan < (:to::date + INTERVAL \'1 day\') '
        . 'GROUP BY d.menu_id, m.nama_menu, m.gambar '
        . 'ORDER BY SUM(d.jumlah) DESC, SUM(d.subtotal) DESC '
        . 'LIMIT :limit'
    );
    $stmt->bindValue(':from', $from);
    $stmt->bindValue(':to', $to);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('best-sellers.php: ' . $e->getMessage());
    json_response(['ok' => false, 'error' => 'Data menu terlaris tidak dapat dimuat.'], 500);
}

$items = array_map(static function (array $row): array {
    return [
        'menu_id'    => (int) $row['menu_id'],
        'nama_menu'  => (string) $row['nama_menu'],
        'gambar'     => (string) $row['gambar'],
        'terjual'    => (int) $row['terjual'],
        'pendapatan' => (float) $row['pendapatan'],
    ];
}, $rows);

json_response([
    'ok'    => true,
    'from'  => $from,
    'to'    => $to,
    'items' => $items,
]);