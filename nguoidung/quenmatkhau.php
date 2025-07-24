<?php
include 'includes/db.php';
session_start();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['emailOrPhone'])) {
        $emailOrPhone = trim($_POST['emailOrPhone']);

        $sql = "SELECT id FROM users WHERE email = ? OR sdt = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $emailOrPhone, $emailOrPhone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $message = "Không tìm thấy tài khoản với thông tin này!";
        } else {
            $user = $result->fetch_assoc();
            $_SESSION['reset_user_id'] = $user['id'];
            $_SESSION['show_reset'] = true;
        }

        $stmt->close();
    } elseif (isset($_POST['newPassword'])) {
        if (!isset($_SESSION['reset_user_id'])) {
            $message = "Phiên làm việc hết hạn, vui lòng thử lại.";
        } else {
            $newPassword = $_POST['newPassword'];
            if (strlen($newPassword) < 6) {
                $message = "Mật khẩu mới phải ít nhất 6 ký tự.";
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $hashedPassword, $_SESSION['reset_user_id']);
                if ($stmt->execute()) {
                    $message = "Mật khẩu đã được đặt lại thành công!";
                    unset($_SESSION['reset_user_id']);
                    unset($_SESSION['show_reset']);
                } else {
                    $message = "Có lỗi xảy ra, vui lòng thử lại.";
                }
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <title>Quên Mật Khẩu</title>
</head>

<body>
    <h2>Quên Mật Khẩu</h2>

    <?php if (!empty($message)): ?>
    <p style="color:red"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if (empty($_SESSION['show_reset'])): ?>
    <form method="post">
        <label for="emailOrPhone">Nhập Email hoặc Số điện thoại:</label>
        <input type="text" id="emailOrPhone" name="emailOrPhone" required />
        <button type="submit">Xác nhận</button>
    </form>
    <?php else: ?>
    <form method="post">
        <label for="newPassword">Nhập mật khẩu mới:</label>
        <input type="password" id="newPassword" name="newPassword" required minlength="6" />
        <button type="submit">Đặt lại mật khẩu</button>
    </form>
    <?php endif; ?>
</body>

</html>