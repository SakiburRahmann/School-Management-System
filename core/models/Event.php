<?php
/**
 * Event Model
 * Handles event management
 */

class Event extends BaseModel {
    protected $table = 'events';
    protected $primaryKey = 'event_id';
    
    /**
     * Get upcoming events
     */
    public function getUpcoming($limit = null, $isPublic = null) {
        $sql = "SELECT * FROM {$this->table} WHERE event_date >= CURDATE()";
        $params = [];
        
        if ($isPublic !== null) {
            $sql .= " AND is_public = :is_public";
            $params['is_public'] = $isPublic;
        }
        
        $sql .= " ORDER BY event_date ASC";
        
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
     * Get past events
     */
    public function getPast($limit = null, $isPublic = null) {
        $sql = "SELECT * FROM {$this->table} WHERE event_date < CURDATE()";
        $params = [];
        
        if ($isPublic !== null) {
            $sql .= " AND is_public = :is_public";
            $params['is_public'] = $isPublic;
        }
        
        $sql .= " ORDER BY event_date DESC";
        
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
     * Get public events for website
     */
    public function getPublicEvents($limit = null) {
        $sql = "SELECT * FROM {$this->table} WHERE is_public = 1 
                ORDER BY event_date DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit";
        }
        
        $stmt = $this->db->prepare($sql);
        
        if ($limit) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Search events
     */
    public function search($keyword) {
        $sql = "SELECT * FROM {$this->table}
                WHERE title LIKE :keyword OR description LIKE :keyword
                ORDER BY event_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['keyword' => "%{$keyword}%"]);
        return $stmt->fetchAll();
    }
}
