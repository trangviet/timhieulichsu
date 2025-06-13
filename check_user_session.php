<?php
session_start();

header('Content-Type: application/json');

$response = ['is_logged_in' => false, 'user_id' => null, 'username' => null, 'role' => null];

if (isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['role'])) {
    $response['is_logged_in'] = true;
    $response['user_id'] = $_SESSION['user_id'];
    $response['username'] = $_SESSION['username'];
    $response['role'] = $_SESSION['role'];
}

echo json_encode($response);
?> 