<?php
/**
 * Admin - Take Attendance
 */

require_once __DIR__ . '/../../config.php';
requireRole('Admin');

$attendanceModel = new Attendance();
$classModel = new ClassModel();
$studentModel = new Student();

$selectedDate = $_GET['date'] ?? date('Y-m-d');
$selectedClass = $_GET['class'] ?? '';
$selectedSection = $_GET['section'] ?? '';

// Handle attendance submission - MOVED TO TOP to prevent header errors
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_attendance') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $date = $_POST['date'];
        $sectionId = $_POST['section_id'];
        $attendance = $_POST['attendance'] ?? [];
        $remarks = $_POST['remarks'] ?? [];
        
        $dataToSave = [];
        foreach ($attendance as $studentId => $status) {
            $dataToSave[] = [
                'student_id' => $studentId,
                'date' => $date,
                'status' => $status,
                'remarks' => $remarks[$studentId] ?? null
            ];
        }
        
        if (!empty($dataToSave)) {
            $result = $attendanceModel->bulkMark($dataToSave);
            setFlash('success', "Attendance saved successfully! Updated: {$result['success']}, Errors: {$result['errors']}");
        } else {
            setFlash('warning', 'No attendance data to save.');
        }
        
        redirect(BASE_URL . '/admin/attendance/take.php?date=' . $date . '&class=' . $selectedClass . '&section=' . $selectedSection);
    }
}

$pageTitle = 'Take Attendance';
require_once __DIR__ . '/../../includes/admin_header.php';

// Get classes for filter
$classes = $classModel->findAll('class_name');

// Get attendance data if class and section selected
$attendanceData = [];
$students = [];
$stats = ['Present' => 0, 'Absent' => 0, 'Late' => 0, 'Excused' => 0, 'Total' => 0];

if ($selectedClass && $selectedSection) {
    $students = $studentModel->getByClass($selectedClass, $selectedSection);
    $attendanceData = $attendanceModel->getByDate($selectedDate, $selectedClass, $selectedSection);
    
    // Create lookup array
    $attendanceLookup = [];
    $remarksLookup = [];
    foreach ($attendanceData as $record) {
        $attendanceLookup[$record['student_id']] = $record['status'];
        $remarksLookup[$record['student_id']] = $record['remarks'];
        if (isset($stats[$record['status']])) {
            $stats[$record['status']]++;
        }
    }
    $stats['Total'] = count($students);
    
    // If no attendance taken yet, all are technically "pending" but we default to Present in UI
    if (empty($attendanceData)) {
        $stats['Present'] = count($students);
    }
}
?>

