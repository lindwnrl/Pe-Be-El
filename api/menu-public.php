<?php
declare(strict_types=1);

/**
 * Public menu catalog for the landing page.
 *
 * Read-only. Exposes everything a marketing card needs (name, price, category,
 * image, availability) but nothing administrative.
 */

require __DIR__ . '/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    json_response(['ok' => false, 'error' => 'Metode tidak diizinkan.'], 405);
}

try {
    $rows = db()->query(
        'SELECT m.menu_id, m.nama_menu, m.harga, m.stok, m.gambar, '
        . 'k.nama_kategori '
        . 'FROM menu m '
        . 'LEFT JOIN kategori_menu k ON k.kategori_id = m.kategori_id '
        . 'WHERE m.aktif = TRUE '
        . 'ORDER BY m.menu_id'
    )->fetchAll();
} catch (PDOException $e) {
    error_log('menu-public.php: ' . $e->getMessage());
    json_response(['ok' => false, 'error' => 'Data menu tidak tersedia.'], 500);
}

$items = array_map(static function (array $row): array {
    $stok = (int) $row['stok'];

    return [
        'menu_id'   => (int) $row['menu_id'],
        'nama_menu' => (string) $row['nama_menu'],
        'harga'     => (float) $row['harga'],
        'kategori'  => $row['nama_kategori'] !== null ? (string) $row['nama_kategori'] : '',
        'gambar'    => $row['gambar'] !== null ? (string) $row['gambar'] : '',
        'stok'      => $stok,
        'tersedia'  => $stok > 0,
    ];
}, $rows);

json_response(['ok' => true, 'items' => $items]);
