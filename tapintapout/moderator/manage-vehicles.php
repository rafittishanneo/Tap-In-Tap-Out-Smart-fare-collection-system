<?php
session_start();
require_once __DIR__ . '/../db/db.php';

if (!isset($_SESSION['userid']) || $_SESSION['userrole'] !== 'moderator') {
    header('Location: ../php/login.php');
    exit;
}

$user_email = $_SESSION['useremail'] ?? 'Moderator';

// Fetch only vehicle info (no scanner/tap feature here)
$stmt = $conn->prepare("
    SELECT
        id,
        COALESCE(vehicle_code, id) AS vehicle_code,
        COALESCE(vehicle_type, type, 'Bus') AS vehicle_type,
        COALESCE(driver_name, 'N/A') AS driver_name,
        COALESCE(driver_mobile, 'N/A') AS driver_mobile,
        COALESCE(driver_license, 'N/A') AS driver_license,
        COALESCE(rfid_scanner_id, 'N/A') AS rfid_scanner_id,
        created_at
    FROM vehicles
    ORDER BY id DESC
");
$stmt->execute();
$vehicles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vehicles | Moderator</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;background:#0f172a;color:#e2e8f0;padding:20px}
        .wrap{max-width:1100px;margin:0 auto}
        .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}
        .card{background:#111827;border:1px solid #1f2937;border-radius:14px;padding:16px}
        .muted{opacity:.8;font-size:14px}
        a{color:#60a5fa;text-decoration:none}
        table{width:100%;border-collapse:collapse;margin-top:12px}
        th,td{padding:12px;border-bottom:1px solid #1f2937;text-align:left;vertical-align:top}
        th{color:#93c5fd;font-weight:600}
        .pill{display:inline-block;padding:4px 10px;border-radius:999px;background:#0b1220;border:1px solid #1f2937}
        .actions{display:flex;gap:10px;flex-wrap:wrap}
        .btn{display:inline-block;padding:10px 14px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e2e8f0}
        .btn:hover{filter:brightness(1.1)}
        @media (max-width: 900px){
            table{display:block;overflow:auto;white-space:nowrap}
            .top{flex-direction:column;align-items:flex-start;gap:10px}
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="top">
        <div>
            <h2>Vehicle Information</h2>
            <div class="muted">Moderator: <?php echo htmlspecialchars($user_email); ?></div>
        </div>
        <div class="actions">
            <a class="btn" href="create-vehicle.php">+ Create Vehicle</a>
            <a class="btn" href="../php/logout.php">Logout</a>
        </div>
    </div>

    <div class="card">
        <div class="muted">Showing <?php echo count($vehicles); ?> vehicles</div>

        <table>
            <thead>
                <tr>
                    <th>Vehicle ID</th>
                    <th>Vehicle Type</th>
                    <th>Driver Info</th>
                    <th>RFID Device</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($vehicles)): ?>
                <tr><td colspan="4">No vehicles found.</td></tr>
            <?php else: ?>
                <?php foreach ($vehicles as $v): ?>
                    <tr>
                        <td>
                            <div class="pill"><?php echo htmlspecialchars($v['vehicle_code']); ?></div>
                            <div class="muted">DB id: <?php echo (int)$v['id']; ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($v['vehicle_type']); ?></td>
                        <td>
                            <div><b><?php echo htmlspecialchars($v['driver_name']); ?></b></div>
                            <div class="muted">Mobile: <?php echo htmlspecialchars($v['driver_mobile']); ?></div>
                            <div class="muted">License: <?php echo htmlspecialchars($v['driver_license']); ?></div>
                        </td>
                        <td>
                            <div class="pill"><?php echo htmlspecialchars($v['rfid_scanner_id']); ?></div>
                            <div class="muted">JT308 (USB)</div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
