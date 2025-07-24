<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['quyen'] != 'admin') {
    header('Location: ../dangnhap.php');
    exit();
}

$total_products = $conn->query("SELECT COUNT(*) as count FROM sanpham")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM donhang")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE quyen = 'user'")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(tongtien) as total FROM donhang WHERE trangthai = 'Đã hoàn thành'")->fetch_assoc()['total'] ?? 0;

$recent_orders = $conn->query("SELECT * FROM donhang ORDER BY ngaydat DESC LIMIT 5");

$best_sellers = $conn->query("SELECT sp.id, sp.tensanpham, sp.giaban, SUM(ct.soluong) as total_sold 
                             FROM sanpham sp 
                             JOIN chitietdonhang ct ON sp.id = ct.masp 
                             JOIN donhang dh ON ct.madon = dh.id 
                             WHERE dh.trangthai != 'Đã hủy' 
                             GROUP BY sp.id 
                             ORDER BY total_sold DESC 
                             LIMIT 5");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Trang quản trị</title>
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

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .stats-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: white;
        border-radius: 5px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        flex: 1;
        min-width: 200px;
    }

    .stat-card h3 {
        margin-top: 0;
        color: #555;
    }

    .stat-card .value {
        font-size: 24px;
        font-weight: bold;
        margin: 10px 0;
    }

    .recent-section {
        background-color: white;
        border-radius: 5px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
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
            <div class="dashboard-header">
                <h1>Bảng điều khiển</h1>
                <p>Xin chào, <?php echo htmlspecialchars($_SESSION['ten'] ?? 'Admin'); ?></p>
            </div>

            <div class="stats-container">
                <div class="stat-card">
                    <h3>Tổng sản phẩm</h3>
                    <div class="value"><?php echo number_format($total_products); ?></div>
                </div>

                <div class="stat-card">
                    <h3>Tổng đơn hàng</h3>
                    <div class="value"><?php echo number_format($total_orders); ?></div>
                </div>

                <div class="stat-card">
                    <h3>Tổng người dùng</h3>
                    <div class="value"><?php echo number_format($total_users); ?></div>
                </div>

                <div class="stat-card">
                    <h3>Doanh thu</h3>
                    <div class="value"><?php echo number_format($total_revenue); ?> VNĐ</div>
                </div>
            </div>

            <div class="recent-section">
                <h2>Đơn hàng gần đây</h2>
                <table>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                    <?php if ($recent_orders->num_rows > 0): ?>
                    <?php while ($order = $recent_orders->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['hoten']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['ngaydat'])); ?></td>
                        <td><?php echo number_format($order['tongtien']); ?> VNĐ</td>
                        <td><?php echo htmlspecialchars($order['trangthai']); ?></td>
                        <td>
                            <a href="xem_chitiet_donhang.php?madon=<?php echo $order['id']; ?>">Xem</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6">Không có đơn hàng nào.</td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

            <div class="recent-section">
                <h2>Sản phẩm bán chạy</h2>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá bán</th>
                        <th>Đã bán</th>
                    </tr>
                    <?php if ($best_sellers->num_rows > 0): ?>
                    <?php while ($product = $best_sellers->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td><?php echo htmlspecialchars($product['tensanpham']); ?></td>
                        <td><?php echo number_format($product['giaban']); ?> VNĐ</td>
                        <td><?php echo $product['total_sold']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="4">Không có dữ liệu.</td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</body>

</html>