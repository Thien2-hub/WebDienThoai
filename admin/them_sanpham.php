<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['quyen'] != 'admin') {
    header('Location: ../dangnhap.php');
    exit();
}

$message = '';
$error = '';

if (isset($_POST['them_sp'])) {
    $masp = trim($_POST['masp']);
    $tensanpham = trim($_POST['tensanpham']);
    $mota = trim($_POST['mota']);
    $giaban = floatval($_POST['giaban']);
    $gia_km = !empty($_POST['gia_km']) ? floatval($_POST['gia_km']) : NULL;
    $soluong = intval($_POST['soluong']);
    $id_danhmuc = intval($_POST['id_danhmuc']);
    $hang = trim($_POST['hang']);
    
    if (empty($masp)) {
        $error = "Vui lòng nhập mã sản phẩm";
    } elseif (empty($tensanpham)) {
        $error = "Vui lòng nhập tên sản phẩm";
    } elseif ($giaban <= 0) {
        $error = "Giá bán phải lớn hơn 0";
    } elseif ($soluong < 0) {
        $error = "Số lượng không được âm";
    } else {
        $check = $conn->prepare("SELECT id FROM sanpham WHERE masp = ?");
        $check->bind_param("s", $masp);
        $check->execute();
        $check_result = $check->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Mã sản phẩm đã tồn tại!";
        } else {
            $hinhanh = '';
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
                        $hinhanh = 'uploads/' . $newname;
                    } else {
                        $error = "Lỗi khi upload hình ảnh";
                    }
                }
            }
            
            if (empty($error)) {
                $stmt = $conn->prepare("INSERT INTO sanpham (masp, tensanpham, mota, giaban, gia_km, hinhanh, soluong, id_danhmuc, hang) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssddsiis", $masp, $tensanpham, $mota, $giaban, $gia_km, $hinhanh, $soluong, $id_danhmuc, $hang);
                
                if ($stmt->execute()) {
                    $message = "Thêm sản phẩm thành công";
                    $masp = $tensanpham = $mota = $hang = '';
                    $giaban = $gia_km = $soluong = $id_danhmuc = 0;
                } else {
                    $error = "Lỗi: " . $stmt->error;
                }
                
                $stmt->close();
            }
        }
        $check->close();
    }
}

$categories = $conn->query("SELECT id, ten FROM danhmuc ORDER BY ten");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thêm sản phẩm mới</title>
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
        <h2>Thêm sản phẩm mới</h2>

        <?php if (!empty($message)): ?>
        <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="masp">Mã sản phẩm:</label>
                <input type="text" id="masp" name="masp"
                    value="<?php echo isset($masp) ? htmlspecialchars($masp) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="tensanpham">Tên sản phẩm:</label>
                <input type="text" id="tensanpham" name="tensanpham"
                    value="<?php echo isset($tensanpham) ? htmlspecialchars($tensanpham) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="mota">Mô tả:</label>
                <textarea id="mota" name="mota"><?php echo isset($mota) ? htmlspecialchars($mota) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="giaban">Giá bán:</label>
                <input type="number" id="giaban" name="giaban" min="0" step="1000"
                    value="<?php echo isset($giaban) ? $giaban : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="gia_km">Giá khuyến mãi (để trống nếu không có):</label>
                <input type="number" id="gia_km" name="gia_km" min="0" step="1000"
                    value="<?php echo isset($gia_km) ? $gia_km : ''; ?>">
            </div>

            <div class="form-group">
                <label for="hinhanh">Hình ảnh:</label>
                <input type="file" id="hinhanh" name="hinhanh">
            </div>

            <div class="form-group">
                <label for="soluong">Số lượng:</label>
                <input type="number" id="soluong" name="soluong" min="0"
                    value="<?php echo isset($soluong) ? $soluong : '0'; ?>" required>
            </div>

            <div class="form-group">
                <label for="id_danhmuc">Danh mục:</label>
                <select id="id_danhmuc" name="id_danhmuc" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php while ($category = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $category['id']; ?>"
                        <?php echo isset($id_danhmuc) && $id_danhmuc == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['ten']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="hang">Hãng sản xuất:</label>
                <input type="text" id="hang" name="hang"
                    value="<?php echo isset($hang) ? htmlspecialchars($hang) : ''; ?>">
            </div>

            <button type="submit" name="them_sp">Thêm sản phẩm</button>
        </form>

        <a href="admin_sanpham.php">Quay lại danh sách sản phẩm</a>
    </div>
</body>

</html>