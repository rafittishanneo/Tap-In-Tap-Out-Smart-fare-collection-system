<?php
session_start();
require_once __DIR__ . '/../db/db.php';

// Only moderator
if (!isset($_SESSION['userid']) || $_SESSION['userrole'] !== 'moderator') {
    header('Location: ../php/login.php');
    exit;
}

$error = '';
$success = '';
$messageText = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $messageText = trim($_POST['message'] ?? '');

    if ($messageText === '') {
        $error = "Message cannot be empty.";
    } elseif (mb_strlen($messageText) > 500) {
        $error = "Message is too long (max 500 characters).";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO notifications (sender_id, sender_role, message, is_read, created_at)
             VALUES (?, 'moderator', ?, 0, NOW())"
        ); // basic insert [web:65][web:90]

        if ($stmt) {
            $moderatorId = (int)$_SESSION['userid'];
            $stmt->bind_param("is", $moderatorId, $messageText);
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok) {
                $success = "Notification sent to admin.";
                $messageText = '';
            } else {
                $error = "Failed to send notification. Please try again.";
            }
        } else {
            $error = "Database error. Please contact admin.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Notification | Moderator</title>
    <link rel="stylesheet" href="moderator-dashboardcss.css">
    <link rel="stylesheet" href="send-notification.css">
</head>
<body>
<div class="dashboard">
    <header class="header">
        <div class="header-content">
            <div class="welcome">📢 Send Notification to Admin</div>
            <div class="user-info">
                <div class="user-email"><?php echo htmlspecialchars($_SESSION['useremail']); ?></div>
                <a href="../php/logout.php" class="logout">Logout</a>
            </div>
        </div>
    </header>

    <main class="content">
        <div class="back-row">
            <a href="/web-tech/tapintapout/moderator/moderator-dashboardphp.php" class="back-link">
                ← Back to Dashboard
            </a>
        </div>

        <section class="card">
            <div class="card-header">
                <h2>Send a message to the admin team</h2>
                <p>Use this to notify admins about important route or fare issues.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form id="notifyForm" method="post" action="" novalidate>
                <div class="form-group">
                    <label for="message">Message to admin</label>
                    <textarea id="message" name="message" rows="5"
                              class="input-field textarea"
                              placeholder="Describe the issue or update you want the admin to know about..."
                              required><?php echo htmlspecialchars($messageText); ?></textarea>
                    <div class="helper-text">
                        Max 500 characters. Be clear and specific.
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Send Notification</button>
                    <button type="reset" class="btn-secondary">Clear</button>
                </div>
            </form>
        </section>
    </main>
</div>

<script src="send-notification.js"></script>
</body>
</html>
