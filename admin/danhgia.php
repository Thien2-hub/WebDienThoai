<?php
include '../includes/db.php';
$ds = mysqli_query($conn, "SELECT * FROM danhgia");
while($r = mysqli_fetch_assoc($ds)) {
    echo "<p>{$r['ten_nguoidung']}: {$r['noidung']} <a href='xoadg.php?id={$r['id']}'>Xoá</a></p>";
}
?>