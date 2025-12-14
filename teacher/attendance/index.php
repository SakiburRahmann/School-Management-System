<?php
/**
 * Teacher - Attendance Dashboard
 */

$pageTitle = 'Attendance';
require_once __DIR__ . '/../../includes/teacher_header.php';

$teacherId = $teacherInfo['teacher_id'];
$teacherModel = new Teacher();
$attendanceModel = new Attendance();

// Get sections where the teacher is the CLASS TEACHER
$assignedClasses = $teacherModel->getClassTeacherSections($teacherId);

// Get today's stats
$today = date('Y-m-d');
$totalStudents = 0;
$totalPresent = 0;
$sectionsMarked = 0;

foreach ($assignedClasses as &$class) {
    $attendance = $attendanceModel->getByDate($today, $class['class_id'], $class['section_id']);
    
    // Status Logic
    $isMarked = !empty($attendance);
    $class['attendance_status'] = $isMarked ? 'Marked' : 'Pending';
    
    // Count Logic
    $studentCount = (new Student())->getCountByClass($class['class_id']); // This might be class-wide, ideally should be section-wide.
    // Let's us Student::getByClass count for accuracy
    $students = (new Student())->getByClass($class['class_id'], $class['section_id']);
    $class['student_count'] = count($students);
    $totalStudents += $class['student_count'];
    
    if ($isMarked) {
        $sectionsMarked++;
        foreach ($attendance as $att) {
            if ($att['status'] === 'Present') $totalPresent++;
        }
    }
}
unset($class);

$attendanceRate = $totalStudents > 0 ? round(($totalPresent / $totalStudents) * 100) : 0;
// If no attendance marked today, rate is 0
if ($sectionsMarked == 0) $attendanceRate = 0;
?>

