<?php
session_start();
include '../includes/db.php';
header('Content-Type: application/json');

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

try {
    $stmt = $conn->prepare("SELECT * FROM donhang WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $order_result = $stmt->get_result();
    
    if ($order_result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền xem đơn hàng này'
        ]);
        exit;
    }
    
    $order = $order_result->fetch_assoc();
    
    $stmt = $conn->prepare("
        SELECT ct.*, sp.tensanpham, sp.hinhanh, sp.gia_km as dongia
        FROM chitietdonhang ct
        JOIN sanpham sp ON ct.id_sp = sp.id
        WHERE ct.id_dh = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items_result = $stmt->get_result();
    
       $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
    
    $shipping_info = json_decode($order['diachi_gh'], true);
    if (!$shipping_info) {
        $shipping_info = [
            'name' => '',
            'phone' => '',
            'address' => ''
        ];
    }
    
    $order_data = [
        'id' => $order['id'],
        'date' => $order['ngaydat'],
        'status' => $order['trangthai'],
        'total' => $order['tongtien'],
        'shipping_fee' => $order['phiship'] ?? 0,
        'payment' => $order['phuongthuc_tt'],
        'shipping' => $shipping_info,
        'notes' => $order['ghichu'] ?? '',
        'items' => $items
    ];
    
    echo json_encode([
        'success' => true,
        'order' => $order_data
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
    ]);
}