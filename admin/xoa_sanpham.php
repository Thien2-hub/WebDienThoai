<?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: dangnhap.php');
    exit();
}

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("DELETE FROM sanpham WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header('Location: admin_sanpham.php');
exit();