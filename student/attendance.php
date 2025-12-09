<?php
/**
 * Student - Attendance History
 */

$pageTitle = 'My Attendance';
require_once __DIR__ . '/../includes/student_header.php';

$studentId = $currentUser['related_id'];
$attendanceModel = new Attendance();

// Get date range (default: last 30 days)
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));

// Get attendance history
$attendanceHistory = $attendanceModel->getStudentAttendance($studentId, $startDate, $endDate);

// Get attendance percentage
$attendancePercentage = $attendanceModel->getAttendancePercentage($studentId, $startDate, $endDate);

// Calculate statistics
$totalDays = count($attendanceHistory);
$presentDays = count(array_filter($attendanceHistory, fn($a) => $a['status'] === 'Present'));
$absentDays = count(array_filter($attendanceHistory, fn($a) => $a['status'] === 'Absent'));
$lateDays = count(array_filter($attendanceHistory, fn($a) => $a['status'] === 'Late'));
$excusedDays = count(array_filter($attendanceHistory, fn($a) => $a['status'] === 'Excused'));
?>

<!-- Statistics -->
<div class="stats-grid" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-check"></i>
        </div>
        <div class="stat-value"><?php echo $presentDays; ?></div>
        <div class="stat-label">Present Days</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon danger">
            <i class="fas fa-times"></i>
        </div>
        <div class="stat-value"><?php echo $absentDays; ?></div>
        <div class="stat-label">Absent Days</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-value"><?php echo $lateDays; ?></div>
        <div class="stat-label">Late Days</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-percentage"></i>
        </div>
        <div class="stat-value"><?php echo round($attendancePercentage, 1); ?>%</div>
        <div class="stat-label">Attendance Rate</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> Attendance History</h3>
    </div>
    <div class="card-body">
        <!-- Date Range Filter -->
        <form method="GET" style="margin-bottom: 1.5rem; background: #f8f9fa; padding: 1.5rem; border-radius: 8px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" 
                           value="<?php echo $startDate; ?>">
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" 
                           value="<?php echo $endDate; ?>">
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Attendance Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($attendanceHistory)): ?>
                        <?php foreach ($attendanceHistory as $record): ?>
                            <tr>
                                <td><?php echo date('d M, Y', strtotime($record['date'])); ?></td>
                                <td><?php echo date('l', strtotime($record['date'])); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = match($record['status']) {
                                        'Present' => 'success',
                                        'Absent' => 'danger',
                                        'Late' => 'warning',
                                        'Excused' => 'info',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge badge-<?php echo $badgeClass; ?>">
                                        <?php echo $record['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($record['remarks'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 2rem; color: #999;">
                                No attendance records found for the selected period.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 1.5rem; text-align: center;">
            <p><strong>Total Days:</strong> <?php echo $totalDays; ?></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/student_footer.php'; ?>
