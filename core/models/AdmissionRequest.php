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
}
