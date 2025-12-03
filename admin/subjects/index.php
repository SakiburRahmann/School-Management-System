<?php
/**
 * Admin - Subject Management
 */

$pageTitle = 'Manage Subjects';
require_once __DIR__ . '/../../includes/admin_header.php';

$subjectModel = new Subject();
$teacherModel = new Teacher();
$classModel = new ClassModel();

// Handle subject creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_subject') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $data = [
            'subject_name' => sanitize($_POST['subject_name']),
            'subject_code' => sanitize($_POST['subject_code']),
            'teacher_id' => $_POST['teacher_id'] ?: null,
            'class_id' => $_POST['class_id'] ?: null
        ];
        
        if (!empty($data['subject_name']) && !empty($data['subject_code'])) {
            if ($subjectModel->subjectCodeExists($data['subject_code'])) {
                setFlash('danger', 'Subject code already exists.');
            } else {
                $subjectId = $subjectModel->create($data);
                if ($subjectId) {
                    setFlash('success', 'Subject added successfully!');
                } else {
                    setFlash('danger', 'Failed to add subject.');
                }
            }
        }
    }
    redirect(BASE_URL . '/admin/subjects/');
}

// Get all subjects
$subjects = $subjectModel->getSubjectsWithDetails();
$allTeachers = $teacherModel->findAll('name');
$allClasses = $classModel->findAll('class_name');
?>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3>Add New Subject</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="add_subject">
            
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
                <div class="form-group">
                    <label for="subject_name">Subject Name <span style="color: red;">*</span></label>
                    <input type="text" id="subject_name" name="subject_name" class="form-control" 
                           placeholder="e.g., Mathematics" required>
                </div>
                
                <div class="form-group">
                    <label for="subject_code">Subject Code <span style="color: red;">*</span></label>
                    <input type="text" id="subject_code" name="subject_code" class="form-control" 
                           placeholder="e.g., MATH101" required>
                </div>
                
                <div class="form-group">
                    <label for="class_id">Class</label>
                    <select id="class_id" name="class_id" class="form-control">
                        <option value="">Select Class</option>
                        <?php foreach ($allClasses as $class): ?>
                            <option value="<?php echo $class['class_id']; ?>">
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="teacher_id">Assign Teacher</label>
                    <select id="teacher_id" name="teacher_id" class="form-control">
                        <option value="">Select Teacher</option>
                        <?php foreach ($allTeachers as $teacher): ?>
                            <option value="<?php echo $teacher['teacher_id']; ?>">
                                <?php echo htmlspecialchars($teacher['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">
                <i class="fas fa-plus"></i> Add Subject
            </button>
        </form>
    </div>
</div>

<!-- Subjects List -->
<div class="card">
    <div class="card-header">
        <h3>All Subjects</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Subject Name</th>
                        <th>Subject Code</th>
                        <th>Class</th>
                        <th>Assigned Teacher</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($subjects)): ?>
                        <?php foreach ($subjects as $subject): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($subject['subject_name']); ?></strong></td>
                                <td><code><?php echo htmlspecialchars($subject['subject_code']); ?></code></td>
                                <td><?php echo htmlspecialchars($subject['class_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($subject['teacher_name']): ?>
                                        <?php echo htmlspecialchars($subject['teacher_name']); ?>
                                    <?php else: ?>
                                        <span style="color: #999;">Not assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/admin/subjects/edit.php?id=<?php echo $subject['subject_id']; ?>" 
                                       class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/admin/subjects/delete.php?id=<?php echo $subject['subject_id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirmDelete('Delete this subject?');"
                                       title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem;">
                                No subjects found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 1.5rem; text-align: center;">
            <p><strong>Total Subjects:</strong> <?php echo count($subjects); ?></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
