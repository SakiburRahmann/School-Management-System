<?php

require_once __DIR__ . '/../../core/Model.php';

class Fee extends Model
{
    public function forStudent(int $studentId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM fees WHERE student_id = ? ORDER BY due_date DESC");
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}


