<?php
/**
 * Teacher - Enter Marks
 */

$pageTitle = 'Enter Marks';
require_once __DIR__ . '/../includes/teacher_header.php';

$teacherId = $currentUser['related_id'];
$examModel = new Exam();
$classModel = new ClassModel();
$subjectModel = new Subject();
$studentModel = new Student();
$resultModel = new Result();

// Get filter parameters
$selectedExam = $_GET['exam_id'] ?? '';
$selectedClass = $_GET['class_id'] ?? '';
$selectedSection = $_GET['section_id'] ?? '';
$selectedSubject = $_GET['subject_id'] ?? '';

// Get exams (upcoming and recent past)
$exams = $examModel->getExamsWithDetails();

// Get teacher's assigned classes and subjects
$assignedClasses = $teacherModel->getAssignedClasses($teacherId);
$assignedSubjects = $subjectModel->getByTeacher($teacherId);

// Filter subjects based on selected class
$availableSubjects = [];
if ($selectedClass) {
    foreach ($assignedSubjects as $subject) {
        if ($subject['class_id'] == $selectedClass) {
            $availableSubjects[] = $subject;
        }
    }
}

// Handle marks submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_marks') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $examId = $_POST['exam_id'];
        $subjectId = $_POST['subject_id'];
        $marksData = $_POST['marks'] ?? [];
        $totalMarks = $_POST['total_marks'] ?? 100;
        
        $count = 0;
        foreach ($marksData as $studentId => $marks) {
            if ($marks !== '') {
                $resultModel->saveResult($examId, $studentId, $subjectId, $marks, $totalMarks);
                $count++;
            }
        }
        
        setFlash('success', "Marks saved for $count students!");
        redirect(BASE_URL . "/teacher/results.php?exam_id=$examId&class_id=$selectedClass&section_id=$selectedSection&subject_id=$subjectId");
    }
}

// Get students and existing marks
$students = [];
$existingMarks = [];
if ($selectedExam && $selectedClass && $selectedSection && $selectedSubject) {
    $students = $studentModel->getByClass($selectedClass, $selectedSection);
    $results = $resultModel->getSubjectResults($selectedExam, $selectedSubject);
    
    foreach ($results as $result) {
        $existingMarks[$result['student_id']] = $result['marks'];
    }
}
?>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3>Select Criteria</h3>
    </div>
    <div class="card-body">
        <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="form-group">
                <label for="exam_id">Exam</label>
                <select id="exam_id" name="exam_id" class="form-control" required>
                    <option value="">Select Exam</option>
                    <?php foreach ($exams as $exam): ?>
                        <option value="<?php echo $exam['exam_id']; ?>" 
                                <?php echo $selectedExam == $exam['exam_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($exam['exam_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="class_id">Class</label>
                <select id="class_id" name="class_id" class="form-control" required onchange="this.form.submit()">
                    <option value="">Select Class</option>
                    <?php foreach ($assignedClasses as $class): ?>
                        <option value="<?php echo $class['class_id']; ?>" 
                                <?php echo $selectedClass == $class['class_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['class_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="section_id">Section</label>
                <select id="section_id" name="section_id" class="form-control" required>
                    <option value="">Select Section</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="subject_id">Subject</label>
                <select id="subject_id" name="subject_id" class="form-control" required>
                    <option value="">Select Subject</option>
                    <?php foreach ($availableSubjects as $subject): ?>
                        <option value="<?php echo $subject['subject_id']; ?>" 
                                <?php echo $selectedSubject == $subject['subject_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-search"></i> Load Students
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($students)): ?>
    <div class="card">
        <div class="card-header">
            <h3>Enter Marks</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="save_marks">
                <input type="hidden" name="exam_id" value="<?php echo $selectedExam; ?>">
                <input type="hidden" name="subject_id" value="<?php echo $selectedSubject; ?>">
                
                <div class="form-group" style="max-width: 200px; margin-bottom: 1.5rem;">
                    <label for="total_marks">Total Marks</label>
                    <input type="number" id="total_marks" name="total_marks" class="form-control" value="100" required>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Student Name</th>
                                <th>Marks Obtained</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($student['name']); ?></strong></td>
                                    <td>
                                        <input type="number" name="marks[<?php echo $student['student_id']; ?>]" 
                                               class="form-control" style="width: 100px;" min="0" max="100"
                                               value="<?php echo $existingMarks[$student['student_id']] ?? ''; ?>"
                                               placeholder="Marks">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Marks
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
// Load sections when class is selected
function loadSections(classId) {
    const sectionSelect = document.getElementById('section_id');
    const selectedSection = '<?php echo $selectedSection; ?>';
    sectionSelect.innerHTML = '<option value="">Select Section</option>';
    
    if (!classId) return;
    
    fetch('<?php echo BASE_URL; ?>/admin/students/get_sections.php?class_id=' + classId)
        .then(response => response.json())
        .then(sections => {
            sections.forEach(section => {
                const option = document.createElement('option');
                option.value = section.section_id;
                option.textContent = section.section_name;
                if (section.section_id == selectedSection) {
                    option.selected = true;
                }
                sectionSelect.appendChild(option);
            });
        });
}

<?php if ($selectedClass): ?>
    loadSections('<?php echo $selectedClass; ?>');
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/teacher_footer.php'; ?>
