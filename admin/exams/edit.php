<?php
/**
 * Admin - Edit Exam
 */

require_once __DIR__ . '/../../config.php';

$examModel = new Exam();
$classModel = new ClassModel();

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

// Handle exam update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_exam') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $data = [
            'exam_name' => sanitize($_POST['exam_name']),
            'class_id' => $_POST['class_id'],
            'subject_id' => $_POST['subject_id'],
            'exam_date' => $_POST['exam_date'],
            'total_marks' => $_POST['total_marks']
        ];
        
        if (!empty($data['exam_name']) && !empty($data['class_id']) && !empty($data['subject_id']) && !empty($data['exam_date'])) {
            if ($examModel->update($examId, $data)) {
                // Update assigned teachers
                if (isset($_POST['assigned_teachers']) && is_array($_POST['assigned_teachers'])) {
                    $examModel->assignTeachers($examId, $_POST['assigned_teachers']);
                } else {
                    // If no teachers selected (or empty array sent), clear assignments
                    // Note: If checkbox is unchecked, it won't be in POST, so we need to handle empty case.
                    // But if the field is not present at all (e.g. some other error), we might not want to clear.
                    // However, in a standard form submission, unchecked checkboxes are just missing.
                    // So we should clear if the form was submitted.
                    $examModel->assignTeachers($examId, []);
                }
                setFlash('success', 'Exam updated successfully!');
                redirect(BASE_URL . '/admin/exams/');
            } else {
                setFlash('danger', 'Failed to update exam.');
            }
        } else {
            setFlash('danger', 'Please fill in all required fields.');
        }
    }
}

// Get all classes for the dropdown
$classes = $classModel->findAll('class_name');

// Get assigned teachers
$assignedTeachers = $examModel->getAssignedTeachers($examId);
$assignedTeacherIds = array_column($assignedTeachers, 'teacher_id');

$pageTitle = 'Edit Exam';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

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
                    <label for="class_id">Class <span style="color: red;">*</span></label>
                    <select id="class_id" name="class_id" class="form-control" required>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['class_id']; ?>" 
                                <?php echo ($class['class_id'] == $exam['class_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="subject_id">Subject <span style="color: red;">*</span></label>
                    <select id="subject_id" name="subject_id" class="form-control" required>
                        <option value="">Select Class First</option>
                    </select>
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
            </div>

            <div class="form-group" style="margin-top: 1rem;">
                <label for="assigned_teachers">Assign Evaluators (Teachers)</label>
                <input type="text" id="teacher_search" class="form-control" placeholder="Search teachers by name or ID..." style="margin-bottom: 10px;">
                <div id="teacher_selection_container" style="border: 1px solid #ddd; padding: 10px; border-radius: 4px; max-height: 200px; overflow-y: auto;">
                    <p class="text-muted">Loading teachers...</p>
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
<script>
function loadTeachers(classId, selectedTeacherIds = []) {
    const teacherContainer = document.getElementById('teacher_selection_container');
    
    if (!classId) {
        teacherContainer.innerHTML = '<p class="text-muted">Select a class first to see available teachers.</p>';
        return;
    }
    
    teacherContainer.innerHTML = '<p class="text-muted">Loading teachers...</p>';
    
    fetch('<?php echo BASE_URL; ?>/admin/exams/get_teachers_by_class.php?class_id=' + classId)
        .then(response => response.json())
        .then(teachers => {
            if (teachers.length === 0) {
                teacherContainer.innerHTML = '<p class="text-muted">No teachers found for this class.</p>';
                return;
            }
            
            let html = '';
            teachers.forEach(teacher => {
                const isChecked = selectedTeacherIds.includes(teacher.teacher_id) ? 'checked' : '';
                html += `
                    <div class="form-check teacher-item">
                        <input class="form-check-input" type="checkbox" name="assigned_teachers[]" 
                               value="${teacher.teacher_id}" id="teacher_${teacher.teacher_id}" ${isChecked}>
                        <label class="form-check-label" for="teacher_${teacher.teacher_id}">
                            ${teacher.name} (${teacher.subject_speciality || 'No Speciality'})
                            <span style="font-size: 0.8em; color: #666;">ID: ${teacher.teacher_id}</span>
                        </label>
                    </div>
                `;
            });
            teacherContainer.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            teacherContainer.innerHTML = '<p class="text-danger">Error loading teachers.</p>';
        });
}

function loadSubjects(classId, selectedSubjectId = null) {
    const subjectSelect = document.getElementById('subject_id');
    subjectSelect.innerHTML = '<option value="">Select Subject</option>';
    
    if (!classId) {
        subjectSelect.innerHTML = '<option value="">Select Class First</option>';
        return;
    }
    
    fetch('<?php echo BASE_URL; ?>/admin/exams/get_subjects_by_class.php?class_id=' + classId)
        .then(response => response.json())
        .then(subjects => {
            subjects.forEach(subject => {
                const option = document.createElement('option');
                option.value = subject.subject_id;
                option.textContent = subject.subject_name;
                if (selectedSubjectId && subject.subject_id == selectedSubjectId) {
                    option.selected = true;
                }
                subjectSelect.appendChild(option);
            });
        });
}

document.getElementById('class_id').addEventListener('change', function() {
    loadTeachers(this.value);
    loadSubjects(this.value);
});

// Teacher Search Functionality
document.getElementById('teacher_search').addEventListener('input', function() {
    const searchText = this.value.toLowerCase();
    const items = document.querySelectorAll('.teacher-item');
    
    items.forEach(item => {
        const label = item.querySelector('label').textContent.toLowerCase();
        if (label.includes(searchText)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Initial load
const initialClassId = '<?php echo $exam['class_id']; ?>';
const initialSubjectId = '<?php echo $exam['subject_id'] ?? ""; ?>';
const initialAssignedTeachers = <?php echo json_encode($assignedTeacherIds); ?>;
const initialAssignedTeachersInt = initialAssignedTeachers.map(id => parseInt(id));

if (initialClassId) {
    loadTeachers(initialClassId, initialAssignedTeachersInt);
    loadSubjects(initialClassId, initialSubjectId);
}
</script>
