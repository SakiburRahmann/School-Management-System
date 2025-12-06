<?php
/**
 * Admin - View Section Details
 * Display comprehensive section information with students and class teacher
 */

require_once __DIR__ . '/../../config.php';

$classModel = new ClassModel();
$studentModel = new Student();

// Get section ID
$sectionId = $_GET['id'] ?? null;

if (!$sectionId) {
    setFlash('danger', 'Invalid section ID.');
    redirect(BASE_URL . '/admin/classes/');
}

// Get section details
$section = $classModel->getSectionWithDetails($sectionId);

if (!$section) {
    setFlash('danger', 'Section not found.');
    redirect(BASE_URL . '/admin/classes/');
}

// Get students in this section
$students = $studentModel->getByClass($section['class_id'], $sectionId);

$pageTitle = 'Section ' . $section['section_name'] . ' - ' . $section['class_name'];
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<style>
/* Premium Section View Styles */
.section-hero {
    background: linear-gradient(135deg, var(--primary) 0%, #667eea 100%);
    border-radius: 16px;
    padding: 2rem;
    color: white;
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
}

.section-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: pulse-glow 4s ease-in-out infinite;
}

@keyframes pulse-glow {
    0%, 100% { opacity: 0.5; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.1); }
}

.section-hero-content {
    position: relative;
    z-index: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.section-title-area h2 {
    font-size: 2rem;
    margin: 0 0 0.5rem;
    font-weight: 700;
}

.section-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255,255,255,0.2);
    padding: 0.4rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    backdrop-filter: blur(10px);
}

.section-actions {
    display: flex;
    gap: 0.75rem;
}

.section-actions .btn {
    border-radius: 10px;
    padding: 0.6rem 1.2rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.section-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05);
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.stat-card i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, var(--primary), #667eea);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.stat-card .stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #333;
}

.stat-card .stat-label {
    font-size: 0.85rem;
    color: #666;
    margin-top: 0.25rem;
}

/* Teacher Card */
.teacher-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid rgba(0,0,0,0.05);
    margin-bottom: 1.5rem;
}

.teacher-card-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.teacher-avatar {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), #667eea);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: white;
    flex-shrink: 0;
}

.teacher-info h4 {
    margin: 0 0 0.25rem;
    font-size: 1.25rem;
    color: #333;
}

.teacher-info .teacher-role {
    color: #666;
    font-size: 0.9rem;
}

.teacher-contact {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}

.teacher-contact-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: #555;
}

