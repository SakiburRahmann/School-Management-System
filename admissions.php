<?php
require_once __DIR__ . '/core/controllers/WebsiteController.php';

$controller = new WebsiteController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->submitAdmission();
} else {
    $controller->admissions();
}


