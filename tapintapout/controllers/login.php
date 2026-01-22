<?php
// public/login.php

require_once __DIR__ . '/../db/db.php';      // creates $conn (mysqli)
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

// $conn should come from db.php
$userModel     = new User($conn);
$authController = new AuthController($userModel);

// Handle the request
$authController->login();
