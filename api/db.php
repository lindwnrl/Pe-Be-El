<?php
declare(strict_types=1);

/**
 * PostgreSQL connection shared by every endpoint.
 *
 * Configuration comes from the environment so the same code runs locally and
 * on Vercel. Resolution order:
 *
 *   1. DATABASE_URL (Neon / Supabase / Vercel Postgres connection string)
 *   2. DB_HOST / DB_PORT / DB_NAME / DB_USER / DB_PASS
 *   3. localhost defaults (local development)
 */

/** Read an env var without leaking a warning when it is absent. */
function db_env(string $key): ?string
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        return null;
    }
    return $value;
}

/** Build the PDO DSN from a DATABASE_URL style connection string. */
function db_dsn_from_url(string $url): string
{
    $parts = parse_url($url);
    if ($parts === false || empty($parts['host'])) {
        throw new RuntimeException('DATABASE_URL tidak valid.');
    }

    $dsn = 'pgsql:host=' . $parts['host'];
    $dsn .= ';port=' . ($parts['port'] ?? 5432);
    $dsn .= ';dbname=' . ltrim($parts['path'] ?? '/postgres', '/');

    // Managed providers require TLS; keep whatever the provider asked for.
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query);
        if (!empty($query['sslmode'])) {
            $dsn .= ';sslmode=' . $query['sslmode'];
        }
    }

    return $dsn;
}

/** Credentials for the DSN produced by db_dsn_from_url(). */
function db_credentials_from_url(string $url): array
{
    $parts = parse_url($url);

    return [
        rawurldecode($parts['user'] ?? 'postgres'),
        rawurldecode($parts['pass'] ?? ''),
    ];
}

/** Lazily-open singleton PDO connection. */
function db(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $url = db_env('DATABASE_URL') ?? db_env('POSTGRES_URL');

    if ($url !== null) {
        [$user, $pass] = db_credentials_from_url($url);
        $dsn = db_dsn_from_url($url);
    } else {
        $host = db_env('DB_HOST') ?? 'localhost';
        $port = db_env('DB_PORT') ?? '5432';
        $name = db_env('DB_NAME') ?? 'ninarasa';
        $user = db_env('DB_USER') ?? 'postgres';
        $pass = db_env('DB_PASS') ?? 'postgres';

        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $name);

        // Remote databases require TLS; a local socket does not.
        if ($host !== 'localhost' && $host !== '127.0.0.1') {
            $dsn .= ';sslmode=require';
        }
    }

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}

/** Read request input from a JSON body (fetch) or a regular form POST. */
function request_input(): array
{
    $raw = file_get_contents('php://input');
    if (is_string($raw) && $raw !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }

    return $_POST;
}

/** Emit a JSON response and stop. */
function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}
