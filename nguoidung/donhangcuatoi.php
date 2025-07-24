<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['id']) || !is_numeric($_SESSION['id'])) {
    echo "Bạn chưa đăng nhập hoặc thông tin không hợp lệ.";
    exit;
}

$id_kh = (int)$_SESSION['id'];

$stmt = $conn->prepare("SELECT id, ngaydat, trangthai FROM donhang WHERE id_kh = ?");
if (!$stmt) {
    die("Lỗi chuẩn bị câu truy vấn: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $id_kh);

if (!$stmt->execute()) {
    die("Lỗi thực thi truy vấn: " . htmlspecialchars($stmt->error));
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div>Không có đơn hàng nào.</div>";
} else {
    while ($row = $result->fetch_assoc()) {
        $trangthai_text = htmlspecialchars($row['trangthai']);

        echo "<div>Mã đơn: " . htmlspecialchars($row['id']) . 
             " - Ngày: " . htmlspecialchars($row['ngaydat']) . 
             " - Trạng thái: " . $trangthai_text . "</div>";
    }
}

$stmt->close();
?>