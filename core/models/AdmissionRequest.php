<?php
/**
 * Admission Request Model
 * Handles online admission applications
 */

class AdmissionRequest extends BaseModel {
    protected $table = 'admission_requests';
    protected $primaryKey = 'request_id';
    
    /**
     * Get all admission requests
     */
    public function getAllRequests($status = null) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if ($status) {
            $sql .= " WHERE status = :status";
            $params['status'] = $status;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get pending requests
     */
    public function getPendingRequests() {
        return $this->getAllRequests('Pending');
    }
    
    /**
     * Approve request
     */
    public function approve($requestId, $remarks = null) {
        return $this->update($requestId, [
            'status' => 'Approved',
            'remarks' => $remarks
        ]);
    }
    
    /**
     * Reject request
     */
    public function reject($requestId, $remarks = null) {
        return $this->update($requestId, [
            'status' => 'Rejected',
            'remarks' => $remarks
        ]);
    }
    
    /**
     * Get pending count
     */
    public function getPendingCount() {
        return $this->count('status = :status', ['status' => 'Pending']);
    }
    
    /**
     * Get statistics for dashboard
     */
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
                FROM {$this->table}";
        
        $result = $this->query($sql);
        return $result[0] ?? ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
    }
    
    /**
     * Search admission requests
     */
    public function search($query, $status = null, $classFilter = null) {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($query)) {
            $sql .= " AND (student_name LIKE :query1 
                      OR guardian_email LIKE :query2 
                      OR guardian_phone LIKE :query3)";
            $params['query1'] = "%{$query}%";
            $params['query2'] = "%{$query}%";
            $params['query3'] = "%{$query}%";
        }
        
        if ($status) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }
        
        if ($classFilter) {
            $sql .= " AND class_applying_for = :class_filter";
            $params['class_filter'] = $classFilter;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get requests with filters
     */
    public function getFiltered($status = null, $classFilter = null, $dateFrom = null, $dateTo = null) {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($status) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }
        
        if ($classFilter) {
            $sql .= " AND class_applying_for = :class_filter";
            $params['class_filter'] = $classFilter;
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
    
    /**
     * Get unique classes from applications
     */
    public function getUniqueClasses() {
        $sql = "SELECT DISTINCT class_applying_for FROM {$this->table} 
                WHERE class_applying_for IS NOT NULL 
                ORDER BY class_applying_for";
        return $this->query($sql);
    }
}
