<?php
/**
 * Student Model
 * Handles student data and operations
 */

class Student extends BaseModel {
    protected $table = 'students';
    protected $primaryKey = 'student_id';
    
    /**
     * Get students with class and section info
     */
    public function getStudentsWithDetails($limit = null, $offset = 0) {
        $sql = "SELECT s.*, c.class_name, sec.section_name 
                FROM {$this->table} s
                LEFT JOIN classes c ON s.class_id = c.class_id
                LEFT JOIN sections sec ON s.section_id = sec.section_id
                ORDER BY c.class_name, sec.section_name, s.roll_number";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->db->prepare($sql);
        
        if ($limit) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get students by class and section
     */
    public function getByClass($classId, $sectionId = null) {
        $sql = "SELECT s.*, c.class_name, sec.section_name 
                FROM {$this->table} s
                LEFT JOIN classes c ON s.class_id = c.class_id
                LEFT JOIN sections sec ON s.section_id = sec.section_id
                WHERE s.class_id = :class_id";
        
        $params = ['class_id' => $classId];
        
        if ($sectionId) {
            $sql .= " AND s.section_id = :section_id";
            $params['section_id'] = $sectionId;
        }
        
        $sql .= " ORDER BY s.roll_number";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Search students
     */
    public function search($keyword, $classId = null, $sectionId = null) {
        $sql = "SELECT s.*, c.class_name, sec.section_name 
                FROM {$this->table} s
                LEFT JOIN classes c ON s.class_id = c.class_id
                LEFT JOIN sections sec ON s.section_id = sec.section_id
                WHERE (s.name LIKE :keyword1 
                OR s.roll_number LIKE :keyword2
                OR s.guardian_name LIKE :keyword3
                OR s.guardian_phone LIKE :keyword4)";
        
        $params = [
            'keyword1' => "%{$keyword}%",
            'keyword2' => "%{$keyword}%",
            'keyword3' => "%{$keyword}%",
            'keyword4' => "%{$keyword}%"
        ];
        
        if ($classId) {
            $sql .= " AND s.class_id = :class_id";
            $params['class_id'] = $classId;
        }
        
        if ($sectionId) {
            $sql .= " AND s.section_id = :section_id";
            $params['section_id'] = $sectionId;
        }
        
        $sql .= " ORDER BY s.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Promote students from one class to another
     */
    public function promoteStudents($fromClassId, $fromSectionId, $toClassId, $toSectionId) {
        $sql = "UPDATE {$this->table} 
                SET class_id = :to_class, section_id = :to_section, roll_number = NULL
                WHERE class_id = :from_class AND section_id = :from_section";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'to_class' => $toClassId,
            'to_section' => $toSectionId,
            'from_class' => $fromClassId,
            'from_section' => $fromSectionId
        ]);
    }
    
    /**
     * Get student with full details
     */
    public function getStudentDetails($studentId) {
        $sql = "SELECT s.*, c.class_name, sec.section_name, t.name as class_teacher
                FROM {$this->table} s
                LEFT JOIN classes c ON s.class_id = c.class_id
                LEFT JOIN sections sec ON s.section_id = sec.section_id
                LEFT JOIN teachers t ON sec.class_teacher_id = t.teacher_id
                WHERE s.student_id = :id";
        
        return $this->queryOne($sql, ['id' => $studentId]);
    }
    
    /**
     * Check if roll number exists in class/section
     */
    public function rollNumberExists($classId, $sectionId, $rollNumber, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE class_id = :class_id AND section_id = :section_id AND roll_number = :roll_number";
        
        $params = [
            'class_id' => $classId,
            'section_id' => $sectionId,
            'roll_number' => $rollNumber
        ];
        
        if ($excludeId) {
            $sql .= " AND student_id != :id";
            $params['id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'] > 0;
    }
    
    /**
     * Get total students count
     */
    public function getTotalCount() {
        return $this->count();
    }
    
    /**
     * Get students count by class
     */
    public function getCountByClass($classId) {
        return $this->count('class_id = :class_id', ['class_id' => $classId]);
    }
}
