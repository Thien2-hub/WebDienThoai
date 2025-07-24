<?php
session_start();
include '../connect.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['quyen'] !== 'admin') {
    header('Location: ../dangnhap.php');
    exit();
}

$madon = isset($_GET['madon']) ? (int)$_GET['madon'] : 0;

$sql = "SELECT ct.*, sp.tensanpham FROM chitietdonhang ct 
        JOIN sanpham sp ON ct.masp = sp.masp 
        WHERE ct.madon = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Lỗi prepare: " . $conn->error);
}

$stmt->bind_param("i", $madon);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng #<?php echo htmlspecialchars($madon); ?></title>
</head>

<body>
    <h2>Chi tiết đơn hàng #<?php echo htmlspecialchars($madon); ?></h2>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>Sản phẩm</th>
            <th>Số lượng</th>
            <th>Giá</th>
            <th>Thành tiền</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['tensanpham']); ?></td>
            <td><?php echo (int)$row['soluong']; ?></td>
            <td><?php echo number_format($row['gia'], 0, ',', '.'); ?>đ</td>
            <td><?php echo number_format($row['soluong'] * $row['gia'], 0, ',', '.'); ?>đ</td>
        </tr>
        <?php } ?>
    </table>
    <br>
    <a href="quanly_donhang.php">← Quay lại danh sách</a>
</body>

</html>