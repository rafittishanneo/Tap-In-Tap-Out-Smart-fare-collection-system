<?php
header('Content-Type: application/json');
require_once '../../db/db.php';

$vehicle_id = (int)($_GET['vehicle_id'] ?? 0);
$stmt = $conn->prepare("SELECT card_id, passenger_name, tap_type, pickup_location, dropoff_location, fare, balance, created_at FROM tap_logs WHERE vehicle_id = ? ORDER BY id DESC LIMIT 20");
$stmt->bind_param('i', $vehicle_id);
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo json_encode($logs);
?>
