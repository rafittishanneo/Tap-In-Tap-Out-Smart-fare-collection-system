<?php
session_start();

if (!isset($_SESSION['userid']) || !isset($_SESSION['userrole'])) {
    header("Location: ../php/login.php");
    exit();
}
if ($_SESSION['userrole'] != 'admin') {
    header("Location: ../php/login.php");
    exit();
}

require_once __DIR__ . '/../../db/db.php'; // ✅ adjust if your db.php path differs

$username  = $_SESSION['username'] ?? 'Admin';
$useremail = $_SESSION['useremail'] ?? '';


// Live stats from DB

// Total Users (passengers/users)
$totalUsers = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS total_users FROM users WHERE role = 'user'");
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$totalUsers = (int)($row['total_users'] ?? 0);
$stmt->close();

// Total Journeys (count successful fare deductions)
// Using tap_logs with tap_type='out'
$totalJourneys = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS total_journeys FROM tap_logs WHERE tap_type = 'out'");
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$totalJourneys = (int)($row['total_journeys'] ?? 0);
$stmt->close();

// Total Revenue (sum of fares)
$totalRevenue = 0.0;
$stmt = $conn->prepare("SELECT COALESCE(SUM(fare), 0) AS total_revenue FROM tap_logs WHERE tap_type = 'out'");
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$totalRevenue = (float)($row['total_revenue'] ?? 0);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Tap in Tap Out</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin-dashboard.css">
</head>
<body>
<div class="dashboard">
    <header class="header">
        <div class="header-content">
            <div class="welcome">
                Welcome, <strong><?php echo htmlspecialchars($username); ?></strong>
            </div>
            <div class="user-info">
                <div class="user-email"><?php echo htmlspecialchars($useremail); ?></div>
                <a href="/web-tech/tapintapout/php/logout.php" class="logout">Logout</a>
            </div>
        </div>
    </header>

    <main class="content">
        <div class="stats-grid">
            <div class="stat-card card-users">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?php echo number_format($totalUsers); ?></div>
                <div class="stat-label">Total Users</div>
            </div>

            <div class="stat-card card-revenue">
                <div class="stat-icon">💰</div>
                <div class="stat-number">৳<?php echo number_format($totalRevenue, 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>

            <div class="stat-card card-journeys">
                <div class="stat-icon">🚌</div>
                <div class="stat-number"><?php echo number_format($totalJourneys); ?></div>
                <div class="stat-label">Total Journeys</div>
            </div>
        </div>

        <div class="quick-actions">
            <a href="/web-tech/tapintapout/moderator/create-moderator.php" class="btn btn-primary">Create Moderator</a>
            <a href="/web-tech/tapintapout/admin/php/system-reports.php" class="btn btn-primary">System Reports</a>
            <a href="/web-tech/tapintapout/admin/php/approve-requests.php" class="btn btn-outline">Approve Requests</a>
            <a href="/web-tech/tapintapout/admin/php/send-notice.php" class="btn btn-outline">Send Notice</a>
            <a href="/web-tech/tapintapout/admin/php/notifications.php" class="btn btn-outline">Notifications</a>
        </div>

        <section class="recent-journeys">
            <h2 class="section-title">Pending Route Changes</h2>
            <div style="padding:1rem;background:#f8fafc;border-radius:12px;color:#64748b;text-align:center;">
                No pending requests at the moment.
            </div>
        </section>
    </main>
</div>
</body>
</html>
