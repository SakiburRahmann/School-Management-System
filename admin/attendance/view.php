<?php
/**
 * Admin - View Attendance
 */

$pageTitle = 'View Attendance';
require_once __DIR__ . '/../../includes/admin_header.php';

$attendanceModel = new Attendance();
$classModel = new ClassModel();
$studentModel = new Student();

$selectedDate = $_GET['date'] ?? date('Y-m-d');
$selectedClass = $_GET['class'] ?? '';
$selectedSection = $_GET['section'] ?? '';

// Get classes for filter
$classes = $classModel->findAll('class_name');

// Get attendance data
$attendanceData = [];
$stats = ['Present' => 0, 'Absent' => 0, 'Late' => 0, 'Excused' => 0];

if ($selectedClass && $selectedSection) {
    $attendanceData = $attendanceModel->getByDate($selectedDate, $selectedClass, $selectedSection);
    
    // Calculate stats
    foreach ($attendanceData as $record) {
        if (isset($stats[$record['status']])) {
            $stats[$record['status']]++;
        }
    }
}
?>

<!-- Modern Attendance View Styles -->
<style>
:root {
    --primary-soft: #eef2ff;
    --primary-border: #c7d2fe;
    --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.page-header {
    background: white;
    padding: 1.5rem 2rem;
    border-radius: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.filter-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: var(--card-shadow);
    margin-bottom: 2rem;
    border: 1px solid #f3f4f6;
}

.form-label-modern {
    font-size: 0.85rem;
    font-weight: 600;
    color: #4b5563;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.form-control-modern, .form-select-modern {
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    width: 100%;
}

.form-control-modern:focus, .form-select-modern:focus {
    background-color: white;
    border-color: #4e73df;
    outline: none;
    box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
}

/* Stats Pills */
.stats-summary {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.stat-pill {
    background: white;
    box-shadow: var(--card-shadow);
    border-radius: 9999px;
    padding: 0.5rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
    color: #374151;
    border: 1px solid #f3f4f6;
}

.stat-pill-value {
    background: #f3f4f6;
    padding: 0.2rem 0.6rem;
    border-radius: 1rem;
    font-size: 0.9rem;
}

.stat-pill.present .stat-pill-value { background: #dcfce7; color: #166534; }
.stat-pill.absent .stat-pill-value { background: #fee2e2; color: #991b1b; }
.stat-pill.late .stat-pill-value { background: #fef3c7; color: #92400e; }
.stat-pill.excused .stat-pill-value { background: #e0f2fe; color: #075985; }

/* Student List Card */
.student-list-card {
    background: white;
    border-radius: 1rem;
    box-shadow: var(--card-shadow);
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.student-list-header {
    padding: 1rem 1.5rem;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.student-row {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    transition: background 0.1s;
}

.student-row:last-child {
    border-bottom: none;
}

.student-row:hover {
    background: #f9fafb;
}

.student-avatar {
    width: 42px;
    height: 42px;
    background: #f3f4f6;
    color: #4b5563;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    margin-right: 1rem;
}

.student-details {
    flex: 1;
}

.student-name {
    font-weight: 600;
    color: #111827;
}

.student-meta {
    font-size: 0.85rem;
    color: #6b7280;
}

/* Status Badges */
.status-badge {
    padding: 0.35rem 0.8rem;
    border-radius: 9999px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.status-badge.present { background: #dcfce7; color: #166534; }
.status-badge.absent { background: #fee2e2; color: #991b1b; }
.status-badge.late { background: #fef3c7; color: #92400e; }
.status-badge.excused { background: #e0f2fe; color: #075985; }

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6b7280;
}
</style>

<div class="page-header">
    <div>
        <h3 style="margin:0; font-weight: 700; color: #111827;">View Attendance</h3>
        <p style="margin: 0.25rem 0 0 0; color: #6b7280;">Browse daily attendance records.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/admin/attendance/" class="btn btn-outline-secondary" 
       style="border-radius: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<!-- Filters -->
<div class="filter-card">
    <form method="GET" class="row g-3">
        <div class="col-md-3">
            <label for="date" class="form-label-modern">Date</label>
            <input type="date" id="date" name="date" class="form-control-modern" 
                   value="<?php echo $selectedDate; ?>">
        </div>
        
        <div class="col-md-3">
            <label for="class" class="form-label-modern">Class</label>
            <select id="class" name="class" class="form-select-modern" onchange="loadSectionsForAttendance(this.value)">
                <option value="">Select Class</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?php echo $class['class_id']; ?>" 
                            <?php echo $selectedClass == $class['class_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($class['class_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="col-md-3">
            <label for="section" class="form-label-modern">Section</label>
            <select id="section" name="section" class="form-select-modern">
                <option value="">Select Section</option>
            </select>
        </div>
        
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100" 
                    style="padding: 0.75rem; border-radius: 0.5rem; font-weight: 600;">
                <i class="fas fa-search me-2"></i> View Records
            </button>
        </div>
    </form>
</div>

<?php if ($selectedClass && $selectedSection): ?>
    <!-- Stats Pills -->
    <div class="stats-summary">
        <div class="stat-pill present">
            <span>Present</span>
            <span class="stat-pill-value"><?php echo $stats['Present']; ?></span>
        </div>
        <div class="stat-pill absent">
            <span>Absent</span>
            <span class="stat-pill-value"><?php echo $stats['Absent']; ?></span>
        </div>
        <div class="stat-pill late">
            <span>Late</span>
            <span class="stat-pill-value"><?php echo $stats['Late']; ?></span>
        </div>
        <div class="stat-pill excused">
            <span>Excused</span>
            <span class="stat-pill-value"><?php echo $stats['Excused']; ?></span>
        </div>
    </div>
    
    <?php if (!empty($attendanceData)): ?>
        <div class="student-list-card">
            <div class="student-list-header">
                <h5 class="mb-0" style="font-weight: 700; color: #374151;">Attendance List</h5>
                <a href="<?php echo BASE_URL; ?>/admin/attendance/take.php?date=<?php echo $selectedDate; ?>&class=<?php echo $selectedClass; ?>&section=<?php echo $selectedSection; ?>" 
                   class="btn btn-warning btn-sm" style="border-radius: 0.5rem; font-weight: 600;">
                    <i class="fas fa-pencil-alt me-1"></i> Edit Records
                </a>
            </div>
            
            <?php foreach ($attendanceData as $record): ?>
                <?php $initials = strtoupper(substr($record['student_name'], 0, 1)); ?>
                <div class="student-row">
                    <div class="student-avatar"><?php echo $initials; ?></div>
                    <div class="student-details">
                        <div class="student-name"><?php echo htmlspecialchars($record['student_name']); ?></div>
                        <div class="student-meta">Roll No: <?php echo htmlspecialchars($record['roll_number'] ?? '-'); ?></div>
                    </div>
                    
                    <div style="flex: 1; margin: 0 1.5rem;">
                        <?php if (!empty($record['remarks'])): ?>
                            <small style="color: #6b7280; font-style: italic;">
                                <i class="fas fa-comment-alt me-1" style="font-size: 0.75rem;"></i>
                                <?php echo htmlspecialchars($record['remarks']); ?>
                            </small>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <span class="status-badge <?php echo strtolower($record['status']); ?>">
                            <?php 
                                $icon = match($record['status']) {
                                    'Present' => 'check',
                                    'Absent' => 'times',
                                    'Late' => 'clock',
                                    'Excused' => 'info-circle',
                                    default => 'circle'
                                };
                            ?>
                            <i class="fas fa-<?php echo $icon; ?>"></i>
                            <?php echo htmlspecialchars($record['status']); ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times fa-3x mb-3" style="color: #e5e7eb;"></i>
            <h4>No records found</h4>
            <p>Attendance hasn't been marked for this class on <?php echo date('F j, Y', strtotime($selectedDate)); ?>.</p>
            <a href="<?php echo BASE_URL; ?>/admin/attendance/take.php?date=<?php echo $selectedDate; ?>&class=<?php echo $selectedClass; ?>&section=<?php echo $selectedSection; ?>" 
               class="btn btn-primary mt-3" style="border-radius: 0.5rem; padding: 0.75rem 1.5rem;">
                <i class="fas fa-plus me-2"></i> Mark Attendance
            </a>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="empty-state">
        <div style="background: #eff6ff; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto;">
            <i class="fas fa-filter fa-2x" style="color: #3b82f6;"></i>
        </div>
        <h4 style="color: #111827; font-weight: 700;">Select Options</h4>
        <p>Please select a Class and Section to view attendance records.</p>
    </div>
<?php endif; ?>

<script>
function loadSectionsForAttendance(classId) {
    const sectionSelect = document.getElementById('section');
    const selectedSection = '<?php echo $selectedSection; ?>';
    sectionSelect.innerHTML = '<option value="">Select Section</option>';
    
    if (!classId) return;
    
    fetch('<?php echo BASE_URL; ?>/admin/students/get_sections.php?class_id=' + classId)
        .then(response => response.json())
        .then(sections => {
            sections.forEach(section => {
                const option = document.createElement('option');
                option.value = section.section_id;
                option.textContent = section.section_name;
                if (section.section_id == selectedSection) {
                    option.selected = true;
                }
                sectionSelect.appendChild(option);
            });
        });
}

<?php if ($selectedClass): ?>
    loadSectionsForAttendance('<?php echo $selectedClass; ?>');
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
