<?php
session_start();
include 'includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['quyen'] != 'admin') {
    header('Location: ../dangnhap.php');
    exit();
}

if (isset($_GET['madon']) && isset($_GET['trangthai'])) {
    $madon = intval($_GET['madon']);
    $trangthai = $_GET['trangthai'];
    
    $stmt = $conn->prepare("UPDATE donhang SET trangthai = ? WHERE madon = ?");
    $stmt->bind_param("si", $trangthai, $madon);
    if ($stmt->execute()) {
        header("Location: quanly_donhang.php?msg=success");
    } else {
        echo "Lỗi cập nhật trạng thái.";
    }
} else {
    echo "Thiếu thông tin đơn hàng.";
}
?>