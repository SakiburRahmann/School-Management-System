<?php

require_once __DIR__ . '/../../core/Model.php';

class Subject extends Model
{
    protected $table = 'subjects';
    protected $primaryKey = 'subject_id';

    public function countAll(): int
    {
        $result = $this->query("SELECT COUNT(*) AS c FROM {$this->table}");
        $row = $result ? $result->fetch_assoc() : ['c' => 0];
        return (int) $row['c'];
    }
}


