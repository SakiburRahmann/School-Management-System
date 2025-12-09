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

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3><i class="fas fa-list-alt"></i> View Attendance Records</h3>
        <a href="<?php echo BASE_URL; ?>/admin/attendance/" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <!-- Filters -->
        <form method="GET" class="bg-light p-4 rounded shadow-sm mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="date" class="form-label fw-bold text-muted small text-uppercase">Date</label>
                    <input type="date" id="date" name="date" class="form-control" 
                           value="<?php echo $selectedDate; ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="class" class="form-label fw-bold text-muted small text-uppercase">Class</label>
                    <select id="class" name="class" class="form-select" onchange="loadSectionsForAttendance(this.value)">
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
                    <label for="section" class="form-label fw-bold text-muted small text-uppercase">Section</label>
                    <select id="section" name="section" class="form-select">
                        <option value="">Select Section</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i> View Records
                    </button>
                </div>
            </div>
        </form>
        
        <?php if ($selectedClass && $selectedSection): ?>
            <!-- Stats Summary -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="alert alert-success text-center mb-0">
                        <strong>Present:</strong> <?php echo $stats['Present']; ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-danger text-center mb-0">
                        <strong>Absent:</strong> <?php echo $stats['Absent']; ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning text-center mb-0">
                        <strong>Late:</strong> <?php echo $stats['Late']; ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-info text-center mb-0">
                        <strong>Excused:</strong> <?php echo $stats['Excused']; ?>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($attendanceData)): ?>
                <div class="d-flex justify-content-end mb-3">
                    <a href="<?php echo BASE_URL; ?>/admin/attendance/take.php?date=<?php echo $selectedDate; ?>&class=<?php echo $selectedClass; ?>&section=<?php echo $selectedSection; ?>" 
                       class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Attendance
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Student Name</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceData as $record): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($record['roll_number'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo match($record['status']) {
                                                'Present' => 'success',
                                                'Absent' => 'danger',
                                                'Late' => 'warning',
                                                'Excused' => 'info',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo htmlspecialchars($record['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($record['remarks'] ?? '-'); ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?php 
                                            if (!empty($record['updated_at'])) {
                                                echo date('M d, H:i', strtotime($record['updated_at'])); 
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="text-muted">No attendance records found for this date.</p>
                    <a href="<?php echo BASE_URL; ?>/admin/attendance/take.php?date=<?php echo $selectedDate; ?>&class=<?php echo $selectedClass; ?>&section=<?php echo $selectedSection; ?>" 
                       class="btn btn-primary">
                        <i class="fas fa-plus"></i> Mark Attendance Now
                    </a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-filter fa-3x mb-3"></i>
                <p>Please select Class and Section to view records.</p>
            </div>
        <?php endif; ?>
    </div>
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
        });
}

<?php if ($selectedClass): ?>
    loadSectionsForAttendance('<?php echo $selectedClass; ?>');
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
