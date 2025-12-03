<?php
// Delegate to AuthController (MVC-style), but keep this as the entry script

require_once __DIR__ . '/core/controllers/AuthController.php';

$controller = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->login();
} else {
    $controller->showLogin();
}
