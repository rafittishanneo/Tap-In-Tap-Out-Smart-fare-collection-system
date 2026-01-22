<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../db/db.php';

$card_id = $_POST['card_id'] ?? '';
$stmt = $conn->prepare("SELECT name, balance FROM passengers WHERE card_id = ?");
$stmt->bind_param('s', $card_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($result) {
    echo json_encode(['success' => true, 'name' => $result['name'], 'balance' => $result['balance']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Card not registered']);
}
?>
