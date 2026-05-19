<?php
require_once __DIR__ . '/../php/config.php';
configure_session();
session_start();
require_user_login();

$user_id    = $_SESSION['user_id'];
$user_name  = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

$conn   = db_connect();
$db_ok  = !$conn->connect_error;
$msg    = '';
$msg_type = '';

// ── CANCEL booking ────────────────────────
if ($db_ok && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $bid = (int)($_POST['bid'] ?? 0);
    $stmt = $conn->prepare("SELECT id, status FROM bookings WHERE id=? AND email=?");
    $stmt->bind_param('is', $bid, $user_email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row && in_array($row['status'], ['New','Confirmed'], true)) {
        $upd = $conn->prepare("UPDATE bookings SET status='Cancelled' WHERE id=? AND email=?");
        $upd->bind_param('is', $bid, $user_email);
        $upd->execute(); $upd->close();
        $msg = 'Booking cancelled successfully.';
        $msg_type = 'success';
    } else {
        $msg = 'This booking cannot be cancelled.';
        $msg_type = 'error';
    }
}

// ── RESCHEDULE booking ────────────────────
if ($db_ok && isset($_POST['action']) && $_POST['action'] === 'reschedule') {
    $bid      = (int)($_POST['bid']      ?? 0);
    $new_date = trim($_POST['new_date']  ?? '');
    $stmt = $conn->prepare("SELECT id, status FROM bookings WHERE id=? AND email=?");
    $stmt->bind_param('is', $bid, $user_email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || !in_array($row['status'], ['New','Confirmed'], true)) {
        $msg = 'This booking cannot be rescheduled.';
        $msg_type = 'error';
    } elseif (!$new_date || strtotime($new_date) <= strtotime('today')) {
        $msg = 'Please choose a future date.';
        $msg_type = 'error';
    } else {
        // Check daily limit on new date
        $chk = $conn->prepare("SELECT COUNT(*) c FROM bookings WHERE event_date=? AND status!='Cancelled' AND id!=?");
        $chk->bind_param('si', $new_date, $bid);
        $chk->execute();
        $cnt = $chk->get_result()->fetch_assoc()['c'];
        $chk->close();
        if ($cnt >= MAX_BOOKINGS_PER_DAY) {
            $msg = 'Sorry, ' . date('d M Y', strtotime($new_date)) . ' is fully booked. Please pick another date.';
            $msg_type = 'error';
        } else {
            $upd = $conn->prepare("UPDATE bookings SET event_date=?, status='New' WHERE id=? AND email=?");
            $upd->bind_param('sis', $new_date, $bid, $user_email);
            $upd->execute(); $upd->close();
            $msg = 'Booking rescheduled to ' . date('d M Y', strtotime($new_date)) . '.';
            $msg_type = 'success';
        }
    }
}

// ── FETCH bookings ────────────────────────
$bookings = [];
if ($db_ok) {
    $q = $conn->prepare("SELECT * FROM bookings WHERE email=? ORDER BY created_at DESC");
    $q->bind_param('s', $user_email);
    $q->execute();
    $res = $q->get_result();
    while ($r = $res->fetch_assoc()) $bookings[] = $r;
    $q->close();
}

// Stats
$total     = count($bookings);
$upcoming  = count(array_filter($bookings, fn($b) => in_array($b['status'],['New','Confirmed']) && strtotime($b['event_date']) >= strtotime('today')));
$completed = count(array_filter($bookings, fn($b) => $b['status'] === 'Completed'));
$cancelled = count(array_filter($bookings, fn($b) => $b['status'] === 'Cancelled'));
if ($db_ok) $conn->close();

