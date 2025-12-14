<?php
/**
 * Teacher - View Student Profile
 * Detailed profile for a student in the teacher's section
 */

$pageTitle = 'Student Profile';
require_once __DIR__ . '/../../includes/teacher_header.php';

// Get teacher ID from current user
$currentUser = (new User())->getUserWithRelated(getUserId());
$teacherId = $currentUser['related_id'];

$teacherModel = new Teacher();
$studentModel = new Student();
$attendanceModel = new Attendance();
$resultModel = new Result();
$examModel = new Exam();

// Get Student ID
$studentId = $_GET['id'] ?? null;
if (!$studentId) {
    setFlash('danger', 'Invalid Student ID');
    redirect(BASE_URL . '/teacher/dashboard.php');
}

// Get Student Details
$student = $studentModel->getStudentDetails($studentId);
if (!$student) {
    setFlash('danger', 'Student not found');
    redirect(BASE_URL . '/teacher/dashboard.php');
}

// VERIFY ACCESS: Is the teacher the Class Teacher of this student's section?
$allowedSections = $teacherModel->getClassTeacherSections($teacherId);
$isAllowed = false;
foreach ($allowedSections as $sec) {
    if ($sec['section_id'] == $student['section_id']) {
        $isAllowed = true;
        break;
    }
}

if (!$isAllowed) {
    setFlash('danger', 'Access Denied: You are not the Class Teacher for this student.');
    redirect(BASE_URL . '/teacher/dashboard.php');
}

// Fetch Additional Data
// 1. Attendance Overview (Current Month)
$attendanceStats = $attendanceModel->getStudentAttendance($studentId, date('Y-m-01'), date('Y-m-d'));
$totalDays = count($attendanceStats);
$presentDays = 0;
foreach ($attendanceStats as $att) {
    if ($att['status'] === 'Present') $presentDays++;
}
$attendancePercentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 0;

// 2. Exam Results
$classExams = $examModel->getByClass($student['class_id']);
$examResults = [];
foreach ($classExams as $exam) {
    if ($resultModel->hasResults($studentId, $exam['exam_id'])) {
        $sheet = $resultModel->getResultSheet($studentId, $exam['exam_id']);
        if ($sheet) {
            $examResults[] = [
                'exam_name' => $exam['exam_name'],
                'date' => $exam['exam_date'],
                'grade' => $sheet['grade'] ?? 'N/A',
                'percentage' => $sheet['percentage'] ?? 0,
                'total_obtained' => $sheet['total_obtained'],
                'total_marks' => $sheet['total_marks']
            ];
        }
    }
}

// Color Generation for Avatar
$hue = crc32($student['name']) % 360;
$avatarColor = "hsl($hue, 70%, 60%)";
$darkerColor = "hsl($hue, 60%, 40%)";
?>

