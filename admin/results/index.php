<?php
/**
 * Admin - Results Management
 */

$pageTitle = 'Results Management';
require_once __DIR__ . '/../../includes/admin_header.php';

$examModel = new Exam();
$classModel = new ClassModel();
$resultModel = new Result();

// Get filter parameters
$selectedExam = $_GET['exam_id'] ?? '';
$selectedClass = $_GET['class_id'] ?? '';

// Get all exams and classes for filter
$exams = $examModel->getExamsWithDetails();
$classes = $classModel->findAll('class_name');

// Get results if filters selected
$classResults = [];
$topPerformers = [];

if ($selectedExam && $selectedClass) {
    $classResults = $resultModel->getClassResults($selectedExam, $selectedClass);
    $topPerformers = $resultModel->getTopPerformers($selectedExam, 5);
}
?>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3>View Results</h3>
    </div>
    <div class="card-body">
        <form method="GET" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem;">
            <div class="form-group">
                <label for="exam_id">Select Exam</label>
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
                <label for="class_id">Select Class</label>
                <select id="class_id" name="class_id" class="form-control" required>
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['class_id']; ?>" 
                                <?php echo $selectedClass == $class['class_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['class_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> View Results
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($selectedExam && $selectedClass): ?>
    <!-- Top Performers -->
    <?php if (!empty($topPerformers)): ?>
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3>Top Performers</h3>
        </div>
        <div class="card-body">
            <div class="stats-grid">
                <?php foreach ($topPerformers as $index => $student): ?>
                    <div class="stat-card">
                        <div class="stat-icon <?php echo $index === 0 ? 'success' : 'primary'; ?>">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-value"><?php echo $student['percentage']; ?>%</div>
                        <div class="stat-label">
                            <?php echo htmlspecialchars($student['name']); ?>
                            <br>
                            <small>Roll: <?php echo $student['roll_number']; ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Class Results Table -->
    <div class="card">
        <div class="card-header">
            <h3>Class Results</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Total Marks</th>
                            <th>Obtained</th>
                            <th>Percentage</th>
                            <th>Result</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($classResults)): ?>
                            <?php foreach ($classResults as $student): ?>
                                <?php 
                                $percentage = ($student['total_marks'] > 0) 
                                    ? round(($student['total_obtained'] / $student['total_marks']) * 100, 2) 
                                    : 0;
                                $isPassed = $percentage >= 33; // Simple pass criteria
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($student['name']); ?></strong></td>
                                    <td><?php echo $student['total_marks']; ?></td>
                                    <td><?php echo $student['total_obtained']; ?></td>
                                    <td><strong><?php echo $percentage; ?>%</strong></td>
                                    <td>
                                        <span class="badge badge-<?php echo $isPassed ? 'success' : 'danger'; ?>">
                                            <?php echo $isPassed ? 'Passed' : 'Failed'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/admin/results/view.php?student_id=<?php echo $student['student_id']; ?>&exam_id=<?php echo $selectedExam; ?>" 
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> View Sheet
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem;">
                                    No results found for this class.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
