<?php
/**
 * Subject Model
 * Handles subject data and multi-teacher assignments
 */

class Subject extends BaseModel {
    protected $table = 'subjects';
    protected $primaryKey = 'subject_id';
    
    /**
     * Get all subjects with class info and teacher count
     */
    public function getSubjectsWithDetails() {
        $sql = "SELECT s.*, c.class_name,
                    (SELECT COUNT(*) FROM subject_teachers st WHERE st.subject_id = s.subject_id) as teacher_count,
                    (SELECT GROUP_CONCAT(t.name SEPARATOR ', ') 
                     FROM subject_teachers st 
                     JOIN teachers t ON st.teacher_id = t.teacher_id 
                     WHERE st.subject_id = s.subject_id) as teacher_names
                FROM {$this->table} s
                LEFT JOIN classes c ON s.class_id = c.class_id
                ORDER BY c.class_name, s.subject_name";
        
        return $this->query($sql);
    }
    
    /**
     * Get subjects by class
     */
    public function getByClass($classId) {
        $sql = "SELECT s.*,
                    (SELECT GROUP_CONCAT(t.name SEPARATOR ', ') 
                     FROM subject_teachers st 
                     JOIN teachers t ON st.teacher_id = t.teacher_id 
                     WHERE st.subject_id = s.subject_id) as teacher_names
                FROM {$this->table} s
                WHERE s.class_id = :class_id AND s.status = 'Active'
                ORDER BY s.subject_name";
        
        return $this->query($sql, ['class_id' => $classId]);
    }
    
    /**
     * Get subjects by teacher (from junction table)
     */
    public function getByTeacher($teacherId) {
        $sql = "SELECT s.*, c.class_name
                FROM {$this->table} s
                LEFT JOIN classes c ON s.class_id = c.class_id
                JOIN subject_teachers st ON s.subject_id = st.subject_id
                WHERE st.teacher_id = :teacher_id AND s.status = 'Active'
                ORDER BY c.class_name, s.subject_name";
        
        return $this->query($sql, ['teacher_id' => $teacherId]);
    }
    
    /**
     * Get full subject details with all related data
     */
    public function getFullDetails($subjectId) {
        $sql = "SELECT s.*, c.class_name
                FROM {$this->table} s
                LEFT JOIN classes c ON s.class_id = c.class_id
                WHERE s.subject_id = :id";
        
        $subject = $this->queryOne($sql, ['id' => $subjectId]);
        
        if ($subject) {
            // Get assigned teachers
            $subject['teachers'] = $this->getTeachers($subjectId);
        }
        
        return $subject;
    }
    
    /**
     * Get teachers assigned to a subject
     */
    public function getTeachers($subjectId) {
        $sql = "SELECT t.teacher_id, t.name, t.email, t.phone, st.assigned_at
                FROM subject_teachers st
                JOIN teachers t ON st.teacher_id = t.teacher_id
                WHERE st.subject_id = :subject_id
                ORDER BY t.name";
        
        return $this->query($sql, ['subject_id' => $subjectId]);
    }
    
    /**
     * Assign multiple teachers to a subject
     */
    public function assignTeachers($subjectId, $teacherIds) {
        // First remove all existing assignments
        $this->removeTeachers($subjectId);
        
        // Then add new assignments
        if (!empty($teacherIds)) {
            foreach ($teacherIds as $teacherId) {
                if (!empty($teacherId)) {
                    $sql = "INSERT INTO subject_teachers (subject_id, teacher_id) VALUES (:subject_id, :teacher_id)";
                    $this->query($sql, ['subject_id' => $subjectId, 'teacher_id' => $teacherId]);
                }
            }
        }
        
        return true;
    }
    
    /**
     * Remove all teacher assignments from a subject
     */
    public function removeTeachers($subjectId) {
        $sql = "DELETE FROM subject_teachers WHERE subject_id = :subject_id";
        return $this->query($sql, ['subject_id' => $subjectId]);
    }
    
    /**
     * Check if subject code exists
     */
    public function subjectCodeExists($subjectCode, $excludeId = null) {
        return $this->exists('subject_code', $subjectCode, $excludeId);
    }
    
    /**
     * Check if subject name exists
     */
    public function subjectNameExists($subjectName, $excludeId = null) {
        return $this->exists('subject_name', $subjectName, $excludeId);
    }
    
    /**
     * Create subject with teachers
     */
    public function createWithTeachers($data, $teacherIds = []) {
        // Extract teachers from data if present
        if (isset($data['teacher_ids'])) {
            $teacherIds = $data['teacher_ids'];
            unset($data['teacher_ids']);
        }
        
        // Create the subject
        $subjectId = $this->create($data);
        
        if ($subjectId && !empty($teacherIds)) {
            $this->assignTeachers($subjectId, $teacherIds);
        }
        
        return $subjectId;
    }
    
    /**
     * Update subject with teachers
     */
    public function updateWithTeachers($subjectId, $data, $teacherIds = []) {
        // Extract teachers from data if present
        if (isset($data['teacher_ids'])) {
            $teacherIds = $data['teacher_ids'];
            unset($data['teacher_ids']);
        }
        
        // Update the subject
        $result = $this->update($subjectId, $data);
        
        // Update teacher assignments
        $this->assignTeachers($subjectId, $teacherIds);
        
        return $result;
    }
    
    /**
     * Check if subject can be deleted (no dependencies)
     */
    public function canDelete($subjectId) {
        // Check for results
        $sql = "SELECT COUNT(*) as count FROM results WHERE subject_id = :id";
        $result = $this->queryOne($sql, ['id' => $subjectId]);
        if ($result && $result['count'] > 0) {
            return ['can_delete' => false, 'reason' => 'Subject has ' . $result['count'] . ' result records'];
        }
        
        return ['can_delete' => true, 'reason' => ''];
    }
    
    /**
     * Get subject statistics
     */
    public function getStatistics($subjectId) {
        $stats = [];
        
        // Count teachers
        $sql = "SELECT COUNT(*) as count FROM subject_teachers WHERE subject_id = :id";
        $result = $this->queryOne($sql, ['id' => $subjectId]);
        $stats['teacher_count'] = $result ? $result['count'] : 0;
        
        // Count results
        $sql = "SELECT COUNT(*) as count FROM results WHERE subject_id = :id";
        $result = $this->queryOne($sql, ['id' => $subjectId]);
        $stats['result_count'] = $result ? $result['count'] : 0;
        
        return $stats;
    }
    
    /**
     * Get active subjects count
     */
    public function getActiveCount() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'Active'";
        $result = $this->queryOne($sql);
        return $result ? $result['count'] : 0;
    }
}