<style>
    /* Profile Header */
    .profile-header {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .profile-cover {
        height: 150px;
        background: linear-gradient(135deg, <?php echo $avatarColor; ?> 0%, <?php echo $darkerColor; ?> 100%);
    }
    
    .profile-content {
        padding: 0 2rem 2rem;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    @media (min-width: 768px) {
        .profile-content {
            flex-direction: row;
            align-items: flex-end;
            text-align: left;
        }
        .profile-text {
            margin-left: 2rem;
            margin-bottom: 0.5rem;
            flex-grow: 1;
        }
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: white;
        border: 5px solid white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-top: -60px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: bold;
        color: <?php echo $avatarColor; ?>;
    }
    
    .profile-name {
        margin-top: 1rem;
        text-align: center;
    }
    
    @media (min-width: 768px) {
        .profile-name { text-align: left; margin-top: 0; }
    }
    
    .profile-name h1 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 800;
        color: #1a202c;
    }
    
    .profile-meta {
        color: #718096;
        margin-top: 0.25rem;
        font-size: 1rem;
    }
    
    /* Tabs */
    .custom-tabs {
        display: flex;
        gap: 1rem;
        border-bottom: 2px solid #edf2f7;
        margin-bottom: 2rem;
        overflow-x: auto;
    }
    
    .tab-btn {
        padding: 1rem 1.5rem;
        background: transparent;
        border: none;
        font-weight: 600;
        color: #718096;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        transition: all 0.2s;
        white-space: nowrap;
    }
    
    .tab-btn:hover {
        color: #4a5568;
    }
    
    .tab-btn.active {
        color: #667eea;
        border-bottom-color: #667eea;
    }
    
    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        border: 1px solid #edf2f7;
    }
    
    .stat-label { color: #718096; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
    .stat-value { font-size: 1.8rem; font-weight: 700; color: #2d3748; }
    
    /* Info Cards */
    .info-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        border: 1px solid #edf2f7;
        margin-bottom: 1.5rem;
    }
    
    .card-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #edf2f7;
    }
    
    .data-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f7fafc;
    }
    
    .data-row:last-child { border-bottom: none; }
    .data-label { color: #718096; font-weight: 500; }
    .data-val { color: #2d3748; font-weight: 600; text-align: right; }

    .tab-content { display: none; }
    .tab-content.active { display: block; animation: fadeIn 0.3s ease; }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Result Badge */
    .grade-badge {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: white;
        background: #cbd5e0;
    }
    .grade-A-plus, .grade-A { background: #48bb78; }
    .grade-B { background: #4299e1; }
    .grade-C { background: #ecc94b; }
    .grade-F { background: #f56565; }
</style>

<!-- Profile Header -->
<div class="profile-header">
    <div class="profile-cover"></div>
    <div class="profile-content">
        <div class="profile-avatar">
            <?php 
                $initials = strtoupper(substr($student['name'], 0, 1));
                if (strpos($student['name'], ' ') !== false) {
                    $names = explode(' ', $student['name']);
                    $initials .= strtoupper(substr(end($names), 0, 1));
                }
                echo $initials;
            ?>
        </div>
        <div class="profile-text profile-name">
            <h1><?php echo htmlspecialchars($student['name']); ?></h1>
            <div class="profile-meta">
                Class <?php echo htmlspecialchars($student['class_name'] ?? ''); ?> - Section <?php echo htmlspecialchars($student['section_name'] ?? ''); ?> | Roll: #<?php echo $student['roll_number'] ?? 'N/A'; ?>
            </div>
        </div>
        <div>
            <a href="mailto:<?php echo htmlspecialchars($student['email'] ?? ''); ?>" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-envelope"></i> Contact
            </a>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="custom-tabs">
    <button class="tab-btn active" onclick="switchTab('overview')">Overview</button>
    <button class="tab-btn" onclick="switchTab('attendance')">Attendance</button>
    <button class="tab-btn" onclick="switchTab('academics')">Academics</button>
</div>

<!-- Overview Tab -->
<div id="overview" class="tab-content active">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Attendance (Monthly)</div>
            <div class="stat-value" style="color: <?php echo $attendancePercentage >= 75 ? '#48bb78' : ($attendancePercentage >= 50 ? '#ecc94b' : '#f56565'); ?>">
                <?php echo $attendancePercentage; ?>%
            </div>
            <div style="font-size: 0.8rem; color: #a0aec0;"><?php echo $presentDays; ?> of <?php echo $totalDays; ?> days present</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-label">Exams Taken</div>
            <div class="stat-value"><?php echo count($examResults); ?></div>
        </div>
        
        <div class="stat-card">
            <div class="stat-label">Status</div>
            <div class="stat-value" style="font-size: 1.2rem; margin-top: 0.5rem;">
                <span class="badge badge-success">Active</span>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="info-card">
                <h3 class="card-title"><i class="fas fa-user mb-2 mr-2 text-primary"></i> Personal Details</h3>
                <div class="data-row">
                    <span class="data-label">Student ID</span>
                    <span class="data-val"><?php echo $student['student_id_custom'] ?? '-'; ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Date of Birth</span>
                    <span class="data-val"><?php echo formatDate($student['date_of_birth'] ?? ''); ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Gender</span>
                    <span class="data-val"><?php echo $student['gender'] ?? '-'; ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Blood Group</span>
                    <span class="data-val"><?php echo $student['blood_group'] ?? 'N/A'; ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Religion</span>
                    <span class="data-val"><?php echo $student['religion'] ?? 'N/A'; ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Address</span>
                    <span class="data-val" style="max-width: 200px;"><?php echo htmlspecialchars($student['address'] ?? 'N/A'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="info-card">
                <h3 class="card-title"><i class="fas fa-users mb-2 mr-2 text-info"></i> Guardian Information</h3>
                <div class="data-row">
                    <span class="data-label">Guardian Name</span>
                    <span class="data-val"><?php echo htmlspecialchars($student['guardian_name'] ?? '-'); ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Phone</span>
                    <span class="data-val">
                        <?php if (!empty($student['guardian_phone'])): ?>
                            <a href="tel:<?php echo $student['guardian_phone']; ?>"><?php echo $student['guardian_phone']; ?></a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </span>
                </div>
                <div class="data-row">
                    <span class="data-label">Email</span>
                    <span class="data-val"><?php echo htmlspecialchars($student['guardian_email'] ?? 'N/A'); ?></span>
                </div>
                <div class="data-row">
                    <span class="data-label">Relationship</span>
                    <span class="data-val"><?php echo htmlspecialchars($student['relationship'] ?? 'Guardian'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Tab -->
<div id="attendance" class="tab-content">
    <div class="info-card">
        <h3 class="card-title">Recent Attendance</h3>
        <?php if (empty($attendanceStats)): ?>
            <p class="text-muted text-center py-4">No attendance records found for this month.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($attendanceStats) as $att): ?>
                            <tr>
                                <td><?php echo formatDate($att['date']); ?></td>
                                <td>
                                    <?php if ($att['status'] === 'Present'): ?>
                                        <span class="badge badge-success">Present</span>
                                    <?php elseif ($att['status'] === 'Absent'): ?>
                                        <span class="badge badge-danger">Absent</span>
                                    <?php elseif ($att['status'] === 'Late'): ?>
                                        <span class="badge badge-warning">Late</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><?php echo $att['status']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($att['remarks'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Academics Tab -->
<div id="academics" class="tab-content">
    <?php if (empty($examResults)): ?>
        <div class="empty-state text-center py-5">
            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
            <p class="text-muted">No exam results found for this student yet.</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($examResults as $res): ?>
                <div class="col-md-6 mb-4">
                    <div class="info-card h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h4 class="mb-1" style="font-weight: 700; color: #2d3748;"><?php echo htmlspecialchars($res['exam_name']); ?></h4>
                                <small class="text-muted"><i class="far fa-calendar-alt"></i> <?php echo formatDate($res['date']); ?></small>
                            </div>
                            <?php 
                                $bgClass = 'grade-badge';
                                $g = preg_replace('/[^A-Z]/', '', $res['grade']);
                                if ($g === 'A') $bgClass .= ' grade-A';
                                elseif ($g === 'B') $bgClass .= ' grade-B';
                                elseif ($g === 'C') $bgClass .= ' grade-C';
                                elseif ($g === 'F') $bgClass .= ' grade-F';
                            ?>
                            <div class="<?php echo $bgClass; ?>">
                                <?php echo $res['grade']; ?>
                            </div>
                        </div>
                        
                        <div class="progress mb-3" style="height: 10px; border-radius: 5px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $res['percentage']; ?>%"></div>
                        </div>
                        
                        <div class="d-flex justify-content-between text-sm font-weight-bold">
                            <span>Score: <?php echo $res['total_obtained']; ?> / <?php echo $res['total_marks']; ?></span>
                            <span><?php echo $res['percentage']; ?>%</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function switchTab(tabId) {
    // Buttons
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    event.currentTarget.classList.add('active');
    
    // Content
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
}
</script>

<?php require_once __DIR__ . '/../../includes/teacher_footer.php'; ?>
