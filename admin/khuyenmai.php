<?php
include '../includes/db.php';
if (isset($_POST['tao'])) {
    $id_sp = $_POST['id_sp'];
    $giakm = $_POST['gia_km'];
    mysqli_query($conn, "UPDATE sanpham SET gia_km = $giakm WHERE id = $id_sp");
}
?>
<form method="post">
    ID SP: <input name="id_sp">
    Giá KM: <input name="gia_km">
    <button name="tao">Tạo KM</button>
</form>
