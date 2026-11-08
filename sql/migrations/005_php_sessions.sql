-- Migration 005: session storage for serverless deployments.
--
-- Vercel functions are stateless: two requests from the same visitor may land on
-- different instances, and /tmp is per-instance and ephemeral. PHP's default
-- file-based sessions therefore lose the login between requests.
--
-- This table backs a PDO session handler so sessions survive across invocations.
-- Local development keeps using the same handler, which also keeps behaviour
-- identical between environments.

CREATE TABLE IF NOT EXISTS php_sessions (
    id            TEXT PRIMARY KEY,
    data          TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS php_sessions_last_activity_idx
    ON php_sessions (last_activity);
