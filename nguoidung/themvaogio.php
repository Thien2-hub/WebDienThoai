<?php
session_start();
include 'includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sl = isset($_GET['sl']) ? intval($_GET['sl']) : 1;
if ($sl < 1) $sl = 1;

if ($id > 0) {
    if (!isset($_SESSION['gio'])) {
        $_SESSION['gio'] = [];
    }

    if (isset($_SESSION['gio'][$id])) {
        $_SESSION['gio'][$id] += $sl;
    } else {
        $_SESSION['gio'][$id] = $sl;
    }
}

header('Location: giohang.php');
exit();
