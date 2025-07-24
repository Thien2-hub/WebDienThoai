<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: dangnhap.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$order_query = $conn->prepare("
    SELECT dh.*, u.hoten, u.email
    FROM donhang dh
    JOIN users u ON dh.user_id = u.id
    WHERE dh.id = ? AND dh.user_id = ?
");
$order_query->bind_param("ii", $order_id, $user_id);
$order_query->execute();
$order_result = $order_query->get_result();

if ($order_result->num_rows === 0) {
    header('Location: lichsudonhang.php');
    exit();
}

$order = $order_result->fetch_assoc();
$order_query->close();

$details_query = $conn->prepare("
    SELECT ct.*, sp.tensanpham, sp.hinhanh, sp.gia_km as dongia
    FROM chitietdonhang ct
    JOIN sanpham sp ON ct.id_sp = sp.id
    WHERE ct.id_dh = ?
");
$details_query->bind_param("i", $order_id);
$details_query->execute();
$details = $details_query->get_result();
$details_query->close();

$status_query = $conn->prepare("
    SELECT * FROM lichsu_trangthai
    WHERE id_dh = ?
    ORDER BY ngaycapnhat ASC
");
$status_query->bind_param("i", $order_id);
$status_query->execute();
$status_history = $status_query->get_result();
$status_query->close();

$return_query = $conn->prepare("SELECT * FROM doitra WHERE id_dh = ? ORDER BY ngaygui DESC");
$return_query->bind_param("i", $order_id);
$return_query->execute();
$return_requests = $return_query->get_result();
$return_query->close();

$total = 0;
$items = [];
while ($item = $details->fetch_assoc()) {
    $total += $item['dongia'] * $item['soluong'];
    $items[] = $item;
}
$details->data_seek(0); 
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?php echo $order_id; ?> - NHoM7 Shop</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../style/style_header.css">
    <link rel="stylesheet" href="../style/style_lichsudonhang.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
    .order-detail-container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 25px;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    }

    .order-detail-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eaeaea;
    }

    .order-detail-header h1 {
        font-size: 24px;
        color: #333;
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        padding: 8px 15px;
        background-color: #f5f5f5;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        color: #333;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.2s;
    }

    .back-btn:hover {
        background-color: #eaeaea;
    }

    .back-btn i {
        margin-right: 5px;
    }

    .order-sections {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 25px;
    }

    @media (max-width: 768px) {
        .order-sections {
            grid-template-columns: 1fr;
        }
    }

    .order-main {
        border: 1px solid #eaeaea;
        border-radius: 10px;
        overflow: hidden;
    }

    .order-status-bar {
        background-color: #f9f9f9;
        padding: 15px 20px;
        border-bottom: 1px solid #eaeaea;
    }

    .status-label {
        font-size: 14px;
        color: #777;
        margin-bottom: 5px;
    }

    .current-status {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
    }

    .current-status.chờ-xử-lý {
        background-color: #fff8e6;
        color: #e6a700;
    }

    .current-status.đang-giao {
        background-color: #e6f4ff;
        color: #0066cc;
    }

    .current-status.đã-hoàn-thành {
        background-color: #e7f7ed;
        color: #0a6b31;
    }

    .current-status.đã-hủy {
        background-color: #ffeaea;
        color: #d70018;
    }

    .order-products {
        padding: 20px;
    }

    .product-list {
        margin-bottom: 20px;
    }

    .product-item {
        display: flex;
        padding: 15px 0;
        border-bottom: 1px solid #eaeaea;
    }

    .product-item:last-child {
        border-bottom: none;
    }

    .product-image {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        object-fit: cover;
        margin-right: 15px;
        border: 1px solid #eaeaea;
    }

    .product-details {
        flex: 1;
    }

    .product-name {
        font-size: 16px;
        color: #333;
        margin-bottom: 5px;
        font-weight: 500;
    }

    .product-variant {
        font-size: 14px;
        color: #777;
        margin-bottom: 5px;
    }

    .product-price {
        font-size: 15px;
        color: #d70018;
        font-weight: 500;
    }

    .product-quantity {
        font-size: 14px;
        color: #555;
        margin-left: 10px;
    }

    .order-summary {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px dashed #eaeaea;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 14px;
        color: #555;
    }

    .summary-row.total {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #eaeaea;
    }

    .summary-row.total .value {
        color: #d70018;
    }

    .order-sidebar {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .sidebar-section {
        border: 1px solid #eaeaea;
        border-radius: 10px;
        overflow: hidden;
    }

    .sidebar-header {
        background-color: #f9f9f9;
        padding: 15px 20px;
        border-bottom: 1px solid #eaeaea;
        font-size: 16px;
        font-weight: 500;
        color: #333;
    }

    .sidebar-content {
        padding: 20px;
    }

    .info-row {
        margin-bottom: 12px;
    }

    .info-row:last-child {
        margin-bottom: 0;
    }

    .info-label {
        font-size: 14px;
        color: #777;
        margin-bottom: 5px;
    }

    .info-value {
        font-size: 15px;
        color: #333;
    }

    .tracking-timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-item::before {
        content: "";
        position: absolute;
        left: -20px;
        top: 5px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #0066cc;
        z-index: 1;
    }

    .timeline-item::after {
        content: "";
        position: absolute;
        left: -16px;
        top: 15px;
        width: 2px;
        height: calc(100% - 15px);
        background-color: #e0e0e0;
    }

    .timeline-item:last-child::after {
        display: none;
    }

    .timeline-date {
        font-size: 13px;
        color: #777;
        margin-bottom: 5px;
    }

    .timeline-status {
        font-size: 14px;
        color: #333;
        font-weight: 500;
    }

    .order-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .action-btn {
        flex: 1;
        padding: 12px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .buy-again-btn {
        background-color: #0066cc;
        color: white;
        border: none;
    }

    .buy-again-btn:hover {
        background-color: #0055aa;
    }

    .return-btn {
        background-color: #fff8e6;
        color: #e6a700;
        border: 1px solid #ffe0a6;
    }

    .return-btn:hover {
        background-color: #ffe0a6;
    }

    .cancel-btn {
        background-color: #ffeaea;
        color: #d70018;
        border: 1px solid #ffcaca;
    }

    .cancel-btn:hover {
        background-color: #ffcaca;
    }

    .return-requests {
        margin-top: 20px;
    }

    .return-item {
        background-color: #f9f9f9;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }

    .return-date {
        font-size: 13px;
        color: #777;
        margin-bottom: 5px;
    }

    .return-reason {
        font-size: 14px;
        color: #333;
        margin-bottom: 10px;
    }

    .return-status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
    }

    .return-status.đang-xử-lý {
        background-color: #fff8e6;
        color: #e6a700;
    }

    .return-status.đã-duyệt {
        background-color: #e7f7ed;
        color: #0a6b31;
    }

    .return-status.đã-từ-chối {
        background-color: #ffeaea;
        color: #d70018;
    }

    /* Modal đổi trả */
    .return-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .return-modal.active {
        display: flex;
    }

    .return-modal-content {
        background-color: white;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    }

    .return-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .return-modal-header h3 {
        font-size: 20px;
        color: #333;
    }

    .close-modal {
        background: none;
        border: none;
        font-size: 22px;
        color: #777;
        cursor: pointer;
    }

    .return-form textarea {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        resize: vertical;
        min-height: 120px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .return-form textarea:focus {
        border-color: #0066cc;
        outline: none;
        box-shadow: 0 0 0 2px rgba(0, 102, 204, 0.2);
    }

    .return-form button {
        width: 100%;
        padding: 12px;
        background-color: #0066cc;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .return-form button:hover {
        background-color: #0055aa;
    }
    </style>
</head>

<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <main>
        <div class="order-detail-container">
            <div class="order-detail-header">
                <h1>Chi tiết đơn hàng #<?php echo $order_id; ?></h1>
                <a href="lichsudonhang.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>

            <div class="order-sections">
                <div class="order-main">
                    <div class="order-status-bar">
                        <div class="status-label">Trạng thái đơn hàng</div>
                        <div
                            class="current-status <?php echo strtolower(str_replace(' ', '-', $order['trangthai'])); ?>">
                            <?php echo $order['trangthai']; ?>
                        </div>
                    </div>

                    <div class="order-products">
                        <h3>Sản phẩm đã đặt</h3>
                        <div class="product-list">
                            <?php foreach ($items as $item): ?>
                            <div class="product-item">
                                <img src="../<?php echo $item['hinhanh']; ?>" alt="<?php echo $item['tensanpham']; ?>"
                                    class="product-image">
                                <div class="product-details">
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

                        <div class="order-summary">
                            <div class="summary-row">
                                <div class="label">Tạm tính</div>
                                <div class="value"><?php echo number_format($total, 0, ',', '.'); ?>₫</div>
                            </div>
                            <div class="summary-row">
                                <div class="label">Phí vận chuyển</div>
                                <div class="value">
                                    <?php echo number_format($order['phivanchuyen'] ?? 0, 0, ',', '.'); ?>₫</div>
                                </