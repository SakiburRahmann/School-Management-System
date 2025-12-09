<?php
/**
 * Teacher - Take Attendance
 */

$pageTitle = 'Take Attendance';
require_once __DIR__ . '/../../includes/teacher_header.php';

$teacherId = $teacherInfo['teacher_id'];
$attendanceModel = new Attendance();
$teacherModel = new Teacher();
$studentModel = new Student();

$selectedDate = $_GET['date'] ?? date('Y-m-d');
$classId = $_GET['class'] ?? '';
$sectionId = $_GET['section'] ?? '';

if (!$classId || !$sectionId) {
    setFlash('danger', 'Invalid class or section.');
    redirect(BASE_URL . '/teacher/attendance/');
}

// Validate assignment
$assignedClasses = $teacherModel->getAssignedClasses($teacherId);
$isAssigned = false;
$className = '';
$sectionName = '';

foreach ($assignedClasses as $class) {
    if ($class['class_id'] == $classId && $class['section_id'] == $sectionId) {
        $isAssigned = true;
        $className = $class['class_name'];
        $sectionName = $class['section_name'];
        break;
    }
}

if (!$isAssigned) {
    setFlash('danger', 'You are not authorized to take attendance for this section.');
    redirect(BASE_URL . '/teacher/attendance/');
}

// Get students and attendance data
$students = $studentModel->getByClass($classId, $sectionId);
$attendanceData = $attendanceModel->getByDate($selectedDate, $classId, $sectionId);

// Create lookup array
$attendanceLookup = [];
$remarksLookup = [];
foreach ($attendanceData as $record) {
    $attendanceLookup[$record['student_id']] = $record['status'];
    $remarksLookup[$record['student_id']] = $record['remarks'];
}

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_attendance') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $date = $_POST['date'];
        $attendance = $_POST['attendance'] ?? [];
        $remarks = $_POST['remarks'] ?? [];
        
        $dataToSave = [];
        foreach ($attendance as $studentId => $status) {
            $dataToSave[] = [
                'student_id' => $studentId,
                'section_id' => $sectionId,
                'date' => $date,
                'status' => $status,
                'remarks' => $remarks[$studentId] ?? null,
                'created_by' => $_SESSION['user_id']
            ];
        }
        
        if (!empty($dataToSave)) {
            $result = $attendanceModel->bulkMark($dataToSave);
            setFlash('success', "Attendance saved! Updated: {$result['success']}, Errors: {$result['errors']}");
        } else {
            setFlash('warning', 'No attendance data to save.');
        }
        
        redirect(BASE_URL . '/teacher/attendance/take.php?date=' . $date . '&class=' . $classId . '&section=' . $sectionId);
    }
}
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3><i class="fas fa-calendar-check"></i> Take Attendance</h3>
                <p class="mb-0 text-muted">
                    <?php echo htmlspecialchars($className); ?> - <?php echo htmlspecialchars($sectionName); ?>
                </p>
            </div>
            <a href="<?php echo BASE_URL; ?>/teacher/attendance/" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Date Filter -->
        <form method="GET" style="margin-bottom: 1.5rem; background: #f8f9fa; padding: 1rem; border-radius: 8px;">
            <input type="hidden" name="class" value="<?php echo $classId; ?>">
            <input type="hidden" name="section" value="<?php echo $sectionId; ?>">
            
            <div class="form-row align-items-end">
                <div class="col-md-4">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" class="form-control" 
                           value="<?php echo $selectedDate; ?>" onchange="this.form.submit()">
                </div>
                <div class="col-md-8 text-right">
                    <span class="text-muted">
                        <i class="fas fa-info-circle"></i> 
                        <?php echo empty($attendanceData) ? 'No attendance marked for this date.' : 'Attendance already marked. You can update it.'; ?>
                    </span>
                </div>
            </div>
        </form>
        
        <!-- Attendance Form -->
        <?php if (!empty($students)): ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="mark_attendance">
                <input type="hidden" name="date" value="<?php echo $selectedDate; ?>">
                
                <div style="margin-bottom: 1rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <button type="button" class="btn btn-success btn-sm" onclick="markAll('Present')">
                        <i class="fas fa-check"></i> Mark All Present
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="markAll('Absent')">
                        <i class="fas fa-times"></i> Mark All Absent
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th width="50">#</th>
                                <th>Roll No</th>
                                <th>Student Name</th>
                                <th class="text-center" width="100">Present</th>
                                <th class="text-center" width="100">Absent</th>
                                <th class="text-center" width="100">Late</th>
                                <th class="text-center" width="100">Excused</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $index => $student): ?>
                                <?php 
                                $currentStatus = $attendanceLookup[$student['student_id']] ?? 'Present'; // Default to Present
                                $currentRemarks = $remarksLookup[$student['student_id']] ?? '';
                                ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($student['roll_number'] ?? '-'); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($student['name']); ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="p_<?php echo $student['student_id']; ?>" 
                                                   name="attendance[<?php echo $student['student_id']; ?>]" 
                                                   value="Present" class="custom-control-input status-present"
                                                   <?php echo $currentStatus === 'Present' ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="p_<?php echo $student['student_id']; ?>"></label>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="a_<?php echo $student['student_id']; ?>" 
                                                   name="attendance[<?php echo $student['student_id']; ?>]" 
                                                   value="Absent" class="custom-control-input status-absent"
                                                   <?php echo $currentStatus === 'Absent' ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="a_<?php echo $student['student_id']; ?>"></label>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="l_<?php echo $student['student_id']; ?>" 
                                                   name="attendance[<?php echo $student['student_id']; ?>]" 
                                                   value="Late" class="custom-control-input status-late"
                                                   <?php echo $currentStatus === 'Late' ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="l_<?php echo $student['student_id']; ?>"></label>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="e_<?php echo $student['student_id']; ?>" 
                                                   name="attendance[<?php echo $student['student_id']; ?>]" 
                                                   value="Excused" class="custom-control-input status-excused"
                                                   <?php echo $currentStatus === 'Excused' ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="e_<?php echo $student['student_id']; ?>"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="remarks[<?php echo $student['student_id']; ?>]" 
                                               class="form-control form-control-sm" 
                                               placeholder="Optional note"
                                               value="<?php echo htmlspecialchars($currentRemarks); ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 1.5rem; text-align: right;">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Save Attendance
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No students found in this section.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function markAll(status) {
    const radios = document.querySelectorAll(`input[value="${status}"]`);
    radios.forEach(radio => radio.checked = true);
}
</script>

<style>
/* Custom radio styling for better visibility */
.custom-control-input:checked ~ .custom-control-label::before {
    border-color: var(--primary);
    background-color: var(--primary);
}
.status-present:checked ~ .custom-control-label::before {
    border-color: #10b981;
    background-color: #10b981;
}
.status-absent:checked ~ .custom-control-label::before {
    border-color: #ef4444;
    background-color: #ef4444;
}
.status-late:checked ~ .custom-control-label::before {
    border-color: #f59e0b;
    background-color: #f59e0b;
}
.status-excused:checked ~ .custom-control-label::before {
    border-color: #3b82f6;
    background-color: #3b82f6;
}
</style>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
