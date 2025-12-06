<?php
/**
 * Teacher Model
 * Handles teacher data and operations
 */

class Teacher extends BaseModel {
    protected $table = 'teachers';
    protected $primaryKey = 'teacher_id';
    
    /**
     * Get all teachers with subject count
     */
    public function getTeachersWithDetails() {
        $sql = "SELECT t.*, 
                COUNT(DISTINCT sub.subject_id) as subject_count,
                COUNT(DISTINCT sec.section_id) as class_teacher_count
                FROM {$this->table} t
                LEFT JOIN subjects sub ON t.teacher_id = sub.teacher_id
                LEFT JOIN sections sec ON t.teacher_id = sec.class_teacher_id
                GROUP BY t.teacher_id
                ORDER BY t.name";
        
        return $this->query($sql);
    }
    
    /**
 * Get teacher with assigned subjects
 */
public function getWithSubjects($teacherId) {
    $teacher = $this->find($teacherId);
    
    if ($teacher) {
        // Use subject_teachers junction table for multi-teacher support
        $sql = "SELECT sub.*, c.class_name 
                FROM subjects sub
                INNER JOIN subject_teachers st ON sub.subject_id = st.subject_id
                LEFT JOIN classes c ON sub.class_id = c.class_id
                WHERE st.teacher_id = :teacher_id
                ORDER BY c.class_name, sub.subject_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['teacher_id' => $teacherId]);
        $teacher['subjects'] = $stmt->fetchAll();
    }
    
    return $teacher;
}    
    /**
     * Get teacher's assigned classes
     */
    public function getAssignedClasses($teacherId) {
        $sql = "SELECT DISTINCT c.class_id, c.class_name, sec.section_id, sec.section_name
                FROM subjects sub
                JOIN classes c ON sub.class_id = c.class_id
                LEFT JOIN sections sec ON c.class_id = sec.class_id
                WHERE sub.teacher_id = :teacher_id
                ORDER BY c.class_name, sec.section_name";
        
        return $this->query($sql, ['teacher_id' => $teacherId]);
    }
    
    /**
     * Get sections where teacher is class teacher
     */
    public function getClassTeacherSections($teacherId) {
        $sql = "SELECT sec.*, c.class_name
                FROM sections sec
                JOIN classes c ON sec.class_id = c.class_id
                WHERE sec.class_teacher_id = :teacher_id
                ORDER BY c.class_name, sec.section_name";
        
        return $this->query($sql, ['teacher_id' => $teacherId]);
    }
    
    /**
     * Search teachers
     */
    public function search($keyword) {
        $sql = "SELECT t.*, 
                COUNT(DISTINCT sub.subject_id) as subject_count
                FROM {$this->table} t
                LEFT JOIN subjects sub ON t.teacher_id = sub.teacher_id
                WHERE t.name LIKE :keyword1 
                OR t.subject_speciality LIKE :keyword2
                OR t.email LIKE :keyword3
                OR t.phone LIKE :keyword4
                GROUP BY t.teacher_id
                ORDER BY t.name";
        
        $stmt = $this->db->prepare($sql);
        $searchTerm = "%{$keyword}%";
        $stmt->execute([
            'keyword1' => $searchTerm,
            'keyword2' => $searchTerm,
            'keyword3' => $searchTerm,
            'keyword4' => $searchTerm
        ]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if teacher custom ID exists
     */
    public function teacherIdExists($teacherId, $excludeTeacherId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE teacher_id_custom = :teacher_id";
        
        $params = ['teacher_id' => $teacherId];
        
        if ($excludeTeacherId) {
            $sql .= " AND teacher_id != :exclude_id";
            $params['exclude_id'] = $excludeTeacherId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'] > 0;
    }
    
    /**
     * Get total teachers count
     */
    public function getTotalCount() {
        return $this->count();
    }
    
    /**
     * Get teacher's schedule
     */
    public function getSchedule($teacherId, $dayOfWeek = null) {
        $sql = "SELECT r.*, c.class_name, sec.section_name, sub.subject_name
                FROM routines r
                JOIN classes c ON r.class_id = c.class_id
                JOIN sections sec ON r.section_id = sec.section_id
                LEFT JOIN subjects sub ON r.subject_id = sub.subject_id
                WHERE r.teacher_id = :teacher_id";
        
        $params = ['teacher_id' => $teacherId];
        
        if ($dayOfWeek) {
            $sql .= " AND r.day_of_week = :day";
            $params['day'] = $dayOfWeek;
        }
        
        $sql .= " ORDER BY r.day_of_week, r.start_time";
        
        return $this->query($sql, $params);
    }
}
