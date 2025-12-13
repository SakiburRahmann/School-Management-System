<?php
require_once 'config.php';
$conn = Database::getInstance()->getConnection();
$stmt = $conn->query("DESCRIBE exams");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo $col['Field'] . " - " . $col['Type'] . "\n";
}
