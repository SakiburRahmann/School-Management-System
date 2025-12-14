<?php
/**
 * Teacher Dashboard
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/teacher_header.php';

$teacherId = $currentUser['related_id'];
$teacherModel = new Teacher();
$subjectModel = new Subject();
$noticeModel = new Notice();
$attendanceModel = new Attendance();

// Get teacher's subjects
$mySubjects = $subjectModel->getByTeacher($teacherId);

// Get assigned classes
$assignedClasses = $teacherModel->getAssignedClasses($teacherId);

// Get class teacher sections
$classTeacherSections = $teacherModel->getClassTeacherSections($teacherId);

// Get latest notices
$latestNotices = $noticeModel->getLatest(5);

// Get today's attendance overview
$todayOverview = $attendanceModel->getTodayOverview();
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-value"><?php echo count($mySubjects); ?></div>
        <div class="stat-label">Assigned Subjects</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-school"></i>
        </div>
        <div class="stat-value"><?php echo count($assignedClasses); ?></div>
        <div class="stat-label">Classes Teaching</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-value"><?php echo count($classTeacherSections); ?></div>
        <div class="stat-label">Class Teacher Of</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-value">
            <?php 
            $total = $todayOverview['total'] ?? 0;
            $present = $todayOverview['present'] ?? 0;
            echo $total > 0 ? round(($present / $total) * 100, 1) : 0; 
            ?>%
        </div>
        <div class="stat-label">Today's Attendance</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- My Subjects -->
    <div class="card">
        <div class="card-header">
            <h3>My Subjects</h3>
            <a href="<?php echo BASE_URL; ?>/teacher/subjects.php" class="btn btn-primary btn-sm">View All</a>
        </div>
        <div class="card-body">
            <?php if (!empty($mySubjects)): ?>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php foreach ($mySubjects as $subject): ?>
                        <div style="padding: 0.75rem; background: var(--light); border-radius: 8px;">
                            <strong><?php echo htmlspecialchars($subject['subject_name']); ?></strong>
                            <br>
                            <small style="color: #666;">
                                <?php echo htmlspecialchars($subject['class_name'] ?? 'N/A'); ?> - 
                                Code: <?php echo htmlspecialchars($subject['subject_code']); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #999;">No subjects assigned yet.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Class Teacher Sections -->
    <div class="card">
        <div class="card-header">
            <h3>Class Teacher Of</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($classTeacherSections)): ?>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php foreach ($classTeacherSections as $section): ?>
                        <div style="padding: 0.75rem; background: var(--light); border-radius: 8px;">
                            <strong><?php echo htmlspecialchars($section['class_name']); ?> - Section <?php echo htmlspecialchars($section['section_name']); ?></strong>
                            <br>
                            <a href="<?php echo BASE_URL; ?>/teacher/attendance.php?class=<?php echo $section['class_id']; ?>&section=<?php echo $section['section_id']; ?>" 
                               class="btn btn-sm btn-primary" style="margin-top: 0.5rem;">
                                <i class="fas fa-calendar-check"></i> Take Attendance
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #999;">Not assigned as class teacher.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Latest Notices -->
<div class="card">
    <div class="card-header">
        <h3>Latest Notices</h3>
        <a href="<?php echo BASE_URL; ?>/teacher/notices.php" class="btn btn-primary btn-sm">View All</a>
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
            <a href="<?php echo BASE_URL; ?>/teacher/attendance.php" class="btn btn-primary">
                <i class="fas fa-calendar-check"></i> Take Attendance
            </a>
            <a href="<?php echo BASE_URL; ?>/teacher/exams/" class="btn btn-success">
                <i class="fas fa-edit"></i> Enter Marks
            </a>
            <a href="<?php echo BASE_URL; ?>/teacher/subjects.php" class="btn btn-info">
                <i class="fas fa-book"></i> View Subjects
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/teacher_footer.php'; ?>
