<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Vui lòng đăng nhập để gửi câu hỏi.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $question_text = $_POST['question_text'] ?? '';

    if (empty($name) || empty($phone) || empty($email) || empty($subject) || empty($question_text)) {
        $response['message'] = 'Vui lòng điền đầy đủ tất cả các trường.';
    } else {
        // Chuẩn bị câu lệnh SQL để chèn câu hỏi với các trường mới
        $stmt = $conn->prepare("INSERT INTO questions (user_id, name, phone, email, subject, question_text, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("isssss", $user_id, $name, $phone, $email, $subject, $question_text);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Câu hỏi của bạn đã được gửi thành công!';
        } else {
            $response['message'] = 'Lỗi khi gửi câu hỏi: ' . $stmt->error;
        }
        $stmt->close();
    }
} else {
    $response['message'] = 'Yêu cầu không hợp lệ.';
}

$conn->close();
echo json_encode($response);
?> 