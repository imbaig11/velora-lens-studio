<?php
// =============================================
//  VELORA LENS STUDIO — Client Logout
//  File: user/logout.php
// =============================================
require_once __DIR__ . '/../php/config.php';
configure_session();
session_start();
// Only destroy user session keys, preserve admin session if any
unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
if (empty($_SESSION)) session_destroy();
header('Location: login.php?loggedout=1');
exit;
?>
