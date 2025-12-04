<?php
/**
 * Gallery Model
 * Handles gallery image management
 */

class Gallery extends BaseModel {
    protected $table = 'gallery';
    protected $primaryKey = 'gallery_id';
    
    /**
     * Get all gallery images
     */
    public function getGalleryImages($category = null, $limit = null) {
        $sql = "SELECT g.*, u.username as uploaded_by_name
                FROM {$this->table} g
                LEFT JOIN users u ON g.uploaded_by = u.user_id";
        
        $params = [];
        
        if ($category) {
            $sql .= " WHERE g.category = :category";
            $params['category'] = $category;
        }
        
        $sql .= " ORDER BY g.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit";
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        if ($limit) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get all categories
     */
    public function getCategories() {
        $sql = "SELECT DISTINCT category FROM {$this->table} 
                WHERE category IS NOT NULL 
                ORDER BY category";
        
        return $this->query($sql);
    }
    
    /**
     * Upload image
     */
    public function uploadImage($title, $description, $imagePath, $category = null, $uploadedBy = null) {
        return $this->create([
            'title' => $title,
            'description' => $description,
            'image_path' => $imagePath,
            'category' => $category,
            'uploaded_by' => $uploadedBy
        ]);
    }
    /**
     * Get all gallery images
     */
    public function getAll() {
        return $this->getGalleryImages();
    }
}
