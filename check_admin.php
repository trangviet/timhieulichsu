<?php
session_start();

header('Content-Type: application/json');

$response = ['is_admin' => false];

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $response['is_admin'] = true;
}

echo json_encode($response);
?> 