<?php

require_once __DIR__ . '/../../core/Model.php';

class Section extends Model
{
    public function countAll(): int
    {
        $result = $this->db->query("SELECT COUNT(*) AS c FROM sections");
        $row = $result ? $result->fetch_assoc() : ['c' => 0];
        return (int) $row['c'];
    }
}


