<?php
// =============================================
//  VELORA LENS STUDIO — Contact Form Handler
//  File: php/contact.php
// =============================================
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

function clean($v) {
    return htmlspecialchars(strip_tags(trim($v ?? '')));
}

$name    = clean($_POST['name']);
$email   = clean($_POST['email']);
$phone   = clean($_POST['phone'] ?? '');
$subject = clean($_POST['subject'] ?? '');
$message = clean($_POST['message']);

if (!$name || !$email || !$message) {
    echo json_encode(['success' => false, 'message' => 'Name, email, and message are required.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

// DB connection (uses shared helper from config.php)
$conn = db_connect_or_die_json();

$sql  = "INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sssss', $name, $email, $phone, $subject, $message);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Message received! We will get back to you within 24 hours.',
        'id'      => $stmt->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again.']);
}

$stmt->close();
$conn->close();
?>
