<?php
/**
 * Exam Model
 * Handles exam data and operations
 */

class Exam extends BaseModel {
    protected $table = 'exams';
    protected $primaryKey = 'exam_id';
    
    /**
     * Get all exams with details
     */
    public function getExamsWithDetails() {
        $sql = "SELECT e.*, c.class_name, s.subject_name,
                COUNT(DISTINCT r.student_id) as students_appeared
                FROM {$this->table} e
                LEFT JOIN classes c ON e.class_id = c.class_id
                LEFT JOIN subjects s ON e.subject_id = s.subject_id
                LEFT JOIN results r ON e.exam_id = r.exam_id
                GROUP BY e.exam_id
                ORDER BY e.exam_date DESC";
        
        return $this->query($sql);
    }
    
    /**
     * Get upcoming exams
     */
    public function getUpcomingExams($classId = null) {
        $sql = "SELECT e.*, c.class_name
                FROM {$this->table} e
                LEFT JOIN classes c ON e.class_id = c.class_id
                WHERE e.exam_date >= CURDATE()";
        
        $params = [];
        
        if ($classId) {
            $sql .= " AND e.class_id = :class_id";
            $params['class_id'] = $classId;
        }
        
        $sql .= " ORDER BY e.exam_date ASC";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get exam with subjects
     */
    public function getExamWithSubjects($examId) {
        $exam = $this->find($examId);
        
        if ($exam) {
            $sql = "SELECT DISTINCT sub.*
                    FROM subjects sub
                    WHERE sub.class_id = :class_id
                    ORDER BY sub.subject_name";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['class_id' => $exam['class_id']]);
            $exam['subjects'] = $stmt->fetchAll();
        }
        
        return $exam;
    }
    
    /**
     * Get exam details
     */
    public function getExamDetails($examId) {
        $sql = "SELECT e.*, c.class_name
                FROM {$this->table} e
                LEFT JOIN classes c ON e.class_id = c.class_id
                WHERE e.exam_id = :id";
        
        return $this->queryOne($sql, ['id' => $examId]);
    }
    
    /**
     * Get exams by class
     */
    public function getByClass($classId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE class_id = :class_id 
                ORDER BY exam_date DESC";
        
        return $this->query($sql, ['class_id' => $classId]);
    }

    /**
     * Assign teachers to exam
     */
    public function assignTeachers($examId, $teacherIds) {
        // First delete existing assignments
        $sql = "DELETE FROM exam_teachers WHERE exam_id = :exam_id";
        $this->query($sql, ['exam_id' => $examId]);
        
        if (empty($teacherIds)) {
            return true;
        }
        
        // Insert new assignments
        $sql = "INSERT INTO exam_teachers (exam_id, teacher_id) VALUES (:exam_id, :teacher_id)";
        $stmt = $this->db->prepare($sql);
        
        foreach ($teacherIds as $teacherId) {
            $stmt->execute(['exam_id' => $examId, 'teacher_id' => $teacherId]);
        }
        
        return true;
    }
    
    /**
     * Get assigned teachers for an exam
     */
    public function getAssignedTeachers($examId) {
        $sql = "SELECT t.* FROM teachers t
                JOIN exam_teachers et ON t.teacher_id = et.teacher_id
                WHERE et.exam_id = :exam_id";
        return $this->query($sql, ['exam_id' => $examId]);
    }
    
    /**
     * Get exams assigned to a teacher
     */
    public function getExamsForTeacher($teacherId) {
        $sql = "SELECT e.*, c.class_name, s.subject_name
                FROM exams e
                JOIN classes c ON e.class_id = c.class_id
                JOIN subjects s ON e.subject_id = s.subject_id
                JOIN exam_teachers et ON e.exam_id = et.exam_id
                WHERE et.teacher_id = :teacher_id
                ORDER BY e.exam_date DESC";
        return $this->query($sql, ['teacher_id' => $teacherId]);
    }
}
