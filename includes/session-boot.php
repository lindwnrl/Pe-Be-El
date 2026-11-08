<?php
declare(strict_types=1);

/**
 * Single entry point for starting a PHP session.
 *
 * Serverless platforms (Vercel) give every request a fresh, non-shared
 * filesystem, so PHP's default file-based sessions cannot hold a login between
 * requests. When the database is reachable, sessions are stored in PostgreSQL
 * instead; otherwise we fall back to the default handler so local development
 * without a database still works.
 */

require_once __DIR__ . '/PdoSessionHandler.php';

/** True when the session table can be queried. Probed once per request. */
function session_store_ready(): bool
{
    static $ready = null;

    if ($ready !== null) {
        return $ready;
    }

    try {
        require_once __DIR__ . '/../api/db.php';
        db()->query('SELECT 1 FROM php_sessions LIMIT 0');
        $ready = true;
    } catch (Throwable $e) {
        error_log('session store unavailable, falling back to files: ' . $e->getMessage());
        $ready = false;
    }

    return $ready;
}

/** Start the session with hardened cookie settings. Safe to call twice. */
function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'secure'   => $https,
        'samesite' => 'Lax',
    ]);

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    if (session_store_ready()) {
        $handler = new PdoSessionHandler(db(), 'php_sessions', 7200);
        session_set_save_handler($handler, true);
    }

    session_start();
}
