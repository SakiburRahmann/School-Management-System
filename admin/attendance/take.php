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

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <h3 class="text-dark mb-0 fw-bold"><i class="fas fa-calendar-check me-2"></i>Take Attendance</h3>
        <a href="<?php echo BASE_URL; ?>/admin/attendance/" class="btn btn-outline-secondary px-4">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <!-- Filters Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="date" class="form-label fw-bold small text-muted">Date</label>
                    <input type="date" id="date" name="date" class="form-control" 
                           value="<?php echo $selectedDate; ?>" required>
                </div>
                
                <div class="col-md-3">
                    <label for="class" class="form-label fw-bold small text-muted">Class</label>
                    <select id="class" name="class" class="form-select" onchange="loadSectionsForAttendance(this.value)" required>
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
                    <label for="section" class="form-label fw-bold small text-muted">Section</label>
                    <select id="section" name="section" class="form-select" required>
                        <option value="">Select Section</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Load Students
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($students)): ?>
        <!-- Stats Summary -->
        <div class="row mb-4">
            <div class="col-md-2 col-6 mb-2">
                <div class="card bg-light border-0 h-100">
                    <div class="card-body text-center p-2">
                        <small class="text-muted fw-bold text-uppercase">Total</small>
                        <h4 class="mb-0 text-dark"><?php echo $stats['Total']; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="card bg-success bg-opacity-10 border-0 h-100">
                    <div class="card-body text-center p-2">
                        <small class="text-success fw-bold text-uppercase">Present</small>
                        <h4 class="mb-0 text-success" id="count-present"><?php echo $stats['Present']; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="card bg-danger bg-opacity-10 border-0 h-100">
                    <div class="card-body text-center p-2">
                        <small class="text-danger fw-bold text-uppercase">Absent</small>
                        <h4 class="mb-0 text-danger" id="count-absent"><?php echo $stats['Absent']; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="card bg-warning bg-opacity-10 border-0 h-100">
                    <div class="card-body text-center p-2">
                        <small class="text-warning fw-bold text-uppercase">Late</small>
                        <h4 class="mb-0 text-warning" id="count-late"><?php echo $stats['Late']; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="card bg-info bg-opacity-10 border-0 h-100">
                    <div class="card-body text-center p-2">
                        <small class="text-info fw-bold text-uppercase">Excused</small>
                        <h4 class="mb-0 text-info" id="count-excused"><?php echo $stats['Excused']; ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="" id="attendanceForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="mark_attendance">
            <input type="hidden" name="date" value="<?php echo $selectedDate; ?>">
            <input type="hidden" name="section_id" value="<?php echo $selectedSection; ?>">
            
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center sticky-top" style="top: 0; z-index: 1000;">
                    <h5 class="mb-0 text-dark">Student List</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="markAll('Present')">
                            <i class="fas fa-check me-1"></i>All Present
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="markAll('Absent')">
                            <i class="fas fa-times me-1"></i>All Absent
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4" width="50">#</th>
                                    <th width="100">Roll No</th>
                                    <th>Student Name</th>
                                    <th class="text-center" width="300">Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $index => $student): ?>
                                    <?php 
                                    $currentStatus = $attendanceLookup[$student['student_id']] ?? 'Present'; 
                                    $currentRemarks = $remarksLookup[$student['student_id']] ?? '';
                                    ?>
                                    <tr>
                                        <td class="ps-4 text-muted"><?php echo $index + 1; ?></td>
                                        <td class="fw-bold text-dark"><?php echo htmlspecialchars($student['roll_number'] ?? '-'); ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($student['name']); ?></div>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check status-radio" name="attendance[<?php echo $student['student_id']; ?>]" 
                                                       id="p_<?php echo $student['student_id']; ?>" value="Present" 
                                                       <?php echo $currentStatus === 'Present' ? 'checked' : ''; ?> onchange="updateStats()">
                                                <label class="btn btn-outline-success btn-sm" for="p_<?php echo $student['student_id']; ?>">P</label>

                                                <input type="radio" class="btn-check status-radio" name="attendance[<?php echo $student['student_id']; ?>]" 
                                                       id="a_<?php echo $student['student_id']; ?>" value="Absent" 
                                                       <?php echo $currentStatus === 'Absent' ? 'checked' : ''; ?> onchange="updateStats()">
                                                <label class="btn btn-outline-danger btn-sm" for="a_<?php echo $student['student_id']; ?>">A</label>

                                                <input type="radio" class="btn-check status-radio" name="attendance[<?php echo $student['student_id']; ?>]" 
                                                       id="l_<?php echo $student['student_id']; ?>" value="Late" 
                                                       <?php echo $currentStatus === 'Late' ? 'checked' : ''; ?> onchange="updateStats()">
                                                <label class="btn btn-outline-warning btn-sm" for="l_<?php echo $student['student_id']; ?>">L</label>

                                                <input type="radio" class="btn-check status-radio" name="attendance[<?php echo $student['student_id']; ?>]" 
                                                       id="e_<?php echo $student['student_id']; ?>" value="Excused" 
                                                       <?php echo $currentStatus === 'Excused' ? 'checked' : ''; ?> onchange="updateStats()">
                                                <label class="btn btn-outline-info btn-sm" for="e_<?php echo $student['student_id']; ?>">E</label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" name="remarks[<?php echo $student['student_id']; ?>]" 
                                                   class="form-control form-control-sm" 
                                                   placeholder="Note"
                                                   value="<?php echo htmlspecialchars($currentRemarks); ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white p-3 text-end sticky-bottom shadow-lg" style="bottom: 0; z-index: 100;">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Save Attendance
                    </button>
                </div>
            </div>
        </form>
    <?php elseif ($selectedClass && $selectedSection): ?>
        <div class="text-center py-5">
            <div class="text-muted mb-3">
                <i class="fas fa-user-graduate fa-3x"></i>
            </div>
            <h4 class="text-muted">No students found</h4>
            <p class="text-muted">There are no students enrolled in this section yet.</p>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="fas fa-clipboard-list fa-3x text-primary opacity-50"></i>
                </div>
            </div>
            <h4 class="text-dark">Ready to take attendance?</h4>
            <p class="text-muted">Please select a Class and Section above to load the student list.</p>
        </div>
    <?php endif; ?>
</div>

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

<style>
.btn-check:checked + .btn-outline-success {
    background-color: #198754;
    border-color: #198754;
    color: white;
}
.btn-check:checked + .btn-outline-danger {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}
.btn-check:checked + .btn-outline-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: black;
}
.btn-check:checked + .btn-outline-info {
    background-color: #0dcaf0;
    border-color: #0dcaf0;
    color: black;
}
.sticky-bottom {
    position: sticky;
    bottom: 0;
    z-index: 1020;
}
.sticky-top {
    position: sticky;
    top: 0;
    z-index: 1020;
}
</style>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
