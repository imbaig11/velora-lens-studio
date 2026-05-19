<?php
require_once __DIR__ . '/../php/config.php';
configure_session();
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Velora Lens Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --gold:#D8A03D; --black:#0D0804; --dark:#160e04; --cream:#F5E8D3; --brown:#523A28; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#0e0904; color:var(--cream); min-height:100vh; }
        a { text-decoration:none; color:inherit; }

        /* Sidebar */
        .sidebar {
            position:fixed; top:0; left:0; width:230px; height:100vh;
            background:#0a0604; border-right:1px solid rgba(216,160,61,0.12);
            display:flex; flex-direction:column; z-index:100;
        }
        .sidebar-logo {
            padding:28px 22px; border-bottom:1px solid rgba(216,160,61,0.12);
        }
        .sidebar-logo span { font-family:'Playfair Display',serif; font-size:1rem; color:var(--gold); display:block; letter-spacing:3px; }
        .sidebar-logo small { font-size:0.62rem; color:rgba(245,232,211,0.45); letter-spacing:2px; }
        .sidebar-nav { flex:1; padding:22px 0; }
        .nav-item {
            display:flex; align-items:center; gap:12px;
            padding:12px 22px; font-size:0.85rem; color:rgba(245,232,211,0.65);
            cursor:pointer; transition:all 0.2s; border-left:2px solid transparent;
        }
        .nav-item:hover, .nav-item.active { color:var(--gold); border-left-color:var(--gold); background:rgba(216,160,61,0.06); }
        .nav-item i { width:18px; text-align:center; }
        .sidebar-footer {
            padding:18px 22px; border-top:1px solid rgba(216,160,61,0.1);
            font-size:0.72rem; color:rgba(245,232,211,0.35);
        }

        /* Main Content */
        .main { margin-left:230px; padding:36px 40px; }

        /* Header */
        .page-header { margin-bottom:36px; }
        .page-header h1 { font-family:'Playfair Display',serif; font-size:1.9rem; color:var(--cream); margin-bottom:4px; }
        .page-header p  { font-size:0.82rem; color:rgba(245,232,211,0.45); }

        /* Stats Row */
        .stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:18px; margin-bottom:36px; }
        .stat-card {
            background:var(--dark); border:1px solid rgba(216,160,61,0.14);
            border-top:3px solid var(--gold); border-radius:6px; padding:24px 20px;
            display:flex; flex-direction:column; gap:8px;
        }
        .stat-card .sc-num { font-family:'Playfair Display',serif; font-size:2.2rem; font-weight:700; color:var(--gold); }
        .stat-card .sc-lbl { font-size:0.78rem; color:rgba(245,232,211,0.55); }
        .stat-card .sc-icon { font-size:1.4rem; color:rgba(216,160,61,0.35); margin-bottom:4px; }

        /* Section Tabs */
        .tab-bar { display:flex; gap:10px; margin-bottom:26px; flex-wrap:wrap; }
        .tab-btn {
            padding:8px 20px; border:1px solid rgba(216,160,61,0.3);
            border-radius:4px; background:transparent; color:rgba(245,232,211,0.65);
            font-family:'Poppins',sans-serif; font-size:0.8rem; cursor:pointer; transition:all 0.2s;
        }
        .tab-btn.active { background:var(--gold); border-color:var(--gold); color:#111; font-weight:600; }

        /* Table */
        .table-box { background:var(--dark); border:1px solid rgba(216,160,61,0.12); border-radius:6px; overflow:hidden; }
        .table-box table { width:100%; border-collapse:collapse; font-size:0.82rem; }
        .table-box thead { background:rgba(216,160,61,0.1); }
        .table-box th { padding:13px 16px; text-align:left; font-weight:600; color:var(--gold); font-size:0.75rem; letter-spacing:1px; text-transform:uppercase; }
        .table-box td { padding:12px 16px; border-top:1px solid rgba(216,160,61,0.07); color:rgba(245,232,211,0.75); vertical-align:top; }
        .table-box tr:hover td { background:rgba(216,160,61,0.03); }

        /* Status badges */
        .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:0.68rem; font-weight:600; letter-spacing:0.5px; }
        .badge-new       { background:rgba(61,153,216,0.2); color:#6eb8e7; border:1px solid rgba(61,153,216,0.3); }
        .badge-confirmed { background:rgba(61,216,120,0.2); color:#6ee7a8; border:1px solid rgba(61,216,120,0.3); }
        .badge-cancelled { background:rgba(216,61,61,0.2);  color:#e78686; border:1px solid rgba(216,61,61,0.3); }
        .badge-completed { background:rgba(216,160,61,0.2); color:#D8A03D; border:1px solid rgba(216,160,61,0.3); }
        .badge-unread    { background:rgba(216,61,61,0.2);  color:#e78686; border:1px solid rgba(216,61,61,0.25); }
        .badge-read      { background:rgba(61,216,120,0.12);color:#6ee7a8; border:1px solid rgba(61,216,120,0.2); }

        /* Empty state */
        .empty-state { text-align:center; padding:60px 20px; color:rgba(245,232,211,0.3); }
        .empty-state i { font-size:3rem; margin-bottom:14px; display:block; }

        /* Section heading inside table area */
        .section-bar {
            display:flex; align-items:center; justify-content:space-between;
            padding:18px 20px; border-bottom:1px solid rgba(216,160,61,0.1);
        }
        .section-bar h3 { font-size:0.95rem; color:var(--cream); font-weight:600; }
        .section-bar span { font-size:0.76rem; color:rgba(245,232,211,0.4); }

        /* Responsive */
        @media(max-width:900px){
            .sidebar { display:none; }
            .main { margin-left:0; padding:24px 18px; }
            .stats-row { grid-template-columns:1fr 1fr; }
        }
    </style>
</head>
<body>
<?php
// =============================================
//  DATABASE — uses shared config.php
// =============================================
$conn  = db_connect();
$db_ok = !$conn->connect_error;

// Fetch counts
$count_bookings  = $db_ok ? $conn->query("SELECT COUNT(*) c FROM bookings")->fetch_assoc()['c']  : 0;
$count_new       = $db_ok ? $conn->query("SELECT COUNT(*) c FROM bookings WHERE status='New'")->fetch_assoc()['c'] : 0;
$count_messages  = $db_ok ? $conn->query("SELECT COUNT(*) c FROM contact_messages")->fetch_assoc()['c'] : 0;
$count_unread    = $db_ok ? $conn->query("SELECT COUNT(*) c FROM contact_messages WHERE is_read=0")->fetch_assoc()['c'] : 0;

// Active tab
$tab = $_GET['tab'] ?? 'bookings';

// Update booking status (uses prepared statement for safety)
if ($db_ok && isset($_GET['set_status'], $_GET['bid'])) {
    $bid = (int)$_GET['bid'];
    $st  = $_GET['set_status'];
    $allowed = ['New','Confirmed','Cancelled','Completed'];
    if (in_array($st, $allowed, true)) {
        $upd = $conn->prepare("UPDATE bookings SET status=? WHERE id=?");
        $upd->bind_param('si', $st, $bid);
        $upd->execute();
        $upd->close();
        header("Location: index.php?tab=bookings&updated=1");
        exit;
    }
}
// Mark message as read
if ($db_ok && isset($_GET['read_msg'])) {
    $mid = (int)$_GET['read_msg'];
    $conn->query("UPDATE contact_messages SET is_read=1 WHERE id=$mid");
    header("Location: index.php?tab=messages&updated=1");
    exit;
}
?>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-logo">
        <span>VELORA</span>
        <small>Admin Panel</small>
    </div>
    <nav class="sidebar-nav">
        <a href="?tab=bookings" class="nav-item <?= $tab==='bookings'?'active':'' ?>">
            <i class="fa-solid fa-calendar-check"></i> Bookings
        </a>
        <a href="?tab=messages" class="nav-item <?= $tab==='messages'?'active':'' ?>">
            <i class="fa-solid fa-envelope"></i> Messages
            <?php if($count_unread>0): ?>
                <span style="background:#e78686;color:#111;font-size:0.65rem;font-weight:700;padding:1px 6px;border-radius:10px;margin-left:auto;"><?= $count_unread ?></span>
            <?php endif; ?>
        </a>
        <a href="?tab=packages" class="nav-item <?= $tab==='packages'?'active':'' ?>">
            <i class="fa-solid fa-gem"></i> Packages
        </a>
        <a href="?tab=gallery" class="nav-item <?= $tab==='gallery'?'active':'' ?>">
            <i class="fa-solid fa-images"></i> Gallery Items
        </a>
        <a href="../index.html" class="nav-item" style="margin-top:16px;">
            <i class="fa-solid fa-arrow-left"></i> Back to Website
        </a>
        <a href="logout.php" class="nav-item" style="margin-top:4px;color:#e78686;">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </nav>
    <div class="sidebar-footer">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
            <i class="fa-solid fa-user-circle" style="color:var(--gold);font-size:1rem;"></i>
            <span style="color:var(--cream);font-size:0.78rem;font-weight:500;"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
        </div>
        © 2026 Velora Lens Studio
    </div>
</div>

<!-- MAIN -->
<div class="main">

    <div class="page-header">
        <h1>Admin <span style="color:var(--gold);">Dashboard</span></h1>
        <p>Manage bookings, messages and studio data</p>
        <?php if(!$db_ok): ?>
            <div style="margin-top:14px;padding:12px 18px;background:rgba(216,61,61,0.12);border:1px solid rgba(216,61,61,0.3);border-radius:4px;font-size:0.84rem;color:#e78686;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Database not connected. Please set up MySQL and run <code>sql/database.sql</code> first.<br>
                <small style="color:rgba(231,134,134,0.7);">See HOW_TO_RUN.txt for setup instructions.</small>
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['updated'])): ?>
            <div style="margin-top:14px;padding:10px 16px;background:rgba(61,216,120,0.12);border:1px solid rgba(61,216,120,0.3);border-radius:4px;font-size:0.84rem;color:#6ee7a8;">
                <i class="fa-solid fa-check-circle"></i> Updated successfully.
            </div>
        <?php endif; ?>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <i class="fa-solid fa-calendar-check sc-icon"></i>
            <span class="sc-num"><?= $count_bookings ?></span>
            <span class="sc-lbl">Total Bookings</span>
        </div>
        <div class="stat-card">
            <i class="fa-solid fa-bell sc-icon"></i>
            <span class="sc-num"><?= $count_new ?></span>
            <span class="sc-lbl">New / Pending</span>
        </div>
        <div class="stat-card">
            <i class="fa-solid fa-envelope sc-icon"></i>
            <span class="sc-num"><?= $count_messages ?></span>
            <span class="sc-lbl">Total Messages</span>
        </div>
        <div class="stat-card">
            <i class="fa-solid fa-envelope-open sc-icon"></i>
            <span class="sc-num"><?= $count_unread ?></span>
            <span class="sc-lbl">Unread Messages</span>
        </div>
    </div>

    <!-- Tab Bar -->
    <div class="tab-bar">
        <a href="?tab=bookings" class="tab-btn <?= $tab==='bookings'?'active':'' ?>"><i class="fa-solid fa-calendar-check"></i> Bookings</a>
        <a href="?tab=messages" class="tab-btn <?= $tab==='messages'?'active':'' ?>"><i class="fa-solid fa-envelope"></i> Messages</a>
        <a href="?tab=packages" class="tab-btn <?= $tab==='packages'?'active':'' ?>"><i class="fa-solid fa-gem"></i> Packages</a>
        <a href="?tab=gallery"  class="tab-btn <?= $tab==='gallery'?'active':'' ?>"><i class="fa-solid fa-images"></i> Gallery</a>
    </div>

    <!-- =============================================
         TAB: BOOKINGS
         ============================================= -->
    <?php if($tab === 'bookings'): ?>
    <div class="table-box">
        <div class="section-bar">
            <h3><i class="fa-solid fa-calendar-check" style="color:var(--gold);margin-right:8px;"></i>All Bookings</h3>
            <span><?= $count_bookings ?> total records</span>
        </div>
        <?php if($db_ok): ?>
        <?php $rows = $conn->query("SELECT * FROM bookings ORDER BY created_at DESC"); ?>
        <?php if($rows && $rows->num_rows > 0): ?>
        <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Client Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Event</th>
                    <th>Event Date</th>
                    <th>Location</th>
                    <th>Package</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while($r = $rows->fetch_assoc()): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><strong style="color:var(--cream);"><?= htmlspecialchars($r['name']) ?></strong></td>
                <td><a href="mailto:<?= htmlspecialchars($r['email']) ?>" style="color:var(--gold);"><?= htmlspecialchars($r['email']) ?></a></td>
                <td><?= htmlspecialchars($r['phone']) ?></td>
                <td><?= htmlspecialchars($r['event_type']) ?></td>
                <td><?= date('d M Y', strtotime($r['event_date'])) ?></td>
                <td><?= htmlspecialchars($r['location']) ?></td>
                <td><?= htmlspecialchars($r['package'] ?? '—') ?></td>
                <td style="max-width:180px;white-space:normal;"><?= htmlspecialchars(substr($r['message'] ?? '—', 0, 80)) ?><?= strlen($r['message'] ?? '') > 80 ? '…' : '' ?></td>
                <td>
                    <?php
                    $cls = ['New'=>'badge-new','Confirmed'=>'badge-confirmed','Cancelled'=>'badge-cancelled','Completed'=>'badge-completed'];
                    echo '<span class="badge '.($cls[$r['status']]??'badge-new').'">'.$r['status'].'</span>';
                    ?>
                </td>
                <td style="white-space:nowrap;"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                <td style="white-space:nowrap;">
                    <a href="?tab=bookings&bid=<?= $r['id'] ?>&set_status=Confirmed" title="Confirm"   style="color:#6ee7a8;margin-right:8px;"><i class="fa-solid fa-check"></i></a>
                    <a href="?tab=bookings&bid=<?= $r['id'] ?>&set_status=Completed" title="Complete"  style="color:var(--gold);margin-right:8px;"><i class="fa-solid fa-star"></i></a>
                    <a href="?tab=bookings&bid=<?= $r['id'] ?>&set_status=Cancelled" title="Cancel"    style="color:#e78686;"><i class="fa-solid fa-xmark"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
        <div class="empty-state"><i class="fa-solid fa-calendar-xmark"></i><p>No bookings yet.</p></div>
        <?php endif; ?>
        <?php else: ?>
        <div class="empty-state"><i class="fa-solid fa-database"></i><p>Database not connected.</p></div>
        <?php endif; ?>
    </div>


    <!-- =============================================
         TAB: CONTACT MESSAGES
         ============================================= -->
    <?php elseif($tab === 'messages'): ?>
    <div class="table-box">
        <div class="section-bar">
            <h3><i class="fa-solid fa-envelope" style="color:var(--gold);margin-right:8px;"></i>Contact Messages</h3>
            <span><?= $count_messages ?> total · <?= $count_unread ?> unread</span>
        </div>
        <?php if($db_ok): ?>
        <?php $rows = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC"); ?>
        <?php if($rows && $rows->num_rows > 0): ?>
        <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while($r = $rows->fetch_assoc()): ?>
            <tr style="<?= !$r['is_read'] ? 'background:rgba(216,160,61,0.04)' : '' ?>">
                <td><?= $r['id'] ?></td>
                <td><strong style="color:var(--cream);"><?= htmlspecialchars($r['name']) ?></strong></td>
                <td><a href="mailto:<?= htmlspecialchars($r['email']) ?>" style="color:var(--gold);"><?= htmlspecialchars($r['email']) ?></a></td>
                <td><?= htmlspecialchars($r['phone'] ?? '—') ?></td>
                <td><?= htmlspecialchars($r['subject'] ?? '—') ?></td>
                <td style="max-width:220px;white-space:normal;"><?= htmlspecialchars(substr($r['message'], 0, 100)) ?><?= strlen($r['message'])>100?'…':'' ?></td>
                <td>
                    <?php if(!$r['is_read']): ?>
                        <span class="badge badge-unread">Unread</span>
                    <?php else: ?>
                        <span class="badge badge-read">Read</span>
                    <?php endif; ?>
                </td>
                <td style="white-space:nowrap;"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                <td>
                    <?php if(!$r['is_read']): ?>
                        <a href="?tab=messages&read_msg=<?= $r['id'] ?>" title="Mark as Read" style="color:#6ee7a8;">
                            <i class="fa-solid fa-envelope-open"></i> Mark Read
                        </a>
                    <?php else: ?>
                        <span style="color:rgba(245,232,211,0.25);font-size:0.78rem;">Done</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
        <div class="empty-state"><i class="fa-solid fa-envelope-open"></i><p>No messages yet.</p></div>
        <?php endif; ?>
        <?php else: ?>
        <div class="empty-state"><i class="fa-solid fa-database"></i><p>Database not connected.</p></div>
        <?php endif; ?>
    </div>


    <!-- =============================================
         TAB: PACKAGES
         ============================================= -->
    <?php elseif($tab === 'packages'): ?>
    <div class="table-box">
        <div class="section-bar">
            <h3><i class="fa-solid fa-gem" style="color:var(--gold);margin-right:8px;"></i>Photography Packages</h3>
            <span>All available packages</span>
        </div>
        <?php if($db_ok): ?>
        <?php $rows = $conn->query("SELECT * FROM packages ORDER BY sort_order ASC"); ?>
        <?php if($rows && $rows->num_rows > 0): ?>
        <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Package Name</th>
                    <th>Tag</th>
                    <th>Price (PKR)</th>
                    <th>Duration</th>
                    <th>Features</th>
                    <th>Popular</th>
                    <th>Active</th>
                </tr>
            </thead>
            <tbody>
            <?php while($r = $rows->fetch_assoc()): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><strong style="color:var(--cream);"><?= htmlspecialchars($r['name']) ?></strong></td>
                <td><span class="badge badge-confirmed"><?= htmlspecialchars($r['tag']) ?></span></td>
                <td style="color:var(--gold);font-weight:600;">PKR <?= number_format($r['price_pkr']) ?></td>
                <td><?= htmlspecialchars($r['duration']) ?></td>
                <td style="max-width:220px;white-space:normal;font-size:0.78rem;color:rgba(245,232,211,0.6);">
                    <?php foreach(explode(',', $r['features']) as $f): ?>
                        <span style="display:inline-block;margin:2px 4px 2px 0;">✓ <?= trim(htmlspecialchars($f)) ?></span>
                    <?php endforeach; ?>
                </td>
                <td><?= $r['is_featured'] ? '<span class="badge badge-completed">Yes</span>' : '<span style="color:rgba(245,232,211,0.25);">No</span>' ?></td>
                <td><?= $r['is_active']  ? '<span class="badge badge-confirmed">Yes</span>' : '<span class="badge badge-cancelled">No</span>' ?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
        <div class="empty-state"><i class="fa-solid fa-gem"></i><p>No packages found.</p></div>
        <?php endif; ?>
        <?php else: ?>
        <div class="empty-state"><i class="fa-solid fa-database"></i><p>Database not connected.</p></div>
        <?php endif; ?>
    </div>


    <!-- =============================================
         TAB: GALLERY
         ============================================= -->
    <?php elseif($tab === 'gallery'): ?>
    <div class="table-box">
        <div class="section-bar">
            <h3><i class="fa-solid fa-images" style="color:var(--gold);margin-right:8px;"></i>Gallery Items</h3>
            <span>Images tracked in database</span>
        </div>
        <?php if($db_ok): ?>
        <?php $rows = $conn->query("SELECT * FROM gallery_items ORDER BY sort_order ASC"); ?>
        <?php if($rows && $rows->num_rows > 0): ?>
        <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Preview</th>
                    <th>Filename</th>
                    <th>Caption / Alt Text</th>
                    <th>Category</th>
                    <th>Sort Order</th>
                    <th>Active</th>
                </tr>
            </thead>
            <tbody>
            <?php while($r = $rows->fetch_assoc()): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td>
                    <img src="../images/<?= htmlspecialchars($r['filename']) ?>"
                         style="width:70px;height:50px;object-fit:cover;border-radius:3px;border:1px solid rgba(216,160,61,0.2);"
                         alt="<?= htmlspecialchars($r['alt_text']) ?>">
                </td>
                <td style="color:var(--gold);font-family:monospace;"><?= htmlspecialchars($r['filename']) ?></td>
                <td><?= htmlspecialchars($r['alt_text']) ?></td>
                <td><span class="badge badge-new"><?= ucfirst(htmlspecialchars($r['category'])) ?></span></td>
                <td><?= $r['sort_order'] ?></td>
                <td><?= $r['is_active'] ? '<span class="badge badge-confirmed">Yes</span>' : '<span class="badge badge-cancelled">No</span>' ?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
        <div class="empty-state"><i class="fa-solid fa-images"></i><p>No gallery items found.</p></div>
        <?php endif; ?>
        <?php else: ?>
        <div class="empty-state"><i class="fa-solid fa-database"></i><p>Database not connected.</p></div>
        <?php endif; ?>
    </div>

    <?php endif; ?>

</div><!-- /main -->

<?php if($db_ok) $conn->close(); ?>
</body>
</html>
