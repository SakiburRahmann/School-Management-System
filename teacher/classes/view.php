<?php
/**
 * Teacher - View Class Section
 * Shows student list for a section where the teacher is the class teacher
 */
$pageTitle = 'My Class Students';
require_once __DIR__ . '/../../includes/teacher_header.php';

// Get teacher ID from current user
$teacherId = $currentUser['related_id'];

$teacherModel = new Teacher();
$studentModel = new Student();
$classModel = new ClassModel();

// Get sections where this teacher is the Class Teacher
$mySections = $teacherModel->getClassTeacherSections($teacherId);

// Get requested section ID or default to first one
$sectionId = $_GET['section_id'] ?? null;

// Validation: Is teacher assigned to any sections?
if (empty($mySections)) {
    echo '<div class="alert alert-info">You are not assigned as a Class Teacher for any section.</div>';
    require_once __DIR__ . '/../../includes/teacher_footer.php';
    exit;
}

// Access Control: Verify teacher owns this section
$currentSection = null;
if ($sectionId) {
    foreach ($mySections as $sec) {
        if ($sec['section_id'] == $sectionId) {
            $currentSection = $sec;
            break;
        }
    }
    
    if (!$currentSection) {
        setFlash('danger', 'Access Denied: You are not the class teacher for this section.');
        redirect(BASE_URL . '/teacher/dashboard.php');
    }
} else {
    // Default to the first section
    $currentSection = $mySections[0];
    $sectionId = $currentSection['section_id'];
}

// Get Students
$students = $studentModel->getByClass($currentSection['class_id'], $sectionId);

// Calculate Stats
$totalStudents = count($students);
$maleCount = 0;
$femaleCount = 0;

foreach ($students as $student) {
    if (($student['gender'] ?? '') === 'Male') $maleCount++;
    if (($student['gender'] ?? '') === 'Female') $femaleCount++;
}
?>

