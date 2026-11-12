<?php
declare(strict_types=1);

/**
 * Public read-only stock endpoint for the customer-facing menu.
 *
 * Exposes only what the menu page needs: which product is still orderable.
 * No prices, categories, or admin fields.
 */

require __DIR__ . '/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    json_response(['ok' => false, 'error' => 'Metode tidak diizinkan.'], 405);
}

try {
    $rows = db()->query(
        'SELECT menu_id, nama_menu, stok FROM menu WHERE aktif = TRUE ORDER BY menu_id'
    )->fetchAll();
} catch (PDOException $e) {
    error_log('menu-stock.php: ' . $e->getMessage());
    json_response(['ok' => false, 'error' => 'Data stok tidak tersedia.'], 500);
}

$items = array_map(static function (array $row): array {
    $stok = (int) $row['stok'];

    return [
        'menu_id'   => (int) $row['menu_id'],
        'nama_menu' => (string) $row['nama_menu'],
        'stok'      => $stok,
        'tersedia'  => $stok > 0,
    ];
}, $rows);

json_response(['ok' => true, 'items' => $items]);