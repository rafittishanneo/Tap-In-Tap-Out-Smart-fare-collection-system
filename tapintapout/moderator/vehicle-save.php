<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../db/db.php';

if (!isset($_SESSION['userid']) || $_SESSION['userrole'] !== 'moderator') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$errors = [];
$data = [];

$fields = [
    'vehicle_type',
    'vehicle_code',
    'driver_name',
    'driver_mobile',
    'driver_license',
    'rfid_scanner_id',
    'status'
];

foreach ($fields as $f) {
    $data[$f] = trim($_POST[$f] ?? '');
}

// Validation (server-side is mandatory for security)
if ($data['vehicle_type'] === '') {
    $errors['vehicle_type'] = 'Vehicle type is required';
}

if ($data['vehicle_code'] === '' || strlen($data['vehicle_code']) < 3) {
    $errors['vehicle_code'] = 'Vehicle ID must be at least 3 characters';
}

if ($data['driver_name'] === '' || strlen($data['driver_name']) < 3) {
    $errors['driver_name'] = 'Driver name must be at least 3 characters';
}

if (!preg_match('/^01[0-9]{9}$/', $data['driver_mobile'])) {
    $errors['driver_mobile'] = 'Invalid mobile number format';
}

if ($data['driver_license'] === '' || strlen($data['driver_license']) < 5) {
    $errors['driver_license'] = 'License number is too short';
}

if ($data['rfid_scanner_id'] === '' || strlen($data['rfid_scanner_id']) < 3) {
    $errors['rfid_scanner_id'] = 'RFID scanner ID is required';
}

if ($data['status'] !== 'active' && $data['status'] !== 'inactive') {
    $data['status'] = 'active';
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => 'Validation failed',
        'errors'  => $errors
    ]);
    exit;
}

$vehicle_type     = $data['vehicle_type'];
$vehicle_code     = $data['vehicle_code'];
$driver_name      = $data['driver_name'];
$driver_mobile    = $data['driver_mobile'];
$driver_license   = $data['driver_license'];
$rfid_scanner_id  = $data['rfid_scanner_id'];
$status           = $data['status'];
$created_by       = $_SESSION['userid'];

// Insert with prepared statement to prevent SQL injection
$stmt = $conn->prepare("
    INSERT INTO vehicles
    (vehicle_type, vehicle_code, driver_name, driver_mobile, driver_license, rfid_scanner_id, status, created_by)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: prepare failed']);
    exit;
}

$stmt->bind_param(
    "sssssssi",
    $vehicle_type,
    $vehicle_code,
    $driver_name,
    $driver_mobile,
    $driver_license,
    $rfid_scanner_id,
    $status,
    $created_by
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Vehicle created successfully']);
} else {
    $msg = 'DB error';
    if ($conn->errno === 1062) {
        $errors['vehicle_code'] = 'Vehicle ID already exists';
        echo json_encode(['success' => false, 'message' => 'Duplicate vehicle ID', 'errors' => $errors]);
    } else {
        echo json_encode(['success' => false, 'message' => $msg]);
    }
}

$stmt->close();