<!-- Modern Attendance UI Styles -->
<style>
:root {
    --primary-soft: #eef2ff;
    --primary-border: #c7d2fe;
    --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    --hover-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
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
    transition: all 0.2s;
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

/* Student List Modern */
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

.student-info {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.student-avatar {
    width: 42px;
    height: 42px;
    background: #e0f2fe;
    color: #0369a1;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
}

.student-details div:first-child {
    font-weight: 600;
    color: #111827;
}

.student-details div:last-child {
    font-size: 0.85rem;
    color: #6b7280;
}

/* Status Segmented Control */
.status-group {
    display: inline-flex;
    background: #f3f4f6;
    padding: 0.25rem;
    border-radius: 0.5rem;
    gap: 0.25rem;
}

.status-option {
    position: relative;
}

.status-option input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.status-label {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.4rem 0.8rem;
    border-radius: 0.375rem;
    font-weight: 600;
    font-size: 0.85rem;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
    min-width: 36px;
}

/* Selected States */
.status-option input:checked + .status-label {
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.status-option input[value="Present"]:checked + .status-label {
    background: #10b981;
    color: white;
}

.status-option input[value="Absent"]:checked + .status-label {
    background: #ef4444;
    color: white;
}

.status-option input[value="Late"]:checked + .status-label {
    background: #f59e0b;
    color: white;
}

.status-option input[value="Excused"]:checked + .status-label {
    background: #3b82f6;
    color: white;
}

.remarks-input {
    border: 1px solid transparent;
    background: transparent;
    padding: 0.4rem 0.6rem;
    border-radius: 0.375rem;
    font-size: 0.9rem;
    margin-left: 1rem;
    color: #4b5563;
    transition: all 0.2s;
    width: 150px;
}

.remarks-input:focus, .remarks-input:not(:placeholder-shown) {
    background: #f3f4f6;
    border-color: #e5e7eb;
    outline: none;
    width: 200px;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6b7280;
}

.mark-all-btn {
    font-size: 0.8rem;
    padding: 0.3rem 0.7rem;
    border-radius: 0.375rem;
    background: white;
    border: 1px solid #e5e7eb;
    color: #374151;
    font-weight: 500;
    transition: all 0.1s;
}

.mark-all-btn:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

.sticky-footer {
    position: sticky;
    bottom: 0;
    background: white;
    padding: 1rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.05);
    z-index: 100;
}
</style>

<div class="page-header">
    <div>
        <h3 style="margin:0; font-weight: 700; color: #111827;">Take Attendance</h3>
        <p style="margin: 0.25rem 0 0 0; color: #6b7280;">Record student attendance for specific classes.</p>
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
                   value="<?php echo $selectedDate; ?>" required>
        </div>
        
        <div class="col-md-3">
            <label for="class" class="form-label-modern">Class</label>
            <select id="class" name="class" class="form-select-modern" onchange="loadSectionsForAttendance(this.value)" required>
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
            <select id="section" name="section" class="form-select-modern" required>
                <option value="">Select Section</option>
            </select>
        </div>
        
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100" 
                    style="padding: 0.75rem; border-radius: 0.5rem; font-weight: 600;">
                <i class="fas fa-search me-2"></i>Load Students
            </button>
        </div>
    </form>
</div>

<?php if (!empty($students)): ?>
    <!-- Stats Pills -->
    <div class="stats-summary">
        <div class="stat-pill">
            <span>Total Students</span>
            <span class="stat-pill-value"><?php echo $stats['Total']; ?></span>
        </div>
        <div class="stat-pill present">
            <span>Present</span>
            <span class="stat-pill-value" id="count-present"><?php echo $stats['Present']; ?></span>
        </div>
        <div class="stat-pill absent">
            <span>Absent</span>
            <span class="stat-pill-value" id="count-absent"><?php echo $stats['Absent']; ?></span>
        </div>
        <div class="stat-pill late">
            <span>Late</span>
            <span class="stat-pill-value" id="count-late"><?php echo $stats['Late']; ?></span>
        </div>
        <div class="stat-pill excused">
            <span>Excused</span>
            <span class="stat-pill-value" id="count-excused"><?php echo $stats['Excused']; ?></span>
        </div>
    </div>

    <form method="POST" action="" id="attendanceForm">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="action" value="mark_attendance">
        <input type="hidden" name="date" value="<?php echo $selectedDate; ?>">
        <input type="hidden" name="section_id" value="<?php echo $selectedSection; ?>">
        
        <div class="student-list-card">
            <div class="student-list-header">
                <h5 class="mb-0" style="font-weight: 700; color: #374151;">Student List</h5>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <span style="font-size: 0.85rem; color: #6b7280; margin-right: 0.5rem;">Mark All:</span>
                    <button type="button" class="mark-all-btn" onclick="markAll('Present')">
                        <i class="fas fa-check text-success"></i> Present
                    </button>
                    <button type="button" class="mark-all-btn" onclick="markAll('Absent')">
                        <i class="fas fa-times text-danger"></i> Absent
                    </button>
                </div>
            </div>
            
            <div class="student-list-body">
                <?php foreach ($students as $index => $student): ?>
                    <?php 
                    $currentStatus = $attendanceLookup[$student['student_id']] ?? 'Present'; 
                    $currentRemarks = $remarksLookup[$student['student_id']] ?? '';
                    $initials = strtoupper(substr($student['name'], 0, 1));
                    ?>
                    <div class="student-row">
                        <div class="student-info">
                            <div class="student-avatar"><?php echo $initials; ?></div>
                            <div class="student-details">
                                <div><?php echo htmlspecialchars($student['name']); ?></div>
                                <div>Roll No: <?php echo htmlspecialchars($student['roll_number'] ?? '-'); ?></div>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center;">
                            <div class="status-group">
                                <label class="status-option">
                                    <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" 
                                           value="Present" <?php echo $currentStatus === 'Present' ? 'checked' : ''; ?>
                                           onchange="updateStats()" class="status-radio">
                                    <span class="status-label" title="Present">P</span>
                                </label>
                                <label class="status-option">
                                    <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" 
                                           value="Absent" <?php echo $currentStatus === 'Absent' ? 'checked' : ''; ?>
                                           onchange="updateStats()" class="status-radio">
                                    <span class="status-label" title="Absent">A</span>
                                </label>
                                <label class="status-option">
                                    <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" 
                                           value="Late" <?php echo $currentStatus === 'Late' ? 'checked' : ''; ?>
                                           onchange="updateStats()" class="status-radio">
                                    <span class="status-label" title="Late">L</span>
                                </label>
                                <label class="status-option">
                                    <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" 
                                           value="Excused" <?php echo $currentStatus === 'Excused' ? 'checked' : ''; ?>
                                           onchange="updateStats()" class="status-radio">
                                    <span class="status-label" title="Excused">E</span>
                                </label>
                            </div>
                            
                            <input type="text" name="remarks[<?php echo $student['student_id']; ?>]" 
                                   class="remarks-input" 
                                   placeholder="Add remark..."
                                   value="<?php echo htmlspecialchars($currentRemarks); ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="sticky-footer">
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-weight: 600; border-radius: 0.5rem; width: 100% max-width: 300px;">
                    <i class="fas fa-save me-2"></i> Save Attendance
                </button>
            </div>
        </div>
    </form>
<?php elseif ($selectedClass && $selectedSection): ?>
    <div class="empty-state">
        <i class="fas fa-user-graduate fa-3x mb-3" style="color: #e5e7eb;"></i>
        <h4>No students found</h4>
        <p>There are no students enrolled in this section yet.</p>
    </div>
<?php else: ?>
    <div class="empty-state">
        <div style="background: #eff6ff; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto;">
            <i class="fas fa-clipboard-list fa-2x" style="color: #3b82f6;"></i>
        </div>
        <h4 style="color: #111827; font-weight: 700;">Ready to take attendance?</h4>
        <p>Select a Class and Section above to load the student list.</p>
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
        })
        .catch(error => console.error('Error loading sections:', error));
}

function markAll(status) {
    const radios = document.querySelectorAll(`input[value="${status}"]`);
    radios.forEach(radio => radio.checked = true);
    updateStats();
}

function updateStats() {
    const stats = {
        'Present': 0,
        'Absent': 0,
        'Late': 0,
        'Excused': 0
    };
    
    document.querySelectorAll('.status-radio:checked').forEach(radio => {
        if (stats.hasOwnProperty(radio.value)) {
            stats[radio.value]++;
        }
    });
    
    document.getElementById('count-present').textContent = stats['Present'];
    document.getElementById('count-absent').textContent = stats['Absent'];
    document.getElementById('count-late').textContent = stats['Late'];
    document.getElementById('count-excused').textContent = stats['Excused'];
}

// Load sections on page load if class is selected
<?php if ($selectedClass): ?>
    loadSectionsForAttendance('<?php echo $selectedClass; ?>');
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
