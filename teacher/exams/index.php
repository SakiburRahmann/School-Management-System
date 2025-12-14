<?php
/**
 * Teacher - My Assigned Exams
 * Shows exams where the teacher is assigned as an evaluator
 */

require_once __DIR__ . '/../../config.php';

$pageTitle = 'My Assigned Exams';
require_once __DIR__ . '/../../includes/teacher_header.php';

// Get teacher ID from current user
$teacherId = $currentUser['related_id'];

$examModel = new Exam();

// Get exams assigned to this teacher
$assignedExams = $examModel->getExamsForTeacher($teacherId);

// Separate upcoming and past exams
$upcomingExams = array_filter($assignedExams, fn($e) => strtotime($e['exam_date']) >= strtotime('today'));
$pastExams = array_filter($assignedExams, fn($e) => strtotime($e['exam_date']) < strtotime('today'));

?>

<style>
.exam-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 1px solid #e5e7eb;
}

.stat-card .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.stat-card .stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.25rem;
}

.stat-card .stat-label {
    color: #6b7280;
    font-size: 0.9rem;
}

.btn-icon-split {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-icon-split .icon {
    padding: 0.25rem 0.5rem;
    background: rgba(255,255,255,0.2);
    border-radius: 4px;
}
</style>

<!-- Stats Overview -->
<div class="exam-stats">
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="stat-value"><?php echo count($assignedExams); ?></div>
        <div class="stat-label">Total Assigned Exams</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="stat-value"><?php echo count($upcomingExams); ?></div>
        <div class="stat-label">Upcoming Exams</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-value"><?php echo count($pastExams); ?></div>
        <div class="stat-label">Past Exams</div>
    </div>
</div>

<!-- Upcoming Exams -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3" style="display: flex; justify-content: space-between; align-items: center;">
        <h5 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-calendar-check"></i> Upcoming Exams
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="pl-4">Exam Name</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Total Marks</th>
                        <th class="text-right pr-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($upcomingExams)): ?>
                        <?php foreach ($upcomingExams as $exam): ?>
                            <tr>
                                <td class="pl-4">
                                    <div class="font-weight-bold text-dark"><?php echo htmlspecialchars($exam['exam_name']); ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-light border"><?php echo htmlspecialchars($exam['class_name']); ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?php echo htmlspecialchars($exam['subject_name']); ?></span>
                                </td>
                                <td><?php echo formatDate($exam['exam_date']); ?></td>
                                <td><?php echo $exam['total_marks'] ?? '-'; ?></td>
                                <td class="text-right pr-4">
                                    <a href="<?php echo BASE_URL; ?>/teacher/exams/marks.php?exam_id=<?php echo $exam['exam_id']; ?>" 
                                       class="btn btn-primary btn-sm btn-icon-split shadow-sm">
                                        <span class="icon text-white-50"><i class="fas fa-edit"></i></span>
                                        <span class="text">Enter Marks</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-info-circle"></i> No upcoming exams assigned to you.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Past Exams -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="m-0 font-weight-bold text-secondary">
            <i class="fas fa-history"></i> Past Exams
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="pl-4">Exam Name</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Total Marks</th>
                        <th class="text-right pr-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pastExams)): ?>
                        <?php foreach ($pastExams as $exam): ?>
                            <tr>
                                <td class="pl-4">
                                    <div class="font-weight-bold text-dark"><?php echo htmlspecialchars($exam['exam_name']); ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-light border"><?php echo htmlspecialchars($exam['class_name']); ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?php echo htmlspecialchars($exam['subject_name']); ?></span>
                                </td>
                                <td><?php echo formatDate($exam['exam_date']); ?></td>
                                <td><?php echo $exam['total_marks'] ?? '-'; ?></td>
                                <td class="text-right pr-4">
                                    <a href="<?php echo BASE_URL; ?>/teacher/exams/marks.php?exam_id=<?php echo $exam['exam_id']; ?>" 
                                       class="btn btn-info btn-sm btn-icon-split shadow-sm">
                                        <span class="icon text-white-50"><i class="fas fa-eye"></i></span>
                                        <span class="text">View/Edit Marks</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-info-circle"></i> No past exams.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/teacher_footer.php'; ?>
