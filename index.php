<?php
declare(strict_types=1);

session_start();

$target = isset($_SESSION['username']) ? '/Dashboard.php' : '/Login.php';

header('Location: ' . $target, true, 302);
exit;
