<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['quyen'] !== 'admin') {
    header('Location: ../dangnhap.php');
    exit();
}

if (isset($_POST['update_status'])) {
    $madon = (int)$_POST['madon'];
    $trangthai = $_POST['trangthai'];

    $allowed_status = ['Chờ xử lý', 'Đang giao', 'Đã hoàn thành', 'Đã hủy'];
    if (in_array($trangthai, $allowed_status)) {
        $update_sql = "UPDATE donhang SET trangthai = ? WHERE id = ?";
        $stmt_update = $conn->prepare($update_sql);
        $stmt_update->bind_param("si", $trangthai, $madon);
        $stmt_update->execute();
        $stmt_update->close();
        $message = "Cập nhật trạng thái đơn hàng #$madon thành công.";
    } else {
        $message = "Trạng thái không hợp lệ!";
    }
}

$sql = "SELECT d.*, u.email FROM donhang d 
        LEFT JOIN users u ON d.user_id = u.id
        ORDER BY d.ngaydat DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng</title>
    <style>
    table {
        border-collapse: collapse;
        width: 100%;
    }

    th,
    td {
        border: 1px solid #ccc;
        padding: 8px;
    }

    th {
        background: #eee;
    }

    select {
        width: 150px;
    }
    </style>
</head>

<body>
    <h2>Quản lý đơn hàng</h2>
    <?php if (!empty($message)) {
        echo "<p style='color:green;'>$message</p>";
    } ?>

    <table>
        <tr>
            <th>Mã đơn</th>
            <th>Khách hàng</th>
            <th>Ngày đặt</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
            <th>Chi tiết</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['id']); ?></td>
            <td><?php echo htmlspecialchars($row['email'] ?? 'Khách vãng lai'); ?></td>
            <td><?php echo htmlspecialchars($row['ngaydat']); ?></td>
            <td><?php echo number_format($row['tongtien']); ?> VNĐ</td>
            <td>
                <form method="post" style="margin:0;">
                    <input type="hidden" name="madon" value="<?php echo $row['id']; ?>">
                    <select name="trangthai" onchange="this.form.submit()">
                        <?php
                            $statuses = ['Chờ xử lý', 'Đang giao', 'Đã hoàn thành', 'Đã hủy'];
                            foreach ($statuses as $status) {
                                $selected = ($row['trangthai'] === $status) ? "selected" : "";
                                echo "<option value=\"$status\" $selected>$status</option>";
                            }
                            ?>
                    </select>
                    <input type="hidden" name="update_status" value="1">
                </form>
            </td>
            <td><a href="chitietdonhang.php?madon=<?php echo $row['id']; ?>">Xem</a></td>
        </tr>
        <?php } ?>
    </table>
</body>

</html>