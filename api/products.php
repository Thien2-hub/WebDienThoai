<?php
include '../includes/db.php';
header('Content-Type: application/json');

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

$sql = "SELECT * FROM sanpham";
if ($category_id > 0) {
    $sql .= " WHERE danhmuc_id = $category_id";
}
$sql .= " ORDER BY id DESC";

$result = $conn->query($sql);
$products = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

echo json_encode($products);