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
    /**
     * Generate Teacher ID
     * Format: TCH + YY + XXXXX (e.g., TCH2600001)
     */
    public function generateTeacherID($joiningDate) {
        $year = date('y', strtotime($joiningDate)); // Last 2 digits of year
        $prefix = "TCH{$year}";
        
        // Find the last ID with this prefix
        $sql = "SELECT teacher_id_custom FROM {$this->table} 
                WHERE teacher_id_custom LIKE :prefix 
                ORDER BY teacher_id_custom DESC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['prefix' => "{$prefix}%"]);
        $lastId = $stmt->fetchColumn();
        
        if ($lastId) {
            // Extract serial number
            $serial = (int)substr($lastId, 5);
            $nextSerial = $serial + 1;
        } else {
            $nextSerial = 1;
        }
        
        // Pad with zeros to 5 digits
        return $prefix . str_pad($nextSerial, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Create new teacher
     */
    public function create($data) {
        // Auto-generate ID if not provided (though it should always be generated now)
        if (empty($data['teacher_id_custom']) && !empty($data['joining_date'])) {
            $data['teacher_id_custom'] = $this->generateTeacherID($data['joining_date']);
        }
        
        return parent::create($data);
    }
}
