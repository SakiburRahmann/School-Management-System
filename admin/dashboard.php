<?php
/**
 * Admin Dashboard
 * Main dashboard with statistics and overview
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/admin_header.php';

// Get statistics
$studentModel = new Student();
$teacherModel = new Teacher();
$classModel = new ClassModel();
$attendanceModel = new Attendance();
$feeModel = new Fee();
$noticeModel = new Notice();
$eventModel = new Event();
$admissionModel = new AdmissionRequest();

$totalStudents = $studentModel->getTotalCount();
$totalTeachers = $teacherModel->getTotalCount();
$totalClasses = $classModel->getTotalCount();
$todayAttendance = $attendanceModel->getTodayOverview();
$unpaidFees = $feeModel->getUnpaidFees();
$pendingAdmissions = $admissionModel->getPendingCount();
$latestNotices = $noticeModel->getLatest(5);
$upcomingEvents = $eventModel->getUpcoming(5);
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div class="stat-value"><?php echo $totalStudents; ?></div>
        <div class="stat-label">Total Students</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="stat-value"><?php echo $totalTeachers; ?></div>
        <div class="stat-label">Total Teachers</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-school"></i>
        </div>
        <div class="stat-value"><?php echo $totalClasses; ?></div>
        <div class="stat-label">Total Classes</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon danger">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-value"><?php echo count($unpaidFees); ?></div>
        <div class="stat-label">Unpaid Fees</div>
    </div>
</div>

<!-- Today's Attendance -->
<div class="card">
    <div class="card-header">
        <h3>Today's Attendance Overview</h3>
        <a href="<?php echo BASE_URL; ?>/admin/attendance/" class="btn btn-primary btn-sm">View All</a>
    </div>
    <div class="card-body">
        <?php if ($todayAttendance && $todayAttendance['total'] > 0): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value" style="color: var(--success);">
                        <?php echo $todayAttendance['present']; ?>
                    </div>
                    <div class="stat-label">Present</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value" style="color: var(--danger);">
                        <?php echo $todayAttendance['absent']; ?>
                    </div>
                    <div class="stat-label">Absent</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value" style="color: var(--warning);">
                        <?php echo $todayAttendance['late']; ?>
                    </div>
                    <div class="stat-label">Late</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-value">
                        <?php echo round(($todayAttendance['present'] / $todayAttendance['total']) * 100, 1); ?>%
                    </div>
                    <div class="stat-label">Attendance Rate</div>
                </div>
            </div>
        <?php else: ?>
            <p>No attendance recorded for today yet.</p>
        <?php endif; ?>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- Latest Notices -->
    <div class="card">
        <div class="card-header">
            <h3>Latest Notices</h3>
            <a href="<?php echo BASE_URL; ?>/admin/notices/" class="btn btn-primary btn-sm">View All</a>
        </div>
        <div class="card-body">
            <?php if (!empty($latestNotices)): ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($latestNotices as $notice): ?>
                        <div style="padding: 1rem; background: var(--light); border-radius: 8px;">
                            <h4 style="margin-bottom: 0.5rem; font-size: 1rem;">
                                <?php echo htmlspecialchars($notice['title']); ?>
                            </h4>
                            <p style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem;">
                                <?php echo substr(strip_tags($notice['content']), 0, 100); ?>...
                            </p>
                            <small style="color: #999;">
                                <?php echo formatDateTime($notice['created_at']); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No notices available.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Upcoming Events -->
    <div class="card">
        <div class="card-header">
            <h3>Upcoming Events</h3>
            <a href="<?php echo BASE_URL; ?>/admin/events/" class="btn btn-primary btn-sm">View All</a>
        </div>
        <div class="card-body">
            <?php if (!empty($upcomingEvents)): ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($upcomingEvents as $event): ?>
                        <div style="padding: 1rem; background: var(--light); border-radius: 8px;">
                            <h4 style="margin-bottom: 0.5rem; font-size: 1rem;">
                                <?php echo htmlspecialchars($event['title']); ?>
                            </h4>
                            <p style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem;">
                                <?php echo htmlspecialchars($event['location'] ?? 'TBA'); ?>
                            </p>
                            <small style="color: #999;">
                                <i class="fas fa-calendar"></i>
                                <?php echo formatDate($event['event_date']); ?>
                                <?php if ($event['event_time']): ?>
                                    at <?php echo date('h:i A', strtotime($event['event_time'])); ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No upcoming events.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Pending Admissions -->
<?php if ($pendingAdmissions > 0): ?>
<div class="card">
    <div class="card-header">
        <h3>Pending Admission Requests</h3>
        <a href="<?php echo BASE_URL; ?>/admin/admissions/" class="btn btn-warning btn-sm">
            Review <?php echo $pendingAdmissions; ?> Request<?php echo $pendingAdmissions > 1 ? 's' : ''; ?>
        </a>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
