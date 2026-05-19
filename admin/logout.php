<?php
// =============================================
//  VELORA LENS STUDIO — Admin Logout
//  File: admin/logout.php
// =============================================
require_once __DIR__ . '/../php/config.php';
configure_session();
session_start();
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
?>
