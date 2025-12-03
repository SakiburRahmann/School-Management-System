<?php

require_once __DIR__ . '/../../core/Model.php';

class ContactMessage extends Model
{
    public function create(array $data): bool
    {
        $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssss", $data['name'], $data['email'], $data['subject'], $data['message']);
        return $stmt->execute();
    }
}


