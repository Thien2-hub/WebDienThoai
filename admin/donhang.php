<?php
include '../includes/db.php';
$ds = mysqli_query($conn, "SELECT * FROM donhang");
while($r = mysqli_fetch_assoc($ds)) {
    echo "<div>Đơn #{$r['id']} - Trạng thái: {$r['trangthai']}
    <form method='post'><input type='hidden' name='id' value='{$r['id']}'><select name='tt'>
    <option>Chờ xử lý</option><option>Đang giao</option><option>Hoàn thành</option></select>
    <button name='capnhat'>Cập nhật</button></form></div>";
}
if (isset($_POST['capnhat'])) {
    $id = $_POST['id'];
    $tt = $_POST['tt'];
    mysqli_query($conn, "UPDATE donhang SET trangthai='$tt' WHERE id=$id");
}
?>