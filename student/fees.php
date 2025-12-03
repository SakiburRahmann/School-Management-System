<?php
/**
 * Student - Fee History
 */

$pageTitle = 'My Fees';
require_once __DIR__ . '/../includes/student_header.php';

$studentId = $currentUser['related_id'];
$feeModel = new Fee();

// Get all fees
$allFees = $feeModel->getStudentFees($studentId);
$unpaidFees = $feeModel->getStudentFees($studentId, 'Unpaid');
$paidFees = $feeModel->getStudentFees($studentId, 'Paid');

// Calculate totals
$totalUnpaid = array_sum(array_column($unpaidFees, 'amount'));
$totalPaid = array_sum(array_column($paidFees, 'amount'));
?>

<!-- Statistics -->
<div class="stats-grid" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-icon danger">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div class="stat-value">$<?php echo number_format($totalUnpaid, 2); ?></div>
        <div class="stat-label">Total Unpaid</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-value">$<?php echo number_format($totalPaid, 2); ?></div>
        <div class="stat-label">Total Paid</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="stat-value"><?php echo count($unpaidFees); ?></div>
        <div class="stat-label">Unpaid Invoices</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-value"><?php echo count($paidFees); ?></div>
        <div class="stat-label">Paid Invoices</div>
    </div>
</div>

<!-- Unpaid Fees -->
<?php if (!empty($unpaidFees)): ?>
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3>Unpaid Fees</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Invoice Date</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Remarks</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unpaidFees as $fee): ?>
                        <tr>
                            <td><?php echo formatDate($fee['created_at']); ?></td>
                            <td><strong>$<?php echo number_format($fee['amount'], 2); ?></strong></td>
                            <td>
                                <?php 
                                $dueDate = strtotime($fee['due_date']);
                                $isOverdue = $dueDate < time();
                                ?>
                                <span style="color: <?php echo $isOverdue ? 'var(--danger)' : 'inherit'; ?>">
                                    <?php echo formatDate($fee['due_date']); ?>
                                    <?php if ($isOverdue): ?>
                                        <i class="fas fa-exclamation-triangle" title="Overdue"></i>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($fee['remarks'] ?? '-'); ?></td>
                            <td>
                                <span class="badge badge-danger">Unpaid</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Payment History -->
<div class="card">
    <div class="card-header">
        <h3>Payment History</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Invoice Date</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Payment Date</th>
                        <th>Payment Method</th>
                        <th>Remarks</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($paidFees)): ?>
                        <?php foreach ($paidFees as $fee): ?>
                            <tr>
                                <td><?php echo formatDate($fee['created_at']); ?></td>
                                <td><strong>$<?php echo number_format($fee['amount'], 2); ?></strong></td>
                                <td><?php echo formatDate($fee['due_date']); ?></td>
                                <td><?php echo formatDate($fee['payment_date']); ?></td>
                                <td><?php echo htmlspecialchars($fee['payment_method'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($fee['remarks'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge badge-success">Paid</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                No payment history found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/student_footer.php'; ?>
