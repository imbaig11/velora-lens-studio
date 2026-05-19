<?php
require_once __DIR__ . '/config.php';
configure_session();
session_start();

header('Content-Type: application/json');

if (is_user_logged_in()) {
    echo json_encode([
        'logged_in' => true,
        'user_name' => $_SESSION['user_name'] ?? '',
        'user_email' => $_SESSION['user_email'] ?? ''
    ]);
} else {
    echo json_encode([
        'logged_in' => false
    ]);
}
