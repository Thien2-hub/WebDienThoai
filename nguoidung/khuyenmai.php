<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['quyen'] != 'admin') {
    header('Location: ../dangnhap.php');
    exit();
}

$message = '';
$error = '';

if (isset($_POST['them_km'])) {
    $tenkhuyenmai = trim($_POST['tenkhuyenmai']);
    $mota = trim($_POST['mota']);
    $phantram = isset($_POST['phantram']) ? intval($_POST['phantram']) : 0;
    $ngaybatdau = $_POST['ngaybatdau'];
    $ngayketthuc = $_POST['ngayketthuc'];
    
    if (empty($tenkhuyenmai)) {
        $error = "Vui lòng nhập tên khuyến mãi";
    } elseif ($phantram <= 0 || $phantram > 100) {
        $error = "Phần trăm giảm giá phải từ 1-100";
    } elseif (empty($ngaybatdau) || empty($ngayketthuc)) {
        $error = "Vui lòng chọn ngày bắt đầu và kết thúc";
    } elseif (strtotime($ngaybatdau) > strtotime($ngayketthuc)) {
        $error = "Ngày kết thúc phải sau ngày bắt đầu";
    } else {
        $stmt = $conn->prepare("INSERT INTO khuyenmai (tenkhuyenmai, mota, phantram, ngaybatdau, ngayketthuc) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $tenkhuyenmai, $mota, $phantram, $ngaybatdau, $ngayketthuc);
        
        if ($stmt->execute()) {
            $message = "Thêm khuyến mãi thành công";
        } else {
            $error = "Lỗi: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

if (isset($_GET['xoa'])) {
    $id = intval($_GET['xoa']);
    
    $stmt = $conn->prepare("DELETE FROM khuyenmai WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Đã xóa khuyến mãi";
    } else {
        $error = "Lỗi khi xóa: " . $stmt->error;
    }
    
    $stmt->close();
}

$result = $conn->query("SELECT * FROM khuyenmai ORDER BY ngaybatdau DESC");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý khuyến mãi</title>
    <style>
    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 8px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #f2f2f2;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 8px;
    }

    .success {
        color: green;
    }

    .error {
        color: red;
    }
    </style>
</head>

<body>
    <h2>Quản lý khuyến mãi</h2>

    <?php if (!empty($message)): ?>
    <div class="success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <h3>Thêm khuyến mãi mới</h3>
    <form method="post" action="">
        <div class="form-group">
            <label for="tenkhuyenmai">Tên khuyến mãi:</label>
            <input type="text" id="tenkhuyenmai" name="tenkhuyenmai" required>
        </div>

        <div class="form-group">
            <label for="mota">Mô tả:</label>
            <textarea id="mota" name="mota" rows="3"></textarea>
        </div>

        <div class="form-group">
            <label for="phantram">Phần trăm giảm giá (%):</label>
            <input type="number" id="phantram" name="phantram" min="1" max="100" required>
        </div>

        <div class="form-group">
            <label for="ngaybatdau">Ngày bắt đầu:</label>
            <input type="date" id="ngaybatdau" name="ngaybatdau" required>
        </div>

        <div class="form-group">
            <label for="ngayketthuc">Ngày kết thúc:</label>
            <input type="date" id="ngayketthuc" name="ngayketthuc" required>
        </div>

        <button type="submit" name="them_km">Thêm khuyến mãi</button>
    </form>

    <h3>Danh sách khuyến mãi</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Tên khuyến mãi</th>
            <th>Phần trăm</th>
            <th>Ngày bắt đầu</th>
            <th>Ngày kết thúc</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <?php 
                    $now = time();
                    $start = strtotime($row['ngaybatdau']);
                    $end = strtotime($row['ngayketthuc']);
                    
                    if ($now < $start) {
                        $status = "Sắp diễn ra";
                        $statusClass = "upcoming";
                    } elseif ($now > $end) {
                        $status = "Đã kết thúc";
                        $statusClass = "ended";
                    } else {
                        $status = "Đang diễn ra";
                        $statusClass = "active";
                    }
                ?>
        <tr>
            <td><?php echo htmlspecialchars($row['id']); ?></td>
            <td><?php echo htmlspecialchars($row['tenkhuyenmai']); ?></td>
            <td><?php echo htmlspecialchars($row['phantram']); ?>%</td>
            <td><?php echo htmlspecialchars($row['ngaybatdau']); ?></td>
            <td><?php echo htmlspecialchars($row['ngayketthuc']); ?></td>
            <td class="<?php echo $statusClass; ?>"><?php echo $status; ?></td>
            <td>
                <a href="?xoa=<?php echo $row['id']; ?>"
                    onclick="return confirm('Bạn có chắc muốn xóa khuyến mãi này?')">Xóa</a>
            </td>
        </tr>
        <?php endwhile; ?>
        <?php else: ?>
        <tr>
            <td colspan="7">Không có khuyến mãi nào.</td>
        </tr>
        <?php endif; ?>
    </table>

    <p><a href="admin_dashboard.php">Quay lại trang quản trị</a></p>
</body>

</html>