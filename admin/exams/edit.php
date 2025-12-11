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
            'exam_date' => $_POST['exam_date'],
            'total_marks' => $_POST['total_marks']
        ];
        
        if (!empty($data['exam_name']) && !empty($data['class_id']) && !empty($data['exam_date'])) {
            if ($examModel->update($examId, $data)) {
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
