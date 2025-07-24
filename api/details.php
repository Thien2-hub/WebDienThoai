<?php
session_start();
include '../includes/db.php';
header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Bạn cần đăng nhập để xem chi tiết đơn hàng'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Mã đơn hàng không hợp lệ'
    ]);
    exit;
}

// Kiểm tra đơn hàng có thuộc về người dùng không
$check_sql = "SELECT * FROM donhang WHERE id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $order_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Không tìm thấy đơn hàng'
    ]);
    exit;
}

$order = $check_result->fetch_assoc();

// Lấy chi tiết sản phẩm trong đơn hàng
$items_sql = "SELECT ct.id_sp, ct.soluong, ct.dongia, sp.tensanpham, sp.hinhanh 
             FROM chitietdonhang ct 
             JOIN sanpham sp ON ct.id_sp = sp.id 
             WHERE ct.id_dh = ?";

$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$items = [];
while ($item = $items_result->fetch_assoc()) {
    $items[] = $item;
}

$order['items'] = $items;

$order['shipping'] = [
    'name' => $order['hoten'] ?? $_SESSION['ten'] ?? '',
    'phone' => $order['sodienthoai'] ?? '',
    'address' => $order['diachi'] ?? ''
];

$order['date'] = $order['ngaydat'];
$order['total'] = $order['tongtien'];
$order['status'] = $order['trangthai'];
$order['payment'] = $order['phuongthuc_thanhtoan'] ?? 'Thanh toán khi nhận hàng';
$order['notes'] = $order['ghichu'] ?? '';

echo json_encode([
    'success' => true,
    'order' => $order
]);