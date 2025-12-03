<?php

require_once __DIR__ . '/../../core/Model.php';

class ClassModel extends Model
{
    protected $table = 'classes';
    protected $primaryKey = 'class_id';

    public function countAll(): int
    {
        $result = $this->query("SELECT COUNT(*) AS c FROM {$this->table}");
        $row = $result ? $result->fetch_assoc() : ['c' => 0];
        return (int) $row['c'];
    }
}


