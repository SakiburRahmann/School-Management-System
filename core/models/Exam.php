<?php

require_once __DIR__ . '/../../core/Model.php';

class Exam extends Model
{
    public function upcoming(int $limit = 10): array
    {
        $stmt = $this->db->prepare("SELECT * FROM exams WHERE exam_date >= CURDATE() ORDER BY exam_date ASC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}


