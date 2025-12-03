<?php

require_once __DIR__ . '/../../core/Model.php';

class Result extends Model
{
    public function forStudent(int $studentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT results.*, subjects.subject_name 
             FROM results 
             JOIN subjects ON results.subject_id = subjects.subject_id
             WHERE results.student_id = ?"
        );
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}


