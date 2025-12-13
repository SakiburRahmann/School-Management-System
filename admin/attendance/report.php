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

<!-- Modern Report Styles -->
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

/* Report Grid */
.report-container {
    background: white;
    border-radius: 1rem;
    box-shadow: var(--card-shadow);
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.report-header {
    padding: 1.5rem;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
}

.report-table-wrapper {
    overflow-x: auto;
    position: relative;
}

.report-table {
    width: 100%;
    border-collapse: separate; /* Required for sticky to work well with borders */
    border-spacing: 0;
}

.report-table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
    padding: 1rem 0.5rem;
    font-size: 0.85rem;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
}

.report-table td {
    padding: 0.75rem 0.5rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

/* Sticky First Column */
.report-table th:first-child,
.report-table td:first-child {
    position: sticky;
    left: 0;
    background: white;
    z-index: 2;
    border-right: 2px solid #f3f4f6;
    box-shadow: 4px 0 4px -4px rgba(0,0,0,0.1);
    min-width: 200px;
    padding-left: 1.5rem;
}

.report-table th:first-child {
    z-index: 3;
    background: #f9fafb;
}

.status-dot {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    margin: 0 auto;
}

.status-dot.P { background: #dcfce7; color: #166534; } /* Present */
.status-dot.A { background: #fee2e2; color: #991b1b; } /* Absent */
.status-dot.L { background: #fef3c7; color: #92400e; } /* Late */
.status-dot.E { background: #e0f2fe; color: #075985; } /* Excused */
.status-dot.dash { color: #d1d5db; }

.percentage-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.85rem;
}
.percentage-high { background: #dcfce7; color: #166534; }
.percentage-mid { background: #fef3c7; color: #92400e; }
.percentage-low { background: #fee2e2; color: #991b1b; }

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6b7280;
}
</style>

<div class="page-header">
    <div>
        <h3 style="margin:0; font-weight: 700; color: #111827;">Attendance Report</h3>
        <p style="margin: 0.25rem 0 0 0; color: #6b7280;">Monthly comprehensive attendance sheet.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/admin/attendance/" class="btn btn-outline-secondary" 
       style="border-radius: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<!-- Filters -->
<div class="filter-card">
    <form method="GET" class="row g-3">
        <div class="col-md-2">
            <label for="month" class="form-label-modern">Month</label>
            <select id="month" name="month" class="form-select-modern">
                <?php foreach ($months as $num => $name): ?>
                    <option value="<?php echo $num; ?>" <?php echo $selectedMonth == $num ? 'selected' : ''; ?>>
                        <?php echo $name; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="col-md-2">
            <label for="year" class="form-label-modern">Year</label>
            <select id="year" name="year" class="form-select-modern">
                <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $selectedYear == $y ? 'selected' : ''; ?>>
                        <?php echo $y; ?>
                    </option>
                <?php endfor; ?>
            </select>
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
        
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100" 
                    style="padding: 0.75rem; border-radius: 0.5rem; font-weight: 600;">
                <i class="fas fa-search me-2"></i> Report
            </button>
        </div>
    </form>
</div>

<?php if ($selectedClass && $selectedSection): ?>
    <?php if (!empty($students)): ?>
        <div class="report-container">
            <div class="report-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0" style="font-weight: 700; color: #374151;">
                    Attendance Sheet: <?php echo $months[$selectedMonth] . ' ' . $selectedYear; ?>
                </h5>
                <div>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 me-2">P = Present</span>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 me-2">A = Absent</span>
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-10">L = Late</span>
                </div>
            </div>
            
            <div class="report-table-wrapper">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                                <th class="text-center" style="min-width: 36px;"><?php echo $d; ?></th>
                            <?php endfor; ?>
                            <th class="text-center bg-gray-50">Present</th>
                            <th class="text-center bg-gray-50">Absent</th>
                            <th class="text-center bg-gray-50">Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <?php $p = 0; $a = 0; $l = 0; $e = 0; ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($student['name']); ?></div>
                                    <div class="small text-muted">Roll: <?php echo htmlspecialchars($student['roll_number'] ?? '-'); ?></div>
                                </td>
                                
                                <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                                    <?php 
                                    $status = $reportData[$student['student_id']][$d] ?? null;
                                    $letter = '&nbsp;';
                                    $class = 'dash';
                                    
                                    if ($status === 'Present') { $p++; $letter = 'P'; $class = 'P'; }
                                    elseif ($status === 'Absent') { $a++; $letter = 'A'; $class = 'A'; }
                                    elseif ($status === 'Late') { $l++; $letter = 'L'; $class = 'L'; }
                                    elseif ($status === 'Excused') { $e++; $letter = 'E'; $class = 'E'; }
                                    ?>
                                    <td>
                                        <div class="status-dot <?php echo $class; ?>" title="<?php echo $status ?? 'Not Marked'; ?>">
                                            <?php echo $status ? substr($letter, 0, 1) : '-'; ?>
                                        </div>
                                    </td>
                                <?php endfor; ?>
                                
                                <?php 
                                $total = $p + $a + $l + $e;
                                $percentage = $total > 0 ? round(($p / $total) * 100) : 0;
                                $pctClass = $percentage >= 80 ? 'percentage-high' : ($percentage >= 60 ? 'percentage-mid' : 'percentage-low');
                                ?>
                                
                                <td class="text-center fw-bold text-success"><?php echo $p; ?></td>
                                <td class="text-center fw-bold text-danger"><?php echo $a; ?></td>
                                <td class="text-center">
                                    <span class="percentage-badge <?php echo $pctClass; ?>">
                                        <?php echo $percentage; ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-user-graduate fa-3x mb-3" style="color: #e5e7eb;"></i>
            <h4>No students found</h4>
            <p>There are no students enrolled in this section.</p>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="empty-state">
        <div style="background: #eff6ff; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto;">
            <i class="fas fa-chart-pie fa-2x" style="color: #3b82f6;"></i>
        </div>
        <h4 style="color: #111827; font-weight: 700;">Generate Report</h4>
        <p>Select Month, Class, and Section to generate an attendance sheet.</p>
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
