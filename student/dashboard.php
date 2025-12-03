<?php
/**
 * Student Dashboard
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/student_header.php';

$studentId = $currentUser['related_id'];
$studentModel = new Student();
$attendanceModel = new Attendance();
$feeModel = new Fee();
$noticeModel = new Notice();
$examModel = new Exam();

// Get student details
$studentDetails = $studentModel->getStudentDetails($studentId);

// Get attendance percentage (last 30 days)
$startDate = date('Y-m-d', strtotime('-30 days'));
$endDate = date('Y-m-d');
$attendancePercentage = $attendanceModel->getAttendancePercentage($studentId, $startDate, $endDate);

// Get unpaid fees
$unpaidFees = $feeModel->getStudentFees($studentId, 'Unpaid');

// Get latest notices
$latestNotices = $noticeModel->getLatest(5);

// Get upcoming exams
$upcomingExams = $examModel->getUpcomingExams($studentDetails['class_id'] ?? null);
?>

<!-- Student Info Card -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <div style="display: flex; align-items: center; gap: 2rem;">
            <div class="user-avatar" style="width: 80px; height: 80px; font-size: 2rem;">
                <?php echo strtoupper(substr($studentDetails['name'], 0, 1)); ?>
            </div>
            <div style="flex: 1;">
                <h2 style="margin: 0 0 0.5rem 0;"><?php echo htmlspecialchars($studentDetails['name']); ?></h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div>
                        <strong>Class:</strong> <?php echo htmlspecialchars($studentDetails['class_name'] ?? 'N/A'); ?> - 
                        Section <?php echo htmlspecialchars($studentDetails['section_name'] ?? 'N/A'); ?>
                    </div>
                    <div>
                        <strong>Roll Number:</strong> <?php echo htmlspecialchars($studentDetails['roll_number'] ?? 'N/A'); ?>
                    </div>
                    <div>
                        <strong>Guardian:</strong> <?php echo htmlspecialchars($studentDetails['guardian_name'] ?? 'N/A'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-value"><?php echo round($attendancePercentage, 1); ?>%</div>
        <div class="stat-label">Attendance (Last 30 Days)</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon danger">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-value"><?php echo count($unpaidFees); ?></div>
        <div class="stat-label">Unpaid Fees</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="stat-value"><?php echo count($upcomingExams); ?></div>
        <div class="stat-label">Upcoming Exams</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-bullhorn"></i>
        </div>
        <div class="stat-value"><?php echo count($latestNotices); ?></div>
        <div class="stat-label">New Notices</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- Unpaid Fees -->
    <div class="card">
        <div class="card-header">
            <h3>Unpaid Fees</h3>
            <a href="<?php echo BASE_URL; ?>/student/fees.php" class="btn btn-primary btn-sm">View All</a>
        </div>
        <div class="card-body">
            <?php if (!empty($unpaidFees)): ?>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php foreach (array_slice($unpaidFees, 0, 3) as $fee): ?>
                        <div style="padding: 0.75rem; background: var(--light); border-radius: 8px;">
                            <div style="display: flex; justify-content: between; align-items: center;">
                                <div style="flex: 1;">
                                    <strong>$<?php echo number_format($fee['amount'], 2); ?></strong>
                                    <br>
                                    <small style="color: #666;">
                                        Due: <?php echo formatDate($fee['due_date']); ?>
                                    </small>
                                </div>
                                <span class="badge badge-danger">Unpaid</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #999;">No unpaid fees.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Upcoming Exams -->
    <div class="card">
        <div class="card-header">
            <h3>Upcoming Exams</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($upcomingExams)): ?>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php foreach ($upcomingExams as $exam): ?>
                        <div style="padding: 0.75rem; background: var(--light); border-radius: 8px;">
                            <strong><?php echo htmlspecialchars($exam['exam_name']); ?></strong>
                            <br>
                            <small style="color: #666;">
                                <i class="fas fa-calendar"></i> <?php echo formatDate($exam['exam_date']); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #999;">No upcoming exams.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Latest Notices -->
<div class="card">
    <div class="card-header">
        <h3>Latest Notices</h3>
        <a href="<?php echo BASE_URL; ?>/student/notices.php" class="btn btn-primary btn-sm">View All</a>
    </div>
    <div class="card-body">
        <?php if (!empty($latestNotices)): ?>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($latestNotices as $notice): ?>
                    <div style="padding: 1rem; border: 1px solid var(--light); border-radius: 8px;">
                        <h4 style="margin: 0 0 0.5rem 0;">
                            <?php echo htmlspecialchars($notice['title']); ?>
                            <?php if ($notice['priority'] === 'High'): ?>
                                <span class="badge badge-danger">Important</span>
                            <?php endif; ?>
                        </h4>
                        <p style="margin: 0; color: #666; font-size: 0.9rem;">
                            <?php echo substr(strip_tags($notice['content']), 0, 150); ?>...
                        </p>
                        <small style="color: #999;">
                            <?php echo formatDateTime($notice['created_at']); ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: #999;">No notices available.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Actions -->
<div class="card">
    <div class="card-header">
        <h3>Quick Actions</h3>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="<?php echo BASE_URL; ?>/student/attendance.php" class="btn btn-primary">
                <i class="fas fa-calendar-check"></i> View Attendance
            </a>
            <a href="<?php echo BASE_URL; ?>/student/results.php" class="btn btn-success">
                <i class="fas fa-chart-line"></i> View Results
            </a>
            <a href="<?php echo BASE_URL; ?>/student/fees.php" class="btn btn-warning">
                <i class="fas fa-dollar-sign"></i> View Fees
            </a>
            <a href="<?php echo BASE_URL; ?>/student/profile.php" class="btn btn-info">
                <i class="fas fa-user"></i> My Profile
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/student_footer.php'; ?>
