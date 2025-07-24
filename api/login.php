<?php
include '../includes/db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$username = isset($data['username']) ? $data['username'] : '';
$password = isset($data['password']) ? $data['password'] : '';

$response = ['success' => false, 'message' => ''];

if (empty($username) || empty($password)) {
    $response['message'] = 'Vui lòng nhập đầy đủ thông tin!';
    echo json_encode($response);
    exit;
}

$stmt = $conn->prepare("SELECT id, username, password, quyen FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    if (password_verify($password, $user['password'])) {
        $response['success'] = true;
        $response['user_id'] = $user['id'];
        $response['username'] = $user['username'];
        $response['role'] = $user['quyen'];
    } else {
        $response['message'] = 'Mật khẩu không chính xác!';
    }
} else {
    $response['message'] = 'Tài khoản không tồn tại!';
}

echo json_encode($response);