<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: dangnhap.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$sql_filter = "";
if ($filter !== 'all') {
    $sql_filter = " AND trangthai = ?";
}

$orders_query = $conn->prepare("
    SELECT dh.*, 
           COUNT(ct.id) as total_items
    FROM donhang dh
    LEFT JOIN chitietdonhang ct ON dh.id = ct.id_dh
    WHERE dh.user_id = ?" . $sql_filter . "
    GROUP BY dh.id
    ORDER BY dh.ngaydat DESC
");

if ($filter !== 'all') {
    $orders_query->bind_param("is", $user_id, $filter);
} else {
    $orders_query->bind_param("i", $user_id);
}

$orders_query->execute();
$orders = $orders_query->get_result();
$orders_query->close();

$message = '';
if (isset($_POST['huydon'])) {
    $order_id = (int)$_POST['order_id'];
    
    $check_query = $conn->prepare("SELECT id FROM donhang WHERE id = ? AND user_id = ? AND trangthai = 'Chờ xử lý'");
    $check_query->bind_param("ii", $order_id, $user_id);
    $check_query->execute();
    $check_result = $check_query->get_result();
    
    if ($check_result->num_rows > 0) {
        $update_query = $conn->prepare("UPDATE donhang SET trangthai = 'Đã hủy' WHERE id = ?");
        $update_query->bind_param("i", $order_id);
        $update_query->execute();
        $update_query->close();
        
        $message = "Đơn hàng #$order_id đã được hủy thành công";
    } else {
        $message = "Không thể hủy đơn hàng này";
    }
    
    $check_query->close();
}

if (isset($_POST['doitra'])) {
    $order_id = (int)$_POST['order_id'];
    $lydo = trim($_POST['lydo']);
    
    if (empty($lydo)) {
        $message = "Vui lòng nhập lý do đổi trả";
    } else {
        $check_query = $conn->prepare("SELECT id FROM donhang WHERE id = ? AND user_id = ? AND trangthai = 'Đã hoàn thành'");
        $check_query->bind_param("ii", $order_id, $user_id);
        $check_query->execute();
        $check_result = $check_query->get_result();
        
        if ($check_result->num_rows > 0) {
            $insert_query = $conn->prepare("INSERT INTO doitra (id_dh, lydo, ngaygui, trangthai) VALUES (?, ?, NOW(), 'Đang xử lý')");
            $insert_query->bind_param("is", $order_id, $lydo);
            $insert_query->execute();
            $insert_query->close();
            
            $message = "Yêu cầu đổi trả đơn hàng #$order_id đã được gửi thành công";
        } else {
            $message = "Không thể gửi yêu cầu đổi trả cho đơn hàng này";
        }
        
        $check_query->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử đơn hàng - NHoM7 Shop</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../style/style_header.css">
    <link rel="stylesheet" href="../style/style_lichsudonhang.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <div class="order-history-container">
            <div class="order-history-header">
                <h1>Lịch sử đơn hàng</h1>
                <p>Xem thông tin và trạng thái các đơn hàng của bạn</p>
            </div>

            <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'thành công') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            <div class="order-navigation">
                <div class="container">
                    <nav class="order-menu">
                        <ul>
                            <li><a href="../index.html">Trang chủ</a></li>
                            <li><a href="../danhmuc.html">Sản phẩm</a></li>
                            <li><a href="../gioithieu.html">Giới thiệu</a></li>
                            <li><a href="../lienhe.html">Liên hệ</a></li>
                            <li><a href="donhang.php" class="active">Đơn hàng</a></li>
                            <li><a href="../dangnhap.php">Đăng xuất</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
            <div class="order-filters">
                <a href="?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                    Tất cả
                </a>
                <a href="?filter=Chờ xử lý" class="filter-btn <?php echo $filter === 'Chờ xử lý' ? 'active' : ''; ?>">
                    Chờ xử lý
                </a>
                <a href="?filter=Đang giao" class="filter-btn <?php echo $filter === 'Đang giao' ? 'active' : ''; ?>">
                    Đang giao
                </a>
                <a href="?filter=Đã hoàn thành"
                    class="filter-btn <?php echo $filter === 'Đã hoàn thành' ? 'active' : ''; ?>">
                    Đã hoàn thành
                </a>
                <a href="?filter=Đã hủy" class="filter-btn <?php echo $filter === 'Đã hủy' ? 'active' : ''; ?>">
                    Đã hủy
                </a>
            </div>

            <!-- Danh sách đơn hàng -->
            <div class="orders-list">
                <?php if ($orders->num_rows > 0): ?>
                <?php while ($order = $orders->fetch_assoc()): ?>
                <?php
                        // Lấy thông tin sản phẩm trong đơn hàng
                        $details_query = $conn->prepare("
                            SELECT ct.*, sp.tensanpham, sp.hinhanh, sp.gia_km as dongia
                            FROM chitietdonhang ct
                            JOIN sanpham sp ON ct.id_sp = sp.id
                            WHERE ct.id_dh = ?
                        ");
                        $details_query->bind_param("i", $order['id']);
                        $details_query->execute();
                        $details = $details_query->get_result();
                        $details_query->close();

                        // Kiểm tra xem đơn hàng đã có yêu cầu đổi trả chưa
                        $return_query = $conn->prepare("SELECT * FROM doitra WHERE id_dh = ? ORDER BY ngaygui DESC LIMIT 1");
                        $return_query->bind_param("i", $order['id']);
                        $return_query->execute();
                        $return_result = $return_query->get_result();
                        $has_return_request = $return_result->num_rows > 0;
                        $return_info = $has_return_request ? $return_result->fetch_assoc() : null;
                        $return_query->close();

                        // Tính tổng tiền đơn hàng
                        $total = 0;
                        $items = [];
                        while ($item = $details->fetch_assoc()) {
                            $total += $item['dongia'] * $item['soluong'];
                            $items[] = $item;
                        }

                        // Chuyển đổi trạng thái thành class CSS
                        $status_class = strtolower(str_replace(' ', '-', $order['trangthai']));
                        ?>

                <div class="order-item">
                    <div class="order-header">
                        <div class="order-id">
                            <h3>Đơn hàng #<?php echo $order['id']; ?></h3>
                            <div class="order-date">
                                <i class="far fa-calendar-alt"></i>
                                <?php echo date('d/m/Y H:i', strtotime($order['ngaydat'])); ?>
                            </div>
                        </div>
                        <div class="order-status <?php echo $status_class; ?>">
                            <?php echo $order['trangthai']; ?>
                        </div>
                    </div>

                    <div class="order-content">
                        <div class="order-summary">
                            <div class="order-info">
                                <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['tennguoinhan']); ?>
                                </p>
                                <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['diachi']); ?></p>
                                <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($order['sodienthoai']); ?>
                                </p>

                                <?php if ($has_return_request): ?>
                                <div class="return-status">
                                    <strong>Trạng thái đổi trả:</strong>
                                    <span
                                        class="<?php echo strtolower(str_replace(' ', '-', $return_info['trangthai'])); ?>">
                                        <?php echo $return_info['trangthai']; ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="order-total">
                                <div class="label">Tổng tiền</div>
                                <div class="price"><?php echo number_format($total, 0, ',', '.'); ?>₫</div>
                            </div>
                        </div>

                        <div class="order-products">
                            <?php foreach ($items as $item): ?>
                            <div class="product-item">
                                <img src="../<?php echo $item['hinhanh']; ?>" alt="<?php echo $item['tensanpham']; ?>"
                                    class="product-image">
                                <div class="product-info">
                                    <div class="product-name"><?php echo $item['tensanpham']; ?></div>
                                    <div class="product-variant">
                                        <?php if (!empty($item['mausac'])): ?>
                                        Màu: <?php echo $item['mausac']; ?>
                                        <?php endif; ?>

                                        <?php if (!empty($item['dungluong'])): ?>
                                        | Dung lượng: <?php echo $item['dungluong']; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-price">
                                        <?php echo number_format($item['dongia'], 0, ',', '.'); ?>₫
                                        <span class="product-quantity">x<?php echo $item['soluong']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="order-actions">
                            <a href="chitietdonhang.php?id=<?php echo $order['id']; ?>" class="view-details-btn">
                                <i class="fas fa-eye"></i> Xem chi tiết
                            </a>

                            <?php if ($order['trangthai'] === 'Chờ xử lý'): ?>
                            <form method="post" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <button type="submit" name="huydon" class="cancel-btn">
                                    <i class="fas fa-times"></i> Hủy đơn
                                </button>
                            </form>
                            <?php endif; ?>

                            <?php if ($order['trangthai'] === 'Đã hoàn thành' && !$has_return_request): ?>
                            <button type="button" class="return-btn"
                                onclick="openReturnModal(<?php echo $order['id']; ?>)">
                                <i class="fas fa-exchange-alt"></i> Yêu cầu đổi trả
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                <div class="no-orders">
                    <p>Bạn chưa có đơn hàng nào</p>
                    <a href="../index.html" class="shop-now-btn">Mua sắm ngay</a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modal đổi trả -->
        <div class="return-modal" id="returnModal">
            <div class="return-modal-content">
                <div class="return-modal-header">
                    <h3>Yêu cầu đổi trả</h3>
                    <button type="button" class="close-modal" onclick="closeReturnModal()">&times;</button>
                </div>
                <form method="post" class="return-form" id="returnForm">
                    <input type="hidden" name="order_id" id="returnOrderId">
                    <textarea name="lydo" placeholder="Vui lòng nhập lý do đổi trả..." required></textarea>
                    <button type="submit" name="doitra">Gửi yêu cầu</button>
                </form>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
    function openReturnModal(orderId) {
        document.getElementById('returnOrderId').value = orderId;
        document.getElementById('returnModal').classList.add('active');
    }

    function closeReturnModal() {
        document.getElementById('returnModal').classList.remove('active');
    }

    window.onclick = function(event) {
        const modal = document.getElementById('returnModal');
        if (event.target === modal) {
            closeReturnModal();
        }
    }
    </script>
</body>

</html>