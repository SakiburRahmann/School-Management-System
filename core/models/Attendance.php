<?php

require_once __DIR__ . '/../../core/Model.php';

class Attendance extends Model
{
    protected $table = 'attendance';
    protected $primaryKey = 'attendance_id';

    public function countToday(): int
    {
        $today = date('Y-m-d');
        $result = $this->query("SELECT COUNT(*) AS c FROM {$this->table} WHERE date = ?", [$today]);
        $row = $result ? $result->fetch_assoc() : ['c' => 0];
        return (int) $row['c'];
    }
}


