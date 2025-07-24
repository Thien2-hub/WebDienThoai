<?php
include 'includes/db.php';

if (isset($_POST['gui'])) {
    if (empty($_POST['id_dh']) || !is_numeric($_POST['id_dh'])) {
        echo "Mã đơn hàng không hợp lệ.";
    } else if (empty(trim($_POST['lydo']))) {
        echo "Bạn cần nhập lý do đổi trả.";
    } else {
        $id_dh = (int)$_POST['id_dh'];
        $lydo = trim($_POST['lydo']);

        $stmt = $conn->prepare("INSERT INTO doitra (id_dh, lydo, ngaygui) VALUES (?, ?, NOW())");
        if ($stmt === false) {
            die("Lỗi chuẩn bị câu truy vấn: " . htmlspecialchars($conn->error));
        }
        $stmt->bind_param("is", $id_dh, $lydo);

        if ($stmt->execute()) {
            echo "Yêu cầu đổi trả đã được gửi.";
        } else {
            echo "Lỗi khi gửi yêu cầu đổi trả: " . htmlspecialchars($stmt->error);
        }

        $stmt->close();
    }
}
?>

<form method="post" action="">
    Mã đơn hàng: <input name="id_dh" required><br>
    Lý do: <textarea name="lydo" required></textarea><br>
    <button type="submit" name="gui">Gửi yêu cầu</button>
</form>