.teacher-contact-item i {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(102, 126, 234, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
}

.no-teacher {
    text-align: center;
    padding: 2rem;
    color: #999;
}

.no-teacher i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* Students List */
.students-card {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.students-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.students-header h4 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.students-search {
    position: relative;
    min-width: 250px;
}

.students-search input {
    width: 100%;
    padding: 0.6rem 1rem 0.6rem 2.5rem;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.students-search input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    outline: none;
}

.students-search i {
    position: absolute;
    left: 0.9rem;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
}

.students-list {
    max-height: 500px;
    overflow-y: auto;
}

.student-item {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.2s ease;
}

.student-item:hover {
    background: #f8f9fa;
}

.student-item:last-child {
    border-bottom: none;
}

.student-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    margin-right: 1rem;
    flex-shrink: 0;
}

.student-details {
    flex: 1;
}

.student-details h5 {
    margin: 0 0 0.25rem;
    font-size: 1rem;
    color: #333;
}

.student-details span {
    font-size: 0.85rem;
    color: #666;
}

.student-actions {
    display: flex;
    gap: 0.5rem;
}

.student-actions .btn {
    padding: 0.4rem 0.75rem;
    font-size: 0.85rem;
}

.no-students {
    text-align: center;
    padding: 3rem;
    color: #999;
}

.no-students i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

/* Responsive */
@media (max-width: 768px) {
    .section-hero-content {
        flex-direction: column;
        text-align: center;
    }
    
    .section-actions {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- Section Hero -->
<div class="section-hero">
    <div class="section-hero-content">
        <div class="section-title-area">
            <h2><i class="fas fa-layer-group"></i> Section <?php echo htmlspecialchars($section['section_name']); ?></h2>
            <span class="section-badge">
                <i class="fas fa-graduation-cap"></i>
                <?php echo htmlspecialchars($section['class_name']); ?>
            </span>
        </div>
        <div class="section-actions">
            <a href="<?php echo BASE_URL; ?>/admin/classes/edit_section.php?id=<?php echo $section['section_id']; ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/classes/delete_section.php?id=<?php echo $section['section_id']; ?>" 
               class="btn btn-danger delete-btn"
               data-delete-url="<?php echo BASE_URL; ?>/admin/classes/delete_section.php?id=<?php echo $section['section_id']; ?>"
               data-delete-message="Are you sure you want to delete Section '<?php echo htmlspecialchars($section['section_name']); ?>' from <?php echo htmlspecialchars($section['class_name']); ?>?">
                <i class="fas fa-trash"></i> Delete
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/classes/" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <i class="fas fa-users"></i>
        <div class="stat-value"><?php echo $section['student_count']; ?></div>
        <div class="stat-label">Students</div>
    </div>
    <div class="stat-card">
        <i class="fas fa-chalkboard-teacher"></i>
        <div class="stat-value"><?php echo $section['class_teacher_name'] ? '1' : '0'; ?></div>
        <div class="stat-label">Class Teacher</div>
    </div>
    <div class="stat-card">
        <i class="fas fa-calendar-alt"></i>
        <div class="stat-value"><?php echo date('d M', strtotime($section['created_at'])); ?></div>
        <div class="stat-label">Created</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
    <!-- Left Column: Class Teacher -->
    <div>
        <h4 style="margin-bottom: 1rem; color: var(--primary);"><i class="fas fa-user-tie"></i> Class Teacher</h4>
        
        <?php if ($section['class_teacher_name']): ?>
            <div class="teacher-card">
                <div class="teacher-card-header">
                    <div class="teacher-avatar">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="teacher-info">
                        <h4><?php echo htmlspecialchars($section['class_teacher_name']); ?></h4>
                        <span class="teacher-role">
                            <?php echo htmlspecialchars($section['subject_speciality'] ?? 'Class Teacher'); ?>
                        </span>
                    </div>
                </div>
                
                <div class="teacher-contact">
                    <?php if ($section['teacher_email']): ?>
                    <div class="teacher-contact-item">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo htmlspecialchars($section['teacher_email']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($section['teacher_phone']): ?>
                    <div class="teacher-contact-item">
                        <i class="fas fa-phone"></i>
                        <span><?php echo htmlspecialchars($section['teacher_phone']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($section['teacher_id_custom']): ?>
                    <div class="teacher-contact-item">
                        <i class="fas fa-id-badge"></i>
                        <span>ID: <?php echo htmlspecialchars($section['teacher_id_custom']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee;">
                    <a href="<?php echo BASE_URL; ?>/admin/teachers/view.php?id=<?php echo $section['teacher_id']; ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-eye"></i> View Profile
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="teacher-card">
                <div class="no-teacher">
                    <i class="fas fa-user-slash"></i>
                    <p>No class teacher assigned</p>
                    <a href="<?php echo BASE_URL; ?>/admin/classes/edit_section.php?id=<?php echo $section['section_id']; ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Assign Teacher
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Right Column: Students List -->
    <div>
        <div class="students-card card">
            <div class="students-header">
                <h4>
                    <i class="fas fa-user-graduate"></i> 
                    Students 
                    <span class="badge badge-info"><?php echo count($students); ?></span>
                </h4>
                
                <?php if (count($students) > 0): ?>
                <div class="students-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="studentSearch" placeholder="Search students...">
                </div>
                <?php endif; ?>
            </div>
            
            <div class="students-list" id="studentsList">
                <?php if (count($students) > 0): ?>
                    <?php foreach ($students as $student): ?>
                        <div class="student-item" data-name="<?php echo strtolower(htmlspecialchars($student['name'])); ?>">
                            <div class="student-avatar">
                                <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                            </div>
                            <div class="student-details">
                                <h5><?php echo htmlspecialchars($student['name']); ?></h5>
                                <span>
                                    Roll: <?php echo htmlspecialchars($student['roll_number'] ?? 'N/A'); ?>
                                    <?php if (!empty($student['student_id_custom'])): ?>
                                        | ID: <?php echo htmlspecialchars($student['student_id_custom']); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="student-actions">
                                <a href="<?php echo BASE_URL; ?>/admin/students/view.php?id=<?php echo $student['student_id']; ?>" 
                                   class="btn btn-info btn-sm" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/admin/students/edit.php?id=<?php echo $student['student_id']; ?>" 
                                   class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-students">
                        <i class="fas fa-users-slash"></i>
                        <p>No students in this section</p>
                        <a href="<?php echo BASE_URL; ?>/admin/students/add.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Student
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Student search functionality
    const searchInput = document.getElementById('studentSearch');
    const studentsList = document.getElementById('studentsList');
    
    if (searchInput && studentsList) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const students = studentsList.querySelectorAll('.student-item');
            
            students.forEach(student => {
                const name = student.getAttribute('data-name');
                if (name.includes(searchTerm)) {
                    student.style.display = 'flex';
                } else {
                    student.style.display = 'none';
                }
            });
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
