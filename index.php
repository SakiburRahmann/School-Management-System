<?php
// Public-facing home page handled via WebsiteController

require_once __DIR__ . '/core/controllers/WebsiteController.php';

$controller = new WebsiteController();
$controller->home();
