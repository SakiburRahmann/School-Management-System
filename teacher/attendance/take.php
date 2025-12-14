<?php
/**
 * Teacher - Take Attendance (Modern UI)
 */

// 1. Initialize System & Auth Checks (BEFORE Output)
require_once __DIR__ . '/../../config.php';

// Ensure user is logged in as Teacher
requireRole('Teacher');

$currentUser = (new User())->getUserWithRelated(getUserId());
$teacherInfo = $currentUser['related_info'] ?? null;
$teacherId = $teacherInfo['teacher_id'];

// 2. Initialize Models
$attendanceModel = new Attendance();
$teacherModel = new Teacher();
$studentModel = new Student();

// 3. Request Parameter Handling
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$classId = $_GET['class'] ?? '';
$sectionId = $_GET['section'] ?? '';

if (!$classId || !$sectionId) {
    setFlash('danger', 'Invalid class or section.');
    redirect(BASE_URL . '/teacher/attendance/');
}

// 4. Access Control Logic
// Validate assignment (Using ClassTeacher logic for consistency)
$assignedClasses = $teacherModel->getClassTeacherSections($teacherId);
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

// 5. POST Request Handling (Saving Data)
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
            setFlash('success', "Attendance saved successfully!");
        } else {
            setFlash('warning', 'No attendance data to save.');
        }
        
        redirect(BASE_URL . '/teacher/attendance/take.php?date=' . $date . '&class=' . $classId . '&section=' . $sectionId);
    }
}

// 6. Data Fetching for View
// Get students and attendance data
$students = $studentModel->getByClass($classId, $sectionId);
$attendanceData = $attendanceModel->getByDate($selectedDate, $classId, $sectionId);

// Create lookup array
$attendanceLookup = [];
$remarksLookup = [];
$totalPresent = 0;
$totalAbsent = 0;
$totalLate = 0;

foreach ($attendanceData as $record) {
    $attendanceLookup[$record['student_id']] = $record['status'];
    $remarksLookup[$record['student_id']] = $record['remarks'];
    
    if($record['status'] == 'Present') $totalPresent++;
    if($record['status'] == 'Absent') $totalAbsent++;
    if($record['status'] == 'Late') $totalLate++;
}

// 7. Output HTML View
$pageTitle = 'Take Attendance';
require_once __DIR__ . '/../../includes/teacher_header.php';
?>

