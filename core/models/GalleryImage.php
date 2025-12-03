<?php

require_once __DIR__ . '/../../core/Model.php';

class GalleryImage extends Model
{
    public function all(): array
    {
        $result = $this->db->query("SELECT * FROM gallery_images ORDER BY created_at DESC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}


