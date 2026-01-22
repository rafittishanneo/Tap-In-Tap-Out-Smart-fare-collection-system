<?php


require_once __DIR__ . '/../db/db.php';              
require_once __DIR__ . '../tapintapout/models/user.php';
require_once __DIR__ . '../tapintapout/controllers/authcontroller.php';

$userModel      = new User($conn);
$authController = new AuthController($userModel);

$authController->login();
