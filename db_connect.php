<?php
// Đảm bảo không có output nào trước header
ob_start();

// Include error handler
require_once 'error_handler.php';

$servername = "localhost";
$username = "root"; // Tên người dùng mặc định của MySQL trong XAMPP
$password = "";     // Mật khẩu mặc định của MySQL trong XAMPP (thường là rỗng)
$dbname = "bmw_db"; // Tên cơ sở dữ liệu bạn đã tạo

try {
    // Tạo kết nối
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Kiểm tra kết nối
    if ($conn->connect_error) {
        throw new Exception("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
    }
} catch (Exception $e) {
    // Lỗi sẽ được xử lý bởi error handler
    throw $e;
}
// echo "Kết nối cơ sở dữ liệu thành công!"; // Bạn có thể bỏ dòng này sau khi kiểm tra
?> 