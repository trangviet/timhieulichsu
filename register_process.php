<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm-password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $response['message'] = 'Vui lòng điền đầy đủ tất cả các trường.';
    } else if ($password !== $confirmPassword) {
        $response['message'] = 'Mật khẩu và xác nhận mật khẩu không khớp.';
    } else {
        // Mã hóa mật khẩu trước khi lưu vào cơ sở dữ liệu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user'; // Mặc định tất cả tài khoản đăng ký là 'user'

        // Chuẩn bị câu lệnh SQL để kiểm tra tên người dùng hoặc email đã tồn tại chưa
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $response['message'] = 'Tên đăng nhập hoặc Email đã tồn tại. Vui lòng chọn tên khác.';
        } else {
            // Chuẩn bị câu lệnh SQL để chèn dữ liệu
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $hashed_password, $email, $role);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.';
            } else {
                $response['message'] = 'Lỗi khi đăng ký: ' . $stmt->error;
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
} else {
    $response['message'] = 'Yêu cầu không hợp lệ.';
}

$conn->close();
echo json_encode($response);
?> 