<style>
    /* Header & Controls */
    .top-controls {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        justify-content: space-between;
        align-items: center;
    }
    
    .page-title h2 { font-weight: 800; color: #2d3748; margin: 0; }
    .page-title p { color: #718096; margin: 0.25rem 0 0 0; }
    
    .date-control {
        display: flex;
        align-items: center;
        background: #f7fafc;
        padding: 0.5rem 1rem;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }
    
    .date-control input {
        border: none;
        background: transparent;
        font-weight: 600;
        color: #4a5568;
        margin-left: 0.5rem;
    }
    
    .date-control input:focus { outline: none; }

    /* Student List */
    .student-list {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    
    .list-header {
        display: grid;
        grid-template-columns: 80px 1fr 2fr 3fr;
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-bottom: 1px solid #edf2f7;
        font-weight: 600;
        color: #718096;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.05em;
    }
    
    .student-row {
        display: grid;
        grid-template-columns: 80px 1fr 2fr 3fr;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f7fafc;
        align-items: center;
        transition: background 0.15s;
    }
    
    .student-row:last-child { border-bottom: none; }
    
    .student-row:hover { background: #fdfdfd; }
    
    .roll { font-weight: 700; color: #a0aec0; }
    
    .student-info { display: flex; align-items: center; gap: 1rem; }
    
    .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        color: white;
        flex-shrink: 0;
    }
    
    .name { font-weight: 700; color: #2d3748; }
    
    /* Interactive Pills */
    .status-selector {
        display: flex;
        background: #edf2f7;
        padding: 4px;
        border-radius: 30px;
        width: fit-content;
    }
    
    .status-option {
        padding: 0.5rem 1.5rem;
        border-radius: 25px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.9rem;
        color: #718096;
        transition: all 0.2s;
        user-select: none;
    }
    
    .status-option:hover { color: #4a5568; }
    
    /* Active States */
    input[type="radio"]:checked + .status-option[data-value="Present"] {
        background: white;
        color: #48bb78;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    input[type="radio"]:checked + .status-option[data-value="Absent"] {
        background: white;
        color: #f56565;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    input[type="radio"]:checked + .status-option[data-value="Late"] {
        background: white;
        color: #ecc94b;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    /* Hidden Radios */
    .start-radio { display: none; }
    
    .remarks-input {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        width: 100%;
        font-size: 0.9rem;
        transition: border-color 0.2s;
    }
    
    .remarks-input:focus {
        border-color: #667eea;
        outline: none;
    }
    
    .floating-save {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 100;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    @media (max-width: 768px) {
        .list-header { display: none; }
        .student-row {
            grid-template-columns: 1fr;
            gap: 1rem;
            text-align: center;
        }
        .student-info { justify-content: center; flex-direction: column; }
        .status-selector { margin: 0 auto; width: 100%; justify-content: space-between; }
        .status-option { padding: 0.75rem 0; flex-grow: 1; text-align: center; }
    }
</style>

<form method="POST" id="attendanceForm">
    <div class="top-controls">
        <div class="page-title">
            <h2>Take Attendance</h2>
            <p><?php echo htmlspecialchars($className); ?> - Section <?php echo htmlspecialchars($sectionName); ?></p>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <div class="date-control">
                <i class="fas fa-calendar text-muted"></i>
                <input type="date" value="<?php echo $selectedDate; ?>" onchange="window.location.href='?class=<?php echo $classId; ?>&section=<?php echo $sectionId; ?>&date='+this.value">
            </div>
            
            <button type="submit" class="btn btn-primary d-none d-md-block">
                <i class="fas fa-save mr-2"></i> Save Changes
            </button>
        </div>
    </div>
    
    <div class="d-flex justify-content-between mb-3 px-2">
        <div>
            <span class="badge badge-success mr-2">Present: <span id="countPresent"><?php echo $totalPresent; ?></span></span>
            <span class="badge badge-danger mr-2">Absent: <span id="countAbsent"><?php echo $totalAbsent; ?></span></span>
            <span class="badge badge-warning">Late: <span id="countLate"><?php echo $totalLate; ?></span></span>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-outline-success" onclick="markAll('Present')">All Present</button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="markAll('Absent')">All Absent</button>
        </div>
    </div>

    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="action" value="mark_attendance">
    <input type="hidden" name="date" value="<?php echo $selectedDate; ?>">

    <?php if (empty($students)): ?>
        <div class="alert alert-info py-5 text-center">
            <i class="fas fa-users-slash fa-3x mb-3 text-muted"></i>
            <p>No students found in this section.</p>
        </div>
    <?php else: ?>
        <div class="student-list">
            <div class="list-header">
                <div>Roll No</div>
                <div>Name</div>
                <div class="text-center">Status</div>
                <div>Remarks</div>
            </div>
            
            <?php foreach ($students as $student): ?>
                <?php 
                    $status = $attendanceLookup[$student['student_id']] ?? 'Present';
                    $remarks = $remarksLookup[$student['student_id']] ?? '';
                    
                    // Avatar Color
                    $hue = crc32($student['name']) % 360;
                    $bg = "hsl($hue, 70%, 60%)";
                ?>
                <div class="student-row">
                    <div class="roll"><?php echo $student['roll_number']; ?></div>
                    <div class="student-info">
                        <div class="avatar" style="background: <?php echo $bg; ?>">
                            <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                        </div>
                        <div class="name"><?php echo htmlspecialchars($student['name']); ?></div>
                    </div>
                    
                    <div class="d-flex justify-content-center">
                        <div class="status-selector">
                            <label>
                                <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" value="Present" class="start-radio" 
                                       <?php echo $status === 'Present' ? 'checked' : ''; ?> onchange="updateCounts()">
                                <div class="status-option" data-value="Present">Present</div>
                            </label>
                            
                            <label>
                                <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" value="Absent" class="start-radio" 
                                       <?php echo $status === 'Absent' ? 'checked' : ''; ?> onchange="updateCounts()">
                                <div class="status-option" data-value="Absent">Absent</div>
                            </label>
                            
                            <label>
                                <input type="radio" name="attendance[<?php echo $student['student_id']; ?>]" value="Late" class="start-radio" 
                                       <?php echo $status === 'Late' ? 'checked' : ''; ?> onchange="updateCounts()">
                                <div class="status-option" data-value="Late">Late</div>
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <input type="text" name="remarks[<?php echo $student['student_id']; ?>]" 
                               class="remarks-input" placeholder="Optional note..." value="<?php echo htmlspecialchars($remarks); ?>">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Mobile Floating Save -->
        <button type="submit" class="btn btn-primary btn-lg rounded-pill floating-save d-md-none">
            <i class="fas fa-check"></i> Save
        </button>
    <?php endif; ?>
</form>

<script>
function markAll(status) {
    const radios = document.querySelectorAll(`input[value="${status}"]`);
    radios.forEach(r => r.click()); // Trigger click to fire change event and update counts
}

function updateCounts() {
    let present = 0, absent = 0, late = 0;
    
    document.querySelectorAll('input.start-radio:checked').forEach(r => {
        if(r.value === 'Present') present++;
        if(r.value === 'Absent') absent++;
        if(r.value === 'Late') late++;
    });
    
    document.getElementById('countPresent').innerText = present;
    document.getElementById('countAbsent').innerText = absent;
    document.getElementById('countLate').innerText = late;
}
</script>

<?php require_once __DIR__ . '/../../includes/teacher_footer.php'; ?>
