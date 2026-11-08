<?php
declare(strict_types=1);

/**
 * Front controller for serverless hosting.
 *
 * Vercel serves one PHP entry point per function, so every page request is
 * rewritten here (see vercel.json) and dispatched to the matching file. Only
 * paths on the whitelist below can be reached — arbitrary includes are refused.
 *
 * Local development does not use this file: `php -S` serves the pages directly.
 */

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$uri = '/' . ltrim(rawurldecode($uri), '/');

$routes = [
    '/'                       => '/index.php',
    '/index.php'              => '/index.php',

    '/Login.php'              => '/Login.php',
    '/Register.php'           => '/Register.php',
    '/Dashboard.php'          => '/Dashboard.php',
    '/Menu.php'               => '/Menu.php',
    '/Kursi.php'              => '/Kursi.php',
    '/Profile.php'            => '/Profile.php',
    '/Admin.php'              => '/Admin.php',
    '/Owner.php'              => '/Owner.php',
    '/User.php'               => '/User.php',

    '/api/login.php'          => '/api/login.php',
    '/api/logout.php'         => '/api/logout.php',
    '/api/register.php'       => '/api/register.php',
    '/api/profile.php'        => '/api/profile.php',
    '/api/menu-public.php'    => '/api/menu-public.php',
    '/api/menu-stock.php'     => '/api/menu-stock.php',
    '/api/admin/menu-stock.php' => '/api/admin/menu-stock.php',
];

$target = $routes[$uri] ?? null;

if ($target === null) {
    http_response_code(404);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8">'
        . '<title>404 — Nina Rasa</title></head><body>'
        . '<p>Halaman tidak ditemukan. <a href="/Dashboard.php">Kembali ke beranda</a>.</p>'
        . '</body></html>';
    exit;
}

$path = dirname(__DIR__) . $target;

if (!is_file($path)) {
    http_response_code(404);
    exit;
}

require $path;
