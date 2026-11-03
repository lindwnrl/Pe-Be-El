<?php
declare(strict_types=1);

/**
 * Pe Be El — PostgreSQL connection shared by the authentication endpoints.
 * Edit the DB_* constants to match your local PostgreSQL instance.
 */

const DB_HOST = 'localhost';
const DB_PORT = '5432';
const DB_NAME = 'ninarasa';
const DB_USER = 'postgres';
const DB_PASS = 'postgres';

/** Lazily-open singleton PDO connection. */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', DB_HOST, DB_PORT, DB_NAME);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

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
