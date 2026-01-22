<?php
session_start();
require_once __DIR__ . '/../db/db.php';

if (!isset($_SESSION['userid']) || $_SESSION['userrole'] !== 'moderator') {
    header('Location: ../php/login.php');
    exit;
}

$user_email = $_SESSION['useremail'] ?? 'Moderator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Vehicle | Tap in Tap Out</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/web-tech/tapintapout/moderator/create-vehicle.css">
</head>
<body>
<div class="dashboard">
    <header class="header">
        <div class="header-content">
            <div class="welcome">🚐 Create Vehicle (Moderator)</div>
            <div class="user-info">
                <div class="user-email"><?php echo htmlspecialchars($user_email); ?></div>
                <a href="../php/logout.php" class="logout">Logout</a>
            </div>
        </div>
    </header>

    <main class="content">
        <h1 class="page-title">New Vehicle</h1>

        <div id="alert" class="alert" style="display:none;"></div>

        <form id="vehicleForm" novalidate>
            <div class="form-row">
                <div class="form-group">
                    <label for="vehicle_type">Vehicle Type</label>
                    <select id="vehicle_type" name="vehicle_type">
                        <option value="">Select type</option>
                        <option value="Bus">Bus</option>
                        <option value="Mini Bus">Mini Bus</option>
                        <option value="Train">Train</option>
                    </select>
                    <span class="error" id="vehicle_type_error"></span>
                </div>

                <div class="form-group">
                    <label for="vehicle_code">Vehicle ID / Code</label>
                    <input type="text" id="vehicle_code" name="vehicle_code" placeholder="e.g. BUS-101">
                    <span class="error" id="vehicle_code_error"></span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="driver_name">Driver Name</label>
                    <input type="text" id="driver_name" name="driver_name" placeholder="Driver full name">
                    <span class="error" id="driver_name_error"></span>
                </div>

                <div class="form-group">
                    <label for="driver_mobile">Driver Mobile</label>
                    <input type="text" id="driver_mobile" name="driver_mobile" placeholder="01XXXXXXXXX">
                    <span class="error" id="driver_mobile_error"></span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="driver_license">Driving Licence No.</label>
                    <input type="text" id="driver_license" name="driver_license" placeholder="License number">
                    <span class="error" id="driver_license_error"></span>
                </div>

                <div class="form-group">
                    <label for="rfid_scanner_id">RFID Scanner ID</label>
                    <input type="text" id="rfid_scanner_id" name="rfid_scanner_id" placeholder="Scanner ID / Serial">
                    <span class="error" id="rfid_scanner_id_error"></span>
                </div>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" selected>Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Save Vehicle</button>
            <a href="moderator-dashboardphp.php" class="btn btn-outline">Back to Dashboard</a>
        </form>
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/web-tech/tapintapout/moderator/create-vehicle.js"></script>
</body>
</html>
