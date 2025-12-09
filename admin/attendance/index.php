<?php
/**
 * Admin - Attendance Dashboard
 */

$pageTitle = 'Attendance Dashboard';
require_once __DIR__ . '/../../includes/admin_header.php';

$attendanceModel = new Attendance();
$todayOverview = $attendanceModel->getTodayOverview();
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-dark mb-0"><i class="fas fa-calendar-alt me-2"></i>Attendance Dashboard</h3>
    </div>

    <!-- Today's Overview -->
    <div class="row mb-5">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase text-muted small fw-bold mb-1">Present</div>
                            <h2 class="text-dark mb-0 fw-bold"><?php echo $todayOverview['present'] ?? 0; ?></h2>
                        </div>
                        <div class="bg-success text-white p-3 rounded-circle shadow-sm">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase text-muted small fw-bold mb-1">Absent</div>
                            <h2 class="text-dark mb-0 fw-bold"><?php echo $todayOverview['absent'] ?? 0; ?></h2>
                        </div>
                        <div class="bg-danger text-white p-3 rounded-circle shadow-sm">
                            <i class="fas fa-times-circle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase text-muted small fw-bold mb-1">Late</div>
                            <h2 class="text-dark mb-0 fw-bold"><?php echo $todayOverview['late'] ?? 0; ?></h2>
                        </div>
                        <div class="bg-warning text-dark p-3 rounded-circle shadow-sm">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase text-muted small fw-bold mb-1">Rate</div>
                            <h2 class="text-dark mb-0 fw-bold">
                                <?php 
                                $total = $todayOverview['total'] ?? 0;
                                $present = $todayOverview['present'] ?? 0;
                                echo $total > 0 ? round(($present / $total) * 100, 1) : 0; 
                                ?>%
                            </h2>
                        </div>
                        <div class="bg-primary text-white p-3 rounded-circle shadow-sm">
                            <i class="fas fa-chart-pie fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h5 class="mb-4 text-dark fw-bold border-bottom pb-2">Quick Actions</h5>
            <div class="d-flex gap-3 flex-wrap">
                <a href="<?php echo BASE_URL; ?>/admin/attendance/take.php" class="btn btn-primary btn-lg shadow-sm px-4 py-3 d-flex align-items-center">
                    <i class="fas fa-calendar-check fa-lg me-2"></i> 
                    <span>Mark Class Attendance</span>
                </a>
                
                <a href="<?php echo BASE_URL; ?>/admin/attendance/view.php" class="btn btn-info btn-lg shadow-sm px-4 py-3 text-white d-flex align-items-center">
                    <i class="fas fa-search fa-lg me-2"></i> 
                    <span>View & Edit Records</span>
                </a>
                
                <a href="<?php echo BASE_URL; ?>/admin/attendance/report.php" class="btn btn-secondary btn-lg shadow-sm px-4 py-3 d-flex align-items-center">
                    <i class="fas fa-file-alt fa-lg me-2"></i> 
                    <span>Generate Reports</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
