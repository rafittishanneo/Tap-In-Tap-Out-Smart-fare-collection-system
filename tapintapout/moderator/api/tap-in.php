<?php
header('Content-Type: application/json');
require_once '../../db/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$card_id = str_pad(trim($data['card_id']), 10, '0', STR_PAD_LEFT);
$vehicle_id = (int)$data['vehicle_id'];
$fare = 25.00; // আপনার নির্ধারিত ভাড়া

// ১. কার্ড রেজিস্টার্ড কি না এবং ব্যালেন্স চেক
$stmt = $conn->prepare("SELECT balance FROM passengers WHERE card_id = ?");
$stmt->bind_param('s', $card_id);
$stmt->execute();
$passenger = $stmt->get_result()->fetch_assoc();

if (!$passenger) {
    exit(json_encode(['success' => false, 'message' => "কার্ড $card_id নিবন্ধিত নয়!"]));
}

// ২. চেক করুন এই কার্ডের কোনো 'active' ট্রিপ আছে কি না
$stmt = $conn->prepare("SELECT id FROM tap_logs WHERE passenger_card_id = ? AND status = 'active' LIMIT 1");
$stmt->bind_param('s', $card_id);
$stmt->execute();
$active_trip = $stmt->get_result()->fetch_assoc();

if ($active_trip) {
    // --- ট্যাপ আউট (Tap Out) ---
    $new_balance = $passenger['balance'] - $fare;
    $trip_id = $active_trip['id'];

    if ($passenger['balance'] < $fare) {
        exit(json_encode(['success' => false, 'message' => "ব্যালেন্স অপর্যাপ্ত! ট্যাপ আউট করা যাচ্ছে না।"]));
    }

    $conn->begin_transaction();
    $conn->query("UPDATE tap_logs SET tap_type = 'out', status = 'completed' WHERE id = $trip_id");
    $conn->query("UPDATE passengers SET balance = $new_balance WHERE card_id = '$card_id'");
    $conn->commit();

    echo json_encode([
        'success' => true,
        'action' => 'tap_out',
        'fare' => $fare,
        'balance' => $new_balance,
        'journey_details' => ['card_id' => $card_id, 'tap_type' => 'out', 'fare' => $fare, 'balance' => $new_balance]
    ]);
} else {
    // --- ট্যাপ ইন (Tap In) ---
    $stmt = $conn->prepare("INSERT INTO tap_logs (vehicle_id, passenger_card_id, tap_type, status) VALUES (?, ?, 'in', 'active')");
    $stmt->bind_param('is', $vehicle_id, $card_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'action' => 'tap_in',
            'balance' => $passenger['balance'],
            'journey_details' => ['card_id' => $card_id, 'tap_type' => 'in', 'balance' => $passenger['balance']]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => "ডাটাবেজ এরর: ইনসার্ট করা যাচ্ছে না।"]);
    }
}