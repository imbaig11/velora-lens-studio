<?php
// =============================================
//  VELORA LENS STUDIO — Client Login
//  File: user/login.php
// =============================================
require_once __DIR__ . '/../php/config.php';
configure_session();
session_start();

if (is_user_logged_in()) { header('Location: dashboard.php'); exit; }

$error = '';
$old_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $old_email = $email;

    if (!$email || !$password) {
        $error = 'Please enter your email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $conn = db_connect();
        if ($conn->connect_error) {
            $error = 'Database connection failed. Please try again.';
        } else {
            $stmt = $conn->prepare("SELECT id, full_name, password_hash, is_active FROM users WHERE email = ? LIMIT 1");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (!$user['is_active']) {
                    $error = 'Your account has been deactivated. Please contact support.';
                } elseif (password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id']    = $user['id'];
                    $_SESSION['user_name']  = $user['full_name'];
                    $_SESSION['user_email'] = $email;
                    // Update last login
                    $upd = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $upd->bind_param('i', $user['id']);
                    $upd->execute();
                    $upd->close();
                    $stmt->close(); $conn->close();
                    $redirect = $_GET['redirect'] ?? 'dashboard.php';
                    header('Location: ' . $redirect);
                    exit;
                } else {
                    $error = 'Invalid email or password.';
                }
            } else {
                $error = 'Invalid email or password.';
            }
            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Login | Velora Lens Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --gold:#D8A03D; --gold2:#e8b84b; --dark:#160e04; --cream:#F5E8D3; --black:#0a0604; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Poppins',sans-serif; background:var(--black);
            color:var(--cream); min-height:100vh;
            display:flex; align-items:center; justify-content:center; padding:24px 16px;
        }
        body::before { content:''; position:fixed; top:-40%; right:-25%; width:700px; height:700px; border-radius:50%; border:1px solid rgba(216,160,61,0.06); pointer-events:none; }
        body::after  { content:''; position:fixed; bottom:-35%; left:-18%; width:550px; height:550px; border-radius:50%; border:1px solid rgba(216,160,61,0.05); pointer-events:none; }
        .card {
            width:100%; max-width:420px; padding:48px 40px;
            background:var(--dark); border:1px solid rgba(216,160,61,0.15);
            border-top:3px solid var(--gold); border-radius:8px;
            box-shadow:0 30px 80px rgba(0,0,0,0.6);
            position:relative; z-index:1; animation:fadeUp 0.55s ease;
        }
        @keyframes fadeUp { from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:translateY(0)} }
        .logo { text-align:center; margin-bottom:32px; }
        .logo-main { display:block; font-family:'Playfair Display',serif; font-size:1.3rem; font-weight:700; color:var(--gold); letter-spacing:5px; }
        .logo-sub  { display:block; font-size:0.62rem; color:rgba(245,232,211,0.4); letter-spacing:3px; margin-top:3px; }
        .logo svg  { margin-bottom:12px; }
        .card-title { font-family:'Playfair Display',serif; font-size:1.25rem; text-align:center; margin-bottom:4px; }
        .card-sub   { font-size:0.77rem; text-align:center; color:rgba(245,232,211,0.4); margin-bottom:28px; }
        .alert { display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:4px; font-size:0.82rem; margin-bottom:20px; }
        .alert-error { background:rgba(216,61,61,0.12); border:1px solid rgba(216,61,61,0.3); color:#e78686; animation:shake .4s ease; }
        @keyframes shake { 0%,100%{transform:translateX(0)} 25%{transform:translateX(-6px)} 75%{transform:translateX(6px)} }
        .form-group { margin-bottom:20px; }
        .form-group label { display:block; font-size:0.7rem; font-weight:500; letter-spacing:1.5px; text-transform:uppercase; color:rgba(245,232,211,0.5); margin-bottom:7px; }
        .input-wrap { position:relative; }
        .input-wrap i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:rgba(216,160,61,0.4); font-size:0.82rem; pointer-events:none; }
        .input-wrap input { width:100%; padding:13px 16px 13px 42px; background:rgba(10,6,4,0.8); border:1px solid rgba(216,160,61,0.18); border-radius:4px; color:var(--cream); font-family:'Poppins',sans-serif; font-size:0.87rem; outline:none; transition:border-color .3s; }
        .input-wrap input::placeholder { color:rgba(245,232,211,0.22); }
        .input-wrap input:focus { border-color:var(--gold); box-shadow:0 0 0 2px rgba(216,160,61,0.1); }
        .input-wrap:focus-within i { color:var(--gold); }
        .btn-submit { width:100%; padding:14px; margin-top:4px; background:linear-gradient(135deg,var(--gold),var(--gold2)); color:#111; font-family:'Poppins',sans-serif; font-size:0.9rem; font-weight:600; border:none; border-radius:4px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:all .3s; box-shadow:0 4px 20px rgba(216,160,61,0.3); }
        .btn-submit:hover { transform:translateY(-2px); box-shadow:0 8px 30px rgba(216,160,61,0.5); }
        .divider { text-align:center; margin:22px 0; font-size:0.75rem; color:rgba(245,232,211,0.25); position:relative; }
        .divider::before,.divider::after { content:''; position:absolute; top:50%; width:42%; height:1px; background:rgba(216,160,61,0.12); }
        .divider::before{left:0} .divider::after{right:0}
        .link-row { text-align:center; font-size:0.8rem; color:rgba(245,232,211,0.4); }
        .link-row a { color:var(--gold); transition:color .3s; }
        .link-row a:hover { color:var(--gold2); }
        .back-link { display:block; text-align:center; margin-top:20px; font-size:0.76rem; color:rgba(245,232,211,0.3); transition:color .3s; }
        .back-link:hover { color:var(--gold); }
        @media(max-width:480px){ .card{padding:36px 22px;} }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">
        <svg width="44" height="44" viewBox="0 0 44 44" fill="none">
            <circle cx="22" cy="22" r="20" stroke="#D8A03D" stroke-width="2"/>
            <circle cx="22" cy="22" r="13" stroke="#D8A03D" stroke-width="1.5"/>
            <circle cx="22" cy="22" r="6"  fill="#D8A03D" opacity="0.85"/>
            <line x1="22" y1="2"  x2="22" y2="9"  stroke="#D8A03D" stroke-width="1.5"/>
            <line x1="22" y1="35" x2="22" y2="42" stroke="#D8A03D" stroke-width="1.5"/>
            <line x1="2"  y1="22" x2="9"  y2="22" stroke="#D8A03D" stroke-width="1.5"/>
            <line x1="35" y1="22" x2="42" y2="22" stroke="#D8A03D" stroke-width="1.5"/>
        </svg>
        <span class="logo-main">VELORA</span>
        <span class="logo-sub">Client Portal</span>
    </div>

    <h1 class="card-title">Welcome Back</h1>
    <p class="card-sub">Sign in to view and manage your bookings</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>
        <div class="form-group">
            <label for="email">Email Address</label>
            <div class="input-wrap">
                <input type="email" id="email" name="email" placeholder="your@email.com"
                       value="<?= htmlspecialchars($old_email) ?>" required autofocus>
                <i class="fa-solid fa-envelope"></i>
            </div>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrap">
                <input type="password" id="password" name="password" placeholder="Your password" required>
                <i class="fa-solid fa-lock"></i>
            </div>
        </div>
        <button type="submit" class="btn-submit">
            <i class="fa-solid fa-right-to-bracket"></i> Sign In
        </button>
    </form>

    <div class="divider">or</div>
    <p class="link-row">New client? <a href="register.php">Create an account</a></p>
    <a href="../index.html" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Website</a>
</div>
</body>
</html>
