<?php
session_start();
require_once __DIR__ . '/../../db/db.php';

// Only admin
if (!isset($_SESSION['userid']) || $_SESSION['userrole'] !== 'admin') {
    header('Location: ../php/login.php');
    exit;
}

// Fetch latest notifications (moderator → admin)
$notifications = [];
$sql = "SELECT n.id, n.sender_id, n.sender_role, n.message, n.is_read, n.created_at,
               u.email AS sender_email
        FROM notifications n
        LEFT JOIN users u ON n.sender_id = u.id
        WHERE n.sender_role = 'moderator'
        ORDER BY n.created_at DESC";

$result = $conn->query($sql);
if ($result) {
    $notifications = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications | Admin</title>
    <link rel="stylesheet" href="../css/admin-dashboard.css">
    <link rel="stylesheet" href="../css/admin-notifications.css">
</head>
<body>
<div class="dashboard">
    <header class="header">
        <div class="header-content">
            <div class="welcome">
                Notifications Center
            </div>
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

        <section class="notifications-header">
            <div>
                <h2>Moderator notifications</h2>
                <p>Messages and alerts sent by route moderators.</p>
            </div>
            <div class="badge-pill">
                Total: <span><?php echo count($notifications); ?></span>
            </div>
        </section>

        <?php if (!$notifications): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>No notifications</h3>
                <p>Moderators have not sent any messages yet.</p>
            </div>
        <?php else: ?>
            <div class="notifications-list">
                <?php foreach ($notifications as $n): ?>
                    <article class="notification-card <?php echo $n['is_read'] ? 'is-read' : 'is-unread'; ?>">
                        <header class="notification-header">
                            <span class="sender">
                                From: <?php echo htmlspecialchars($n['sender_email'] ?? 'Moderator'); ?>
                            </span>
                            <?php if (!$n['is_read']): ?>
                                <span class="chip chip-unread">New</span>
                            <?php else: ?>
                                <span class="chip chip-read">Read</span>
                            <?php endif; ?>
                        </header>

                        <p class="notification-message">
                            <?php echo nl2br(htmlspecialchars($n['message'])); ?>
                        </p>

                        <footer class="notification-footer">
                            <span class="time">
                                <?php echo htmlspecialchars($n['created_at']); ?>
                            </span>
                            <?php if (!$n['is_read']): ?>
                                <form method="post" action="notifications-mark-read.php" class="mark-form">
                                    <input type="hidden" name="id" value="<?php echo (int)$n['id']; ?>">
                                    <button type="submit" class="mark-btn">Mark as read</button>
                                </form>
                            <?php endif; ?>
                        </footer>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
