<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../db/db.php';

$res = $conn->query("SELECT id, COALESCE(name, CONCAT('Bus-', id)) AS name FROM vehicles ORDER BY id DESC");
echo json_encode($res->fetch_all(MYSQLI_ASSOC));
exit;
