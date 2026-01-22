<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../db/db.php';

if (!isset($_SESSION['userid']) || ($_SESSION['userrole'] ?? '') !== 'user') {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

$user_id = (int)$_SESSION['userid'];
$vehicle_id = (int)($_POST['vehicle_id'] ?? 0);
$card_id = trim($_POST['card_id'] ?? '');
$pickup  = trim($_POST['pickup'] ?? '');
$dropoff = trim($_POST['dropoff'] ?? '');

$card_id = str_pad(strtoupper(preg_replace('/[^A-F0-9]/', '', $card_id)), 10, '0', STR_PAD_LEFT);

if ($vehicle_id <= 0 || !$card_id || !$pickup || !$dropoff) {
    echo json_encode(['success'=>false,'message'=>'Missing data']);
    exit;
}

// ensure vehicle exists (FK safety)
$stmt = $conn->prepare("SELECT id FROM vehicles WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $vehicle_id);
$stmt->execute();
$veh = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$veh) {
    echo json_encode(['success'=>false,'message'=>'Invalid vehicle selected']);
    exit;
}

// Card belongs to this user
$stmt = $conn->prepare("SELECT name, balance FROM passengers WHERE user_id = ? AND card_id = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param('is', $user_id, $card_id);
$stmt->execute();
$passenger = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$passenger) {
    echo json_encode(['success'=>false,'message'=>'This card is not linked to your account']);
    exit;
}

// Route fare
$stmt = $conn->prepare("SELECT fare FROM routes WHERE pickup_location = ? AND dropoff_location = ? LIMIT 1");
$stmt->bind_param('ss', $pickup, $dropoff);
$stmt->execute();
$route = $stmt->get_result()->fetch_assoc();
$stmt->close();

$fare = $route ? (float)$route['fare'] : 0.0;
if ($fare <= 0) {
    echo json_encode(['success'=>false,'message'=>"No route found: $pickup → $dropoff"]);
    exit;
}

$balance = (float)$passenger['balance'];
if ($balance < $fare) {
    echo json_encode(['success'=>false,'message'=>"Insufficient balance. Need ৳".number_format($fare,2)]);
    exit;
}

$new_balance = $balance - $fare;

try {
    $conn->begin_transaction();

    // 1) Deduct balance
    $upd = $conn->prepare("UPDATE passengers SET balance = ? WHERE user_id = ? AND card_id = ?");
    $upd->bind_param('dis', $new_balance, $user_id, $card_id);
    if (!$upd->execute()) {
        throw new Exception("Balance update failed: " . $upd->error);
    }
    $upd->close();

    // 2) Insert tap log (vehicle_id now valid)
    $log = $conn->prepare("
        INSERT INTO tap_logs (vehicle_id, passenger_card_id, tap_type, route_fare, pickup_location, dropoff_location, fare)
        VALUES (?, ?, 'out', ?, ?, ?, ?)
    ");
    $types = "isdssd";
    $log->bind_param($types, $vehicle_id, $card_id, $fare, $pickup, $dropoff, $fare);
    if (!$log->execute()) {
        throw new Exception("Tap log insert failed: " . $log->error);
    }
    $log->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'new_balance' => $new_balance,
        'fare' => $fare,
        'message' => "Fare -৳".number_format($fare,2)." deducted. New balance: ৳".number_format($new_balance,2)
    ]);
    exit;

} catch (Throwable $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Transaction failed',
        'debug' => $e->getMessage()
    ]);
    exit;
}
