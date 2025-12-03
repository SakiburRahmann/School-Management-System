<?php
/**
 * Admin - Teachers List
 */

$pageTitle = 'Manage Teachers';
require_once __DIR__ . '/../../includes/admin_header.php';

$teacherModel = new Teacher();
$searchQuery = $_GET['search'] ?? '';

// Get teachers
if ($searchQuery) {
    $teachers = $teacherModel->search($searchQuery);
} else {
    $teachers = $teacherModel->getTeachersWithDetails();
}
?>

<div class="card">
    <div class="card-header">
        <h3>All Teachers</h3>
        <a href="<?php echo BASE_URL; ?>/admin/teachers/add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Teacher
        </a>
    </div>
    
    <div class="card-body">
        <!-- Search -->
        <form method="GET" style="margin-bottom: 1.5rem;">
            <div style="display: flex; gap: 1rem;">
                <input type="text" name="search" placeholder="Search by name, subject, email..." 
                       value="<?php echo htmlspecialchars($searchQuery); ?>" 
                       class="form-control" style="flex: 1;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <?php if ($searchQuery): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/teachers/" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
        
        <!-- Teachers Table -->
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Specialization</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Subjects</th>
                        <th>Class Teacher</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($teachers)): ?>
                        <?php foreach ($teachers as $teacher): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($teacher['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($teacher['subject_speciality'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($teacher['email'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($teacher['phone'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo $teacher['subject_count']; ?> Subject<?php echo $teacher['subject_count'] != 1 ? 's' : ''; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($teacher['class_teacher_count'] > 0): ?>
                                        <span class="badge badge-success">Yes</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/admin/teachers/view.php?id=<?php echo $teacher['teacher_id']; ?>" 
                                       class="btn btn-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/admin/teachers/edit.php?id=<?php echo $teacher['teacher_id']; ?>" 
                                       class="btn btn-warning btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/admin/teachers/delete.php?id=<?php echo $teacher['teacher_id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirmDelete('Are you sure you want to delete this teacher?');"
                                       title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                No teachers found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 1.5rem; text-align: center;">
            <p><strong>Total Teachers:</strong> <?php echo count($teachers); ?></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
