<?php
include '../includes/db.php';
$ds = mysqli_query($conn, "SELECT * FROM danhmuc");
while($row = mysqli_fetch_assoc($ds)) {
    echo "<div>{$row['ten']} <a href='xoa.php?id={$row['id']}'>Xoá</a></div>";
}
?>