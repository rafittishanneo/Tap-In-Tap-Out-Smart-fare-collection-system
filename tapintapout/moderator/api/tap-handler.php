<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../db/db.php';

$card_id = trim($_POST['card_id'] ?? '');
$vehicle_id = (int)($_POST['vehicle_id'] ?? 0);
$pickup = $_POST['pickup'] ?? 'Unknown';
$dropoff = $_POST['dropoff'] ?? 'Unknown';
$tap_type = $_POST['tap_type'] ?? 'in';

if (empty($card_id) || empty($vehicle_id)) {
    echo json_encode(['success' => false, 'message' => 'Missing card/vehicle']);
    exit;
}

// Normalize Card ID (consistent with JS)
$card_id = str_pad(strtoupper(preg_replace('/[^A-F0-9]/', '', $card_id)), 10, '0', STR_PAD_LEFT);

// Get Passenger
$stmt = $conn->prepare("SELECT name, balance FROM passengers WHERE card_id = ?");
$stmt->bind_param('s', $card_id);
$stmt->execute();
$passenger = $stmt->get_result()->fetch_assoc();

if (!$passenger) {
    echo json_encode(['success' => false, 'message' => "Card '$card_id' not registered!"]);
    exit;
}

if ($tap_type === 'in') {
    // TAP IN LOGIC
    $log = $conn->prepare("INSERT INTO tap_logs (vehicle_id, card_id, passenger_name, tap_type, pickup_location, balance) VALUES (?, ?, ?, 'in', ?, ?)");
    $log->bind_param('isssd', $vehicle_id, $card_id, $passenger['name'], $pickup, $passenger['balance']);
    
    if ($log->execute()) {
        echo json_encode(['success' => true, 'message' => "✅ Tap IN $pickup | Bal: ৳" . number_format($passenger['balance'], 2)]);
    } else {
        echo json_encode(['success' => false, 'message' => "DB Error: " . $conn->error]);
    }

} else {
    // TAP OUT LOGIC (Calculate Fare)
    $fare_stmt = $conn->prepare("SELECT fare FROM routes WHERE pickup_location = ? AND dropoff_location = ? LIMIT 1");
    $fare_stmt->bind_param('ss', $pickup, $dropoff);
    $fare_stmt->execute();
    $route = $fare_stmt->get_result()->fetch_assoc();
    
    // Default fallback if route missing
    $fare = $route ? (float)$route['fare'] : 0.00;

    if ($fare == 0) {
        echo json_encode(['success' => false, 'message' => "❌ No route found: $pickup -> $dropoff"]);
        exit;
    }

    // Check Balance
    if ($passenger['balance'] < $fare) {
        echo json_encode(['success' => false, 'message' => "❌ Low Balance! Need ৳$fare (Has ৳{$passenger['balance']})"]);
        exit;
    }

    $new_balance = $passenger['balance'] - $fare;

    // Deduct Balance
    $update = $conn->prepare("UPDATE passengers SET balance = ? WHERE card_id = ?");
    $update->bind_param('ds', $new_balance, $card_id);
    $update->execute();

    // Log Tap Out
    $log_out = $conn->prepare("INSERT INTO tap_logs (vehicle_id, card_id, passenger_name, tap_type, pickup_location, dropoff_location, fare, balance) VALUES (?, ?, ?, 'out', ?, ?, ?, ?)");
    $log_out->bind_param('isssdss', $vehicle_id, $card_id, $passenger['name'], $pickup, $dropoff, $fare, $new_balance);
    $log_out->execute();

    echo json_encode(['success' => true, 'message' => "✅ Tap OUT $dropoff | Fare: -৳$fare | New Bal: ৳$new_balance"]);
}
?>
