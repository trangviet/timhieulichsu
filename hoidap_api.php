<?php
// Đảm bảo không có output nào trước header
ob_start();

// Include error handler
require_once 'error_handler.php';

// Bắt đầu session
session_start();

// Thiết lập header JSON
header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'data' => []];

try {
    // Kết nối database
    require_once 'db_connect.php';

    $action = $_GET['action'] ?? '';
    if (empty($action)) {
        throw new Exception('Không có hành động được chỉ định.');
    }

    switch ($action) {
        case 'get_public_questions':
            // Lấy các câu hỏi đã được trả lời (công khai)
            $search_term = $_GET['search'] ?? '';
            $stmt = $conn->prepare("SELECT q.id, q.user_id, u.username, q.name, q.phone, q.email, q.subject, q.question_text, q.answer_text, q.status, q.created_at FROM questions q JOIN users u ON q.user_id = u.id WHERE q.status = 'answered' AND (q.subject LIKE ? OR q.question_text LIKE ? OR q.name LIKE ? OR q.email LIKE ? OR q.phone LIKE ?) ORDER BY q.created_at DESC");
            if (!$stmt) {
                throw new Exception('Lỗi chuẩn bị truy vấn: ' . $conn->error);
            }
            $search_param = "%$search_term%";
            if (!$stmt->bind_param("sssss", $search_param, $search_param, $search_param, $search_param, $search_param)) {
                throw new Exception('Lỗi ràng buộc tham số: ' . $stmt->error);
            }
            if (!$stmt->execute()) {
                throw new Exception('Lỗi thực thi truy vấn: ' . $stmt->error);
            }
            $result = $stmt->get_result();
            $questions = [];
            while ($row = $result->fetch_assoc()) {
                $questions[] = $row;
            }
            $response['success'] = true;
            $response['data'] = $questions;
            $stmt->close();
            break;

        case 'get_personal_questions':
            // Lấy câu hỏi của người dùng hiện tại
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Vui lòng đăng nhập để xem câu hỏi của bạn.');
            }
            $user_id = $_SESSION['user_id'];
            $search_term = $_GET['search'] ?? '';
            
            // Lấy cả câu hỏi chờ xử lý và đã trả lời, không lấy câu hỏi đã thu hồi
            $stmt = $conn->prepare("SELECT q.id, q.user_id, u.username, q.name, q.phone, q.email, q.subject, q.question_text, q.answer_text, q.status, q.created_at FROM questions q JOIN users u ON q.user_id = u.id WHERE q.user_id = ? AND q.status != 'withdrawn' AND (q.subject LIKE ? OR q.question_text LIKE ? OR q.name LIKE ? OR q.email LIKE ? OR q.phone LIKE ?) ORDER BY q.created_at DESC");
            if (!$stmt) {
                throw new Exception('Lỗi chuẩn bị truy vấn: ' . $conn->error);
            }
            $search_param = "%$search_term%";
            if (!$stmt->bind_param("issssss", $user_id, $search_param, $search_param, $search_param, $search_param, $search_param)) {
                throw new Exception('Lỗi ràng buộc tham số: ' . $stmt->error);
            }
            if (!$stmt->execute()) {
                throw new Exception('Lỗi thực thi truy vấn: ' . $stmt->error);
            }
            $result = $stmt->get_result();
            $questions = [];
            while ($row = $result->fetch_assoc()) {
                $questions[] = $row;
            }
            $response['success'] = true;
            $response['data'] = $questions;
            $stmt->close();
            break;

        case 'withdraw_question':
            // Thu hồi câu hỏi (chuyển trạng thái sang 'withdrawn')
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Vui lòng đăng nhập để thực hiện hành động này.');
            }
            $question_id = $_POST['id'] ?? 0;
            $user_id = $_SESSION['user_id'];

            if ($question_id <= 0) {
                throw new Exception('ID câu hỏi không hợp lệ.');
            }

            // Đảm bảo chỉ người dùng sở hữu câu hỏi mới được thu hồi
            $stmt = $conn->prepare("UPDATE questions SET status = 'withdrawn' WHERE id = ? AND user_id = ?");
            if (!$stmt) {
                throw new Exception('Lỗi chuẩn bị truy vấn: ' . $conn->error);
            }
            if (!$stmt->bind_param("ii", $question_id, $user_id)) {
                throw new Exception('Lỗi ràng buộc tham số: ' . $stmt->error);
            }
            if (!$stmt->execute()) {
                throw new Exception('Lỗi thực thi truy vấn: ' . $stmt->error);
            }
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Câu hỏi đã được thu hồi thành công.';
            } else {
                throw new Exception('Không tìm thấy câu hỏi hoặc bạn không có quyền thu hồi câu hỏi này.');
            }
            $stmt->close();
            break;

        default:
            throw new Exception('Hành động không hợp lệ.');
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

// Đóng kết nối và gửi response
if (isset($conn)) {
    $conn->close();
}

// Xóa bất kỳ output buffer nào còn lại
ob_end_clean();

// Gửi response
echo json_encode($response);
?> 