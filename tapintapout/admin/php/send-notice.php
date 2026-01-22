<?php
session_start();
require_once __DIR__ . '/../../db/db.php';

// Only admin
if (!isset($_SESSION['userid']) || $_SESSION['userrole'] !== 'admin') {
    header('Location: ../php/login.php');
    exit;
}

$error = '';
$success = '';
$title = '';
$body  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body  = trim($_POST['body'] ?? '');

    if ($title === '' || $body === '') {
        $error = "Title and message are required.";
    } elseif (mb_strlen($title) > 100) {
        $error = "Title is too long (max 100 characters).";
    } elseif (mb_strlen($body) > 1000) {
        $error = "Message is too long (max 1000 characters).";
    } else {
        // SIMPLE INSERT using ONLY the columns you have
        $message = "Admin Notice: " . $title . "\n\n" . $body;

        // Use INSERT without sender_role or type
        $stmt = $conn->prepare("INSERT INTO notifications (message) VALUES (?)");
        if ($stmt) {
            $stmt->bind_param("s", $message);
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok) {
                $success = "Notice sent successfully.";
                $title = $body = '';
            } else {
                $error = "Failed to send notice. Please try again.";
            }
        } else {
            $error = "Database error. Please contact support.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Notice | Admin</title>
    <link rel="stylesheet" href="../css/admin-dashboard.css">
    <link rel="stylesheet" href="../css/send-notice.css">
</head>
<body>
<div class="dashboard">
    <header class="header">
        <div class="header-content">
            <div class="welcome">📢 Send System Notice</div>
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

        <section class="card">
            <div class="card-header">
                <h2>Create a new system notice</h2>
                <p>This notice can be shown on moderator/passenger dashboards or a notice board.</p>
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

            <form id="noticeForm" method="post" action="" novalidate>
                <div class="form-group">
                    <label for="title">Notice title</label>
                    <input type="text" id="title" name="title"
                           class="input-field"
                           placeholder="e.g. New fare policy from next week"
                           required
                           value="<?php echo htmlspecialchars($title); ?>">
                </div>

                <div class="form-group">
                    <label for="body">Notice message</label>
                    <textarea id="body" name="body" rows="6"
                              class="input-field textarea"
                              placeholder="Write the details of this notice..."
                              required><?php echo htmlspecialchars($body); ?></textarea>
                    <div class="helper-text">Max 1000 characters.</div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Publish Notice</button>
                    <button type="reset" class="btn-secondary">Clear</button>
                </div>
            </form>
        </section>
    </main>
</div>

<script src="../js/send-notice.js"></script>
</body>
</html>
