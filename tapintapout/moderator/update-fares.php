<?php
session_start();
require_once __DIR__ . '/../db/db.php';
require_once __DIR__ . '/../models/FareChangeRequest.php';

if (!isset($_SESSION['userid']) || $_SESSION['userrole'] !== 'moderator') {
    header('Location: ../php/login.php');
    exit;
}

$fareModel = new FareChangeRequest($conn);
$error = '';
$success = '';

$routes = [];
$res = $conn->query("SELECT id, name, base_fare FROM routes ORDER BY name");
if ($res) {
    $routes = $res->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $routeId = (int)($_POST['route_id'] ?? 0);
    $oldFare = (float)($_POST['old_fare'] ?? 0);
    $newFare = (float)($_POST['new_fare'] ?? 0);

    if ($routeId <= 0 || $newFare <= 0) {
        $error = "Please select a route and enter a valid fare.";
    } elseif ($newFare === $oldFare) {
        $error = "New fare must be different from old fare.";
    } else {
        $ok = $fareModel->create($routeId, $oldFare, $newFare, (int)$_SESSION['userid']);
        if ($ok) {
            $success = "Fare change request submitted for admin approval.";
        } else {
            $error = "Failed to submit request. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Fares | Tap in Tap Out</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="moderator-dashboardcss.css">
    <link rel="stylesheet" href="update-fares.css">
</head>
<body>
<div class="dashboard">
    <header class="header">
        <div class="header-content">
            <div class="welcome">
            <h1 class="page-title">💰 Update Fares</h1>   
            </div>
            <div class="user-info">
                <div class="user-email"><?php echo htmlspecialchars($_SESSION['useremail']); ?></div>
                <a href="../php/logout.php" class="logout">Logout</a>
            </div>
        </div>
    </header>

    <main class="content">
        <div class="page-header">
            <h1 class="page-title">💰 Update Fares</h1>
            <p class="page-subtitle">Submit fare change requests for admin approval</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form id="fareForm" method="post">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="route_id">Select Route</label>
                        <select id="route_id" name="route_id" class="input-field" required>
                            <option value="">Choose a route...</option>
                            <?php foreach ($routes as $r): ?>
                                <option value="<?php echo (int)$r['id']; ?>"
                                        data-old-fare="<?php echo $r['base_fare']; ?>">
                                    <?php echo htmlspecialchars($r['name']); ?> (৳<?php echo number_format($r['base_fare'], 2); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="old_fare">Current Fare</label>
                        <input type="text" id="old_fare" name="old_fare" class="input-field readonly" readonly>
                    </div>

                    <div class="form-group">
                        <label for="new_fare">New Fare (৳)</label>
                        <input type="number" step="0.01" min="0.01" id="new_fare" name="new_fare" class="input-field" required>
                    </div>
                </div>

                <div id="farePreview" class="fare-preview" style="display: none;">
                    <div class="preview-content">
                        <span class="preview-label">Change from:</span>
                        <span class="preview-old" id="previewOld">৳0.00</span>
                        <span class="preview-arrow">→</span>
                        <span class="preview-new" id="previewNew">৳0.00</span>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="moderator-dashboardphp.php" class="btn btn-secondary">← Back to Dashboard</a>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Submit Request</button>
                </div>
            </form>
        </div>
    </main>
</div>

<script src="update-fares.js"></script>
</body>
</html>
