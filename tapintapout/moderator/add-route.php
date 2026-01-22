<?php
session_start();
require_once __DIR__ . '/../db/db.php';

// Only moderator can access
if (!isset($_SESSION['userid']) || $_SESSION['userrole'] !== 'moderator') {
    header('Location: ../php/login.php');
    exit;
}

$error = '';
$success = '';

// Keep old values for sticky form
$routeName  = '';
$startPoint = '';
$endPoint   = '';
$baseFare   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $routeName   = trim($_POST['route_name'] ?? '');
    $startPoint  = trim($_POST['start_point'] ?? '');
    $endPoint    = trim($_POST['end_point'] ?? '');
    $baseFare    = trim($_POST['base_fare'] ?? '');

    // PHP validation (server-side)
    if ($routeName === '' || $startPoint === '' || $endPoint === '' || $baseFare === '') {
        $error = "All fields are required.";
    } elseif (!is_numeric($baseFare) || (float)$baseFare <= 0) {
        $error = "Base fare must be a positive number.";
    } else {
        $fare = (float)$baseFare;

        // Prepared statement to prevent SQL injection
        $stmt = $conn->prepare(
            "INSERT INTO routes (name, start_point, end_point, base_fare, created_at)
             VALUES (?, ?, ?, ?, NOW())"
        );

        if ($stmt) {
            $stmt->bind_param("sssd", $routeName, $startPoint, $endPoint, $fare);
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok) {
                $success   = "Route added successfully.";
                $routeName = $startPoint = $endPoint = $baseFare = '';
            } else {
                $error = "Failed to add route. Please try again.";
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
    <title>Add Route | Moderator</title>
    <link rel="stylesheet" href="moderator-dashboardcss.css">
    <link rel="stylesheet" href="add-route.css">
</head>
<body>
<div class="dashboard">
    <header class="header">
        <div class="header-content">
            <div class="welcome">➕ Add New Route</div>
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
                <h2>Create a new bus route</h2>
                <p>Define the start, end points and base fare for this route.</p>
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

            <form id="addRouteForm" method="post" action="" novalidate>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="route_name">Route name</label>
                        <input type="text" id="route_name" name="route_name"
                               class="input-field"
                               placeholder="e.g. Route 1 - Mirpur to Dhanmondi"
                               required
                               value="<?php echo htmlspecialchars($routeName); ?>">
                    </div>

                    <div class="form-group">
                        <label for="start_point">Start point</label>
                        <input type="text" id="start_point" name="start_point"
                               class="input-field"
                               placeholder="e.g. Mirpur 1"
                               required
                               value="<?php echo htmlspecialchars($startPoint); ?>">
                    </div>

                    <div class="form-group">
                        <label for="end_point">End point</label>
                        <input type="text" id="end_point" name="end_point"
                               class="input-field"
                               placeholder="e.g. Dhanmondi 27"
                               required
                               value="<?php echo htmlspecialchars($endPoint); ?>">
                    </div>

                    <div class="form-group">
                        <label for="base_fare">Base fare (৳)</label>
                        <input type="number" step="0.01" min="0.01"
                               id="base_fare" name="base_fare"
                               class="input-field"
                               placeholder="e.g. 20"
                               required
                               value="<?php echo htmlspecialchars($baseFare); ?>">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Save Route</button>
                    <button type="reset" class="btn-secondary">Clear</button>
                </div>
            </form>
        </section>
    </main>
</div>

<script src="add-route.js"></script>
</body>
</html>