<style>
    /* Modern Card Design */
    .student-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }
    
    .student-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #f0f0f0;
        position: relative;
    }
    
    .student-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }
    
    .card-banner {
        height: 80px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .card-content {
        padding: 0 1.5rem 1.5rem;
        margin-top: -40px;
        text-align: center;
    }
    
    .student-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #fff;
        border: 4px solid #fff;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin: 0 auto 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: #667eea;
        font-weight: bold;
    }
    
    .student-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.25rem;
    }
    
    .student-roll {
        color: #718096;
        font-size: 0.9rem;
        margin-bottom: 1rem;
        background: #f7fafc;
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
    }
    
    .info-list {
        text-align: left;
        margin-bottom: 1.5rem;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 0.9rem;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .label { color: #718096; }
    .value { font-weight: 600; color: #4a5568; }
    
    .view-btn {
        display: block;
        width: 100%;
        padding: 0.75rem;
        background: #edf2f7;
        color: #4a5568;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        transition: background 0.2s, color 0.2s;
        border: none;
    }
    
    .view-btn:hover {
        background: #4a5568;
        color: white;
    }

    /* Stats Quick View */
    .section-header {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .section-title h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a202c;
    }
    
    .section-title p {
        margin: 0;
        color: #718096;
    }
    
    .stats-pills {
        display: flex;
        gap: 1rem;
    }
    
    .stat-pill {
        background: #f7fafc;
        padding: 0.5rem 1rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: #4a5568;
    }
    
    .stat-pill i { color: #667eea; }
    
    /* Search */
    .modern-search {
        position: relative;
        max-width: 300px;
        width: 100%;
    }
    
    .modern-search input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 2px solid #edf2f7;
        border-radius: 30px;
        font-size: 0.95rem;
        transition: border-color 0.2s;
    }
    
    .modern-search input:focus {
        border-color: #667eea;
        outline: none;
    }
    
    .modern-search i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #a0aec0;
    }
    
    /* Section Switcher */
    .section-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        overflow-x: auto;
        padding-bottom: 0.5rem;
    }
    
    .section-tab {
        padding: 0.75rem 1.5rem;
        background: white;
        border-radius: 30px;
        color: #718096;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        white-space: nowrap;
    }
    
    .section-tab.active {
        background: #667eea;
        color: white;
        box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
    }
    
    .section-tab:hover:not(.active) {
        background: #f7fafc;
        color: #4a5568;
    }
</style>

<!-- Access Control / Multi-section Logic -->
<?php if (count($mySections) > 1): ?>
<div class="section-tabs">
    <?php foreach ($mySections as $sec): ?>
        <a href="?section_id=<?php echo $sec['section_id']; ?>" 
           class="section-tab <?php echo ($sec['section_id'] == $sectionId) ? 'active' : ''; ?>">
            <?php echo htmlspecialchars($sec['class_name']); ?> - <?php echo htmlspecialchars($sec['section_name']); ?>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Header & Stats -->
<div class="section-header">
    <div class="section-title">
        <h2><?php echo htmlspecialchars($currentSection['class_name']); ?> - Section <?php echo htmlspecialchars($currentSection['section_name']); ?></h2>
        <p>My Class Students</p>
    </div>
    
    <div class="modern-search">
        <i class="fas fa-search"></i>
        <input type="text" id="studentSearch" placeholder="Search students...">
    </div>
    
    <div class="stats-pills">
        <div class="stat-pill">
            <i class="fas fa-users"></i>
            <span><?php echo $totalStudents; ?> Students</span>
        </div>
        <div class="stat-pill">
            <i class="fas fa-male"></i>
            <span><?php echo $maleCount; ?> Boys</span>
        </div>
        <div class="stat-pill">
            <i class="fas fa-female"></i>
            <span><?php echo $femaleCount; ?> Girls</span>
        </div>
    </div>
</div>

<?php if (empty($students)): ?>
    <div style="text-align: center; padding: 4rem; color: #a0aec0;">
        <i class="fas fa-user-graduate" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
        <p>No students found in this section.</p>
    </div>
<?php else: ?>
    <!-- Student Grid -->
    <div class="student-grid" id="studentGrid">
        <?php foreach ($students as $student): ?>
            <?php 
                $initials = strtoupper(substr($student['name'], 0, 1));
                if (strpos($student['name'], ' ') !== false) {
                    $names = explode(' ', $student['name']);
                    $initials .= strtoupper(substr(end($names), 0, 1));
                }
                
                // Color generation based on name
                $hue = crc32($student['name']) % 360;
                $avatarColor = "hsl($hue, 70%, 60%)";
            ?>
            <div class="student-card" data-name="<?php echo strtolower($student['name']); ?>" 
                 data-roll="<?php echo $student['roll_number']; ?>">
                <div class="card-banner" style="background: linear-gradient(135deg, <?php echo $avatarColor; ?> 0%, <?php echo "hsl($hue, 60%, 40%)"; ?> 100%);"></div>
                <div class="card-content">
                    <div class="student-avatar" style="color: <?php echo $avatarColor; ?>;">
                        <?php echo $initials; ?>
                    </div>
                    
                    <h3 class="student-name"><?php echo htmlspecialchars($student['name']); ?></h3>
                    <div class="student-roll">Roll No: <?php echo $student['roll_number'] ?? 'N/A'; ?></div>
                    
                    <div class="info-list">
                        <div class="info-item">
                            <span class="label">Student ID</span>
                            <span class="value"><?php echo $student['student_id_custom'] ?? '-'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Gender</span>
                            <span class="value"><?php echo $student['gender'] ?? '-'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Guardian</span>
                            <span class="value"><?php echo htmlspecialchars($student['guardian_name'] ?? '-'); ?></span>
                        </div>
                        <?php if(!empty($student['guardian_phone'])): ?>
                        <div class="info-item">
                            <span class="label">Phone</span>
                            <span class="value"><?php echo $student['guardian_phone']; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <a href="<?php echo BASE_URL; ?>/teacher/students/view.php?id=<?php echo $student['student_id']; ?>" class="view-btn">
                        View Full Profile <i class="fas fa-arrow-right" style="margin-left: 5px;"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('studentSearch');
    const studentGrid = document.getElementById('studentGrid');
    
    if (searchInput && studentGrid) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const cards = studentGrid.querySelectorAll('.student-card');
            
            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                const roll = card.getAttribute('data-roll') || '';
                
                if (name.includes(searchTerm) || roll.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/teacher_footer.php'; ?>
