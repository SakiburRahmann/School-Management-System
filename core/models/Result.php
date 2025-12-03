<?php
/**
 * Result Model
 * Handles exam results and marks
 */

class Result extends BaseModel {
    protected $table = 'results';
    protected $primaryKey = 'result_id';
    
    /**
     * Save or update result
     */
    public function saveResult($examId, $studentId, $subjectId, $marks, $totalMarks = 100, $remarks = null) {
        $grade = calculateGrade($marks, $totalMarks);
        
        $data = [
            'exam_id' => $examId,
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'marks' => $marks,
            'total_marks' => $totalMarks,
            'grade' => $grade,
            'remarks' => $remarks
        ];
        
        // Check if result already exists
        $existing = $this->getResult($examId, $studentId, $subjectId);
        
        if ($existing) {
            return $this->update($existing['result_id'], $data);
        } else {
            return $this->create($data);
        }
    }
    
    /**
     * Get specific result
     */
    public function getResult($examId, $studentId, $subjectId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE exam_id = :exam_id AND student_id = :student_id AND subject_id = :subject_id";
        
        return $this->queryOne($sql, [
            'exam_id' => $examId,
            'student_id' => $studentId,
            'subject_id' => $subjectId
        ]);
    }
    
    /**
     * Get student results for an exam
     */
    public function getStudentResults($studentId, $examId) {
        $sql = "SELECT r.*, sub.subject_name, sub.subject_code, e.exam_name
                FROM {$this->table} r
                JOIN subjects sub ON r.subject_id = sub.subject_id
                JOIN exams e ON r.exam_id = e.exam_id
                WHERE r.student_id = :student_id AND r.exam_id = :exam_id
                ORDER BY sub.subject_name";
        
        return $this->query($sql, [
            'student_id' => $studentId,
            'exam_id' => $examId
        ]);
    }
    
    /**
     * Get complete result sheet for student
     */
    public function getResultSheet($studentId, $examId) {
        $results = $this->getStudentResults($studentId, $examId);
        
        if (!empty($results)) {
            // Calculate totals
            $totalMarks = 0;
            $totalObtained = 0;
            
            foreach ($results as $result) {
                $totalMarks += $result['total_marks'];
                $totalObtained += $result['marks'];
            }
            
            $percentage = ($totalObtained / $totalMarks) * 100;
            $overallGrade = calculateGrade($totalObtained, $totalMarks);
            
            // Get student and exam info
            $sql = "SELECT s.*, c.class_name, sec.section_name, e.exam_name, e.exam_date
                    FROM students s
                    LEFT JOIN classes c ON s.class_id = c.class_id
                    LEFT JOIN sections sec ON s.section_id = sec.section_id
                    CROSS JOIN exams e
                    WHERE s.student_id = :student_id AND e.exam_id = :exam_id";
            
            $info = $this->queryOne($sql, [
                'student_id' => $studentId,
                'exam_id' => $examId
            ]);
            
            return [
                'info' => $info,
                'results' => $results,
                'total_marks' => $totalMarks,
                'total_obtained' => $totalObtained,
                'percentage' => round($percentage, 2),
                'grade' => $overallGrade
            ];
        }
        
        return null;
    }
    
    /**
     * Get class results for an exam
     */
    public function getClassResults($examId, $classId, $sectionId = null) {
        $sql = "SELECT s.student_id, s.name, s.roll_number,
                SUM(r.marks) as total_obtained,
                SUM(r.total_marks) as total_marks,
                COUNT(r.result_id) as subjects_count
                FROM students s
                LEFT JOIN {$this->table} r ON s.student_id = r.student_id AND r.exam_id = :exam_id
                WHERE s.class_id = :class_id";
        
        $params = [
            'exam_id' => $examId,
            'class_id' => $classId
        ];
        
        if ($sectionId) {
            $sql .= " AND s.section_id = :section_id";
            $params['section_id'] = $sectionId;
        }
        
        $sql .= " GROUP BY s.student_id ORDER BY s.roll_number";
        
        return $this->query($sql, $params);
    }
    
    /**
     * Get results by subject
     */
    public function getSubjectResults($examId, $subjectId) {
        $sql = "SELECT r.*, s.name as student_name, s.roll_number
                FROM {$this->table} r
                JOIN students s ON r.student_id = s.student_id
                WHERE r.exam_id = :exam_id AND r.subject_id = :subject_id
                ORDER BY s.roll_number";
        
        return $this->query($sql, [
            'exam_id' => $examId,
            'subject_id' => $subjectId
        ]);
    }
    
    /**
     * Check if student has results for exam
     */
    public function hasResults($studentId, $examId) {
        $count = $this->count(
            'student_id = :student_id AND exam_id = :exam_id',
            ['student_id' => $studentId, 'exam_id' => $examId]
        );
        return $count > 0;
    }
    
    /**
     * Get top performers
     */
    public function getTopPerformers($examId, $limit = 10) {
        $sql = "SELECT s.student_id, s.name, s.roll_number, c.class_name, sec.section_name,
                SUM(r.marks) as total_obtained,
                SUM(r.total_marks) as total_marks,
                ROUND((SUM(r.marks) / SUM(r.total_marks)) * 100, 2) as percentage
                FROM {$this->table} r
                JOIN students s ON r.student_id = s.student_id
                LEFT JOIN classes c ON s.class_id = c.class_id
                LEFT JOIN sections sec ON s.section_id = sec.section_id
                WHERE r.exam_id = :exam_id
                GROUP BY s.student_id
                ORDER BY percentage DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':exam_id', $examId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
