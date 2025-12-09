<?php
/**
 * Attendance Model
 * Handles student attendance tracking
 */

class Attendance extends BaseModel {
    protected $table = 'attendance';
    protected $primaryKey = 'attendance_id';
    
    /**
     * Mark attendance for a student
     */
    public function markAttendance($studentId, $date, $status, $remarks = null) {
        $data = [
            'student_id' => $studentId,
            'date' => $date,
            'status' => $status,
            'remarks' => $remarks
        ];
        
        // Check if attendance already exists
        $existing = $this->getAttendance($studentId, $date);
        
        if ($existing) {
            return $this->update($existing['attendance_id'], $data);
        } else {
            return $this->create($data);
        }
    }
    
    /**
     * Get attendance for a student on a specific date
     */
    public function getAttendance($studentId, $date) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE student_id = :student_id AND date = :date";
        
        return $this->queryOne($sql, [
            'student_id' => $studentId,
            'date' => $date
        ]);
    }
    
    /**
     * Get attendance by date and class
     */
    public function getByDate($date, $classId = null, $sectionId = null) {
        $sql = "SELECT a.*, s.name as student_name, s.roll_number, 
                c.class_name, sec.section_name
                FROM {$this->table} a
                JOIN students s ON a.student_id = s.student_id
                LEFT JOIN classes c ON s.class_id = c.class_id
                LEFT JOIN sections sec ON s.section_id = sec.section_id
                WHERE a.date = :date";
        
        $params = ['date' => $date];
        
        if ($classId) {
            $sql .= " AND s.class_id = :class_id";
            $params['class_id'] = $classId;
        }
        
        if ($sectionId) {
            $sql .= " AND s.section_id = :section_id";
            $params['section_id'] = $sectionId;
        }
        
        $sql .= " ORDER BY s.roll_number";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get student attendance history
     */
    public function getStudentAttendance($studentId, $startDate = null, $endDate = null) {
        $sql = "SELECT * FROM {$this->table} WHERE student_id = :student_id";
        $params = ['student_id' => $studentId];
        
        if ($startDate) {
            $sql .= " AND date >= :start_date";
            $params['start_date'] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND date <= :end_date";
            $params['end_date'] = $endDate;
        }
        
        $sql .= " ORDER BY date DESC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get attendance report for class
     */
    public function getAttendanceReport($classId, $sectionId, $month, $year) {
        $sql = "SELECT s.student_id, s.name, s.roll_number,
                SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN a.status = 'Late' THEN 1 ELSE 0 END) as late_count,
                COUNT(a.attendance_id) as total_days
                FROM students s
                LEFT JOIN {$this->table} a ON s.student_id = a.student_id 
                    AND MONTH(a.date) = :month AND YEAR(a.date) = :year
                WHERE s.class_id = :class_id AND s.section_id = :section_id
                GROUP BY s.student_id
                ORDER BY s.roll_number";
        
        return $this->query($sql, [
            'class_id' => $classId,
            'section_id' => $sectionId,
            'month' => $month,
            'year' => $year
        ]);
    }
    
    /**
     * Get today's attendance overview
     */
    public function getTodayOverview() {
        $today = date('Y-m-d');
        
        $sql = "SELECT 
                COUNT(CASE WHEN status = 'Present' THEN 1 END) as present,
                COUNT(CASE WHEN status = 'Absent' THEN 1 END) as absent,
                COUNT(CASE WHEN status = 'Late' THEN 1 END) as late,
                COUNT(*) as total
                FROM {$this->table}
                WHERE date = :date";
        
        return $this->queryOne($sql, ['date' => $today]);
    }
    
    /**
     * Get attendance percentage for student
     */
    public function getAttendancePercentage($studentId, $startDate = null, $endDate = null) {
        $sql = "SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_days
                FROM {$this->table}
                WHERE student_id = :student_id";
        
        $params = ['student_id' => $studentId];
        
        if ($startDate) {
            $sql .= " AND date >= :start_date";
            $params['start_date'] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND date <= :end_date";
            $params['end_date'] = $endDate;
        }
        
        $result = $this->queryOne($sql, $params);
        
        if ($result && $result['total_days'] > 0) {
            return ($result['present_days'] / $result['total_days']) * 100;
        }
        
        return 0;
    }
    /**
     * Bulk mark attendance
     */
    public function bulkMark($attendanceData) {
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($attendanceData as $record) {
            $studentId = $record['student_id'];
            $date = $record['date'];
            $status = $record['status'];
            $remarks = $record['remarks'] ?? null;
            $sectionId = $record['section_id'];
            $createdBy = $record['created_by'] ?? null;
            
            // Check if exists
            $existing = $this->getAttendance($studentId, $date);
            
            if ($existing) {
                // Update if status changed
                if ($existing['status'] !== $status || $existing['remarks'] !== $remarks) {
                    $updateData = [
                        'status' => $status,
                        'remarks' => $remarks
                    ];
                    
                    if ($this->update($existing['attendance_id'], $updateData)) {
                        // Log change
                        // $this->logChange($existing['attendance_id'], 'Update', 
                        //     "Status: {$existing['status']}", "Status: {$status}", $createdBy);
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                }
            } else {
                // Create new
                $data = [
                    'student_id' => $studentId,
                    'date' => $date,
                    'status' => $status,
                    'remarks' => $remarks
                ];
                
                $id = $this->create($data);
                if ($id) {
                    // $this->logChange($id, 'Create', null, "Status: {$status}", $createdBy);
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }
        }
        
        return ['success' => $successCount, 'errors' => $errorCount];
    }
    
    /**
     * Log attendance change
     */
    private function logChange($attendanceId, $action, $from, $to, $userId) {
        // Logging disabled temporarily to prevent errors if table missing
        /*
        $sql = "INSERT INTO attendance_logs (attendance_id, action, changed_from, changed_to, changed_by)
                VALUES (:attendance_id, :action, :changed_from, :changed_to, :changed_by)";
        
        $this->query($sql, [
            'attendance_id' => $attendanceId,
            'action' => $action,
            'changed_from' => $from,
            'changed_to' => $to,
            'changed_by' => $userId
        ]);
        */
    }

    /**
     * Get student attendance stats
     */
    public function getStudentStats($studentId) {
        $sql = "SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = 'Excused' THEN 1 ELSE 0 END) as excused
                FROM {$this->table}
                WHERE student_id = :student_id";
        
        $stats = $this->queryOne($sql, ['student_id' => $studentId]);
        
        if ($stats['total_days'] > 0) {
            $stats['percentage'] = round(($stats['present'] / $stats['total_days']) * 100, 1);
        } else {
            $stats['percentage'] = 0;
        }
        
        return $stats;
    }

    /**
     * Get detailed monthly attendance for class
     */
    public function getMonthlyAttendance($classId, $sectionId, $month, $year) {
        $sql = "SELECT a.*, s.student_id 
                FROM {$this->table} a
                JOIN students s ON a.student_id = s.student_id
                WHERE s.class_id = :class_id 
                AND s.section_id = :section_id
                AND MONTH(a.date) = :month 
                AND YEAR(a.date) = :year";
        
        return $this->query($sql, [
            'class_id' => $classId,
            'section_id' => $sectionId,
            'month' => $month,
            'year' => $year
        ]);
    }
}
