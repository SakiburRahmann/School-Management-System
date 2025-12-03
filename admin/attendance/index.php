<?php
/**
 * Admin - Attendance Management
 */

$pageTitle = 'Attendance Management';
require_once __DIR__ . '/../../includes/admin_header.php';

$attendanceModel = new Attendance();
$classModel = new ClassModel();
$studentModel = new Student();

$selectedDate = $_GET['date'] ?? date('Y-m-d');
$selectedClass = $_GET['class'] ?? '';
$selectedSection = $_GET['section'] ?? '';

// Get classes for filter
$classes = $classModel->findAll('class_name');

// Get attendance data if class and section selected
$attendanceData = [];
$students = [];
if ($selectedClass && $selectedSection) {
    $students = $studentModel->getByClass($selectedClass, $selectedSection);
    $attendanceData = $attendanceModel->getByDate($selectedDate, $selectedClass, $selectedSection);
    
    // Create lookup array
    $attendanceLookup = [];
    foreach ($attendanceData as $record) {
        $attendanceLookup[$record['student_id']] = $record['status'];
    }
}

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_attendance') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $date = $_POST['date'];
        $attendance = $_POST['attendance'] ?? [];
        
        foreach ($attendance as $studentId => $status) {
            $attendanceModel->markAttendance($studentId, $date, $status);
        }
        
        setFlash('success', 'Attendance marked successfully!');
        redirect(BASE_URL . '/admin/attendance/?date=' . $date . '&class=' . $selectedClass . '&section=' . $selectedSection);
    }
}

// Get today's overview
$todayOverview = $attendanceModel->getTodayOverview();
?>

<!-- Today's Overview -->
<div class="stats-grid" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-value" style="color: var(--success);">
            <?php echo $todayOverview['present'] ?? 0; ?>
        </div>
        <div class="stat-label">Present Today</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value" style="color: var(--danger);">
            <?php echo $todayOverview['absent'] ?? 0; ?>
        </div>
        <div class="stat-label">Absent Today</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value" style="color: var(--warning);">
            <?php echo $todayOverview['late'] ?? 0; ?>
        </div>
        <div class="stat-label">Late Today</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value">
            <?php 
            $total = $todayOverview['total'] ?? 0;
            $present = $todayOverview['present'] ?? 0;
            echo $total > 0 ? round(($present / $total) * 100, 1) : 0; 
            ?>%
        </div>
        <div class="stat-label">Attendance Rate</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Mark Attendance</h3>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" style="margin-bottom: 1.5rem;">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" class="form-control" 
                           value="<?php echo $selectedDate; ?>" onchange="this.form.submit()">
                </div>
                
                <div class="form-group">
                    <label for="class">Class</label>
                    <select id="class" name="class" class="form-control" onchange="loadSectionsForAttendance(this.value)">
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['class_id']; ?>" 
                                    <?php echo $selectedClass == $class['class_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="section">Section</label>
                    <select id="section" name="section" class="form-control">
                        <option value="">Select Section</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-search"></i> Load Students
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Attendance Form -->
        <?php if (!empty($students)): ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="mark_attendance">
                <input type="hidden" name="date" value="<?php echo $selectedDate; ?>">
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Roll No.</th>
                                <th>Student Name</th>
                                <th style="text-align: center;">Present</th>
                                <th style="text-align: center;">Absent</th>
                                <th style="text-align: center;">Late</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <?php 
                                $currentStatus = $attendanceLookup[$student['student_id']] ?? 'Present';
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['roll_number'] ?? 'N/A'); ?></td>
                                    <td><strong><?php echo htmlspecialchars($student['name']); ?></strong></td>
                                    <td style="text-align: center;">
                                        <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" 
                                               value="Present" <?php echo $currentStatus === 'Present' ? 'checked' : ''; ?>>
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" 
                                               value="Absent" <?php echo $currentStatus === 'Absent' ? 'checked' : ''; ?>>
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" 
                                               value="Late" <?php echo $currentStatus === 'Late' ? 'checked' : ''; ?>>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Attendance
                    </button>
                </div>
            </form>
        <?php else: ?>
            <p style="text-align: center; padding: 2rem; color: #999;">
                Please select date, class, and section to mark attendance.
            </p>
        <?php endif; ?>
    </div>
</div>

<script>
// Load sections when class is selected
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

// Load sections on page load if class is selected
<?php if ($selectedClass): ?>
    loadSectionsForAttendance('<?php echo $selectedClass; ?>');
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
