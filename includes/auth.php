<?php
declare(strict_types=1);

/**
 * Session- and role-based access helpers.
 *
 * Role is read exclusively from the server-side session, which api/login.php
 * populates after verifying credentials. Never trust a request parameter.
 */

/** Start the PHP session once. */
function auth_start(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

/**
 * Current authenticated user, or null.
 *
 * @return array{akun_id:int, username:string, role:string}|null
 */
function auth_user(): ?array
{
    auth_start();

    if (empty($_SESSION['akun_id'])) {
        return null;
    }

    return [
        'akun_id'  => (int) $_SESSION['akun_id'],
        'username' => (string) ($_SESSION['username'] ?? ''),
        'role'     => (string) ($_SESSION['role'] ?? ''),
    ];
}

/** True when the current session belongs to an admin. */
function auth_is_admin(): bool
{
    $user = auth_user();
    return $user !== null && $user['role'] === 'admin';
}

/**
 * Guard for JSON endpoints: emit 401/403 and stop when not an admin.
 * Requires api/db.php (for json_response) to be loaded first.
 */
function require_admin(): void
{
    $user = auth_user();

    if ($user === null) {
        json_response(['ok' => false, 'error' => 'Login diperlukan.'], 401);
    }

    if ($user['role'] !== 'admin') {
        json_response(['ok' => false, 'error' => 'Akses ditolak.'], 403);
    }
}

/** Guard for HTML pages: redirect non-admins to the login page. */
function require_admin_page(): void
{
    if (!auth_is_admin()) {
        header('Location: /Login.php', true, 302);
        exit;
    }
}