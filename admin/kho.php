<?php
include '../includes/db.php';
$ds = mysqli_query($conn, "SELECT * FROM sanpham");
while($r = mysqli_fetch_assoc($ds)) {
    echo "<div>{$r['ten']} - Tồn kho: {$r['soluong']}</div>";
}
?>
