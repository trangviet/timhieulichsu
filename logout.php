<?php
session_start();

// Xóa tất cả các biến session
$_SESSION = array();

// Hủy session
session_destroy();

// Trả về JSON thành công
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Bạn đã đăng xuất thành công.']);
exit();
?> 