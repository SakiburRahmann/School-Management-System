<?php

require_once __DIR__ . '/../../core/Model.php';

class Teacher extends Model
{
    protected $table = 'teachers';
    protected $primaryKey = 'teacher_id';

    public function countAll(): int
    {
        $result = $this->query("SELECT COUNT(*) AS c FROM {$this->table}");
        $row = $result ? $result->fetch_assoc() : ['c' => 0];
        return (int) $row['c'];
    }
}


