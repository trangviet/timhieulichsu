<?php
// Tắt hiển thị lỗi
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Thiết lập error handler
function handleError($errno, $errstr, $errfile, $errline) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $errstr,
        'data' => []
    ]);
    exit();
}

// Thiết lập exception handler
function handleException($exception) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $exception->getMessage(),
        'data' => []
    ]);
    exit();
}

// Đăng ký các handler
set_error_handler('handleError');
set_exception_handler('handleException');
?> 