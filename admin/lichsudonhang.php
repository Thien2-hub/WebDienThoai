<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: dangnhap.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$orders_query = $conn->prepare("SELECT * FROM donhang WHERE user_id = ? ORDER BY ngaydat DESC");
$orders_query->bind_param("i", $user_id);
$orders_query->execute();
$orders = $orders_query->get_result();
$orders_query->close();

$message = '';
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
            $check_return = $conn->prepare("SELECT id FROM doitra WHERE id_dh = ?");
            $check_return->bind_param("i", $order_id);
            $check_return->execute();
            $return_exists = $check_return->get_result()->num_rows > 0;
            $check_return->close();
            
            if ($return_exists) {
                $message = "Đơn hàng này đã có yêu cầu đổi trả!";
            } else {
                $insert_query = $conn->prepare("INSERT INTO doitra (id_dh, lydo, ngaygui) VALUES (?, ?, NOW())");
                $insert_query->bind_param("is", $order_id, $lydo);
                $insert_query->execute();
                $insert_query->close();
                
                $message = "Yêu cầu đổi trả đã được gửi thành công!";
            }
        } else {
            $message = "Không thể yêu cầu đổi trả cho đơn hàng này!";
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
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="style/style_lichsudonhang.css">
</head>

<body>
    <header>
        <div class="daudo">
            <div class="dau">
                <div class="logo">
                    <a href="index.php">NH<span class="chu_o">o</span>M7</a>
                </div>
                <div class="search-container">
                    <div class="search-box">
                        <span class="search-icon">🔍</span>
                        <input type="text" placeholder="Tìm kiếm ở đây" id="searchInput">
                        <button id="searchButton">Tìm Kiếm</button>
                    </div>
                </div>
            </div>
            <div class="giohang_taikhoan">
                <a href="giohang.php" class="giohang_taikhoan-item">
                    <div class="giohang-icon">🛒 <span class="cart-count">0</span></div>
                    <p>Giỏ Hàng</p>
                </a>
                <a href="taikhoan.php" class="giohang_taikhoan-item">
                    <img class="user-avatar" src="anh/avt_trang.png" alt="Avatar">
                    <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                </a>
            </div>
        </div>
    </header>

    <nav class="menu">
        <ul>
            <li><a href="index.php">Trang chủ</a></li>
            <li class="dropdown">
                <a href="#">Danh mục sản phẩm</a>
                <div class="dropdown-content">
                    <?php 
                    $categories = $conn->query("SELECT * FROM danhmuc ORDER BY ten");
                    while ($category = $categories->fetch_assoc()): 
                    ?>
                    <a
                        href="danhmuc.php?id=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['ten']); ?></a>
                    <?php endwhile; ?>
                </div>
            </li>
            <li><a href="khuyenmai.php">Khuyến mãi</a></li>
            <li><a href="hotro.php">Hỗ trợ</a></li>
            <li><a href="lichsudonhang.php" class="active">Lịch sử đơn hàng</a></li>
            <li><a href="dangxuat.php">Đăng xuất</a></li>
        </ul>
    </nav>

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

            <div class="order-list">
                <?php if ($orders->num_rows > 0): ?>
                <?php while ($order = $orders->fetch_assoc()): ?>
                <?php
                        $check_return = $conn->prepare("SELECT id, trangthai FROM doitra WHERE id_dh = ?");
                        $check_return->bind_param("i", $order['id']);
                        $check_return->execute();
                        $return_result = $check_return->get_result();
                        $has_return = $return_result->num_rows > 0;
                        $return_status = $has_return ? $return_result->fetch_assoc()['trangthai'] : '';
                        $check_return->close();
                        
                        $details_query = $conn->prepare("
                            SELECT ct.*, sp.tensanpham, sp.hinhanh 
                            FROM chitietdonhang ct 
                            JOIN sanpham sp ON ct.masp = sp.id 
                            WHERE ct.madon = ?
                        ");
                        $details_query->bind_param("i", $order['id']);
                        $details_query->execute();
                        $details = $details_query->get_result();
                        $details_query->close();
                        
                        $product_count = $details->num_rows;
                        ?>
                <div class="order-item">
                    <div class="order-header">
                        <div class="order-id">
                            <h3>Đơn hàng #<?php echo $order['id']; ?></h3>
                            <span
                                class="order-date"><?php echo date('d/m/Y H:i', strtotime($order['ngaydat'])); ?></span>
                        </div>
                        <div class="order-status <?php echo strtolower(str_replace(' ', '-', $order['trangthai'])); ?>">
                            <?php echo $order['trangthai']; ?>
                        </div>
                    </div>

                    <div class="order-summary">
                        <div class="order-products">
                            <?php 
                                    $count = 0;
                                    $details->data_seek(0);
                                    while ($detail = $details->fetch_assoc()): 
                                        $count++;
                                        if ($count > 3) break; 
                                        $image = !empty($detail['hinhanh']) ? $detail['hinhanh'] : 'anh/no-image.jpg';
                                    ?>
                            <div class="product-thumbnail">
                                <img src="<?php echo htmlspecialchars($image); ?>"
                                    alt="<?php echo htmlspecialchars($detail['tensanpham']); ?>">
                            </div>
                            <?php endwhile; ?>

                            <?php if ($product_count > 3): ?>
                            <div class="more-products">+<?php echo $product_count - 3; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="order-total">
                            <span class="total-label">Tổng tiền:</span>
                            <span
                                class="total-amount"><?php echo number_format($order['tongtien'], 0, ',', '.'); ?>₫</span>
                        </div>
                    </div>

                    <div class="order-actions">
                        <a href="chitietdonhang.php?id=<?php echo $order['id']; ?>" class="view-details-btn">Xem chi
                            tiết</a>

                        <?php if ($order['trangthai'] === 'Đã hoàn thành' && !$has_return): ?>
                        <button class="return-btn" data-id="<?php echo $order['id']; ?>">Yêu cầu đổi trả</button>
                        <?php elseif ($has_return): ?>
                        <div class="return-status">
                            Trạng thái đổi trả: <span
                                class="<?php echo strtolower($return_status); ?>"><?php echo $return_status; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php else: ?>
                <div class="no-orders">
                    <p>Bạn chưa có đơn hàng nào.</p>
                    <a href="index.php" class="shop-now-btn">Mua sắm ngay</a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="returnModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Yêu cầu đổi trả</h2>
                <form method="post" action="" id="returnForm">
                    <input type="hidden" name="order_id" id="order_id">
                    <div class="form-group">
                        <label for="lydo">Lý do đổi trả:</label>
                        <textarea name="lydo" id="lydo" required></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="doitra" class="submit-btn">Gửi yêu cầu</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>NHoM7 Shop</h3>
                <p>Cửa hàng điện thoại uy tín hàng đầu Việt Nam</p>
            </div>
            <div class="footer-section">
                <h3>Liên hệ</h3>
                <p>Email: contact@nhom7shop.com</p>
                <p>Hotline: 1900 1234</p>
            </div>
            <div class="footer-section">
                <h3>Theo dõi chúng tôi</h3>
                <p>Facebook | Instagram | YouTube</p>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2023 NHoM7 Shop. All rights reserved.
        </div>
    </footer>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const modal = document.getElementById("returnModal");
        const returnBtns = document.querySelectorAll(".return-btn");
        const closeBtn = document.querySelector(".close");
        const orderIdInput = document.getElementById("order_id");

        returnBtns.forEach(btn => {
            btn.addEventListener("click", function() {
                const orderId = this.getAttribute("data-id");
                orderIdInput.value = orderId;
                modal.style.display = "block";
            });
        });

        if (closeBtn) {
            closeBtn.addEventListener("click", function() {
                modal.style.display = "none";
            });
        }

        window.addEventListener("click", function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        });

        function updateCartCount() {
            fetch('nguoidung/dem_giohang.php')
                .then(response => response.json())
                .then(data => {
                    document.querySelector('.cart-count').innerText = data.count;
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        updateCartCount();

        document.getElementById('searchButton').addEventListener('click', function() {
            const keyword = document.getElementById('searchInput').value.trim();
            if (keyword) {
                window.location.href = 'timkiem.php?keyword=' + encodeURIComponent(keyword);
            }
        });

        // Xử lý tìm kiếm khi nhấn Enter
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const keyword = this.value.trim();
                if (keyword) {
                    window.location.href = 'timkiem.php?keyword=' + encodeURIComponent(keyword);
                }
            }
        });
    });
    </script>
</body>

</html>