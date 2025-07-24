<?php
session_start();
include 'includes/db.php';

// Kiểm tra giỏ hàng
if (!isset($_SESSION['gio'])) {
    $_SESSION['gio'] = [];
}

// Xử lý cập nhật số lượng
if (isset($_POST['capnhat'])) {
    foreach ($_POST['soluong'] as $id => $sl) {
        $id = (int)$id;
        $sl = (int)$sl;
        
        if ($sl > 0) {
            $_SESSION['gio'][$id] = $sl;
        } else {
            unset($_SESSION['gio'][$id]);
        }
    }
}

// Xử lý xóa sản phẩm
if (isset($_GET['xoa'])) {
    $id = (int)$_GET['xoa'];
    if (isset($_SESSION['gio'][$id])) {
        unset($_SESSION['gio'][$id]);
    }
    header('Location: giohang.php');
    exit();
}

// Lấy thông tin sản phẩm trong giỏ hàng
$products = [];
$total = 0;

if (!empty($_SESSION['gio'])) {
    $ids = array_keys($_SESSION['gio']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    $stmt = $conn->prepare("SELECT * FROM sanpham WHERE id IN ($placeholders)");
    $types = str_repeat('i', count($ids));
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $quantity = $_SESSION['gio'][$row['id']];
        $price = !empty($row['gia_km']) ? $row['gia_km'] : $row['giaban'];
        $subtotal = $price * $quantity;
        $total += $subtotal;
        
        $products[] = [
            'id' => $row['id'],
            'tensanpham' => $row['tensanpham'],
            'hinhanh' => $row['hinhanh'],
            'giaban' => $row['giaban'],
            'gia_km' => $row['gia_km'],
            'soluong' => $quantity,
            'subtotal' => $subtotal
        ];
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - NHoM7 Shop</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="style/style_giohang.css">
</head>
<body>
    <!-- Header -->
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
                    <div class="giohang-icon">🛒 <span class="cart-count"><?php echo count($_SESSION['gio']); ?></span></div>
                    <p>Giỏ Hàng</p>
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="taikhoan.php" class="giohang_taikhoan-item">
                        <img class="user-avatar" src="anh/avt_trang.png" alt="Avatar">
                        <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    </a>
                <?php else: ?>
                    <a href="dangnhap.php" class="giohang_taikhoan-item">
                        <img class="user-avatar" src="anh/avt_trang.png" alt="Avatar">
                        <p>Tài khoản</p>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Menu -->
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
                        <a href="danhmuc.php?id=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['ten']); ?></a>
                    <?php endwhile; ?>
                </div>
            </li>
            <li><a href="khuyenmai.php">Khuyến mãi</a></li>
            <li><a href="hotro.php">Hỗ trợ</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="lichsudonhang.php">Lịch sử đơn hàng</a></li>
                <li><a href="dangxuat.php">Đăng xuất</a></li>
            <?php else: ?>
                <li><a href="dangnhap.php">Đăng nhập</a></li>
                <li><a href="dangky.php">Đăng ký</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Main Content -->
    <main>
        <h1>Giỏ hàng của bạn</h1>
        
        <?php if (empty($products)): ?>
            <div class="empty-cart">
                <img src="anh/empty-cart.png" alt="Giỏ hàng trống">
                <p>Giỏ hàng của bạn đang trống</p>
                <a href="index.php" class="continue-shopping">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
            <form method="post" action="">
                <div class="cart-container">
                    <div class="cart-header">
                        <div class="product-info">Sản phẩm</div>
                        <div class="product-price">Đơn giá</div>
                        <div class="product-quantity">Số lượng</div>
                        <div class="product-subtotal">Thành tiền</div>
                        <div class="product-remove">Xóa</div>
                    </div>
                    
                    <?php foreach ($products as $product): ?>
                        <div class="cart-item">
                            <div class="product-info">
                                <div class="product-image">
                                    <?php if (!empty($product['hinhanh'])): ?>
                                        <img src="<?php echo htmlspecialchars($product['hinhanh']); ?>" alt="<?php echo htmlspecialchars($product['tensanpham']); ?>">
                                    <?php else: ?>
                                        <img src="anh/no-image.jpg" alt="No Image">
                                    <?php endif; ?>
                                </div>
                                <div class="product-name">
                                    <a href="thongtinsanpham.php?id=<?php echo $product['id']; ?>">
                                        <?php echo htmlspecialchars($product['tensanpham']); ?>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="product-price">
                                <?php if (!empty($product['gia_km'])): ?>
                                    <span class="original-price"><?php echo number_format($product['giaban'], 0, ',', '.'); ?>đ</span>
                                    <span class="sale-price"><?php echo number_format($product['gia_km'], 0, ',', '.'); ?>đ</span>
                                <?php else: ?>
                                    <span class="price"><?php echo number_format($product['giaban'], 0, ',', '.'); ?>đ</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-quantity">
                                <div class="quantity-selector">
                                    <button type="button" class="quantity-btn minus" data-id="<?php echo $product['id']; ?>">-</button>
                                    <input type="number" name="soluong[<?php echo $product['id']; ?>]" value="<?php echo $product['soluong']; ?>" min="1" class="quantity-input">
                                    <button type="button" class="quantity-btn plus" data-id="<?php echo $product['id']; ?>">+</button>
                                </div>
                            </div>
                            
                            <div class="product-subtotal">
                                <?php echo number_format($product['subtotal'], 0, ',', '.'); ?>đ
                            </div>
                            
                            <div class="product-remove">
                                <a href="giohang.php?xoa=<?php echo $product['id']; ?>" class="remove-btn">×</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-actions">
                    <div class="cart-update">
                        <button type="submit" name="capnhat" class="update-cart-btn">Cập nhật giỏ hàng</button>
                        <a href="index.php" class="continue-shopping">Tiếp tục mua sắm</a>
                    </div>
                    
                    <div class="cart-summary">
                        <h3>Tổng giỏ hàng</h3>
                        <div class="summary-row">
                            <span>Tạm tính:</span>
                            <span><?php echo number_format($total, 0, ',', '.'); ?>đ</span>
                        </div>
                        <div class="summary-row total">
                            <span>Tổng cộng:</span>
                            <span><?php echo number_format($total, 0, ',', '.'); ?>đ</span>
                        </div>
                        <a href="thanhtoan.php" class="checkout-btn">Tiến hành thanh toán</a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </main>

    <!-- Footer -->
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
            // Xử lý nút tăng giảm số lượng
            const minusButtons = document.querySelectorAll('.quantity-btn.minus');
            const plusButtons = document.querySelectorAll('.quantity-btn.plus');
            
            minusButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const input = document.querySelector(`input[name="soluong[${id}]"]`);
                    let value = parseInt(input.value);
                    
                    if (value > 1) {
                        input.value = value - 1;
                    }
                });
            });
            
            plusButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const input = document.querySelector(`input[name="soluong[${id}]"]`);
                    let value = parseInt(input.value);
                    
                    input.value = value + 1;
                });
            });
            
            // Xử lý tìm kiếm
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


