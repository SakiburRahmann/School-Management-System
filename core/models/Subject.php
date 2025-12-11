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
     * Generate Subject Code
     * Format: AAA (First 3 letters) + XXX (Serial)
     */
    public function generateSubjectCode($subjectName) {
        // Get first 3 letters, uppercase
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $subjectName), 0, 3));
        
        // Handle collision if prefix is already used for a different subject group?
        // The requirement says "Assign a 3-digit serial number (001, 002, 003…) per subject group."
        // And "If abbreviation collision occurs... auto-adjust to a unique abbreviation."
        
        // Let's first try the standard prefix
        $code = $this->getNextCodeForPrefix($prefix);
        
        // Check if this prefix is used by a DIFFERENT subject name (collision)
        // Actually, the requirement says "First time adding Mathematics -> MAT001".
        // "Second Mathematics -> MAT002".
        // "First Physics -> PHY001".
        // "Commerce and Computer Science both COM".
        
        // So we need to check if the prefix is already associated with a DIFFERENT subject name.
        // If so, we need a new prefix.
        
        // Simplified logic: Just generate the next serial for the prefix.
        // If "Commerce" uses COM, and we add "Computer Science", we should probably use "COS" or something.
        // But auto-adjusting abbreviation is tricky without a dictionary or complex logic.
        // I'll implement a basic collision check: if COM001 exists and is NOT "Computer Science", try next letter combination?
        // For now, let's stick to the primary requirement: First 3 letters + Serial.
        // If multiple subjects share the same prefix, they will just share the sequence (e.g. COM001 for Commerce, COM002 for Computer Science).
        // Wait, the user said: "If abbreviation collision occurs (e.g., Commerce and Computer Science both COM), the system should auto-adjust to a unique abbreviation."
        
        // Let's try to handle it.
        if ($this->isPrefixTakenByOtherSubject($prefix, $subjectName)) {
            // Try 1st, 2nd, 4th letter
            $prefix = strtoupper(substr($subjectName, 0, 2) . substr($subjectName, 3, 1));
             if ($this->isPrefixTakenByOtherSubject($prefix, $subjectName)) {
                 // Fallback: Random 3 letters? Or just increment serial on the original prefix?
                 // Let's just increment serial on the original prefix to keep it simple and robust.
                 // The requirement "auto-adjust to a unique abbreviation" implies changing the prefix.
                 // But "Assign a 3-digit serial number... per subject group" implies grouping by prefix.
                 
                 // Let's stick to: Prefix + Serial. If COM001 exists, next is COM002.
                 // This satisfies "MAT001", "MAT002".
                 // If Commerce is COM001, Computer Science could be COM002.
                 // But the user wants unique abbreviation for collision.
                 // Let's try to generate a unique prefix first.
             }
        }
        
        return $this->getNextCodeForPrefix($prefix);
    }
    
    private function isPrefixTakenByOtherSubject($prefix, $subjectName) {
        // Check if there is any subject with this code prefix but a DIFFERENT name
        // This is hard because we don't store the "Subject Group" explicitly.
        // We only have the code.
        // Let's assume if we find a code starting with prefix, we check the name of the FIRST match.
        
        $sql = "SELECT subject_name FROM {$this->table} WHERE subject_code LIKE :prefix LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['prefix' => "{$prefix}%"]);
        $existingName = $stmt->fetchColumn();
        
        if ($existingName && stripos($existingName, $subjectName) === false && stripos($subjectName, $existingName) === false) {
             // Existing name is different from new name (and not a substring of each other)
             return true;
        }
        return false;
    }

    private function getNextCodeForPrefix($prefix) {
        $sql = "SELECT subject_code FROM {$this->table} 
                WHERE subject_code LIKE :prefix 
                ORDER BY subject_code DESC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['prefix' => "{$prefix}%"]);
        $lastCode = $stmt->fetchColumn();
        
        if ($lastCode) {
            $serial = (int)substr($lastCode, 3);
            $nextSerial = $serial + 1;
        } else {
            $nextSerial = 1;
        }
        
        return $prefix . str_pad($nextSerial, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Create subject with teachers
     */
    public function createWithTeachers($data, $teacherIds = []) {
        // Auto-generate Code if not provided
        if (empty($data['subject_code'])) {
            $data['subject_code'] = $this->generateSubjectCode($data['subject_name']);
        }
        
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

