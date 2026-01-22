<?php
session_start();
require_once __DIR__ . '/../../db/db.php';

// Only admin
if (!isset($_SESSION['userid']) || $_SESSION['userrole'] !== 'admin') {
    header('Location: ../php/login.php');
    exit;
}

// Default values
$totalUsers = $totalModerators = $totalPassengers = 0;
$totalRoutes = 0;
$totalPendingFareRequests = 0;
$totalJourneys = 0;
$totalRevenue = 0.0;

// 1) Users by role
$sqlUsers = "SELECT role, COUNT(*) AS cnt FROM users GROUP BY role";
if ($res = $conn->query($sqlUsers)) {
    while ($row = $res->fetch_assoc()) {
        if ($row['role'] === 'admin') {
            $totalUsers += (int)$row['cnt'];
        } elseif ($row['role'] === 'moderator') {
            $totalModerators = (int)$row['cnt'];
            $totalUsers += (int)$row['cnt'];
        } else {
            $totalPassengers += (int)$row['cnt'];
            $totalUsers += (int)$row['cnt'];
        }
    }
    $res->close();
}

// 2) Total routes
$sqlRoutes = "SELECT COUNT(*) AS c FROM routes";
if ($res = $conn->query($sqlRoutes)) {
    $row = $res->fetch_assoc();
    $totalRoutes = (int)$row['c'];
    $res->close();
}

// 3) Pending fare change requests
$sqlPending = "SELECT COUNT(*) AS c FROM fare_change_requests WHERE status = 'pending'";
if ($res = $conn->query($sqlPending)) {
    $row = $res->fetch_assoc();
    $totalPendingFareRequests = (int)$row['c'];
    $res->close();
}

// 4) Journeys and revenue (if you have a journeys table)
$sqlJourneys = "SELECT COUNT(*) AS trips, COALESCE(SUM(fare), 0) AS revenue FROM journeys";
if ($res = $conn->query($sqlJourneys)) {
    $row = $res->fetch_assoc();
    $totalJourneys = (int)$row['trips'];
    $totalRevenue = (float)$row['revenue'];
    $res->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Reports | Admin</title>
    <link rel="stylesheet" href="../css/admin-dashboard.css">
    <link rel="stylesheet" href="../css/system-reports.css">
</head>
<body>
<div class="dashboard">
    <header class="header">
        <div class="header-content">
            <div class="welcome">System Reports</div>
            <div class="user-info">
                <div class="user-email"><?php echo htmlspecialchars($_SESSION['useremail']); ?></div>
                <a href="/web-tech/tapintapout/php/logout.php" class="logout">Logout</a>
            </div>
        </div>
    </header>

    <main class="content">
        <div class="back-row">
            <a href="/web-tech/tapintapout/admin/php/admin-dashboard.php" class="back-link">
                ← Back to Dashboard
            </a>
        </div>

        <section class="reports-grid">
            <div class="report-card card-users">
                <div class="label">Total Users</div>
                <div class="value"><?php echo $totalUsers; ?></div>
                <div class="meta">
                    Admins/Moderators/Passengers:
                    <?php echo $totalUsers; ?> =
                    <?php echo $totalModerators; ?> mods,
                    <?php echo $totalPassengers; ?> passengers
                </div>
            </div>

            <div class="report-card card-routes">
                <div class="label">Total Routes</div>
                <div class="value"><?php echo $totalRoutes; ?></div>
                <div class="meta">Configured in the system</div>
            </div>

            <div class="report-card card-journeys">
                <div class="label">Total Journeys</div>
                <div class="value"><?php echo $totalJourneys; ?></div>
                <div class="meta">Completed rides (from journeys table)</div>
            </div>

            <div class="report-card card-revenue">
                <div class="label">Total Revenue</div>
                <div class="value">৳<?php echo number_format($totalRevenue, 2); ?></div>
                <div class="meta">Sum of fare column</div>
            </div>

            <div class="report-card card-pending">
                <div class="label">Pending Fare Requests</div>
                <div class="value"><?php echo $totalPendingFareRequests; ?></div>
                <div class="meta">
                    <a href="approve-requests.php">View details</a>
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>
