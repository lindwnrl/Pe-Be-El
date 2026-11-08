<?php
/**
 * Router shim for emulating Vercel routing locally.
 *
 * Vercel rewrites every .php request and "/" to api/index.php, then lets real
 * files through untouched (vercel.json). Serving with:
 *
 *     php -S 127.0.0.1:8030 router.php
 *
 * reproduces that behaviour, with one addition: only `assets/` is treated as
 * publicly downloadable. `sql/`, `includes/`, and dotfiles are never served,
 * matching `.vercelignore` in production.
 *
 * For ordinary development keep using `php -S . -t .`, which bypasses this file.
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// PHP entry points always go through the front controller and its whitelist.
if ($path === '/' || preg_match('/\.php$/i', $path)) {
    require __DIR__ . '/api/index.php';
    return true;
}

// Only assets/ is publicly downloadable.
$isPublicAsset = str_starts_with($path, '/assets/')
    && !str_contains($path, '..')
    && is_file(__DIR__ . $path);

if ($isPublicAsset) {
    return false; // let the built-in server serve it
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo "404 Not Found\n";
return true;
