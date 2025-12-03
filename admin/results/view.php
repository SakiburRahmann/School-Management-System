<?php
/**
 * Admin - View Student Result Sheet
 */

$pageTitle = 'Student Result Sheet';
require_once __DIR__ . '/../../includes/admin_header.php';

$resultModel = new Result();
$studentId = $_GET['student_id'] ?? '';
$examId = $_GET['exam_id'] ?? '';

if (!$studentId || !$examId) {
    setFlash('danger', 'Invalid request.');
    redirect(BASE_URL . '/admin/results/');
}

$resultSheet = $resultModel->getResultSheet($studentId, $examId);

if (!$resultSheet) {
    setFlash('warning', 'Results not found for this student.');
    redirect(BASE_URL . '/admin/results/');
}
?>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>Result Sheet</h3>
        <div>
            <button onclick="window.print()" class="btn btn-secondary btn-sm">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="<?php echo BASE_URL; ?>/admin/results/" class="btn btn-primary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Student & Exam Info -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 2px solid var(--light);">
            <div>
                <h4 style="margin: 0 0 0.5rem 0; color: var(--primary);">Student Details</h4>
                <p style="margin: 0;"><strong>Name:</strong> <?php echo htmlspecialchars($resultSheet['info']['name']); ?></p>
                <p style="margin: 0;"><strong>Class:</strong> <?php echo htmlspecialchars($resultSheet['info']['class_name']); ?></p>
                <p style="margin: 0;"><strong>Roll No:</strong> <?php echo htmlspecialchars($resultSheet['info']['roll_number']); ?></p>
            </div>
            <div style="text-align: right;">
                <h4 style="margin: 0 0 0.5rem 0; color: var(--primary);">Exam Details</h4>
                <p style="margin: 0;"><strong>Exam:</strong> <?php echo htmlspecialchars($resultSheet['info']['exam_name']); ?></p>
                <p style="margin: 0;"><strong>Date:</strong> <?php echo formatDate($resultSheet['info']['exam_date']); ?></p>
            </div>
        </div>
        
        <!-- Marks Table -->
        <div class="table-responsive" style="margin-bottom: 2rem;">
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Code</th>
                        <th style="text-align: center;">Total Marks</th>
                        <th style="text-align: center;">Obtained</th>
                        <th style="text-align: center;">Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultSheet['results'] as $result): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($result['subject_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($result['subject_code']); ?></td>
                            <td style="text-align: center;"><?php echo $result['total_marks']; ?></td>
                            <td style="text-align: center;"><?php echo $result['marks']; ?></td>
                            <td style="text-align: center;">
                                <span class="badge badge-primary"><?php echo $result['grade']; ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="background: var(--light); font-weight: bold;">
                        <td colspan="2">Total</td>
                        <td style="text-align: center;"><?php echo $resultSheet['total_marks']; ?></td>
                        <td style="text-align: center;"><?php echo $resultSheet['total_obtained']; ?></td>
                        <td style="text-align: center;">
                            <?php echo $resultSheet['percentage']; ?>%
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Final Result -->
        <div style="text-align: center; padding: 1.5rem; background: var(--light); border-radius: 10px;">
            <h2 style="margin: 0 0 0.5rem 0; color: var(--primary);">
                Overall Grade: <?php echo $resultSheet['grade']; ?>
            </h2>
            <p style="margin: 0; font-size: 1.1rem;">
                Result Status: 
                <span style="color: <?php echo $resultSheet['grade'] === 'F' ? 'var(--danger)' : 'var(--success)'; ?>; font-weight: bold;">
                    <?php echo $resultSheet['grade'] === 'F' ? 'FAILED' : 'PASSED'; ?>
                </span>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
