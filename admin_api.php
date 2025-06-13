<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Kiểm tra xem người dùng có phải là admin không
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $response['message'] = 'Truy cập bị từ chối. Bạn không có quyền quản trị.';
    echo json_encode($response);
    exit();
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_users':
        // Lấy danh sách tất cả người dùng
        $stmt = $conn->prepare("SELECT id, username, email, role FROM users");
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $response['success'] = true;
        $response['data'] = $users;
        $stmt->close();
        break;

    case 'update_user':
        // Cập nhật thông tin người dùng (chủ yếu là vai trò)
        $user_id = $_POST['id'] ?? 0;
        $role = $_POST['role'] ?? '';

        if ($user_id > 0 && !empty($role)) {
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $role, $user_id);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Cập nhật người dùng thành công.';
            } else {
                $response['message'] = 'Lỗi khi cập nhật người dùng: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'Dữ liệu không hợp lệ để cập nhật người dùng.';
        }
        break;

    case 'delete_user':
        // Xóa người dùng
        $user_id = $_POST['id'] ?? 0;

        if ($user_id > 0) {
            // Tránh xóa chính tài khoản admin đang đăng nhập
            if ($user_id == $_SESSION['user_id']) {
                $response['message'] = 'Không thể xóa tài khoản admin hiện tại.';
            } else {
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Xóa người dùng thành công.';
                } else {
                    $response['message'] = 'Lỗi khi xóa người dùng: ' . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            $response['message'] = 'ID người dùng không hợp lệ.';
        }
        break;

    case 'get_questions':
        // Lấy danh sách tất cả câu hỏi
        // Join với bảng users để lấy tên người dùng
        // Thêm các cột mới: name, phone, email
        $stmt = $conn->prepare("SELECT q.id, q.user_id, u.username, q.name, q.phone, q.email, q.subject, q.question_text, q.answer_text, q.status, q.created_at FROM questions q JOIN users u ON q.user_id = u.id ORDER BY q.created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        $questions = [];
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row;
        }
        $response['success'] = true;
        $response['data'] = $questions;
        $stmt->close();
        break;

    case 'answer_question':
        // Trả lời câu hỏi
        $question_id = $_POST['id'] ?? 0;
        $answer_text = $_POST['answer'] ?? '';

        if ($question_id > 0 && !empty($answer_text)) {
            $stmt = $conn->prepare("UPDATE questions SET answer_text = ?, status = 'answered' WHERE id = ?");
            $stmt->bind_param("si", $answer_text, $question_id);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Đã gửi câu trả lời thành công.';
            } else {
                $response['message'] = 'Lỗi khi trả lời câu hỏi: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'Dữ liệu không hợp lệ để trả lời câu hỏi.';
        }
        break;

    case 'delete_question':
        // Xóa câu hỏi
        $question_id = $_POST['id'] ?? 0;

        if ($question_id > 0) {
            $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
            $stmt->bind_param("i", $question_id);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Xóa câu hỏi thành công.';
            } else {
                $response['message'] = 'Lỗi khi xóa câu hỏi: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'ID câu hỏi không hợp lệ.';
        }
        break;

    default:
        $response['message'] = 'Hành động không hợp lệ.';
        break;
}

$conn->close();
echo json_encode($response);
?> 