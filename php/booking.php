<?php
// =============================================
//  VELORA LENS STUDIO — Booking Form Handler
//  File: php/booking.php
// =============================================
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

function clean($v) {
    return htmlspecialchars(strip_tags(trim($v ?? '')));
}

$name       = clean($_POST['name']);
$email      = clean($_POST['email']);
$phone      = clean($_POST['phone']);
$event_type = clean($_POST['event_type']);
$event_date = clean($_POST['event_date']);
$location   = clean($_POST['location']);
$package    = clean($_POST['package'] ?? '');
$message    = clean($_POST['message'] ?? '');

// Validation
if (!$name || !$email || !$phone || !$event_type || !$event_date || !$location) {
    echo json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}
if (strlen($name) < 2 || strlen($name) > 100) {
    echo json_encode(['success' => false, 'message' => 'Name must be 2–100 characters.']);
    exit;
}

// DB connection (uses shared helper from config.php)
$conn = db_connect_or_die_json();

// Check daily booking limit (uses MAX_BOOKINGS_PER_DAY from config.php)
$check_sql  = "SELECT COUNT(*) as cnt FROM bookings WHERE event_date = ? AND status != 'Cancelled'";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param('s', $event_date);
$check_stmt->execute();
$check_result = $check_stmt->get_result()->fetch_assoc();
$check_stmt->close();

if ($check_result['cnt'] >= MAX_BOOKINGS_PER_DAY) {
    echo json_encode([
        'success' => false,
        'message' => 'Sorry, this date (' . date('d M Y', strtotime($event_date)) . ') is fully booked. '
                   . 'Maximum ' . MAX_BOOKINGS_PER_DAY . ' bookings per day. Please choose a different date.'
    ]);
    $conn->close();
    exit;
}

// Insert
$sql  = "INSERT INTO bookings (name, email, phone, event_type, event_date, location, package, message)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssssssss', $name, $email, $phone, $event_type, $event_date, $location, $package, $message);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Booking submitted successfully! We will contact you within 24 hours.',
        'id'      => $stmt->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save booking. Please try again.']);
}

$stmt->close();
$conn->close();
?>
