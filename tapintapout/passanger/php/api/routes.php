<?php
header('Content-Type: application/json');
require_once '../../../db/db.php';

$stmt = $conn->prepare("SELECT pickup_location, dropoff_location, fare FROM routes ORDER BY pickup_location, dropoff_location");
$stmt->execute();
echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
