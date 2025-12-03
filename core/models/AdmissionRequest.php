<?php

require_once __DIR__ . '/../../core/Model.php';

class AdmissionRequest extends Model
{
    public function create(array $data): bool
    {
        $sql = "INSERT INTO admission_requests 
                (student_name, class_applied, guardian_name, guardian_phone, message) 
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            "sssss",
            $data['student_name'],
            $data['class_applied'],
            $data['guardian_name'],
            $data['guardian_phone'],
            $data['message']
        );

        return $stmt->execute();
    }
}


