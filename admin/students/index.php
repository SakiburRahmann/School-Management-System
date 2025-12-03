<?php
/**
 * Admin - Students List
 * Display all students with search and filter
 */

$pageTitle = 'Manage Students';
require_once __DIR__ . '/../../includes/admin_header.php';

$studentModel = new Student();
$classModel = new ClassModel();

// Get filter parameters
$classFilter = $_GET['class'] ?? '';
$sectionFilter = $_GET['section'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Get students
if ($searchQuery) {
    $students = $studentModel->search($searchQuery);
} elseif ($classFilter) {
    $students = $studentModel->getByClass($classFilter, $sectionFilter ?: null);
} else {
    $students = $studentModel->getStudentsWithDetails();
}

// Get all classes for filter
$classes = $classModel->findAll('class_name');
?>

<div class="card">
    <div class="card-header">
        <h3>All Students</h3>
        <a href="<?php echo BASE_URL; ?>/admin/students/add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Student
        </a>
    </div>
    
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" style="display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
            <input type="text" name="search" placeholder="Search by name, roll number..." 
                   value="<?php echo htmlspecialchars($searchQuery); ?>" 
                   class="form-control" style="flex: 1; min-width: 250px;">
            
            <select name="class" class="form-control" style="width: 150px;" onchange="this.form.submit()">
                <option value="">All Classes</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?php echo $class['class_id']; ?>" 
                            <?php echo $classFilter == $class['class_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($class['class_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Search
            </button>
            
            <?php if ($searchQuery || $classFilter): ?>
                <a href="<?php echo BASE_URL; ?>/admin/students/" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Clear
                </a>
            <?php endif; ?>
        </form>
        
        <!-- Students Table -->
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Roll No.</th>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Guardian</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['roll_number'] ?? 'N/A'); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($student['name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($student['section_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($student['guardian_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($student['guardian_phone'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/admin/students/view.php?id=<?php echo $student['student_id']; ?>" 
                                       class="btn btn-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/admin/students/edit.php?id=<?php echo $student['student_id']; ?>" 
                                       class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/admin/students/delete.php?id=<?php echo $student['student_id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirmDelete('Are you sure you want to delete this student?');"
                                       title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                No students found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 1.5rem; text-align: center;">
            <p><strong>Total Students:</strong> <?php echo count($students); ?></p>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card">
    <div class="card-header">
        <h3>Quick Actions</h3>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="<?php echo BASE_URL; ?>/admin/students/promote.php" class="btn btn-success">
                <i class="fas fa-level-up-alt"></i> Promote Students
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/students/import.php" class="btn btn-info">
                <i class="fas fa-file-import"></i> Import Students
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/students/export.php" class="btn btn-secondary">
                <i class="fas fa-file-export"></i> Export Students
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
