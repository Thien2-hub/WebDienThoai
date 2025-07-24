<?php
include 'includes/db.php';

$message = "";

if (isset($_POST['gui'])) {
    $email = trim($_POST['email']);
    $noidung = trim($_POST['noidung']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email không hợp lệ!";
    } elseif (empty($noidung)) {
        $message = "Bạn chưa nhập nội dung!";
    } else {
        $stmt = $conn->prepare("INSERT INTO hotro(email, noidung, ngaygui) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $email, $noidung);

        if ($stmt->execute()) {
            $message = "Đã gửi yêu cầu thành công.";
        } else {
            $message = "Gửi yêu cầu thất bại: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<?php if ($message): ?>
<p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="post" action="">
    Email: <input name="email" type="email" required><br>
    Nội dung: <textarea name="noidung" required></textarea><br>
    <button name="gui" type="submit">Gửi</button>
</form>