<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance()->getConnection();

echo "Fixing schema...\n";

// Whitelist of allowed tables to prevent SQL injection
$allowedTables = ['teachers', 'students'];

foreach ($allowedTables as $table) {
    try {
        echo "Checking $table...\n";

        // Use a placeholder for the table name in the query, even though it's not a real prepared statement parameter
        $stmt = $db->prepare("SHOW COLUMNS FROM `$table` LIKE 'status'");
        $stmt->execute();

        if (!$stmt->fetch()) {
            echo "Adding status column to $table...\n";
            $db->exec("ALTER TABLE `$table` ADD COLUMN status ENUM('Active', 'Inactive') DEFAULT 'Active'");
            echo "Success.\n";
        } else {
            echo "Status column already exists in $table.\n";
        }
    } catch (Exception $e) {
        echo "Error updating $table: " . $e->getMessage() . "\n";
    }
}

echo "Schema fix complete.\n";
