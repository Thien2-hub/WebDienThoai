<?php
include '../includes/db.php';
$ds = mysqli_query($conn, "SELECT * FROM khachhang");
while($u = mysqli_fetch_assoc($ds)) {
    echo "<div>{$u['ten']} - {$u['email']}</div>";
}
?>