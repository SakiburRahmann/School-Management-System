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
    
    /**
     * Get statistics for dashboard
     */
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(DISTINCT category) as categories,
                    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today,
                    SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as this_week
                FROM {$this->table}";
        
        $result = $this->query($sql);
        return $result[0] ?? ['total' => 0, 'categories' => 0, 'today' => 0, 'this_week' => 0];
    }
    
    /**
     * Search gallery images
     */
    public function search($query, $category = null) {
        $sql = "SELECT g.*, u.username as uploaded_by_name
                FROM {$this->table} g
                LEFT JOIN users u ON g.uploaded_by = u.user_id
                WHERE 1=1";
        $params = [];
        
        if (!empty($query)) {
            $sql .= " AND (g.title LIKE :query1 OR g.description LIKE :query2)";
            $params['query1'] = "%{$query}%";
            $params['query2'] = "%{$query}%";
        }
        
        if ($category) {
            $sql .= " AND g.category = :category";
            $params['category'] = $category;
        }
        
        $sql .= " ORDER BY g.created_at DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Delete image with file
     */
    public function deleteWithFile($id) {
        $image = $this->find($id);
        if ($image && !empty($image['image_path'])) {
            $filePath = __DIR__ . '/../../' . $image['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        return $this->delete($id);
    }
}
