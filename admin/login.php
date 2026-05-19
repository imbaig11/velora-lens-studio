<?php
// =============================================
//  VELORA LENS STUDIO — Admin Login
//  File: admin/login.php
// =============================================
require_once __DIR__ . '/../php/config.php';
configure_session();
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = 'Please enter both username and password.';
    } else {
        $conn = db_connect();
        if ($conn->connect_error) {
            $error = 'Database connection failed. Please check your setup.';
        } else {
            $conn->set_charset('utf8mb4');
            $stmt = $conn->prepare("SELECT id, username, password_hash, full_name FROM admins WHERE username = ? AND is_active = 1 LIMIT 1");
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                if (password_verify($password, $admin['password_hash'])) {
                    // Login success
                    $_SESSION['admin_id']   = $admin['id'];
                    $_SESSION['admin_user'] = $admin['username'];
                    $_SESSION['admin_name'] = $admin['full_name'];

                    // Update last login
                    $conn->query("UPDATE admins SET last_login = NOW() WHERE id = " . (int)$admin['id']);

                    $stmt->close();
                    $conn->close();
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Invalid username or password.';
                }
            } else {
                $error = 'Invalid username or password.';
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
    <title>Admin Login | Velora Lens Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --gold:#D8A03D; --gold2:#e8b84b; --black:#0D0804; --dark:#160e04; --cream:#F5E8D3; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Poppins',sans-serif;
            background:#0a0604;
            color:var(--cream);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            overflow:hidden;
        }

        /* Background decoration */
        body::before {
            content:''; position:fixed; top:-50%; right:-30%;
            width:800px; height:800px; border-radius:50%;
            border:1px solid rgba(216,160,61,0.06);
            pointer-events:none;
        }
        body::after {
            content:''; position:fixed; bottom:-40%; left:-20%;
            width:600px; height:600px; border-radius:50%;
            border:1px solid rgba(216,160,61,0.05);
            pointer-events:none;
        }

        /* Login Card */
        .login-card {
            width:100%; max-width:420px; padding:48px 40px;
            background:var(--dark);
            border:1px solid rgba(216,160,61,0.15);
            border-top:3px solid var(--gold);
            border-radius:8px;
            box-shadow:0 30px 80px rgba(0,0,0,0.6);
            position:relative; z-index:1;
            animation:fadeUp 0.6s ease;
        }
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(30px); }
            to   { opacity:1; transform:translateY(0); }
        }

        /* Logo */
        .login-logo {
            text-align:center; margin-bottom:36px;
        }
        .login-logo svg { margin-bottom:14px; }
        .login-logo-main {
            display:block; font-family:'Playfair Display',serif;
            font-size:1.4rem; font-weight:700; color:var(--gold);
            letter-spacing:5px;
        }
        .login-logo-sub {
            display:block; font-size:0.65rem; color:rgba(245,232,211,0.45);
            letter-spacing:3px; margin-top:4px;
        }

        /* Form */
        .login-title {
            font-family:'Playfair Display',serif;
            font-size:1.3rem; text-align:center;
            color:var(--cream); margin-bottom:6px;
        }
        .login-subtitle {
            font-size:0.78rem; text-align:center;
            color:rgba(245,232,211,0.4); margin-bottom:32px;
        }

        .form-group { margin-bottom:20px; }
        .form-group label {
            display:block; font-size:0.72rem; font-weight:500;
            letter-spacing:1.5px; text-transform:uppercase;
            color:rgba(245,232,211,0.5); margin-bottom:8px;
        }
        .input-wrap {
            position:relative;
        }
        .input-wrap i {
            position:absolute; left:14px; top:50%; transform:translateY(-50%);
            color:rgba(216,160,61,0.4); font-size:0.85rem;
        }
        .form-group input {
            width:100%; padding:13px 16px 13px 42px;
            background:rgba(10,6,4,0.8);
            border:1px solid rgba(216,160,61,0.18);
            border-radius:4px; color:var(--cream);
            font-family:'Poppins',sans-serif; font-size:0.88rem;
            transition:border-color 0.3s; outline:none;
        }
        .form-group input::placeholder { color:rgba(245,232,211,0.25); }
        .form-group input:focus {
            border-color:var(--gold);
            box-shadow:0 0 0 2px rgba(216,160,61,0.1);
        }
        .form-group input:focus + i,
        .input-wrap:focus-within i { color:var(--gold); }

        /* Submit */
        .btn-login {
            width:100%; padding:14px;
            background:linear-gradient(135deg, var(--gold), var(--gold2));
            color:#111; font-family:'Poppins',sans-serif;
            font-size:0.9rem; font-weight:600;
            border:none; border-radius:4px; cursor:pointer;
            display:flex; align-items:center; justify-content:center; gap:8px;
            transition:all 0.3s;
            box-shadow:0 4px 20px rgba(216,160,61,0.3);
            margin-top:8px;
        }
        .btn-login:hover {
            transform:translateY(-2px);
            box-shadow:0 8px 30px rgba(216,160,61,0.5);
        }

        /* Error */
        .login-error {
            display:flex; align-items:center; gap:10px;
            padding:12px 16px; border-radius:4px; margin-bottom:20px;
            background:rgba(216,61,61,0.12);
            border:1px solid rgba(216,61,61,0.3);
            color:#e78686; font-size:0.82rem;
            animation:shake 0.4s ease;
        }
        @keyframes shake {
            0%,100% { transform:translateX(0); }
            25%     { transform:translateX(-6px); }
            75%     { transform:translateX(6px); }
        }

        /* Back link */
        .back-link {
            display:block; text-align:center; margin-top:28px;
            font-size:0.78rem; color:rgba(245,232,211,0.35);
            transition:color 0.3s;
        }
        .back-link:hover { color:var(--gold); }
        .back-link i { margin-right:4px; }

        /* Responsive */
        @media(max-width:480px) {
            .login-card { margin:16px; padding:36px 24px; }
        }
    </style>
</head>
<body>

<div class="login-card">
    <!-- Logo -->
    <div class="login-logo">
        <svg width="48" height="48" viewBox="0 0 44 44" fill="none">
            <circle cx="22" cy="22" r="20" stroke="#D8A03D" stroke-width="2"/>
            <circle cx="22" cy="22" r="13" stroke="#D8A03D" stroke-width="1.5"/>
            <circle cx="22" cy="22" r="6" fill="#D8A03D" opacity="0.85"/>
            <line x1="22" y1="2" x2="22" y2="9" stroke="#D8A03D" stroke-width="1.5"/>
            <line x1="22" y1="35" x2="22" y2="42" stroke="#D8A03D" stroke-width="1.5"/>
            <line x1="2" y1="22" x2="9" y2="22" stroke="#D8A03D" stroke-width="1.5"/>
            <line x1="35" y1="22" x2="42" y2="22" stroke="#D8A03D" stroke-width="1.5"/>
        </svg>
        <span class="login-logo-main">VELORA</span>
        <span class="login-logo-sub">Admin Panel</span>
    </div>

    <h1 class="login-title">Welcome Back</h1>
    <p class="login-subtitle">Sign in to manage your studio</p>

    <?php if($error): ?>
        <div class="login-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="username">Username</label>
            <div class="input-wrap">
                <input type="text" id="username" name="username" placeholder="Enter your username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
                <i class="fa-solid fa-user"></i>
            </div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrap">
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <i class="fa-solid fa-lock"></i>
            </div>
        </div>

        <button type="submit" class="btn-login">
            <i class="fa-solid fa-right-to-bracket"></i> Sign In
        </button>
    </form>

    <a href="../index.html" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to Website
    </a>
</div>

</body>
</html>
