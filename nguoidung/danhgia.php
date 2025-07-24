<?php
session_start();
include 'includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID sản phẩm không hợp lệ');
}

$id_sp = (int)$_GET['id'];

if (!isset($_SESSION['ten'])) {
    die('Bạn cần đăng nhập để đánh giá sản phẩm.');
}

if (isset($_POST['danhgia'])) {
    $nd = trim($_POST['noidung']);
    $ten = $_SESSION['ten'];

    if (empty($nd)) {
        echo "Nội dung đánh giá không được để trống.";
    } else {
        $stmt = $conn->prepare("INSERT INTO danhgia (id_sp, ten_nguoidung, noidung) VALUES (?, ?, ?)");
        if ($stmt === false) {
            die('Lỗi chuẩn bị câu truy vấn: ' . htmlspecialchars($conn->error));
        }
        $stmt->bind_param("iss", $id_sp, $ten, $nd);

        if ($stmt->execute()) {
            echo "Cảm ơn bạn đã đánh giá!";
        } else {
            echo "Lỗi khi gửi đánh giá: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}
?>

<form method="post" action="">
    <input type="hidden" name="id_sp" value="<?= htmlspecialchars($id_sp) ?>">
    <textarea name="noidung" required></textarea>
    <button type="submit" name="danhgia">Gửi đánh giá</button>
</form>