<?php
session_start();

$count = 0;
if (isset($_SESSION['gio']) && is_array($_SESSION['gio'])) {
    foreach ($_SESSION['gio'] as $quantity) {
        $count += $quantity;
    }
}

header('Content-Type: application/json');
echo json_encode(['count' => $count]);