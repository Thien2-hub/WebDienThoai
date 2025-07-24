<?php
session_start();
include '../includes/db.php';
header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Bạn cần đăng nhập để xem lịch sử đơn hàng'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Lấy danh sách đơn hàng của người dùng
    $stmt = $conn->prepare("
        SELECT dh.*, 
               COUNT(ct.id) as total_items
        FROM donhang dh
        LEFT JOIN chitietdonhang ct ON dh.id = ct.id_dh
        WHERE dh.user_id = ?
        GROUP BY dh.id
        ORDER BY dh.ngaydat DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($order = $result->fetch_assoc()) {
        $stmt_items = $conn->prepare("
            SELECT ct.*, sp.tensanpham, sp.hinhanh, sp.gia_km as dongia
            FROM chitietdonhang ct
            JOIN sanpham sp ON ct.id_sp = sp.id
            WHERE ct.id_dh = ?
            LIMIT 3
        ");
        $stmt_items->bind_param("i", $order['id']);
        $stmt_items->execute();
        $items_result = $stmt_items->get_result();
        
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
        
        $orders[] = [
            'id' => $order['id'],
            'date' => $order['ngaydat'],
            'status' => $order['trangthai'],
            'total' => $order['tongtien'],
            'shipping_fee' => $order['phiship'] ?? 0,
            'payment' => $order['phuongthuc_tt'],
            'shipping' => $shipping_info,
            'notes' => $order['ghichu'] ?? '',
            'total_items' => $order['total_items'],
            'items' => $items
        ];
    }
    
    echo json_encode([
        'success' => true,
        'orders' => $orders
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
    ]);
}