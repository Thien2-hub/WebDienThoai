<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['quyen'] != 'admin') {
    header('Location: ../dangnhap.php');
    exit();
}

$message = '';
$error = '';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT * FROM sanpham WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('Location: admin_sanpham.php');
        exit();
    }
    
    $product = $result->fetch_assoc();
    $stmt->close();
} else {
    header('Location: admin_sanpham.php');
    exit();
}

if (isset($_POST['capnhat_sp'])) {
    $tensanpham = trim($_POST['tensanpham']);
    $mota = trim($_POST['mota']);
    $giaban = floatval($_POST['giaban']);
    $gia_km = !empty($_POST['gia_km']) ? floatval($_POST['gia_km']) : NULL;
    $soluong = intval($_POST['soluong']);
    $id_danhmuc = intval($_POST['id_danhmuc']);
    $hang = trim($_POST['hang']);
    
    if (empty($tensanpham)) {
        $error = "Vui lòng nhập tên sản phẩm";
    } elseif ($giaban <= 0) {
        $error = "Giá bán phải lớn hơn 0";
    } elseif ($soluong < 0) {
        $error = "Số lượng không được âm";
    } else {
        $hinhanh = $product['hinhanh'];
        if (isset($_FILES['hinhanh']) && $_FILES['hinhanh']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['hinhanh']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (!in_array(strtolower($ext), $allowed)) {
                $error = "Chỉ chấp nhận file hình ảnh (jpg, jpeg, png, gif)";
            } else {
                $newname = 'product_' . time() . '.' . $ext;
                $target = '../uploads/' . $newname;
                
                if (move_uploaded_file($_FILES['hinhanh']['tmp_name'], $target)) {
                    if (!empty($product['hinhanh']) && file_exists("../" . $product['hinhanh'])) {
                        unlink("../" . $product['hinhanh']);
                    }
                    $hinhanh = 'uploads/' . $newname;
                } else {
                    $error = "Lỗi khi upload hình ảnh";
                }
            }
        }
        
        if (empty($error)) {
            $stmt = $conn->prepare("UPDATE sanpham SET tensanpham = ?, mota = ?, giaban = ?, gia_km = ?, hinhanh = ?, soluong = ?, id_danhmuc = ?, hang = ? WHERE id = ?");
            $stmt->bind_param("ssddsiisi", $tensanpham, $mota, $giaban, $gia_km, $hinhanh, $soluong, $id_danhmuc, $hang, $id);
            
            if ($stmt->execute()) {
                $message = "Cập nhật sản phẩm thành công";
                $product['tensanpham'] = $tensanpham;
                $product['mota'] = $mota;
                $product['giaban'] = $giaban;
                $product['gia_km'] = $gia_km;
                $product['hinhanh'] = $hinhanh;
                $product['soluong'] = $soluong;
                $product['id_danhmuc'] = $id_danhmuc;
                $product['hang'] = $hang;
            } else {
                $error = "Lỗi: " . $stmt->error;
            }
            
            $stmt->close();
        }
    }
}

$categories = $conn->query("SELECT id, ten FROM danhmuc ORDER BY ten");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Sửa sản phẩm</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f4;
    }

    .container {
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
        background-color: white;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    h2 {
        margin-top: 0;
        color: #333;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .form-group textarea {
        height: 100px;
    }

    .current-image {
        margin: 10px 0;
    }

    .current-image img {
        max-width: 200px;
        max-height: 200px;
        border: 1px solid #ddd;
    }

    .success {
        color: green;
        padding: 10px;
        background-color: #f0fff0;
        border: 1px solid #d0e9c6;
        margin-bottom: 15px;
    }

    .error {
        color: red;
        padding: 10px;
        background-color: #fff0f0;
        border: 1px solid #ebccd1;
        margin-bottom: 15px;
    }

    button {
        padding: 10px 15px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    button:hover {
        background-color: #45a049;
    }

    a {
        display: inline-block;
        margin-top: 15px;
        color: #333;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }
    </style>
</head>

<body>
    <div class="container">
        <h2>Sửa sản phẩm</h2>

        <?php if (!empty($message)): ?>
        <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="masp">Mã sản phẩm:</label>
                <input type="text" id="masp" name="masp" value="<?php echo htmlspecialchars($product['masp']); ?>"
                    readonly>
                <small>Mã sản phẩm không thể thay đổi</small>
            </div>

            <div class="form-group">
                <label for="tensanpham">Tên sản phẩm:</label>
                <input type="text" id="tensanpham" name="tensanpham"
                    value="<?php echo htmlspecialchars($product['tensanpham']); ?>" required>
            </div>

            <div class="form-group">
                <label for="mota">Mô tả:</label>
                <textarea id="mota" name="mota"><?php echo htmlspecialchars($product['mota']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="giaban">Giá bán:</label>
                <input type="number" id="giaban" name="giaban" min="0" step="1000"
                    value="<?php echo $product['giaban']; ?>" required>
            </div>

            <div class="form-group">
                <label for="gia_km">Giá khuyến mãi (để trống nếu không có):</label>
                <input type="number" id="gia_km" name="gia_km" min="0" step="1000"
                    value="<?php echo $product['gia_km']; ?>">
            </div>

            <div class="form-group">
                <label for="hinhanh">Hình ảnh hiện tại:</label>
                <div class="current-image">
                    <?php if (!empty($product['hinhanh'])): ?>
                    <img src="../<?php echo htmlspecialchars($product['hinhanh']); ?>" alt="Sản phẩm">
                    <?php else: ?>
                    <p>Không có hình ảnh</p>
                    <?php endif; ?>
                </div>
                <label for="hinhanh">Thay đổi hình ảnh (để trống nếu giữ nguyên):</label>
                <input type="file" id="hinhanh" name="hinhanh">
            </div>

            <div class="form-group">
                <label for="soluong">Số lượng:</label>
                <input type="number" id="soluong" name="soluong" min="0" value="<?php echo $product['soluong']; ?>"
                    required>
            </div>

            <div class="form-group">
                <label for="id_danhmuc">Danh mục:</label>
                <select id="id_danhmuc" name="id_danhmuc" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php while ($category = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $category['id']; ?>"
                        <?php echo $product['id_danhmuc'] == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['ten']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="hang">Hãng sản xuất:</label>
                <input type="text" id="hang" name="hang" value="<?php echo htmlspecialchars($product['hang']); ?>">
            </div>

            <button type="submit" name="capnhat_sp">Cập nhật sản phẩm</button>
        </form>

        <a href="admin_sanpham.php">Quay lại danh sách sản phẩm</a>
    </div>
</body>

</html>