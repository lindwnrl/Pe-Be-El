<?php
declare(strict_types=1);

/**
 * PostgreSQL-backed PHP session handler.
 *
 * Needed because serverless instances do not share a filesystem. Sessions are
 * keyed by PHP's own session id and stored verbatim (PHP has already serialized
 * the data), so no serialization format has to be maintained here.
 */
final class PdoSessionHandler implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    private int $ttl;

    public function __construct(
        private PDO $pdo,
        private string $table = 'php_sessions',
        int $ttlSeconds = 7200
    ) {
        $this->ttl = $ttlSeconds;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT data FROM ' . $this->table . ' WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? '' : (string) $row['data'];
    }

    public function write(string $id, string $data): bool
    {
        // PHP serializes an empty $_SESSION to ''. Storing a row for every
        // anonymous visitor would be pure overhead, so drop it instead.
        if ($data === '') {
            return $this->destroy($id);
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO ' . $this->table . ' (id, data, last_activity)
             VALUES (:id, :data, :now)
             ON CONFLICT (id) DO UPDATE
             SET data = EXCLUDED.data, last_activity = EXCLUDED.last_activity'
        );

        return $stmt->execute([
            ':id'   => $id,
            ':data' => $data,
            ':now'  => time(),
        ]);
    }

    public function destroy(string $id): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM ' . $this->table . ' WHERE id = :id'
        );

        return $stmt->execute([':id' => $id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM ' . $this->table . ' WHERE last_activity < :cutoff'
        );
        $stmt->execute([':cutoff' => time() - $this->ttl]);

        return $stmt->rowCount();
    }

    public function validateId(string $id): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM ' . $this->table . ' WHERE id = :id AND last_activity >= :cutoff'
        );
        $stmt->execute([':id' => $id, ':cutoff' => time() - $this->ttl]);

        return $stmt->fetchColumn() !== false;
    }

    public function updateTimestamp(string $id, string $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE ' . $this->table . ' SET last_activity = :now WHERE id = :id'
        );

        return $stmt->execute([':id' => $id, ':now' => time()]);
    }
}
