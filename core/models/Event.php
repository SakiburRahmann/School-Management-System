<?php

require_once __DIR__ . '/../../core/Model.php';

class Event extends Model
{
    public function upcoming(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT ?"
        );
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}


