<?php
/**
 * Admin - Exam Management
 */

require_once __DIR__ . '/../../config.php';

$examModel = new Exam();
$classModel = new ClassModel();

// Handle exam creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_exam') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $data = [
            'exam_name' => sanitize($_POST['exam_name']),
            'class_id' => $_POST['class_id'],
            'exam_date' => $_POST['exam_date'],
            'total_marks' => $_POST['total_marks']
        ];
        
        if (!empty($data['exam_name']) && !empty($data['class_id']) && !empty($data['exam_date'])) {
            $examId = $examModel->create($data);
            if ($examId) {
                setFlash('success', 'Exam created successfully!');
            } else {
                setFlash('danger', 'Failed to create exam.');
            }
        }
    }
    redirect(BASE_URL . '/admin/exams/');
}

// Handle exam deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    // Verify CSRF token for deletion (safer) - for now just check ID
    if ($examModel->delete($_GET['id'])) {
        setFlash('success', 'Exam deleted successfully!');
    } else {
        setFlash('danger', 'Failed to delete exam.');
    }
    redirect(BASE_URL . '/admin/exams/');
}

// Get all exams
$exams = $examModel->getExamsWithDetails();
$classes = $classModel->findAll('class_name');

// Separate upcoming and past exams
$upcomingExams = array_filter($exams, fn($e) => strtotime($e['exam_date']) >= strtotime('today'));
$pastExams = array_filter($exams, fn($e) => strtotime($e['exam_date']) < strtotime('today'));

$pageTitle = 'Exam Management';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3>Create New Exam</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="create_exam">
            
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="exam_name">Exam Name <span style="color: red;">*</span></label>
                    <input type="text" id="exam_name" name="exam_name" class="form-control" 
                           placeholder="e.g., Mid-Term Exam 2025" required>
                </div>
                
                <div class="form-group">
                    <label for="class_id">Class <span style="color: red;">*</span></label>
                    <select id="class_id" name="class_id" class="form-control" required>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['class_id']; ?>">
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="exam_date">Exam Date <span style="color: red;">*</span></label>
                    <input type="date" id="exam_date" name="exam_date" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="total_marks">Total Marks</label>
                    <input type="number" id="total_marks" name="total_marks" class="form-control" 
                           placeholder="100" min="0">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">
                <i class="fas fa-plus"></i> Create Exam
            </button>
        </form>
    </div>
</div>

<!-- Upcoming Exams -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3>Upcoming Exams</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Exam Name</th>
                        <th>Class</th>
                        <th>Exam Date</th>
                        <th>Total Marks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($upcomingExams)): ?>
                        <?php foreach ($upcomingExams as $exam): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($exam['exam_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($exam['class_name']); ?></td>
                                <td><?php echo formatDate($exam['exam_date']); ?></td>
                                <td><?php echo $exam['total_marks'] ?? 'N/A'; ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/admin/exams/edit.php?id=<?php echo $exam['exam_id']; ?>" 
                                       class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/admin/exams/?delete=1&id=<?php echo $exam['exam_id']; ?>" 
                                       class="btn btn-danger btn-sm delete-btn"
                                       data-delete-url="<?php echo BASE_URL; ?>/admin/exams/?delete=1&id=<?php echo $exam['exam_id']; ?>"
                                       data-delete-message="Are you sure you want to delete this exam?"
                                       title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem;">
                                No upcoming exams scheduled.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Past Exams -->
<div class="card">
    <div class="card-header">
        <h3>Past Exams</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Exam Name</th>
                        <th>Class</th>
                        <th>Exam Date</th>
                        <th>Total Marks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pastExams)): ?>
                        <?php foreach ($pastExams as $exam): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($exam['exam_name']); ?></td>
                                <td><?php echo htmlspecialchars($exam['class_name']); ?></td>
                                <td><?php echo formatDate($exam['exam_date']); ?></td>
                                <td><?php echo $exam['total_marks'] ?? 'N/A'; ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/admin/results/?exam_id=<?php echo $exam['exam_id']; ?>" 
                                       class="btn btn-info btn-sm">
                                        <i class="fas fa-chart-line"></i> View Results
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/admin/exams/edit.php?id=<?php echo $exam['exam_id']; ?>" 
                                       class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem;">
                                No past exams found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
