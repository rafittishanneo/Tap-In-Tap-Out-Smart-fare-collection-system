<?php
header('Content-Type: application/json');
require_once '../../db/db.php';
$stmt = $conn->prepare("SELECT DISTINCT pickup_location, dropoff_location, fare FROM routes ORDER BY pickup_location");
$stmt->execute();
$routes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo json_encode($routes);
?>