$status_cls = ['New'=>'s-new','Confirmed'=>'s-confirmed','Cancelled'=>'s-cancelled','Completed'=>'s-completed'];
$today_str  = date('Y-m-d');
$welcome    = isset($_GET['welcome']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | Velora Lens Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root{--gold:#D8A03D;--gold2:#e8b84b;--dark:#160e04;--cream:#F5E8D3;--black:#0e0904;--card:#1a1005;}
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Poppins',sans-serif;background:var(--black);color:var(--cream);min-height:100vh;}
        a{text-decoration:none;color:inherit;}

        /* Sidebar */
        .sidebar{position:fixed;top:0;left:0;width:230px;height:100vh;background:#0a0604;border-right:1px solid rgba(216,160,61,.12);display:flex;flex-direction:column;z-index:100;}
        .sb-logo{padding:26px 22px;border-bottom:1px solid rgba(216,160,61,.1);}
        .sb-logo span{font-family:'Playfair Display',serif;font-size:.95rem;color:var(--gold);display:block;letter-spacing:3px;}
        .sb-logo small{font-size:.6rem;color:rgba(245,232,211,.4);letter-spacing:2px;}
        .sb-nav{flex:1;padding:20px 0;}
        .sb-item{display:flex;align-items:center;gap:12px;padding:12px 22px;font-size:.84rem;color:rgba(245,232,211,.6);cursor:pointer;transition:all .2s;border-left:2px solid transparent;}
        .sb-item:hover,.sb-item.active{color:var(--gold);border-left-color:var(--gold);background:rgba(216,160,61,.06);}
        .sb-item i{width:18px;text-align:center;}
        .sb-footer{padding:16px 22px;border-top:1px solid rgba(216,160,61,.1);font-size:.72rem;color:rgba(245,232,211,.3);}
        .sb-user{display:flex;align-items:center;gap:8px;margin-bottom:6px;}
        .sb-user-name{font-size:.78rem;font-weight:500;color:var(--cream);}

        /* Main */
        .main{margin-left:230px;padding:36px 40px;}
        .page-header{margin-bottom:32px;}
        .page-header h1{font-family:'Playfair Display',serif;font-size:1.85rem;margin-bottom:4px;}
        .page-header p{font-size:.8rem;color:rgba(245,232,211,.4);}

        /* Welcome banner */
        .welcome-banner{padding:14px 20px;background:rgba(216,160,61,.1);border:1px solid rgba(216,160,61,.25);border-radius:6px;margin-bottom:28px;font-size:.85rem;color:var(--gold);display:flex;align-items:center;gap:10px;}

        /* Alert */
        .alert{padding:12px 18px;border-radius:5px;font-size:.84rem;margin-bottom:24px;display:flex;align-items:center;gap:10px;}
        .alert-success{background:rgba(61,216,120,.12);border:1px solid rgba(61,216,120,.3);color:#6ee7a8;}
        .alert-error  {background:rgba(216,61,61,.12); border:1px solid rgba(216,61,61,.3); color:#e78686;}

        /* Stats */
        .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:32px;}
        .stat-card{background:var(--dark);border:1px solid rgba(216,160,61,.12);border-top:3px solid var(--gold);border-radius:6px;padding:22px 18px;}
        .stat-num{font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:var(--gold);}
        .stat-lbl{font-size:.76rem;color:rgba(245,232,211,.5);margin-top:4px;}
        .stat-icon{font-size:1.3rem;color:rgba(216,160,61,.3);margin-bottom:6px;}

        /* Booking cards */
        .section-title{font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:18px;color:var(--cream);}
        .book-grid{display:flex;flex-direction:column;gap:16px;}
        .book-card{background:var(--dark);border:1px solid rgba(216,160,61,.12);border-radius:8px;overflow:hidden;transition:border-color .3s;}
        .book-card:hover{border-color:rgba(216,160,61,.28);}
        .book-card-head{padding:16px 22px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(216,160,61,.08);}
        .book-event{font-weight:600;font-size:.92rem;color:var(--cream);}
        .book-id{font-size:.7rem;color:rgba(245,232,211,.3);}
        .book-body{padding:16px 22px;display:grid;grid-template-columns:repeat(3,1fr);gap:14px;}
        .book-field label{font-size:.65rem;letter-spacing:1px;text-transform:uppercase;color:rgba(245,232,211,.4);display:block;margin-bottom:3px;}
        .book-field span{font-size:.83rem;color:rgba(245,232,211,.85);}
        .book-actions{padding:14px 22px;border-top:1px solid rgba(216,160,61,.08);display:flex;align-items:center;gap:12px;flex-wrap:wrap;}

        /* Status badges */
        .badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:.68rem;font-weight:600;letter-spacing:.5px;}
        .s-new      {background:rgba(61,153,216,.18);color:#6eb8e7;border:1px solid rgba(61,153,216,.3);}
        .s-confirmed{background:rgba(61,216,120,.18);color:#6ee7a8;border:1px solid rgba(61,216,120,.3);}
        .s-cancelled{background:rgba(216,61,61,.18); color:#e78686;border:1px solid rgba(216,61,61,.3);}
        .s-completed{background:rgba(216,160,61,.18);color:var(--gold);border:1px solid rgba(216,160,61,.3);}

        /* Action buttons */
        .btn-action{padding:8px 16px;border-radius:4px;font-family:'Poppins',sans-serif;font-size:.78rem;font-weight:500;cursor:pointer;border:none;transition:all .25s;display:inline-flex;align-items:center;gap:6px;}
        .btn-reschedule{background:rgba(216,160,61,.15);color:var(--gold);border:1px solid rgba(216,160,61,.3);}
        .btn-reschedule:hover{background:rgba(216,160,61,.25);}
        .btn-cancel{background:rgba(216,61,61,.12);color:#e78686;border:1px solid rgba(216,61,61,.25);}
        .btn-cancel:hover{background:rgba(216,61,61,.22);}
        .btn-book-new{background:linear-gradient(135deg,var(--gold),var(--gold2));color:#111;padding:10px 22px;border-radius:4px;font-family:'Poppins',sans-serif;font-size:.82rem;font-weight:600;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:7px;transition:all .3s;box-shadow:0 4px 18px rgba(216,160,61,.3);}
        .btn-book-new:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(216,160,61,.45);}

        /* Reschedule inline panel */
        .reschedule-panel{display:none;padding:16px 22px;background:rgba(216,160,61,.05);border-top:1px solid rgba(216,160,61,.12);}
        .reschedule-panel.open{display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
        .reschedule-panel label{font-size:.75rem;color:rgba(245,232,211,.55);}
        .reschedule-panel input[type=date]{padding:9px 14px;background:rgba(10,6,4,.8);border:1px solid rgba(216,160,61,.25);border-radius:4px;color:var(--cream);font-family:'Poppins',sans-serif;font-size:.84rem;outline:none;}
        .reschedule-panel input[type=date]:focus{border-color:var(--gold);}
        .btn-confirm-reschedule{padding:9px 18px;background:var(--gold);color:#111;border:none;border-radius:4px;font-family:'Poppins',sans-serif;font-size:.8rem;font-weight:600;cursor:pointer;transition:all .25s;}
        .btn-confirm-reschedule:hover{background:var(--gold2);}
        .btn-cancel-reschedule{padding:9px 14px;background:transparent;color:rgba(245,232,211,.4);border:1px solid rgba(245,232,211,.15);border-radius:4px;font-size:.78rem;cursor:pointer;}

        /* Empty state */
        .empty{text-align:center;padding:60px 20px;color:rgba(245,232,211,.3);}
        .empty i{font-size:2.8rem;margin-bottom:14px;display:block;}
        .empty p{margin-bottom:20px;}

        /* Responsive */
        @media(max-width:900px){
            .sidebar{display:none;}
            .main{margin-left:0;padding:22px 16px;}
            .stats{grid-template-columns:1fr 1fr;}
            .book-body{grid-template-columns:1fr 1fr;}
        }
        @media(max-width:480px){
            .stats{grid-template-columns:1fr 1fr;}
            .book-body{grid-template-columns:1fr;}
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sb-logo">
        <span>VELORA</span>
        <small>Client Portal</small>
    </div>
    <nav class="sb-nav">
        <a href="dashboard.php" class="sb-item active"><i class="fa-solid fa-calendar-check"></i> My Bookings</a>
        <a href="../booking.html" class="sb-item"><i class="fa-solid fa-plus"></i> New Booking</a>
        <a href="../index.html" class="sb-item" style="margin-top:12px;"><i class="fa-solid fa-arrow-left"></i> Back to Website</a>
        <a href="logout.php" class="sb-item" style="color:#e78686;margin-top:4px;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
    <div class="sb-footer">
        <div class="sb-user">
            <i class="fa-solid fa-circle-user" style="color:var(--gold);font-size:1rem;"></i>
            <span class="sb-user-name"><?= htmlspecialchars($user_name) ?></span>
        </div>
        <div><?= htmlspecialchars($user_email) ?></div>
    </div>
</div>

<!-- MAIN -->
<div class="main">
    <div class="page-header">
        <h1>My <span style="color:var(--gold);">Bookings</span></h1>
        <p>View, reschedule or cancel your photography sessions</p>
    </div>

    <?php if ($welcome): ?>
        <div class="welcome-banner"><i class="fa-solid fa-star"></i> Welcome to Velora Lens Studio, <?= htmlspecialchars($user_name) ?>! Your account is ready.</div>
    <?php endif; ?>

    <?php if ($msg): ?>
        <div class="alert alert-<?= $msg_type ?>">
            <i class="fa-solid fa-<?= $msg_type === 'success' ? 'check-circle' : 'circle-exclamation' ?>"></i>
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats">
        <div class="stat-card">
            <i class="fa-solid fa-calendar-alt stat-icon"></i>
            <div class="stat-num"><?= $total ?></div>
            <div class="stat-lbl">Total Bookings</div>
        </div>
        <div class="stat-card">
            <i class="fa-solid fa-clock stat-icon"></i>
            <div class="stat-num"><?= $upcoming ?></div>
            <div class="stat-lbl">Upcoming</div>
        </div>
        <div class="stat-card">
            <i class="fa-solid fa-check-circle stat-icon"></i>
            <div class="stat-num"><?= $completed ?></div>
            <div class="stat-lbl">Completed</div>
        </div>
        <div class="stat-card">
            <i class="fa-solid fa-times-circle stat-icon"></i>
            <div class="stat-num"><?= $cancelled ?></div>
            <div class="stat-lbl">Cancelled</div>
        </div>
    </div>

    <!-- Bookings -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
        <h2 class="section-title" style="margin-bottom:0;">Your Sessions</h2>
        <a href="../booking.html" class="btn-book-new"><i class="fa-solid fa-plus"></i> Book New Session</a>
    </div>

    <?php if (!$db_ok): ?>
        <div class="alert alert-error"><i class="fa-solid fa-database"></i> Database not connected.</div>
    <?php elseif (empty($bookings)): ?>
        <div class="empty">
            <i class="fa-solid fa-camera"></i>
            <p>No bookings yet. Ready to capture your story?</p>
            <a href="../booking.html" class="btn-book-new"><i class="fa-solid fa-plus"></i> Book Your First Session</a>
        </div>
    <?php else: ?>
        <div class="book-grid">
        <?php foreach ($bookings as $b):
            $can_act    = in_array($b['status'], ['New','Confirmed'], true);
            $is_past    = strtotime($b['event_date']) < strtotime('today');
            $can_cancel = $can_act;
            $can_resched = $can_act;
            $status_cls_val = $status_cls[$b['status']] ?? 's-new';
        ?>
        <div class="book-card" id="card-<?= $b['id'] ?>">
            <div class="book-card-head">
                <div>
                    <div class="book-event"><?= htmlspecialchars($b['event_type']) ?> — <?= htmlspecialchars($b['location']) ?></div>
                    <div class="book-id">Booking #<?= $b['id'] ?> &nbsp;·&nbsp; Submitted <?= date('d M Y', strtotime($b['created_at'])) ?></div>
                </div>
                <span class="badge <?= $status_cls_val ?>"><?= htmlspecialchars($b['status']) ?></span>
            </div>

            <div class="book-body">
                <div class="book-field">
                    <label><i class="fa-solid fa-calendar"></i> Event Date</label>
                    <span><?= date('d M Y', strtotime($b['event_date'])) ?></span>
                </div>
                <div class="book-field">
                    <label><i class="fa-solid fa-gem"></i> Package</label>
                    <span><?= htmlspecialchars($b['package'] ?: '—') ?></span>
                </div>
                <div class="book-field">
                    <label><i class="fa-solid fa-location-dot"></i> Location</label>
                    <span><?= htmlspecialchars($b['location']) ?></span>
                </div>
                <?php if ($b['message']): ?>
                <div class="book-field" style="grid-column:1/-1;">
                    <label><i class="fa-solid fa-comment"></i> Notes</label>
                    <span><?= htmlspecialchars(substr($b['message'],0,120)) . (strlen($b['message'])>120?'…':'') ?></span>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($can_cancel || $can_resched): ?>
            <div class="book-actions">
                <?php if ($can_resched): ?>
                <button class="btn-action btn-reschedule" onclick="toggleReschedule(<?= $b['id'] ?>)">
                    <i class="fa-solid fa-calendar-pen"></i> Reschedule
                </button>
                <?php endif; ?>
                <?php if ($can_cancel): ?>
                <form method="POST" style="display:inline;"
                      onsubmit="return confirm('Cancel this booking? This cannot be undone.')">
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="bid"    value="<?= $b['id'] ?>">
                    <button type="submit" class="btn-action btn-cancel">
                        <i class="fa-solid fa-xmark"></i> Cancel Booking
                    </button>
                </form>
                <?php endif; ?>
            </div>

            <!-- Inline reschedule panel -->
            <div class="reschedule-panel" id="resched-<?= $b['id'] ?>">
                <form method="POST" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;width:100%">
                    <input type="hidden" name="action" value="reschedule">
                    <input type="hidden" name="bid"    value="<?= $b['id'] ?>">
                    <label>New Date:</label>
                    <input type="date" name="new_date"
                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                           value="<?= htmlspecialchars($b['event_date']) ?>" required>
                    <button type="submit" class="btn-confirm-reschedule">
                        <i class="fa-solid fa-check"></i> Confirm
                    </button>
                    <button type="button" class="btn-cancel-reschedule" onclick="toggleReschedule(<?= $b['id'] ?>)">
                        Cancel
                    </button>
                </form>
            </div>
            <?php else: ?>
            <div class="book-actions" style="color:rgba(245,232,211,.3);font-size:.78rem;">
                <?php if ($b['status']==='Completed'): ?>
                    <i class="fa-solid fa-star" style="color:var(--gold);"></i> Session completed — thank you!
                <?php else: ?>
                    <i class="fa-solid fa-ban"></i> Booking cancelled
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleReschedule(id) {
    var panel = document.getElementById('resched-' + id);
    panel.classList.toggle('open');
}
</script>
</body>
</html>
