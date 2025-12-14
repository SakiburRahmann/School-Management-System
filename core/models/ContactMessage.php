<?php
/**
 * Contact Message Model
 * Handles contact form submissions
 */

class ContactMessage extends BaseModel {
    protected $table = 'contact_messages';
    protected $primaryKey = 'message_id';
    
    /**
     * Get all messages
     */
    public function getAllMessages($isRead = null) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if ($isRead !== null) {
            $sql .= " WHERE is_read = :is_read";
            $params['is_read'] = $isRead;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Mark as read
     */
    public function markAsRead($messageId) {
        return $this->update($messageId, ['is_read' => 1]);
    }
    
    /**
     * Mark as unread
     */
    public function markAsUnread($messageId) {
        return $this->update($messageId, ['is_read' => 0]);
    }
    
    /**
     * Get unread count
     */
    /**
     * Get unread count
     */
    public function getUnreadCount() {
        return $this->count('is_read = 0');
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
                    SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read_count,
                    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today
                FROM {$this->table}";
        
        $result = $this->query($sql);
        return $result[0] ?? ['total' => 0, 'unread' => 0, 'read_count' => 0, 'today' => 0];
    }
    
    /**
     * Search messages
     */
    public function search($query, $status = null) {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($query)) {
            $sql .= " AND (name LIKE :query1 
                      OR email LIKE :query2 
                      OR subject LIKE :query3)";
            $params['query1'] = "%{$query}%";
            $params['query2'] = "%{$query}%";
            $params['query3'] = "%{$query}%";
        }
        
        if ($status !== null && $status !== '') {
            $sql .= " AND is_read = :status";
            $params['status'] = $status;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get messages with filters
     */
    public function getFiltered($status = null, $dateFrom = null, $dateTo = null) {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($status !== null && $status !== '') {
            $sql .= " AND is_read = :status";
            $params['status'] = $status;
        }
        
        if ($dateFrom) {
            $sql .= " AND DATE(created_at) >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND DATE(created_at) <= :date_to";
            $params['date_to'] = $dateTo;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->query($sql, $params);
    }
}
