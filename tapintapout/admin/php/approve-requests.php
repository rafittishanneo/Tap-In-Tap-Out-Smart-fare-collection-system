<?php
session_start();
require_once __DIR__ . '/../../db/db.php';
require_once __DIR__ . '/../../models/FareChangeRequest.php';

if (!isset($_SESSION['userid']) || $_SESSION['userrole'] !== 'admin') {
    header('Location: ../php/login.php');
    exit;
}

$fareModel = new FareChangeRequest($conn);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action']    ?? '';
    $requestId = (int)($_POST['request_id'] ?? 0);

    if ($requestId <= 0) {
        $error = "Invalid request id.";
    } else {
        if ($action === 'approve') {
            $ok = $fareModel->approve($requestId, (int)$_SESSION['userid']);
            $success = $ok ? "Request approved and fare updated."
                           : "Failed to approve request. Possibly already processed.";
        } elseif ($action === 'reject') {
            $ok = $fareModel->reject($requestId, (int)$_SESSION['userid']);
            $success = $ok ? "Request rejected."
                           : "Failed to reject request. Possibly already processed.";
        } else {
            $error = "Invalid action.";
        }
    }
}

$pending = $fareModel->getPending();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approve Fare Requests | Admin</title>
    <link rel="stylesheet" href="../db/admin-dashboard.css">
    <link rel="stylesheet" href="/web-tech/tapintapout/admin/css/approve-requests.css">
</head>
<body>
<div class="dashboard">
    <header class="header">
        <div class="header-content">
            <div class="welcome">
                Welcome, <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong>
            </div>
            <div class="user-info">
                <div class="user-email"><?php echo htmlspecialchars($_SESSION['useremail']); ?></div>
                <a href="/web-tech/tapintapout/php/logout.php" class="logout">Logout</a>
            </div>
            
        </div>
    </header>

    <main class="content">
        <section class="approve-header">
            <div>
                <h2>Pending Fare Change Requests</h2>
                <p>Review and approve or reject fare changes proposed by moderators.</p>
            </div>
            <div class="badge-pill">
                Pending: <span><?php echo count($pending); ?></span>
            </div>
        </section>

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

        <?php if (!$pending): ?>
            <div class="empty-state">
                <div class="empty-icon">✅</div>
                <h3>No pending requests</h3>
                <p>All fare changes are up to date. Check back later.</p>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach ($pending as $req): ?>
                    <article class="request-card">
                        <header class="request-header">
                            <div class="route-name">
                                🚌 <?php echo htmlspecialchars($req['route_name']); ?>
                            </div>
                            <div class="chip chip-pending">Pending</div>
                        </header>

                        <div class="fare-row">
                            <div class="fare-column">
                                <span class="fare-label">Current Fare</span>
                                <span class="fare-value old">৳<?php echo htmlspecialchars($req['old_fare']); ?></span>
                            </div>
                            <div class="fare-arrow">➜</div>
                            <div class="fare-column">
                                <span class="fare-label">Requested Fare</span>
                                <span class="fare-value new">৳<?php echo htmlspecialchars($req['new_fare']); ?></span>
                            </div>
                        </div>

                        <div class="meta-row">
                            <div class="meta-item">
                                <span class="meta-label">Requested by</span>
                                <span class="meta-value"><?php echo htmlspecialchars($req['moderator_email']); ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Requested at</span>
                                <span class="meta-value"><?php echo htmlspecialchars($req['created_at']); ?></span>
                            </div>
                        </div>

                        <div class="back-row">
                            <a href="/web-tech/tapintapout/admin/php/admin-dashboard.php" class="back-link">← Back to Dashboard</a>
                        </div>


                        <form method="post" action="" class="actions-row">
                            <input type="hidden" name="request_id" value="<?php echo (int)$req['id']; ?>">
                            <button type="submit" name="action" value="reject"
                                    class="btn-secondary js-confirm"
                                    data-message="Reject this fare change?">
                                ✖ Reject
                            </button>
                            <button type="submit" name="action" value="approve"
                                    class="btn-primary js-confirm"
                                    data-message="Approve this fare change and update route fare?">
                                ✔ Approve
                            </button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>
<!-- Back to dashboard -->
    <div class="back-row">
        <a href="/web-tech/tapintapout/admin/php/admin-dashboard.php" class="back-link">← Back to Dashboard</a>
</div>


<script src="/web-tech/tapintapout/admin/js/approve-requests.js"></script>
</body>
</html>