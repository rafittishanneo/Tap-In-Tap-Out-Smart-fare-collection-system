<?php
session_start();
require_once __DIR__ . '/../db/db.php';

// Only moderator
if (!isset($_SESSION['userid']) || $_SESSION['userrole'] !== 'moderator') {
    header('Location: ../php/login.php');
    exit;
}

$error = '';

// Fetch all routes
$routes = [];
$sql = "SELECT id, name, start_point, end_point, base_fare, created_at
        FROM routes
        ORDER BY created_at DESC, id DESC";

$result = $conn->query($sql); // simple read, no user input
if ($result) {
    $routes = $result->fetch_all(MYSQLI_ASSOC); // [web:93]
} else {
    $error = "Failed to load routes.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Route Reports | Moderator</title>
    <link rel="stylesheet" href="moderator-dashboardcss.css">
    <link rel="stylesheet" href="route-reports.css">
</head>
<body>
<div class="dashboard">
    <header class="header">
        <div class="header-content">
            <div class="welcome">📊 Route Reports</div>
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

        <section class="reports-header">
            <div>
                <h2>All active routes</h2>
                <p>Summary of routes with start/end points and base fares.</p>
            </div>
            <div class="badge-pill">
                Total Routes: <span><?php echo count($routes); ?></span>
            </div>
        </section>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!$routes): ?>
            <div class="empty-state">
                <div class="empty-icon">🛣️</div>
                <h3>No routes found</h3>
                <p>Use “Add Route” to create your first route.</p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="routes-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Route name</th>
                        <th>Start point</th>
                        <th>End point</th>
                        <th>Base fare (৳)</th>
                        <th>Created at</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($routes as $route): ?>
                        <tr>
                            <td><?php echo (int)$route['id']; ?></td>
                            <td><?php echo htmlspecialchars($route['name']); ?></td>
                            <td><?php echo htmlspecialchars($route['start_point']); ?></td>
                            <td><?php echo htmlspecialchars($route['end_point']); ?></td>
                            <td><?php echo htmlspecialchars($route['base_fare']); ?></td>
                            <td><?php echo htmlspecialchars($route['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</div>

<script src="route-reports.js"></script>
</body>
</html>
