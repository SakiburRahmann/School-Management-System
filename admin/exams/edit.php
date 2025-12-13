<?php
/**
 * Admin - Edit Exam
 */

require_once __DIR__ . '/../../config.php';

$examModel = new Exam();
$classModel = new ClassModel();
$subjectModel = new Subject();

// Get exam ID
$examId = $_GET['id'] ?? null;

if (!$examId) {
    setFlash('danger', 'Invalid exam ID.');
    redirect(BASE_URL . '/admin/exams/');
}

// Fetch exam details
$exam = $examModel->find($examId);

if (!$exam) {
    setFlash('danger', 'Exam not found.');
    redirect(BASE_URL . '/admin/exams/');
}

// Get subject details
$subject = $subjectModel->find($exam['subject_id']);
$subjectName = $subject['subject_name'] ?? '';

// Handle exam update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_exam') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $examName = sanitize($_POST['exam_name']);
        $classId = $_POST['class_id'] ?? '';
        $subjectNamePost = $_POST['subject_name'] ?? '';
        $examDate = $_POST['exam_date'];
        $totalMarks = $_POST['total_marks'];
        $gradingSystem = $_POST['grading_system'] ?? null;
        $assignedTeachers = $_POST['assigned_teachers'] ?? [];
        
        // Validate required fields including teachers
        if (!empty($examName) && !empty($classId) && !empty($subjectNamePost) && !empty($examDate)) {
            // Check if at least one teacher is assigned
            if (empty($assignedTeachers) || !is_array($assignedTeachers) || count($assignedTeachers) === 0) {
                setFlash('danger', 'Please assign at least one teacher as an evaluator for this exam.');
                redirect(BASE_URL . '/admin/exams/edit.php?id=' . $examId);
                exit;
            }
            
            $conn = Database::getInstance()->getConnection();
            
            // Find subject_id for this class and subject_name (or global subject)
            $sql = "SELECT subject_id FROM subjects WHERE (class_id = :class_id OR class_id IS NULL) AND subject_name = :subject_name ORDER BY class_id DESC LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['class_id' => $classId, 'subject_name' => $subjectNamePost]);
            $subjectId = $stmt->fetchColumn();
            
            if ($subjectId) {
                $data = [
                    'exam_name' => $examName,
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'exam_date' => $examDate,
                    'total_marks' => $totalMarks,
                    'grading_system' => $gradingSystem
                ];
                
                if ($examModel->update($examId, $data)) {
                    // Update assigned teachers
                    $examModel->assignTeachers($examId, $assignedTeachers);
                    setFlash('success', 'Exam updated successfully!');
                    redirect(BASE_URL . '/admin/exams/');
                } else {
                    setFlash('danger', 'Failed to update exam.');
                }
            } else {
                setFlash('danger', 'Subject not found for the selected class.');
            }
        } else {
            setFlash('danger', 'Please fill in all required fields.');
        }
    }
}

// Get all classes
$classes = $classModel->findAll('class_name');

// Get assigned teachers
$assignedTeachers = $examModel->getAssignedTeachers($examId);
$assignedTeacherIds = array_column($assignedTeachers, 'teacher_id');

$pageTitle = 'Edit Exam';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<style>
/* Reuse styles from create exam page */
.class-checkboxes, .teacher-checkboxes {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 0.75rem;
    background: #fafafa;
}

.class-checkbox-item, .teacher-checkbox-item, .subject-radio-item {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    border-radius: 6px;
    transition: background 0.2s;
}

.class-checkbox-item:hover, .teacher-checkbox-item:hover, .subject-radio-item:hover {
    background: #f0f0f0;
}

.class-checkbox-item input[type="checkbox"],
.teacher-checkbox-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-right: 0.75rem;
    accent-color: var(--primary);
    cursor: pointer;
}

.subject-radio-item input[type="radio"] {
    width: 18px;
    height: 18px;
    margin-right: 0.75rem;
    accent-color: var(--primary);
    cursor: pointer;
}

.class-checkbox-item label,
.teacher-checkbox-item label,
.subject-radio-item label {
    margin: 0;
    cursor: pointer;
    flex: 1;
}

