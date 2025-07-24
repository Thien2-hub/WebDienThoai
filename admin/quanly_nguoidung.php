<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['quyen'] != 'admin') {
    header('Location: ../dangnhap.php');
    exit();
}

$message = '';
$error = '';

if (isset($_GET['xoa'])) {
    $id = intval($_GET['xoa']);
    
    if ($id == $_SESSION['user_id']) {
        $error = "Không thể xóa tài khoản của chính bạn!";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = "Đã xóa người dùng thành công";
        } else {
            $error = "Lỗi khi xóa người dùng: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

if (isset($_POST['capnhat_quyen'])) {
    $id = intval($_POST['user_id']);
    $quyen = $_POST['quyen'];
    
    if ($id == $_SESSION['user_id']) {
        $error = "Không thể thay đổi quyền của chính bạn!";
    } else {
        $stmt = $conn->prepare("UPDATE users SET quyen = ? WHERE id = ?");
        $stmt->bind_param("si", $quyen, $id);
        
        if ($stmt->execute()) {
            $message = "Đã cập nhật quyền thành công";
        } else {
            $error = "Lỗi khi cập nhật quyền: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where_clause = "";
$params = [];
$types = "";

if (!empty($search)) {
    $where_clause = "WHERE ten LIKE ? OR email LIKE ? OR sdt LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
    $types = "sss";
}

$count_sql = "SELECT COUNT(*) as total FROM users $where_clause";
$count_stmt = $conn->prepare($count_sql);

if (!empty($types)) {
    $count_stmt->bind_param($types, ...$params);
}

$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

$sql = "SELECT * FROM users $where_clause ORDER BY id DESC LIMIT ?, ?";

$stmt = $conn->prepare($sql);

if (!empty($types)) {
    $params[] = $offset;
    $params[] = $limit;
    $types .= "ii";
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $offset, $limit);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý người dùng</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f4;
    }

    .container {
        display: flex;
        min-height: 100vh;
    }

    .sidebar {
        width: 250px;
        background-color: #333;
        color: white;
        padding: 20px 0;
    }

    .sidebar h2 {
        text-align: center;
        margin-bottom: 30px;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
    }

    .sidebar li {
        padding: 10px 20px;
    }

    .sidebar li:hover {
        background-color: #444;
    }

    .sidebar a {
        color: white;
        text-decoration: none;
        display: block;
    }

    .main-content {
        flex: 1;
        padding: 20px;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .search-form {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .search-form input,
    .search-form button {
        padding: 8px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background-color: white;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    table th,
    table td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    table th {
        background-color: #f2f2f2;
    }

    .pagination {
        margin-top: 20px;
        display: flex;
        justify-content: center;
        gap: 5px;
    }

    .pagination a,
    .pagination span {
        padding: 8px 12px;
        border: 1px solid #ddd;
        text-decoration: none;
        color: #333;
    }

    .pagination a:hover {
        background-color: #f5f5f5;
    }

    .pagination .active {
        background-color: #4CAF50;
        color: white;
        border-color: #4CAF50;
    }

    .success {
        color: green;
        margin-bottom: 10px;
    }

    .error {
        color: red;
        margin-bottom: 10px;
    }

    .action-links a {
        margin-right: 10px;
        text-decoration: none;
    }

    .role-form {
        display: flex;
        gap: 5px;
    }

    .role-form select {
        padding: 5px;
    }

    .role-form button {
        padding: 5px 10px;
        background-color: #4CAF50;
        color: white;
        border: none;
        cursor: pointer;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="admin_dashboard.php">Trang chủ</a></li>
                <li><a href="admin_sanpham.php">Quản lý sản phẩm</a></li>
                <li><a href="quanly_donhang.php">Quản lý đơn hàng</a></li>
                <li><a href="quanly_nguoidung.php">Quản lý người dùng</a></li>
                <li><a href="khuyenmai.php">Quản lý khuyến mãi</a></li>
                <li><a href="hoidap.php">Hỏi đáp</a></li>
                <li><a href="thongke.php">Thống kê</a></li>
                <li><a href="../index.php">Xem trang web</a></li>
                <li><a href="../dangxuat.php">Đăng xuất</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Quản lý người dùng</h1>
            </div>

            <?php if (!empty($message)): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form class="search-form" method="GET" action="">
                <input type="text" name="search" placeholder="Tìm kiếm người dùng..."
                    value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Tìm kiếm</button>
                <a href="quanly_nguoidung.php" style="padding: 8px; text-decoration: none;">Làm mới</a>
            </form>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Quyền</th>
                    <th>Ngày đăng ký</th>
                    <th>Thao tác</th>
                </tr>
                <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['ten']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['sdt']); ?></td>
                    <td>
                        <?php if ($row['id'] == $_SESSION['user_id']): ?>
                        <?php echo htmlspecialchars($row['quyen']); ?>
                        <?php else: ?>
                        <form class="role-form" method="post" action="">
                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                            <select name="quyen">
                                <option value="user" <?php echo $row['quyen'] == 'user' ? 'selected' : ''; ?>>User
                                </option>
                                <option value="admin" <?php echo $row['quyen'] == 'admin' ? 'selected' : ''; ?>>Admin
                                </option>
                            </select>
                            <button type="submit" name="capnhat_quyen">Cập nhật</button>
                        </form>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($row['ngaydangky'])); ?></td>
                    <td class="action-links">
                        <?php if ($row['id'] != $_SESSION['user_id']): ?>
                        <a href="?xoa=<?php echo $row['id']; ?>"
                            onclick="return confirm('Bạn có chắc muốn xóa người dùng này?')">Xóa</a>
                        <?php else: ?>
                        <span style="color: #999;">Tài khoản hiện tại</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr>
                    <td colspan="7">Không có người dùng nào.</td>
                </tr>
                <?php endif; ?>
            </table>

            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&laquo; Đầu</a>
                <a
                    href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&lsaquo;
                    Trước</a>
                <?php endif; ?>

                <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                <?php if ($i == $page): ?>
                <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                <a
                    href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                <a
                    href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Sau
                    &rsaquo;</a>
                <a
                    href="?page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Cuối
                    &raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>