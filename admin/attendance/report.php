<?php
/**
 * Admin - Attendance Report
 */

$pageTitle = 'Attendance Report';
require_once __DIR__ . '/../../includes/admin_header.php';

$attendanceModel = new Attendance();
$classModel = new ClassModel();
$studentModel = new Student();

$selectedMonth = $_GET['month'] ?? date('m');
$selectedYear = $_GET['year'] ?? date('Y');
$selectedClass = $_GET['class'] ?? '';
$selectedSection = $_GET['section'] ?? '';

// Get classes for filter
$classes = $classModel->findAll('class_name');

// Get report data
$reportData = [];
$students = [];
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);

if ($selectedClass && $selectedSection) {
    $students = $studentModel->getByClass($selectedClass, $selectedSection);
    $attendanceRecords = $attendanceModel->getMonthlyAttendance($selectedClass, $selectedSection, $selectedMonth, $selectedYear);
    
    // Process data for grid
    foreach ($attendanceRecords as $record) {
        $day = (int)date('d', strtotime($record['date']));
        $reportData[$record['student_id']][$day] = $record['status'];
    }
}

// Month names
$months = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3><i class="fas fa-chart-bar"></i> Monthly Attendance Report</h3>
        <a href="<?php echo BASE_URL; ?>/admin/attendance/" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" style="margin-bottom: 1.5rem; background: #f8f9fa; padding: 1.5rem; border-radius: 8px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="month">Month</label>
                    <select id="month" name="month" class="form-control">
                        <?php foreach ($months as $num => $name): ?>
                            <option value="<?php echo $num; ?>" <?php echo $selectedMonth == $num ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="year">Year</label>
                    <select id="year" name="year" class="form-control">
                        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo $selectedYear == $y ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
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
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="section">Section</label>
                    <select id="section" name="section" class="form-control">
                        <option value="">Select Section</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-search"></i> Generate Report
                    </button>
                </div>
            </div>
        </form>
        
        <?php if ($selectedClass && $selectedSection): ?>
            <?php if (!empty($students)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm report-table">
                        <thead>
                            <tr>
                                <th style="min-width: 150px; position: sticky; left: 0; background: #fff; z-index: 2;">Student Name</th>
                                <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                                    <th class="text-center" style="min-width: 30px; font-size: 0.8rem;">
                                        <?php echo $d; ?>
                                    </th>
                                <?php endfor; ?>
                                <th class="text-center bg-light">P</th>
                                <th class="text-center bg-light">A</th>
                                <th class="text-center bg-light">L</th>
                                <th class="text-center bg-light">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <?php 
                                $p = 0; $a = 0; $l = 0; $e = 0;
                                ?>
                                <tr>
                                    <td style="position: sticky; left: 0; background: #fff; z-index: 1;">
                                        <strong><?php echo htmlspecialchars($student['name']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($student['roll_number'] ?? ''); ?></small>
                                    </td>
                                    <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                                        <?php 
                                        $status = $reportData[$student['student_id']][$d] ?? '-';
                                        $class = '';
                                        $text = '';
                                        
                                        if ($status === 'Present') { $p++; $class = 'bg-success-light'; $text = 'P'; }
                                        elseif ($status === 'Absent') { $a++; $class = 'bg-danger-light'; $text = 'A'; }
                                        elseif ($status === 'Late') { $l++; $class = 'bg-warning-light'; $text = 'L'; }
                                        elseif ($status === 'Excused') { $e++; $class = 'bg-info-light'; $text = 'E'; }
                                        ?>
                                        <td class="text-center <?php echo $class; ?>" style="font-size: 0.8rem; padding: 0.25rem;">
                                            <?php echo $text; ?>
                                        </td>
                                    <?php endfor; ?>
                                    
                                    <?php 
                                    $total = $p + $a + $l + $e;
                                    $percentage = $total > 0 ? round(($p / $total) * 100) : 0;
                                    ?>
                                    <td class="text-center bg-light"><strong><?php echo $p; ?></strong></td>
                                    <td class="text-center bg-light"><strong><?php echo $a; ?></strong></td>
                                    <td class="text-center bg-light"><strong><?php echo $l; ?></strong></td>
                                    <td class="text-center bg-light">
                                        <span class="badge badge-<?php echo $percentage >= 75 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger'); ?>">
                                            <?php echo $percentage; ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <span class="badge badge-success">P</span> Present &nbsp;
                        <span class="badge badge-danger">A</span> Absent &nbsp;
                        <span class="badge badge-warning">L</span> Late &nbsp;
                        <span class="badge badge-info">E</span> Excused
                    </small>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No students found in this section.</div>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-chart-area fa-3x mb-3"></i>
                <p>Select criteria to generate report.</p>
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

<style>
.bg-success-light { background-color: #d1fae5; color: #065f46; }
.bg-danger-light { background-color: #fee2e2; color: #991b1b; }
.bg-warning-light { background-color: #fef3c7; color: #92400e; }
.bg-info-light { background-color: #dbeafe; color: #1e40af; }
.report-table th, .report-table td { vertical-align: middle; }
</style>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
