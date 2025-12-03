<?php

require_once __DIR__ . '/../../core/Model.php';

class Notice extends Model
{
    public function latest(int $limit = 5): array
    {
        $stmt = $this->db->prepare("SELECT * FROM notices ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}


