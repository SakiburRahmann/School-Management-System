<?php

require_once __DIR__ . '/Database.php';

abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Find a record by ID
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Find all records
    public function findAll()
    {
        $result = $this->db->query("SELECT * FROM {$this->table}");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Insert a new record
    public function insert(array $data)
    {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $types = str_repeat("s", count($data)); // Assuming all strings for simplicity, can be improved
        $values = array_values($data);

        $stmt = $this->db->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})");
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    // Update a record
    public function update($id, array $data)
    {
        $setClause = [];
        $types = "";
        $values = [];

        foreach ($data as $key => $value) {
            $setClause[] = "{$key} = ?";
            $types .= "s";
            $values[] = $value;
        }
        $setClause = implode(", ", $setClause);
        
        // Add ID to values for WHERE clause
        $types .= "i";
        $values[] = $id;

        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?");
        $stmt->bind_param($types, ...$values);
        
        return $stmt->execute();
    }

    // Delete a record
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    // Custom query helper
    public function query($sql, $params = [], $types = "")
    {
        if (empty($params)) {
            return $this->db->query($sql);
        }
        
        $stmt = $this->db->prepare($sql);
        if ($types === "") {
             $types = str_repeat("s", count($params));
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result();
    }
}