.class-search-wrapper {
    margin-bottom: 0;
}

.class-search-results {
    color: #666;
    display: block;
    padding: 0.25rem 0.5rem;
    background: #f5f5f5;
    border-radius: 0 0 8px 8px;
    font-size: 0.875rem;
}
</style>

<div class="card">
    <div class="card-header">
        <h3>Edit Exam</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="update_exam">
            
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="exam_name">Exam Name <span style="color: red;">*</span></label>
                    <input type="text" id="exam_name" name="exam_name" class="form-control" 
                           value="<?php echo htmlspecialchars($exam['exam_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Class <span style="color: red;">*</span></label>
                    <select id="class_id" name="class_id" class="form-control" required>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['class_id']; ?>" 
                                <?php echo ($class['class_id'] == $exam['class_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" id="initial_class_id" value="<?php echo $exam['class_id']; ?>">
                </div>
                
                <div class="form-group">
                    <label>Subject <span style="color: red;">*</span></label>
                    <div class="class-search-wrapper">
                        <input type="text" id="subjectSearch" class="form-control" 
                               placeholder="🔍 Search subjects..." 
                               style="border-radius: 8px 8px 0 0;">
                    </div>
                    <div class="class-checkboxes" id="subject_selection_container">
                        <p class="text-muted" style="margin: 0;">Loading...</p>
                    </div>
                    <input type="hidden" id="initial_subject_name" value="<?php echo htmlspecialchars($subjectName); ?>">
                </div>
                
                <div class="form-group">
                    <label for="exam_date">Exam Date <span style="color: red;">*</span></label>
                    <input type="date" id="exam_date" name="exam_date" class="form-control" 
                           value="<?php echo $exam['exam_date']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="total_marks">Total Marks</label>
                    <input type="number" id="total_marks" name="total_marks" class="form-control" 
                           value="<?php echo $exam['total_marks']; ?>" placeholder="100" min="0">
                </div>
                
                <?php $currentGradingSystem = isset($exam['grading_system']) ? json_decode($exam['grading_system'], true) : null; ?>
                <?php require_once __DIR__ . '/grading_system_ui.php'; ?>
            </div>

            <div class="form-group" style="margin-top: 1rem;">
                <label>Assign Evaluators (Teachers) <span style="color: red;">*</span></label>
                <input type="text" id="teacher_search" class="form-control" 
                       placeholder="🔍 Search teachers by name or ID..." 
                       style="margin-bottom: 10px; border-radius: 8px;">
                <div class="teacher-checkboxes" id="teacher_selection_container">
                    <p class="text-muted" style="margin: 0;">Loading...</p>
                </div>
            </div>
            
            <div style="margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Exam
                </button>
                <a href="<?php echo BASE_URL; ?>/admin/exams/" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('class_id');
    const subjectContainer = document.getElementById('subject_selection_container');
    const teacherContainer = document.getElementById('teacher_selection_container');
    const initialClassId = document.getElementById('initial_class_id').value;
    const initialSubjectName = document.getElementById('initial_subject_name').value;
    const initialAssignedTeachers = <?php echo json_encode($assignedTeacherIds); ?>;
    
    // Load subjects when class changes
    function loadSubjects(classId, selectSubject = null) {
        subjectContainer.innerHTML = '<p class="text-muted" style="margin: 0;">Loading subjects...</p>';
        teacherContainer.innerHTML = '<p class="text-muted" style="margin: 0;">Select a subject first to see available teachers.</p>';
        
        if (!classId) {
            subjectContainer.innerHTML = '<p class="text-muted" style="margin: 0;">Select a class first.</p>';
            return;
        }
        
        fetch('<?php echo BASE_URL; ?>/admin/exams/get_subjects_by_class.php?class_id=' + classId)
            .then(response => response.json())
            .then(subjects => {
                if (subjects.length === 0) {
                    subjectContainer.innerHTML = '<p class="text-muted" style="margin: 0;">No subjects found.</p>';
                } else {
                    let html = '';
                    subjects.forEach(subject => {
                        const safeId = 'subject_' + subject.subject_name.replace(/[^a-zA-Z0-9]/g, '_');
                        const isChecked = (selectSubject && subject.subject_name === selectSubject) ? 'checked' : '';
                        html += `
                            <div class="subject-radio-item">
                                <input type="radio" name="subject_name" 
                                       value="${subject.subject_name}" id="${safeId}" required ${isChecked}>
                                <label for="${safeId}">
                                    ${subject.subject_name}
                                </label>
                            </div>
                        `;
                    });
                    subjectContainer.innerHTML = html;
                    attachSubjectChangeListeners();
                    
                    // If a subject was pre-selected, load its teachers
                    if (selectSubject) {
                        loadTeachersBySubject(selectSubject, initialAssignedTeachers);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                subjectContainer.innerHTML = '<p class="text-danger" style="margin: 0;">Error loading subjects.</p>';
            });
    }
    
    // Load teachers by subject
    function loadTeachersBySubject(subjectName, selectedTeacherIds = []) {
        teacherContainer.innerHTML = '<p class="text-muted" style="margin: 0;">Loading teachers...</p>';
        
        fetch('<?php echo BASE_URL; ?>/admin/exams/get_teachers_by_subject.php?subject_name=' + encodeURIComponent(subjectName))
            .then(response => response.json())
            .then(teachers => {
                if (teachers.length === 0) {
                    teacherContainer.innerHTML = '<p class="text-muted" style="margin: 0;">No teachers found for this subject.</p>';
                    return;
                }
                
                let html = '';
                teachers.forEach(teacher => {
                    const isChecked = selectedTeacherIds.includes(parseInt(teacher.teacher_id)) ? 'checked' : '';
                    html += `
                        <div class="teacher-checkbox-item">
                            <input type="checkbox" name="assigned_teachers[]" class="teacher-checkbox" 
                                   value="${teacher.teacher_id}" id="teacher_${teacher.teacher_id}" required ${isChecked}>
                            <label for="teacher_${teacher.teacher_id}">
                                ${teacher.name}
                                ${teacher.subject_speciality ? `<small style="color: #888;"> - ${teacher.subject_speciality}</small>` : ''}
                            </label>
                        </div>
                    `;
                });
                teacherContainer.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                teacherContainer.innerHTML = '<p class="text-danger" style="margin: 0;">Error loading teachers.</p>';
            });
    }
    
    // Attach listeners to subject radio buttons
    function attachSubjectChangeListeners() {
        const subjectRadios = document.querySelectorAll('input[name="subject_name"]');
        subjectRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    loadTeachersBySubject(this.value);
                }
            });
        });
    }
    
    // Class change event
    classSelect.addEventListener('change', function() {
        loadSubjects(this.value);
    });
    
    // Subject search
    const subjectSearchInput = document.getElementById('subjectSearch');
    if (subjectSearchInput) {
        subjectSearchInput.addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            const items = document.querySelectorAll('.subject-radio-item');
            
            items.forEach(item => {
                const label = item.querySelector('label');
                if (label) {
                    const labelText = label.textContent.toLowerCase();
                    if (labelText.includes(searchText)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });
        });
    }
    
    // Teacher search
    const teacherSearchInput = document.getElementById('teacher_search');
    if (teacherSearchInput) {
        teacherSearchInput.addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            const items = document.querySelectorAll('.teacher-checkbox-item');
            
            items.forEach(item => {
                const label = item.querySelector('label');
                if (label) {
                    const labelText = label.textContent.toLowerCase();
                    if (labelText.includes(searchText)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });
        });
    }
    
    // Form validation
    const examForm = document.querySelector('form[action=""]');
    if (examForm) {
        examForm.addEventListener('submit', function(e) {
            const selectedTeachers = document.querySelectorAll('input[name="assigned_teachers[]"]:checked');
            
            if (selectedTeachers.length === 0) {
                e.preventDefault();
                alert('Please select at least one teacher as an evaluator for this exam.');
                return false;
            }
        });
    }
    
    // Initial load
    if (initialClassId) {
        loadSubjects(initialClassId, initialSubjectName);
    }
});
</script>
