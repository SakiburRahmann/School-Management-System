<?php

class Database
{
    private static ?mysqli $instance = null;

    public static function getInstance(): mysqli
    {
        if (self::$instance === null) {
            // Reuse existing config.php for credentials / connection
            require __DIR__ . '/../config.php';

            if (!isset($conn) || !($conn instanceof mysqli)) {
                throw new Exception('Database connection not initialized correctly.');
            }

            self::$instance = $conn;
        }

        return self::$instance;
    }
}


