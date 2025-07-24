<?php
include 'includes/db.php';

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$brand = isset($_GET['brand']) ? trim($_GET['brand']) : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 999999999;

$sql = "SELECT * FROM sanpham WHERE tensanpham LIKE ? AND hang LIKE ? AND giaban BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);
$searchKeyword = "%{$keyword}%";
$searchBrand = "%{$brand}%";

$stmt->bind_param("ssdd", $searchKeyword, $searchBrand, $min_price, $max_price);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Kết quả tìm kiếm</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <h2>Kết quả tìm kiếm</h2>
    <form method="GET" action="timkiem.php">
        <input type="text" name="keyword" placeholder="Tìm theo tên..."
            value="<?php echo htmlspecialchars($keyword); ?>">
        <input type="text" name="brand" placeholder="Hãng..." value="<?php echo htmlspecialchars($brand); ?>">
        <input type="number" step="any" name="min_price" placeholder="Giá từ"
            value="<?php echo htmlspecialchars($min_price); ?>">
        <input type="number" step="any" name="max_price" placeholder="Giá đến"
            value="<?php echo htmlspecialchars($max_price); ?>">
        <button type="submit">Tìm kiếm</button>
    </form>
    <div class="product-list">
        <?php while ($row = $result->fetch_assoc()) { ?>
        <div class="product-item">
            <h3><?php echo htmlspecialchars($row['tensanpham']); ?></h3>
            <p>Hãng: <?php echo htmlspecialchars($row['hang']); ?></p>
            <p>Giá: <?php echo number_format($row['giaban']); ?> VNĐ</p>
            <a href="thongtinsanpham.php?id=<?php echo (int)$row['id']; ?>">Chi tiết</a>
        </div>
        <?php } ?>
    </div>
</body>

</html>