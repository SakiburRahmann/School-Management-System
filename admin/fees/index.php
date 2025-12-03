<?php
/**
 * Admin - Fee Management
 */

$pageTitle = 'Fee Management';
require_once __DIR__ . '/../../includes/admin_header.php';

$feeModel = new Fee();
$studentModel = new Student();
$classModel = new ClassModel();

// Handle fee invoice creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_invoice') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $studentId = $_POST['student_id'];
        $amount = $_POST['amount'];
        $dueDate = $_POST['due_date'];
        $remarks = sanitize($_POST['remarks']);
        
        if ($studentId && $amount && $dueDate) {
            $feeId = $feeModel->createInvoice($studentId, $amount, $dueDate, $remarks);
            if ($feeId) {
                setFlash('success', 'Fee invoice created successfully!');
            } else {
                setFlash('danger', 'Failed to create invoice.');
            }
        }
    }
    redirect(BASE_URL . '/admin/fees/');
}

// Handle payment
if (isset($_GET['mark_paid']) && isset($_GET['id'])) {
    $feeId = $_GET['id'];
    if ($feeModel->markAsPaid($feeId, 'Cash')) {
        setFlash('success', 'Payment recorded successfully!');
    } else {
        setFlash('danger', 'Failed to record payment.');
    }
    redirect(BASE_URL . '/admin/fees/');
}

// Get fee statistics
$feeStats = $feeModel->getFeeStatistics();

// Get unpaid fees
$unpaidFees = $feeModel->getUnpaidFees();

// Get all students for dropdown
$allStudents = $studentModel->getStudentsWithDetails();
?>

<!-- Statistics -->
<div class="stats-grid" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-value" style="color: var(--success);">
            $<?php echo number_format($feeStats['paid_amount'] ?? 0, 2); ?>
        </div>
        <div class="stat-label">Total Collected</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value" style="color: var(--danger);">
            $<?php echo number_format($feeStats['unpaid_amount'] ?? 0, 2); ?>
        </div>
        <div class="stat-label">Total Pending</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value">
            <?php echo $feeStats['paid_count'] ?? 0; ?>
        </div>
        <div class="stat-label">Paid Invoices</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value">
            <?php echo $feeStats['unpaid_count'] ?? 0; ?>
        </div>
        <div class="stat-label">Unpaid Invoices</div>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3>Create Fee Invoice</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="create_invoice">
            
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="student_id">Student <span style="color: red;">*</span></label>
                    <select id="student_id" name="student_id" class="form-control" required>
                        <option value="">Select Student</option>
                        <?php foreach ($allStudents as $student): ?>
                            <option value="<?php echo $student['student_id']; ?>">
                                <?php echo htmlspecialchars($student['name']); ?> 
                                (<?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?> - 
                                Roll: <?php echo htmlspecialchars($student['roll_number'] ?? 'N/A'); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="amount">Amount ($) <span style="color: red;">*</span></label>
                    <input type="number" id="amount" name="amount" class="form-control" 
                           step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="due_date">Due Date <span style="color: red;">*</span></label>
                    <input type="date" id="due_date" name="due_date" class="form-control" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="remarks">Remarks</label>
                <input type="text" id="remarks" name="remarks" class="form-control" 
                       placeholder="e.g., Tuition Fee - January 2025">
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Invoice
            </button>
        </form>
    </div>
</div>

<!-- Unpaid Fees -->
<div class="card">
    <div class="card-header">
        <h3>Unpaid Fee Invoices</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Remarks</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($unpaidFees)): ?>
                        <?php foreach ($unpaidFees as $fee): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($fee['student_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($fee['class_name'] ?? 'N/A'); ?></td>
                                <td><strong>$<?php echo number_format($fee['amount'], 2); ?></strong></td>
                                <td>
                                    <?php 
                                    $dueDate = strtotime($fee['due_date']);
                                    $isOverdue = $dueDate < time();
                                    ?>
                                    <span style="color: <?php echo $isOverdue ? 'var(--danger)' : 'inherit'; ?>">
                                        <?php echo formatDate($fee['due_date']); ?>
                                        <?php if ($isOverdue): ?>
                                            <i class="fas fa-exclamation-triangle"></i>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($fee['remarks'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo getStatusBadge($fee['status']); ?>">
                                        <?php echo $fee['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/admin/fees/?mark_paid=1&id=<?php echo $fee['fee_id']; ?>" 
                                       class="btn btn-success btn-sm"
                                       onclick="return confirm('Mark this invoice as paid?');">
                                        <i class="fas fa-check"></i> Mark Paid
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                No unpaid fees found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
