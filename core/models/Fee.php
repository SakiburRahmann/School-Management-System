<?php
/**
 * Fee Model
 * Handles fee management and payments
 */

class Fee extends BaseModel {
    protected $table = 'fees';
    protected $primaryKey = 'fee_id';
    
    /**
     * Create fee invoice
     */
    public function createInvoice($studentId, $amount, $dueDate, $remarks = null) {
        return $this->create([
            'student_id' => $studentId,
            'amount' => $amount,
            'due_date' => $dueDate,
            'status' => 'Unpaid',
            'remarks' => $remarks
        ]);
    }
    
    /**
     * Mark fee as paid
     */
    public function markAsPaid($feeId, $paymentMethod = null, $transactionId = null) {
        return $this->update($feeId, [
            'status' => 'Paid',
            'payment_date' => date('Y-m-d'),
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId
        ]);
    }
    
    /**
     * Get student fees
     */
    public function getStudentFees($studentId, $status = null) {
        $sql = "SELECT * FROM {$this->table} WHERE student_id = :student_id";
        $params = ['student_id' => $studentId];
        
        if ($status) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }
        
        $sql .= " ORDER BY due_date DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get unpaid fees
     */
    public function getUnpaidFees($studentId = null) {
        $sql = "SELECT f.*, s.name as student_name, s.roll_number, 
                c.class_name, sec.section_name
                FROM {$this->table} f
                JOIN students s ON f.student_id = s.student_id
                LEFT JOIN classes c ON s.class_id = c.class_id
                LEFT JOIN sections sec ON s.section_id = sec.section_id
                WHERE f.status = 'Unpaid'";
        
        $params = [];
        
        if ($studentId) {
            $sql .= " AND f.student_id = :student_id";
            $params['student_id'] = $studentId;
        }
        
        $sql .= " ORDER BY f.due_date ASC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get overdue fees
     */
    public function getOverdueFees($studentId = null) {
        $sql = "SELECT f.*, s.name as student_name, s.roll_number,
                c.class_name, sec.section_name
                FROM {$this->table} f
                JOIN students s ON f.student_id = s.student_id
                LEFT JOIN classes c ON s.class_id = c.class_id
                LEFT JOIN sections sec ON s.section_id = sec.section_id
                WHERE f.status = 'Unpaid' AND f.due_date < CURDATE()";
        
        $params = [];
        
        if ($studentId) {
            $sql .= " AND f.student_id = :student_id";
            $params['student_id'] = $studentId;
        }
        
        $sql .= " ORDER BY f.due_date ASC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get payment history
     */
    public function getPaymentHistory($studentId = null, $startDate = null, $endDate = null) {
        $sql = "SELECT f.*, s.name as student_name, s.roll_number,
                c.class_name, sec.section_name
                FROM {$this->table} f
                JOIN students s ON f.student_id = s.student_id
                LEFT JOIN classes c ON s.class_id = c.class_id
                LEFT JOIN sections sec ON s.section_id = sec.section_id
                WHERE f.status = 'Paid'";
        
        $params = [];
        
        if ($studentId) {
            $sql .= " AND f.student_id = :student_id";
            $params['student_id'] = $studentId;
        }
        
        if ($startDate) {
            $sql .= " AND f.payment_date >= :start_date";
            $params['start_date'] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND f.payment_date <= :end_date";
            $params['end_date'] = $endDate;
        }
        
        $sql .= " ORDER BY f.payment_date DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get fee statistics
     */
    public function getFeeStatistics($classId = null) {
        $sql = "SELECT 
                COUNT(*) as total_invoices,
                SUM(CASE WHEN status = 'Paid' THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN status = 'Unpaid' THEN 1 ELSE 0 END) as unpaid_count,
                SUM(amount) as total_amount,
                SUM(CASE WHEN status = 'Paid' THEN amount ELSE 0 END) as paid_amount,
                SUM(CASE WHEN status = 'Unpaid' THEN amount ELSE 0 END) as unpaid_amount
                FROM {$this->table} f";
        
        $params = [];
        
        if ($classId) {
            $sql .= " JOIN students s ON f.student_id = s.student_id
                     WHERE s.class_id = :class_id";
            $params['class_id'] = $classId;
        }
        
        return $this->queryOne($sql, $params);
    }
    
    /**
     * Get fee details
     */
    public function getFeeDetails($feeId) {
        $sql = "SELECT f.*, s.name as student_name, s.roll_number,
                c.class_name, sec.section_name, s.guardian_name, s.guardian_phone
                FROM {$this->table} f
                JOIN students s ON f.student_id = s.student_id
                LEFT JOIN classes c ON s.class_id = c.class_id
                LEFT JOIN sections sec ON s.section_id = sec.section_id
                WHERE f.fee_id = :id";
        
        return $this->queryOne($sql, ['id' => $feeId]);
    }
}
