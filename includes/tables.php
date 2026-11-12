<?php
declare(strict_types=1);

/**
 * Floor plan definition.
 *
 * One source of truth for the table ids and their seating capacity, shared by
 * the page that renders them (Kursi.php) and the endpoint that validates a
 * booking (api/reservasi.php).
 */
function nina_tables(): array
{
    return [
        'MO1'  => 4,
        'MO2'  => 4,
        'MO3'  => 4,
        'MO4'  => 6,
        'MO5'  => 6,
        'MO6'  => 2,
        'MO7'  => 2,
        'MO8'  => 4,
        'MO9'  => 4,
        'MO10' => 8,
    ];
}