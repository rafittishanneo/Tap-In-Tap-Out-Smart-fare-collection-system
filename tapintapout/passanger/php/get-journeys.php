<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../db/db.php';

$limit = $_GET['limit'] ?? 5;
$stmt = $conn->prepare("
    SELECT v.vehicle_code, tl.tap_type, tl.route_fare, tl.timestamp 
    FROM tap_logs tl 
    JOIN vehicles v ON tl.vehicle_id = v.id 
    WHERE tl.passenger_card_id IN (SELECT card_id FROM passengers WHERE user_id = ?)
    ORDER BY tl.timestamp DESC LIMIT ?
");
$stmt->bind_param('ii', $_SESSION['userid'], $limit);
$stmt->execute();
$journeys = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['success' => true, 'journeys' => $journeys]);
?>