<style>
    /* Hero Section */
    .hero-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .hero-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        border: 1px solid rgba(226, 232, 240, 0.6);
        transition: transform 0.2s;
    }
    
    .hero-card:hover {
        transform: translateY(-2px);
    }
    
    .hero-icon {
        width: 60px;
        height: 60px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        margin-right: 1.25rem;
        flex-shrink: 0;
    }
    
    .icon-blue { background: #ebf8ff; color: #3182ce; }
    .icon-green { background: #f0fff4; color: #38a169; }
    .icon-purple { background: #faf5ff; color: #805ad5; }
    
    .hero-info h3 {
        margin: 0;
        font-size: 2rem;
        font-weight: 800;
        color: #2d3748;
        line-height: 1.1;
    }
    
    .hero-info p {
        margin: 0;
        color: #718096;
        font-size: 0.95rem;
        font-weight: 500;
        margin-top: 0.25rem;
    }

    /* Section Cards */
    .section-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 2rem;
    }

    .class-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
        border: 1px solid #edf2f7;
        transition: all 0.3s ease;
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .class-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
    }

    .card-banner {
        height: 100px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
    }
    
    .card-banner::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 40px;
        background: linear-gradient(to bottom, transparent, white);
    }

    .card-status {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: rgba(255, 255, 255, 0.9);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        backdrop-filter: blur(4px);
    }
    
    .status-marked { color: #10b981; }
    .status-pending { color: #f59e0b; }

    .card-main {
        padding: 1.5rem;
        padding-top: 0.5rem;
        flex-grow: 1;
        position: relative;
    }

    .class-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 0.25rem;
    }
    
    .section-subtitle {
        color: #718096;
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }

    .meta-row {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 12px;
        gap: 1.5rem;
    }
    
    .meta-item {
        display: flex;
        flex-direction: column;
    }
    
    .meta-label { font-size: 0.75rem; color: #a0aec0; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; }
    .meta-val { font-weight: 600; color: #4a5568; }

    .action-area {
        margin-top: auto;
    }
    
    .btn-take {
        width: 100%;
        padding: 1rem;
        border-radius: 12px;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s;
        border: none;
    }
    
    .btn-primary-custom {
        background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
        color: white;
        box-shadow: 0 4px 6px rgba(66, 153, 225, 0.3);
    }
    
    .btn-primary-custom:hover {
        background: linear-gradient(135deg, #3182ce 0%, #2b6cb0 100%);
        transform: translateY(-1px);
        box-shadow: 0 6px 8px rgba(66, 153, 225, 0.4);
        color: white;
    }
    
    .btn-success-custom {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        color: white;
        box-shadow: 0 4px 6px rgba(72, 187, 120, 0.3);
    }
    
    .btn-success-custom:hover {
        background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
        transform: translateY(-1px);
        box-shadow: 0 6px 8px rgba(72, 187, 120, 0.4);
        color: white;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 style="font-weight: 800; color: #1a202c; margin-bottom: 0.5rem;">Attendance Dashboard</h2>
        <p class="text-muted mb-0">Manage daily attendance for your sections</p>
    </div>
    <div style="background: white; padding: 0.5rem 1rem; border-radius: 50px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); font-weight: 600; color: #4a5568;">
        <i class="fas fa-calendar-alt text-primary mr-2"></i> <?php echo date('l, d F Y'); ?>
    </div>
</div>

<!-- Hero Stats -->
<?php if (!empty($assignedClasses)): ?>
<div class="hero-stats">
    <div class="hero-card">
        <div class="hero-icon icon-purple">
            <i class="fas fa-layer-group"></i>
        </div>
        <div class="hero-info">
            <h3><?php echo count($assignedClasses); ?></h3>
            <p>Your Sections</p>
        </div>
    </div>
    
    <div class="hero-card">
        <div class="hero-icon icon-blue">
            <i class="fas fa-users"></i>
        </div>
        <div class="hero-info">
            <h3><?php echo $totalStudents; ?></h3>
            <p>Total Students</p>
        </div>
    </div>
    
    <div class="hero-card">
        <div class="hero-icon icon-green">
            <i class="fas fa-chart-pie"></i>
        </div>
        <div class="hero-info">
            <h3><?php echo $sectionsMarked; ?>/<?php echo count($assignedClasses); ?></h3>
            <p>Sections Marked Today</p>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (empty($assignedClasses)): ?>
    <div style="text-align: center; padding: 5rem 2rem; background: white; border-radius: 20px; margin-top: 2rem;">
        <div style="background: #ebf8ff; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: #3182ce; font-size: 2rem;">
            <i class="fas fa-info"></i>
        </div>
        <h3 style="color: #2d3748; margin-bottom: 1rem;">No Sections Assigned</h3>
        <p style="color: #718096; max-width: 500px; margin: 0 auto; line-height: 1.6;">
            You are not currently assigned as the <strong>Class Teacher</strong> for any section. 
            <br>Normally, only Class Teachers can take attendance. Please contact the administrator if this is a mistake.
        </p>
    </div>
<?php else: ?>
    <!-- Class Grid -->
    <div class="section-grid">
        <?php foreach ($assignedClasses as $class): ?>
            <?php 
                // Random gradient variations for visual interest
                $seed = crc32($class['class_name'] . $class['section_name']);
                $hue = $seed % 360;
                $gradient = "linear-gradient(135deg, hsl($hue, 70%, 60%) 0%, hsl(" . ($hue + 40) . ", 70%, 50%) 100%)";
            ?>
            
            <div class="class-card">
                <div class="card-banner" style="background: <?php echo $gradient; ?>;">
                    <div class="card-status <?php echo $class['attendance_status'] === 'Marked' ? 'status-marked' : 'status-pending'; ?>">
                        <?php if ($class['attendance_status'] === 'Marked'): ?>
                            <i class="fas fa-check-circle"></i> Completed
                        <?php else: ?>
                            <i class="fas fa-clock"></i> Pending
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card-main">
                    <h3 class="class-title"><?php echo htmlspecialchars($class['class_name']); ?></h3>
                    <div class="section-subtitle">Section <?php echo htmlspecialchars($class['section_name']); ?></div>
                    
                    <div class="meta-row">
                        <div class="meta-item">
                            <span class="meta-label">Students</span>
                            <span class="meta-val"><?php echo $class['student_count']; ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Latest</span>
                            <span class="meta-val"><?php echo $class['attendance_status'] === 'Marked' ? 'Today' : 'Yesterday'; // Simplified ?></span>
                        </div>
                    </div>
                    
                    <div class="action-area">
                        <a href="<?php echo BASE_URL; ?>/teacher/attendance/take.php?class=<?php echo $class['class_id']; ?>&section=<?php echo $class['section_id']; ?>" 
                           class="btn-take <?php echo $class['attendance_status'] === 'Marked' ? 'btn-success-custom' : 'btn-primary-custom'; ?>">
                            <?php if ($class['attendance_status'] === 'Marked'): ?>
                                <i class="fas fa-edit"></i> Edit Attendance
                            <?php else: ?>
                                <i class="fas fa-plus-circle"></i> Take Attendance
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/teacher_footer.php'; ?>
