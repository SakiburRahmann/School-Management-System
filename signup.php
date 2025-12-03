<?php
require_once __DIR__ . '/core/controllers/AuthController.php';

$controller = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->signup();
} else {
    $controller->showSignup();
}