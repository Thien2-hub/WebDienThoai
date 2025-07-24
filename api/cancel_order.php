<?php
session_start();
include '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Bạn cần đăng nhập để hủy đơn hàng'
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
    $stmt = $conn->prepare("
        SELECT * FROM donhang 
        WHERE id = ? AND user_id = ? AND trangthai = 'Chờ xử lý'
    ");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Không thể hủy đơn hàng này. Đơn hàng không tồn tại, không thuộc về bạn hoặc đã được xử lý.'
        ]);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE donhang SET trangthai = 'Đã hủy' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    
    $stmt = $conn->prepare("SELECT id_sp, soluong FROM chitietdonhang WHERE id_dh = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items_result = $stmt->get_result();
    
    while ($item = $items_result->fetch_assoc()) {
        $stmt = $conn->prepare("UPDATE sanpham SET soluong = soluong + ? WHERE id = ?");
        $stmt->bind_param("ii", $item['soluong'], $item['id_sp']);
        $stmt->execute();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Đơn hàng đã được hủy thành công'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
    ]);
}