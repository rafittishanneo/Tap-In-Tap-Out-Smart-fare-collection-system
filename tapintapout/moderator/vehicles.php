<?php
session_start();
require_once __DIR__ . '/../db/db.php';

if (!isset($_SESSION['userid']) || $_SESSION['userrole'] !== 'moderator') {
    header('Location: ../php/login.php');
    exit;
}

$user_email = $_SESSION['useremail'] ?? 'Moderator';

// Safe vehicles fetch
$stmt = $conn->prepare("
    SELECT 
        id, 
        COALESCE(vehicle_code, id) as vehicle_code,
        COALESCE(name, CONCAT('Bus-', COALESCE(vehicle_code, id))) AS name, 
        COALESCE(type, 'Bus') AS type
    FROM vehicles ORDER BY created_at DESC
");
$stmt->execute();
$vehicles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚌 Manage Vehicles - Tap in Tap Out</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden; }
        header { background: #1e293b; color: white; padding: 20px; text-align: center; }
        .dashboard { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding: 20px; }
        .card { background: #f8fafc; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .scanner-section { grid-column: span 2; background: #ecfdf5; border: 3px dashed #10b981; text-align: center; padding: 40px; border-radius: 16px; }
        #rfidInput { width: 100%; height: 80px; font-size: 24px; text-align: center; border: 3px solid #10b981; border-radius: 12px; background: white; margin: 20px 0; font-family: monospace; }
        .status { font-size: 28px; font-weight: bold; margin: 20px 0; min-height: 40px; padding: 10px; border-radius: 8px; }
        .success { color: #10b981; background: #d1fae5; }
        .error { color: #ef4444; background: #fee2e2; }
        .tap-log { background: #0f172a; color: #e2e8f0; padding: 15px; border-radius: 8px; max-height: 200px; overflow-y: auto; font-family: monospace; font-size: 14px; }
        .tap-entry { padding: 8px 0; border-bottom: 1px solid #334155; }
        select, button { padding: 12px 20px; border-radius: 8px; border: 1px solid #d1d5db; font-size: 16px; width: 100%; margin: 5px 0; }
        button { background: #3b82f6; color: white; border: none; cursor: pointer; }
        button:hover { background: #2563eb; }
        .full-width { width: 100%; }
        @media (max-width: 768px) { .dashboard { grid-template-columns: 1fr; } .scanner-section { grid-column: span 1; } }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🚌 Vehicle Management & RFID Scanner</h1>
            <p>Moderator: <?php echo htmlspecialchars($user_email); ?> | <a href="../php/logout.php" style="color: #fbbf24;">Logout</a></p>
        </header>
        <div class="dashboard">
            <div class="card">
                <h3>Vehicles (<?php echo count($vehicles); ?>)</h3>
                <select id="vehicleSelect">
                    <option value="">Select Vehicle</option>
                    <?php foreach ($vehicles as $v): ?>
                        <option value="<?php echo $v['id']; ?>">
                            <?php echo htmlspecialchars($v['name'] . ' (' . $v['type'] . ') ID:' . $v['vehicle_code']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="card">
                <h3>Route Selector (Synced with DB)</h3>
                <select id="pickupSelect">
                    <option value="">Pickup (Tap IN)</option>
                </select>
                <select id="dropoffSelect">
                    <option value="">Dropoff (Tap OUT)</option>
                </select>
            </div>
            <div class="scanner-section">
                <h2>📱 JT308 RFID Scanner</h2>
                <p>Hold passenger card 1-2cm → Auto detects → Select destination → Deduct fare</p>
                <input type="text" id="rfidInput" placeholder="0008080924 appears here..." maxlength="10" autofocus>
                <div id="status" class="status success">✅ READY - Tap card now!</div>
                <button onclick="testScan()">🧪 Test Card (0008080924)</button>
                <div id="journeyList" class="tap-log">Recent taps appear here...</div>
            </div>
        </div>
    </div>

    <script>
        let currentVehicleId = null;
        let isJourneyActive = false;

        // Load Routes from DB
        fetch('api/routes.php')
            .then(r => r.json())
            .then(routes => {
                const pickups = new Set();
                const dropoffs = new Set();
                routes.forEach(r => {
                    pickups.add(r.pickup_location);
                    dropoffs.add(r.dropoff_location);
                });
                // Sort and add to dropdowns
                [...pickups].sort().forEach(loc => {
                    document.getElementById('pickupSelect').innerHTML += `<option value="${loc}">${loc}</option>`;
                });
                [...dropoffs].sort().forEach(loc => {
                    document.getElementById('dropoffSelect').innerHTML += `<option value="${loc}">${loc}</option>`;
                });
            });

        // JT308 normalization
        function normalizeCard(rawId) {
            return rawId.replace(/[^0-9A-Fa-f]/g, '').padStart(10, '0').toUpperCase().slice(-10);
        }

        // Auto-detect input
        document.getElementById('rfidInput').addEventListener('input', function(e) {
            if (e.target.value.length >= 8) {
                handleTap(e.target.value);
                e.target.value = '';
            }
        });

        function handleTap(rawId) {
            const cardId = normalizeCard(rawId);
            const statusEl = document.getElementById('status');
            statusEl.textContent = `🟡 Detecting ${cardId}...`;
            statusEl.className = 'status';

            if (!currentVehicleId) return showStatus('❌ Select vehicle first!', 'error');
            
            const pickup = document.getElementById('pickupSelect').value || 'Unknown';
            const dropoff = document.getElementById('dropoffSelect').value || 'Unknown';
            // Logic: If pickup selected & no dropoff -> Tap IN. If dropoff selected -> Tap OUT
            const tapType = (dropoff && dropoff !== 'Unknown' && dropoff !== '') ? 'out' : 'in';

            const formData = new FormData();
            formData.append('card_id', cardId);
            formData.append('vehicle_id', currentVehicleId);
            formData.append('pickup', pickup);
            formData.append('dropoff', dropoff);
            formData.append('tap_type', tapType);

            fetch('api/tap-handler.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showStatus(data.message, 'success');
                        loadJourneys();
                    } else {
                        showStatus('❌ ' + data.message, 'error');
                    }
                }).catch((e) => {
                    console.error(e);
                    showStatus('❌ API Error - Check Console', 'error');
                });
        }

        function testScan() { handleTap('0008080924'); }

        function showStatus(msg, type) {
            const el = document.getElementById('status');
            el.textContent = msg;
            el.className = `status ${type}`;
        }

        function loadJourneys() {
            if (!currentVehicleId) return;
            fetch(`api/tap-logs.php?vehicle_id=${currentVehicleId}`)
                .then(r => r.json())
                .then(logs => {
                    let html = logs.map(log => 
                        `<div class="tap-entry">
                            ${log.card_id} | ${log.passenger_name} | 
                            ${log.tap_type.toUpperCase()} | 
                            ${log.pickup_location || '?'}→${log.dropoff_location || '?'} | 
                            Fare: ৳${log.fare || 0} | Bal:৳${log.balance || 0}
                        </div>`
                    ).join('');
                    document.getElementById('journeyList').innerHTML = html || 'No taps yet...';
                });
        }

        document.getElementById('vehicleSelect').addEventListener('change', e => {
            currentVehicleId = e.target.value;
            if (currentVehicleId) {
                loadJourneys();
                showStatus('✅ Vehicle selected - Tap card!', 'success');
            }
        });
        
        // Auto-focus scanner
        document.getElementById('rfidInput').focus();
        document.body.addEventListener('click', () => document.getElementById('rfidInput').focus());
    </script>
</body>
</html>
