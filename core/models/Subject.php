<?php
/**
 * Subject Model
 * Handles subject data and teacher assignments
 */

class Subject extends BaseModel {
    protected $table = 'subjects';
    protected $primaryKey = 'subject_id';
    
    /**
     * Get all subjects with teacher and class info
     */
    public function getSubjectsWithDetails() {
        $sql = "SELECT s.*, t.name as teacher_name, c.class_name
                FROM {$this->table} s
                LEFT JOIN teachers t ON s.teacher_id = t.teacher_id
                LEFT JOIN classes c ON s.class_id = c.class_id
                ORDER BY c.class_name, s.subject_name";
        
        return $this->query($sql);
    }
    
    /**
     * Get subjects by class
     */
    public function getByClass($classId) {
        $sql = "SELECT s.*, t.name as teacher_name
                FROM {$this->table} s
                LEFT JOIN teachers t ON s.teacher_id = t.teacher_id
                WHERE s.class_id = :class_id
                ORDER BY s.subject_name";
        
        return $this->query($sql, ['class_id' => $classId]);
    }
    
    /**
     * Get subjects by teacher
     */
    public function getByTeacher($teacherId) {
        $sql = "SELECT s.*, c.class_name
                FROM {$this->table} s
                LEFT JOIN classes c ON s.class_id = c.class_id
                WHERE s.teacher_id = :teacher_id
                ORDER BY c.class_name, s.subject_name";
        
        return $this->query($sql, ['teacher_id' => $teacherId]);
    }
    
    /**
     * Assign teacher to subject
     */
    public function assignTeacher($subjectId, $teacherId) {
        return $this->update($subjectId, ['teacher_id' => $teacherId]);
    }
    
    /**
     * Check if subject code exists
     */
    public function subjectCodeExists($subjectCode, $excludeId = null) {
        return $this->exists('subject_code', $subjectCode, $excludeId);
    }
    
    /**
     * Get subject details
     */
    public function getSubjectDetails($subjectId) {
        $sql = "SELECT s.*, t.name as teacher_name, c.class_name
                FROM {$this->table} s
                LEFT JOIN teachers t ON s.teacher_id = t.teacher_id
                LEFT JOIN classes c ON s.class_id = c.class_id
                WHERE s.subject_id = :id";
        
        return $this->queryOne($sql, ['id' => $subjectId]);
    }
}
