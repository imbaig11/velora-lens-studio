<?php
// =============================================
//  VELORA LENS STUDIO — Shared DB Configuration
//  File: php/config.php
//  Include this file at the top of every PHP file
//  that needs a database connection.
// =============================================

// ── Database credentials ───────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // Default XAMPP password is blank
define('DB_NAME', 'photography_booking');
define('DB_PORT', 3306);

// ── App settings ───────────────────────────
define('APP_NAME',       'Velora Lens Studio');
define('ADMIN_SESSION',  'admin_id');          // Session key used to verify login
define('MAX_BOOKINGS_PER_DAY', 2);             // Daily booking limit per event date

// ── Session hardening ──────────────────────
// Call this BEFORE session_start() in admin files.
function configure_session(): void {
    ini_set('session.cookie_httponly', '1');   // Block JS from reading session cookie
    ini_set('session.use_strict_mode',  '1');  // Reject uninitialized session IDs
    ini_set('session.cookie_samesite',  'Strict');
}

// ── DB connection helper ───────────────────
// Returns a live mysqli connection or exits with JSON error
// (suitable for API endpoints like booking.php / contact.php)
function db_connect_or_die_json(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_error) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed. Please check your setup.'
        ]);
        exit;
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

define('USER_SESSION',  'user_id');            // Session key for client login

// ── DB connection helper (for admin + user pages) ─
// Returns mysqli connection; caller checks $conn->connect_error
function db_connect(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if (!$conn->connect_error) {
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

// ── User auth helpers ──────────────────────
// Returns true if a client is currently logged in
function is_user_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Redirect to user login if not authenticated (call from inside /user/ pages)
function require_user_login(): void {
    if (!is_user_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
