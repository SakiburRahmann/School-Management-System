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
    public function getUnreadCount() {
        return $this->count('is_read = 0');
    }
}
