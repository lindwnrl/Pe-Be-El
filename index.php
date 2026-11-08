<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/session-boot.php';

start_session();

// Guests land on the public landing page; signed-in users go to the app.
$target = isset($_SESSION['username']) ? '/Menu.php' : '/Dashboard.php';

header('Location: ' . $target, true, 302);
exit;
