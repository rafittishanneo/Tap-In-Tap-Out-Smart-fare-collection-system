<?php
session_start();
require_once __DIR__ . '/../db/db.php';

// Session check
if (!isset($_SESSION['userid']) || $_SESSION['userrole'] !== 'moderator') {
    header('Location: ../php/login.php');
    exit;
}

$user_email = $_SESSION['useremail'] ?? 'Moderator';

// Show ONLY admin notices using content filtering
$adminNotices = [];
$sqlNotices = "SELECT id, message, created_at
               FROM notifications
               WHERE message LIKE 'Admin Notice:%' 
                  OR message LIKE 'Notice:%' 
                  OR message LIKE 'IMPORTANT:%'
               ORDER BY created_at DESC
               LIMIT 3";

if ($res = $conn->query($sqlNotices)) {
    $adminNotices = $res->fetch_all(MYSQLI_ASSOC);
    $res->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Moderator Dashboard | Tap in Tap Out</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/web-tech/tapintapout/moderator/moderator-dashboardcss.css">
</head>
<body>
    <div class="dashboard">
        <header class="header">
            <div class="header-content">
                <div class="welcome">Route Manager Dashboard</div>
                <div class="user-info">
                    <div class="user-email"><?php echo htmlspecialchars($user_email); ?></div>
                    <a href="../php/logout.php" class="logout">Logout</a>
                </div>
            </div>
        </header>

        <main class="content">
            <div class="stats-grid">
                <div class="stat-card" style="background: linear-gradient(135deg, #eab308, #ca8a04);">
                    <div class="stat-icon">🛣️</div>
                    <div class="stat-number">9</div>
                    <div class="stat-label">Active Routes</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                    <div class="stat-icon">📈</div>
                    <div class="stat-number">4</div>
                    <div class="stat-label">Today's Journeys</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                    <div class="stat-icon">💬</div>
                    <div class="stat-number">0</div>
                    <div class="stat-label">Pending Messages</div>
                </div>
            </div>

            <!-- Admin notices section -->
            <?php if ($adminNotices): ?>
                <section class="notice-board">
                    <h2 class="notice-title">📢 Recent Notices</h2>
                    <?php foreach ($adminNotices as $n): ?>
                        <article class="notice-item">
                            <div class="notice-message">
                                <?php echo nl2br(htmlspecialchars($n['message'])); ?>
                            </div>
                            <div class="notice-meta">
                                <?php echo htmlspecialchars($n['created_at']); ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

            <div class="quick-actions">
                <a href="add-route.php" class="btn btn-primary">Add Route</a>
                <a href="update-fares.php" class="btn btn-primary">Update Fares</a>
                <a href="manage-vehicles.php" class="btn btn-primary">Manage Vehicles</a>
                <a href="route-reports.php" class="btn btn-outline">Route Reports</a>
                <a href="create-vehicle.php" class="btn btn-outline">Add Vehicle</a>
                <a href="send-notification.php" class="btn btn-outline">Send Notification</a>
            </div>
        </main>
    </div>
    <script src="/web-tech/tapintapout/moderator/moderator-dashboardjs.js"></script>
</body>
</html>
