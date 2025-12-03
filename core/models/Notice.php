<?php
/**
 * Notice Model
 * Handles notice/announcement management
 */

class Notice extends BaseModel {
    protected $table = 'notices';
    protected $primaryKey = 'notice_id';
    
    /**
     * Get latest notices
     */
    public function getLatest($limit = 10, $isPublic = null) {
        $sql = "SELECT n.*, u.username as created_by_name
                FROM {$this->table} n
                LEFT JOIN users u ON n.created_by = u.user_id";
        
        $params = [];
        
        if ($isPublic !== null) {
            $sql .= " WHERE n.is_public = :is_public";
            $params['is_public'] = $isPublic;
        }
        
        $sql .= " ORDER BY n.created_at DESC LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get public notices for website
     */
    public function getPublicNotices($limit = null) {
        return $this->getLatest($limit ?? 999, 1);
    }
    
    /**
     * Get all notices with details
     */
    public function getNoticesWithDetails() {
        $sql = "SELECT n.*, u.username as created_by_name
                FROM {$this->table} n
                LEFT JOIN users u ON n.created_by = u.user_id
                ORDER BY n.created_at DESC";
        
        return $this->query($sql);
    }
    
    /**
     * Get notice details
     */
    public function getNoticeDetails($noticeId) {
        $sql = "SELECT n.*, u.username as created_by_name
                FROM {$this->table} n
                LEFT JOIN users u ON n.created_by = u.user_id
                WHERE n.notice_id = :id";
        
        return $this->queryOne($sql, ['id' => $noticeId]);
    }
    
    /**
     * Search notices
     */
    public function search($keyword) {
        $sql = "SELECT n.*, u.username as created_by_name
                FROM {$this->table} n
                LEFT JOIN users u ON n.created_by = u.user_id
                WHERE n.title LIKE :keyword OR n.content LIKE :keyword
                ORDER BY n.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['keyword' => "%{$keyword}%"]);
        return $stmt->fetchAll();
    }
}
