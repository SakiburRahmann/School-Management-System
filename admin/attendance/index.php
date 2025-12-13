<?php
/**
 * Admin - Attendance Dashboard
 */

$pageTitle = 'Attendance Dashboard';
require_once __DIR__ . '/../../includes/admin_header.php';

$attendanceModel = new Attendance();
$todayOverview = $attendanceModel->getTodayOverview();
?>

<style>
/* Modern Dashboard Styling */
:root {
    --primary-soft: #eef2ff;
    --primary-border: #c7d2fe;
    --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    --hover-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
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

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

.stat-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: var(--card-shadow);
    transition: transform 0.2s, box-shadow 0.2s;
    border: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--hover-shadow);
}

.stat-content h2 {
    font-size: 2rem;
    font-weight: 800;
    margin: 0;
    color: #1f2937;
    line-height: 1.2;
}

.stat-content .label {
    color: #6b7280;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

/* Color schemes for stats */
.stat-present .stat-icon { background: #dcfce7; color: #166534; }
.stat-present:hover { border-color: #bbf7d0; }

.stat-absent .stat-icon { background: #fee2e2; color: #991b1b; }
.stat-absent:hover { border-color: #fecaca; }

.stat-late .stat-icon { background: #fef3c7; color: #92400e; }
.stat-late:hover { border-color: #fde68a; }

.stat-rate .stat-icon { background: #e0f2fe; color: #075985; }
.stat-rate:hover { border-color: #bae6fd; }

/* Quick Actions Cards */
.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.action-card-link {
    text-decoration: none;
    color: inherit;
    display: block;
    height: 100%;
}

.action-card {
    background: white;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: var(--card-shadow);
    transition: all 0.2s;
    border: 1px solid #f3f4f6;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.action-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--hover-shadow);
    border-color: #e5e7eb;
}

.action-icon-circle {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    margin-bottom: 1.25rem;
    transition: transform 0.2s;
}

.action-card:hover .action-icon-circle {
    transform: scale(1.1);
}

.action-card h4 {
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
}

.action-card p {
    color: #6b7280;
    font-size: 0.95rem;
    margin: 0;
}

/* Action variations */
.action-take .action-icon-circle { background: #eff6ff; color: #2563eb; }
.action-view .action-icon-circle { background: #f0fdf4; color: #16a34a; }
.action-report .action-icon-circle { background: #f5f3ff; color: #7c3aed; }

</style>

<div class="page-header">
    <div>
        <h3 style="margin:0; font-weight: 700; color: #111827;">Attendance Dashboard</h3>
        <p style="margin: 0.25rem 0 0 0; color: #6b7280;">Overview of today's attendance and quick actions.</p>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card stat-present">
        <div class="stat-content">
            <div class="label">Present Today</div>
            <h2><?php echo $todayOverview['present'] ?? 0; ?></h2>
        </div>
        <div class="stat-icon">
            <i class="fas fa-user-check"></i>
        </div>
    </div>

    <div class="stat-card stat-absent">
        <div class="stat-content">
            <div class="label">Absent Today</div>
            <h2><?php echo $todayOverview['absent'] ?? 0; ?></h2>
        </div>
        <div class="stat-icon">
            <i class="fas fa-user-times"></i>
        </div>
    </div>

    <div class="stat-card stat-late">
        <div class="stat-content">
            <div class="label">Late Arrivals</div>
            <h2><?php echo $todayOverview['late'] ?? 0; ?></h2>
        </div>
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
    </div>

    <div class="stat-card stat-rate">
        <div class="stat-content">
            <div class="label">Attendance Rate</div>
            <h2>
                <?php 
                $total = $todayOverview['total'] ?? 0;
                $present = $todayOverview['present'] ?? 0;
                echo $total > 0 ? round(($present / $total) * 100, 1) : 0; 
                ?>%
            </h2>
        </div>
        <div class="stat-icon">
            <i class="fas fa-chart-pie"></i>
        </div>
    </div>
</div>

<h4 style="margin-bottom: 1.5rem; font-weight: 700; color: #374151; padding-left: 0.5rem; border-left: 4px solid #4e73df;">
    Attendance Actions
</h4>

<!-- Actions Grid -->
<div class="actions-grid">
    <!-- Take Attendance -->
    <a href="<?php echo BASE_URL; ?>/admin/attendance/take.php" class="action-card-link">
        <div class="action-card action-take">
            <div class="action-icon-circle">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h4>Take Attendance</h4>
            <p>Mark daily attendance for classes and sections.</p>
        </div>
    </a>

    <!-- View & Edit -->
    <a href="<?php echo BASE_URL; ?>/admin/attendance/view.php" class="action-card-link">
        <div class="action-card action-view">
            <div class="action-icon-circle">
                <i class="fas fa-list-alt"></i>
            </div>
            <h4>View & Edit Records</h4>
            <p>Browse history and correct attendance entries.</p>
        </div>
    </a>

    <!-- Reports -->
    <a href="<?php echo BASE_URL; ?>/admin/attendance/report.php" class="action-card-link">
        <div class="action-card action-report">
            <div class="action-icon-circle">
                <i class="fas fa-file-analytics"></i>
            </div>
            <h4>Generate Reports</h4>
            <p>Export attendance sheets and view monthly analytics.</p>
        </div>
    </a>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
