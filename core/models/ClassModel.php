<?php
/**
 * Class Model
 * Handles class and section data
 */

class ClassModel extends BaseModel {
    protected $table = 'classes';
    protected $primaryKey = 'class_id';
    
    /**
     * Get all classes with section count
     */
    public function getClassesWithSections() {
        $sql = "SELECT c.*, COUNT(s.section_id) as section_count
                FROM {$this->table} c
                LEFT JOIN sections s ON c.class_id = s.class_id
                GROUP BY c.class_id
                ORDER BY c.class_name";
        
        return $this->query($sql);
    }
    
    /**
     * Get class with all sections
     */
    public function getClassDetails($classId) {
        $class = $this->find($classId);
        
        if ($class) {
            $sql = "SELECT s.*, t.name as class_teacher_name
                    FROM sections s
                    LEFT JOIN teachers t ON s.class_teacher_id = t.teacher_id
                    WHERE s.class_id = :class_id
                    ORDER BY s.section_name";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['class_id' => $classId]);
            $class['sections'] = $stmt->fetchAll();
        }
        
        return $class;
    }
    
    /**
     * Get sections for a class
     */
    public function getSections($classId) {
        $sql = "SELECT s.*, t.name as class_teacher_name
                FROM sections s
                LEFT JOIN teachers t ON s.class_teacher_id = t.teacher_id
                WHERE s.class_id = :class_id
                ORDER BY s.section_name";
        
        return $this->query($sql, ['class_id' => $classId]);
    }
    
    /**
     * Create section
     */
    public function createSection($data) {
        $sql = "INSERT INTO sections (class_id, section_name, class_teacher_id) 
                VALUES (:class_id, :section_name, :class_teacher_id)";
        
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($data)) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Update section
     */
    public function updateSection($sectionId, $data) {
        $fields = [];
        foreach (array_keys($data) as $field) {
            $fields[] = "{$field} = :{$field}";
        }
        $fieldList = implode(', ', $fields);
        
        $sql = "UPDATE sections SET {$fieldList} WHERE section_id = :section_id";
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':section_id', $sectionId);
        
        return $stmt->execute();
    }
    
    /**
     * Delete section
     */
    public function deleteSection($sectionId) {
        $sql = "DELETE FROM sections WHERE section_id = :section_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['section_id' => $sectionId]);
    }
    
    /**
     * Get section details
     */
    public function getSection($sectionId) {
        $sql = "SELECT s.*, c.class_name, t.name as class_teacher_name
                FROM sections s
                JOIN classes c ON s.class_id = c.class_id
                LEFT JOIN teachers t ON s.class_teacher_id = t.teacher_id
                WHERE s.section_id = :section_id";
        
        return $this->queryOne($sql, ['section_id' => $sectionId]);
    }
    
    /**
     * Check if section name exists in class
     */
    public function sectionExists($classId, $sectionName, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM sections 
                WHERE class_id = :class_id AND section_name = :section_name";
        
        $params = [
            'class_id' => $classId,
            'section_name' => $sectionName
        ];
        
        if ($excludeId) {
            $sql .= " AND section_id != :id";
            $params['id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'] > 0;
    }
    
    /**
     * Check if class name exists
     */
    public function classNameExists($className, $excludeId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE class_name = :class_name";
        
        $params = ['class_name' => $className];
        
        if ($excludeId) {
            $sql .= " AND class_id != :id";
            $params['id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'] > 0;
    }
    
    /**
     * Get total classes count
     */
    public function getTotalCount() {
        return $this->count();
    }
}
