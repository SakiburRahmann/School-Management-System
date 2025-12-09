<?php
/**
 * Teacher - Attendance Dashboard
 */

$pageTitle = 'Attendance';
require_once __DIR__ . '/../../includes/teacher_header.php';

$teacherId = $teacherInfo['teacher_id'];
$teacherModel = new Teacher();
$attendanceModel = new Attendance();

// Get assigned classes
$assignedClasses = $teacherModel->getAssignedClasses($teacherId);

// Get today's status for each class
$today = date('Y-m-d');
foreach ($assignedClasses as &$class) {
    $attendance = $attendanceModel->getByDate($today, $class['class_id'], $class['section_id']);
    $class['attendance_status'] = !empty($attendance) ? 'Marked' : 'Pending';
    $class['student_count'] = count($attendance);
}
unset($class);
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="fas fa-calendar-check"></i> My Classes Attendance</h2>
        <p class="text-muted">Manage attendance for your assigned sections.</p>
    </div>
</div>

<?php if (empty($assignedClasses)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> You are not assigned to any classes yet.
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($assignedClasses as $class): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h4 class="card-title mb-1"><?php echo htmlspecialchars($class['class_name']); ?></h4>
                                <h5 class="text-muted"><?php echo htmlspecialchars($class['section_name']); ?></h5>
                            </div>
                            <span class="badge badge-<?php echo $class['attendance_status'] === 'Marked' ? 'success' : 'warning'; ?>">
                                <?php echo $class['attendance_status']; ?>
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="fas fa-calendar-day"></i> Today: <?php echo date('d M Y'); ?>
                            </small>
                        </div>
                        
                        <a href="<?php echo BASE_URL; ?>/teacher/attendance/take.php?class=<?php echo $class['class_id']; ?>&section=<?php echo $class['section_id']; ?>" 
                           class="btn btn-primary btn-block">
                            <?php echo $class['attendance_status'] === 'Marked' ? '<i class="fas fa-edit"></i> Edit Attendance' : '<i class="fas fa-plus"></i> Take Attendance'; ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
