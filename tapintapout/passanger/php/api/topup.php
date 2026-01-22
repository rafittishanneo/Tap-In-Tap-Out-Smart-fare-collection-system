<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../db/db.php';

if (!isset($_SESSION['userid']) || $_SESSION['userrole'] !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = (int)$_SESSION['userid'];
$amount = (float)($_POST['amount'] ?? 0);

if ($amount < 10) {
    echo json_encode(['success' => false, 'message' => 'Minimum top up is ৳10']);
    exit;
}
if ($amount > 5000) {
    echo json_encode(['success' => false, 'message' => 'Maximum top up is ৳5000']);
    exit;
}

// Get latest linked card
$stmt = $conn->prepare("SELECT id, card_id, balance FROM passengers WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$passenger = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$passenger) {
    echo json_encode(['success' => false, 'message' => 'No card linked with this account. Register card first.']);
    exit;
}

$new_balance = (float)$passenger['balance'] + $amount;

// Update balance
$upd = $conn->prepare("UPDATE passengers SET balance = ? WHERE id = ?");
$upd->bind_param('di', $new_balance, $passenger['id']);
$ok = $upd->execute();
$upd->close();

if (!$ok) {
    echo json_encode(['success' => false, 'message' => 'DB update failed']);
    exit;
}

echo json_encode([
    'success' => true,
    'new_balance' => $new_balance,
    'message' => '✅ Top up successful: +৳' . number_format($amount, 2)
]);
