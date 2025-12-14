<?php
/**
 * Teacher - Enter Marks
 * Allows teachers to enter marks for exams they are assigned to as evaluators
 */

require_once __DIR__ . '/../../config.php';

// Get teacher ID from current user
$currentUser = (new User())->getUserWithRelated(getUserId());
$teacherId = $currentUser['related_id'];

$examModel = new Exam();
$studentModel = new Student();
$resultModel = new Result();
$subjectModel = new Subject();
$classModel = new ClassModel();

// Get exam ID
$examId = $_GET['exam_id'] ?? ($_POST['exam_id'] ?? null);

if (!$examId) {
    setFlash('danger', 'Invalid Exam ID.');
    redirect(BASE_URL . '/teacher/exams/');
}

// Get exam details
$exam = $examModel->find($examId);
if (!$exam) {
    setFlash('danger', 'Exam not found.');
    redirect(BASE_URL . '/teacher/exams/');
}

// Verify that this teacher is assigned to this exam
$assignedTeachers = $examModel->getAssignedTeachers($examId);
$isAssigned = false;
foreach ($assignedTeachers as $teacher) {
    if ($teacher['teacher_id'] == $teacherId) {
        $isAssigned = true;
        break;
    }
}

if (!$isAssigned) {
    setFlash('danger', 'You are not assigned as an evaluator for this exam.');
    redirect(BASE_URL . '/teacher/exams/');
}

// Get subject details
$subject = $subjectModel->find($exam['subject_id']);
$subjectName = $subject['subject_name'] ?? 'Unknown Subject';

// Get class details
$class = $classModel->find($exam['class_id']);
$className = $class['class_name'] ?? 'Unknown Class';

// Handle marks submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_marks') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $marksData = $_POST['marks'] ?? [];
        $totalMarks = $_POST['total_marks'] ?? 100;
        
        $count = 0;
        foreach ($marksData as $studentId => $marks) {
            if ($marks !== '') {
                // Ensure marks don't exceed total
                if ($marks > $totalMarks) {
                    $marks = $totalMarks; 
                }
                
                // Get grading system
                $gradingSystem = isset($exam['grading_system']) ? json_decode($exam['grading_system'], true) : null;
                
                $resultModel->saveResult($examId, $studentId, $exam['subject_id'], $marks, $totalMarks, null, $gradingSystem);
                $count++;
            }
        }
        
        setFlash('success', "Marks saved for $count students!");
        redirect(BASE_URL . "/teacher/exams/marks.php?exam_id=$examId");
    }
}

// Get all students in the class
$students = $studentModel->getByClass($exam['class_id']);

// Get existing marks
$existingResults = $resultModel->getSubjectResults($examId, $exam['subject_id']);
$existingMarks = [];
foreach ($existingResults as $result) {
    $existingMarks[$result['student_id']] = $result['marks'];
}

$pageTitle = 'Enter Marks - ' . $exam['exam_name'];
require_once __DIR__ . '/../../includes/teacher_header.php';
?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3><i class="fas fa-edit"></i> Enter Marks</h3>
            <p class="text-muted" style="margin-bottom: 0;">
                Exam: <strong><?php echo htmlspecialchars($exam['exam_name']); ?></strong> | 
                Class: <strong><?php echo htmlspecialchars($className); ?></strong> | 
                Subject: <strong><?php echo htmlspecialchars($subjectName); ?></strong>
            </p>
        </div>
        <a href="<?php echo BASE_URL; ?>/teacher/exams/" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to My Exams
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($students)): ?>
            <div class="alert alert-info">
                No students found in <?php echo htmlspecialchars($className); ?>.
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="save_marks">
                <input type="hidden" name="exam_id" value="<?php echo $examId; ?>">
                
                <div class="form-group" style="max-width: 200px; margin-bottom: 1.5rem;">
                    <label for="total_marks">Total Marks</label>
                    <input type="number" id="total_marks" name="total_marks" class="form-control" 
                           value="<?php echo $exam['total_marks'] ?? 100; ?>" required readonly>
                    <small class="text-muted">Total marks defined in exam settings</small>
                </div>
                
                <!-- Search Filter -->
                <div class="form-group" style="margin-bottom: 1rem;">
                    <input type="text" id="studentSearch" class="form-control" placeholder="Search students by name or roll number...">
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="100">Roll No</th>
                                <th>Student Name</th>
                                <th>Section</th>
                                <th width="150">Marks Obtained</th>
                            </tr>
                        </thead>
                        <tbody id="studentTableBody">
                            <?php foreach ($students as $student): ?>
                                <tr class="student-row">
                                    <td><?php echo htmlspecialchars($student['roll_number'] ?? '-'); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($student['name']); ?></strong>
                                        <div style="font-size: 0.85em; color: #666;">ID: <?php echo $student['student_id_custom']; ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['section_name'] ?? '-'); ?></td>
                                    <td>
                                        <input type="number" name="marks[<?php echo $student['student_id']; ?>]" 
                                               class="form-control marks-input" style="width: 100%;" min="0" max="<?php echo $exam['total_marks']; ?>"
                                               value="<?php echo $existingMarks[$student['student_id']] ?? ''; ?>"
                                               placeholder="0" tabindex="<?php echo $student['roll_number']; ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 1.5rem; position: sticky; bottom: 0; background: white; padding: 1rem; border-top: 1px solid #ddd; display: flex; justify-content: flex-end; gap: 10px;">
                    <a href="<?php echo BASE_URL; ?>/teacher/exams/" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Marks
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Student Search Functionality
    const searchInput = document.getElementById('studentSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('.student-row');
            
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                if (text.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Auto-save validation (visual only)
    const totalMarks = <?php echo $exam['total_marks'] ?? 100; ?>;
    const inputs = document.querySelectorAll('.marks-input');
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            const val = parseFloat(this.value);
            if (val > totalMarks) {
                this.style.borderColor = 'red';
                this.title = `Marks cannot exceed ${totalMarks}`;
            } else {
                this.style.borderColor = '#ddd';
                this.title = '';
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/teacher_footer.php'; ?>
