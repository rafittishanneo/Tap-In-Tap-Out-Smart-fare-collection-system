<?php
session_start();

// from admin/php → admin → tapintapout → db/db.php
require_once __DIR__ . '/../../db/db.php';

if (!isset($_SESSION['userid']) || $_SESSION['userrole'] !== 'admin') {
    header('Location: ../php/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

header('Location: notifications.php');
exit;
