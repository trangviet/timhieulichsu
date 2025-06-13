<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $response['message'] = 'Vui lòng điền đầy đủ tên đăng nhập và mật khẩu.';
    } else {
        // Chuẩn bị câu lệnh SQL để lấy thông tin người dùng
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $db_username, $hashed_password, $role);

        if ($stmt->num_rows > 0) {
            $stmt->fetch();
            // Xác minh mật khẩu
            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $db_username;
                $_SESSION['role'] = $role;

                $response['success'] = true;
                $response['message'] = 'Đăng nhập thành công!';
                $response['redirect'] = ($role === 'admin') ? 'admin/admin.html' : 'index.html';
            } else {
                $response['message'] = 'Mật khẩu không đúng.';
            }
        } else {
            $response['message'] = 'Tên đăng nhập không tồn tại.';
        }
        $stmt->close();
    }
} else {
    $response['message'] = 'Yêu cầu không hợp lệ.';
}

$conn->close();
echo json_encode($response);
?> 