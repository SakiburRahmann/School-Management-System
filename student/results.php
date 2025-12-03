<?php
/**
 * Student - View Results
 */

$pageTitle = 'My Results';
require_once __DIR__ . '/../includes/student_header.php';

$studentId = $currentUser['related_id'];
$examModel = new Exam();
$resultModel = new Result();

// Get all exams
$exams = $examModel->getExamsWithDetails();

// Get selected exam result
$selectedExam = $_GET['exam_id'] ?? '';
$resultSheet = null;

if ($selectedExam) {
    $resultSheet = $resultModel->getResultSheet($studentId, $selectedExam);
}
?>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3>Select Exam</h3>
    </div>
    <div class="card-body">
        <form method="GET" style="display: flex; gap: 1rem;">
            <select name="exam_id" class="form-control" style="max-width: 300px;" required>
                <option value="">Select Exam</option>
                <?php foreach ($exams as $exam): ?>
                    <option value="<?php echo $exam['exam_id']; ?>" 
                            <?php echo $selectedExam == $exam['exam_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($exam['exam_name']); ?> 
                        (<?php echo formatDate($exam['exam_date']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> View Result
            </button>
        </form>
    </div>
</div>

<?php if ($resultSheet): ?>
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3>Result Sheet</h3>
            <button onclick="window.print()" class="btn btn-secondary btn-sm">
                <i class="fas fa-print"></i> Print
            </button>
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
<?php elseif ($selectedExam): ?>
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 3rem;">
            <i class="fas fa-file-alt" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
            <p style="color: #999; font-size: 1.1rem;">Results not published yet for this exam.</p>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/student_footer.php'; ?>
