<?php
include '../includes/db.php';
$ds = mysqli_query($conn, "SELECT ngaydat, SUM(ct.soluong * sp.gia_km) as doanhthu
FROM donhang dh JOIN chitietdonhang ct ON dh.id=ct.id_dh
JOIN sanpham sp ON ct.id_sp=sp.id GROUP BY ngaydat");
while($r = mysqli_fetch_assoc($ds)) {
    echo "<div>{$r['ngaydat']} - Doanh thu: {$r['doanhthu']}đ</div>";
}
?>