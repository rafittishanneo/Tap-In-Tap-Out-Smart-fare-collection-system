<?php
session_start();
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../models/FareChangeRequest.php';

// 1) Auth: only admin
if (!isset($_SESSION['userid']) || $_SESSION['userrole'] !== 'admin') {
    header('Location: ../php/login.php');
    exit;
}

$fareModel = new FareChangeRequest($conn);
$error = '';
$success = '';

// 2) Handle approve / reject POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // very important: names must match form fields
    $action    = $_POST['action']    ?? '';
    $requestId = (int)($_POST['request_id'] ?? 0);

    if ($requestId <= 0) {
        $error = "Invalid request id.";
    } else {
        if ($action === 'approve') {
            $ok = $fareModel->approve($requestId, (int)$_SESSION['userid']);
            if ($ok) {
                $success = "Request approved and fare updated.";
            } else {
                $error = "Failed to approve request. Possibly already approved/rejected.";
            }
        } elseif ($action === 'reject') {
            $ok = $fareModel->reject($requestId, (int)$_SESSION['userid']);
            if ($ok) {
                $success = "Request rejected.";
            } else {
                $error = "Failed to reject request. Possibly already processed.";
            }
        } else {
            $error = "Invalid action.";
        }
    }
}

// 3) Fetch pending requests after processing
$pending = $fareModel->getPending();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approve Requests | Admin</title>
    <link rel="stylesheet" href="../db/admin-dashboard.css">
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
        <h2 class="section-title">
            Pending Fare Change Requests (<?php echo count($pending); ?>)
        </h2>

        <?php if ($error): ?>
            <div style="background:#fee2e2;color:#b91c1c;padding:10px;border-radius:8px;margin-bottom:15px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background:#dcfce7;color:#166534;padding:10px;border-radius:8px;margin-bottom:15px;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (!$pending): ?>
            <div style="padding:1rem;background:#f8fafc;border-radius:12px;color:#64748b;text-align:center;">
                No pending requests at the moment.
            </div>
        <?php else: ?>
            <table style="width:100%;border-collapse:collapse;font-size:0.9rem;">
                <thead>
                <tr style="background:#f1f5f9;">
                    <th style="padding:8px;border:1px solid #e2e8f0;">Route</th>
                    <th style="padding:8px;border:1px solid #e2e8f0;">Old Fare</th>
                    <th style="padding:8px;border:1px solid #e2e8f0;">New Fare</th>
                    <th style="padding:8px;border:1px solid #e2e8f0;">Moderator</th>
                    <th style="padding:8px;border:1px solid #e2e8f0;">Requested At</th>
                    <th style="padding:8px;border:1px solid #e2e8f0;">Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($pending as $req): ?>
                    <tr>
                        <td style="padding:8px;border:1px solid #e2e8f0;">
                            <?php echo htmlspecialchars($req['route_name']); ?>
                        </td>
                        <td style="padding:8px;border:1px solid #e2e8f0;">
                            <?php echo htmlspecialchars($req['old_fare']); ?>
                        </td>
                        <td style="padding:8px;border:1px solid #e2e8f0;">
                            <?php echo htmlspecialchars($req['new_fare']); ?>
                        </td>
                        <td style="padding:8px;border:1px solid #e2e8f0;">
                            <?php echo htmlspecialchars($req['moderator_email']); ?>
                        </td>
                        <td style="padding:8px;border:1px solid #e2e8f0;">
                            <?php echo htmlspecialchars($req['created_at']); ?>
                        </td>
                        <td style="padding:8px;border:1px solid #e2e8f0;">
                            <form method="post" action="" style="display:inline;">
                                <input type="hidden" name="request_id"
                                       value="<?php echo (int)$req['id']; ?>">
                                <button type="submit" name="action" value="approve"
                                        class="btn btn-primary" style="margin-right:4px;">
                                    Approve
                                </button>
                                <button type="submit" name="action" value="reject"
                                        class="btn btn-outline">
                                    Reject
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
