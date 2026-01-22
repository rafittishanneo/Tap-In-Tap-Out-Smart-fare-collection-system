<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../db/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$vehicle_id = (int)$_POST['vehicle_id'] ?? 0;
$card_id = trim($_POST['card_id'] ?? '');
$tap_type = $_POST['tap_type'] ?? 'in'; // 'in' or 'out'

if (!$vehicle_id || !$card_id || !in_array($tap_type, ['in', 'out'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Get passenger balance (assume passengers table has card_id, balance)
$stmt = $conn->prepare("SELECT balance FROM passengers WHERE card_id = ?");
$stmt->bind_param('s', $card_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$current_balance = $result['balance'] ?? 0.0;
$stmt->close();

// Simulate fare (replace with real route lookup)
$route_fare = 25.00; // e.g., Dhaka route fare

if ($tap_type === 'out') {
    // Deduct fare on exit
    if ($current_balance < $route_fare) {
        echo json_encode(['success' => false, 'message' => 'Insufficient balance']);
        exit;
    }
    $new_balance = $current_balance - $route_fare;
} else {
    $new_balance = $current_balance;
}

// Log tap
$stmt = $conn->prepare("
    INSERT INTO tap_logs (vehicle_id, passenger_card_id, tap_type, route_fare)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param('issd', $vehicle_id, $card_id, $tap_type, $route_fare);
$stmt->execute();
$stmt->close();

// Update balance
$stmt = $conn->prepare("UPDATE passengers SET balance = ? WHERE card_id = ?");
$stmt->bind_param('ds', $new_balance, $card_id);
$stmt->execute();
$stmt->close();

echo json_encode([
    'success' => true,
    'tap_type' => $tap_type,
    'card_id' => $card_id,
    'fare' => $route_fare,
    'new_balance' => $new_balance
]);
?>
