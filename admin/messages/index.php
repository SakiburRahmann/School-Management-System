<?php
/**
 * Admin - Messages Management
 * Modern dashboard for managing contact form submissions
 */

require_once __DIR__ . '/../../config.php';
requireRole('Admin');

$messageModel = new ContactMessage();

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $messageId = intval($_GET['id']);
    
    if ($_GET['action'] === 'delete') {
        if ($messageModel->delete($messageId)) {
            setFlash('success', 'Message deleted successfully!');
        } else {
            setFlash('danger', 'Failed to delete message.');
        }
    } elseif ($_GET['action'] === 'toggle_read') {
        $msg = $messageModel->find($messageId);
        if ($msg) {
            $newStatus = $msg['is_read'] ? 0 : 1;
            $result = $newStatus ? $messageModel->markAsRead($messageId) : $messageModel->markAsUnread($messageId);
            if ($result) {
                setFlash('success', 'Message status updated!');
            }
        }
    }
    
    redirect(BASE_URL . '/admin/messages/');
}

// Get filter parameters
$searchQuery = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Get messages based on filters
if ($searchQuery) {
    $messages = $messageModel->search($searchQuery, $statusFilter);
} elseif ($statusFilter !== '' || $dateFrom || $dateTo) {
    $messages = $messageModel->getFiltered(
        $statusFilter,
        $dateFrom ?: null,
        $dateTo ?: null
    );
} else {
    $messages = $messageModel->getAllMessages();
}

// Get statistics
$stats = $messageModel->getStatistics();

$pageTitle = 'Messages';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<style>
/* Modern Messages Dashboard Styles */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    padding: 1.5rem;
    border-radius: 16px;
    color: white;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    pointer-events: none;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.stat-card.unread {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.stat-card.read {
    background: linear-gradient(135deg, #10b981, #059669);
}

.stat-card.today {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.stat-card.total {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.stat-icon {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    opacity: 0.9;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0.5rem 0;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.95;
    font-weight: 500;
    letter-spacing: 0.5px;
}

.filters-section {
    background: white;
    padding: 1.5rem;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
}

.filters-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-group label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #4b5563;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-input,
.filter-select {
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.3s;
    background: #fafafa;
}

.filter-input:focus,
.filter-select:focus {
    outline: none;
    border-color: var(--primary);
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.btn-filter {
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    height: fit-content;
}

.btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.btn-clear {
    padding: 0.75rem 1.5rem;
    background: #6b7280;
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
    height: fit-content;
}

.btn-clear:hover {
    background: #4b5563;
    color: white;
    text-decoration: none;
}

.messages-table {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
}

.messages-table table {
    width: 100%;
    border-collapse: collapse;
}

.messages-table thead {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.messages-table th {
    padding: 1.25rem 1rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.messages-table td {
    padding: 1.25rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    vertical-align: middle;
}

.messages-table tbody tr {
    transition: background 0.2s;
}

.messages-table tbody tr:hover {
    background: #f9fafb;
}

.messages-table tbody tr.unread {
    background: #f0fdf4; /* Very light green hint for unread, optional */
    font-weight: 600;
}

.messages-table tbody tr.unread td {
    color: #1f2937;
}

.sender-info {
    display: flex;
    flex-direction: column;
}

.sender-name {
    font-size: 1rem;
    color: #1f2937;
}

.sender-email {
    font-size: 0.85rem;
    color: #6b7280;
    font-weight: 400;
}

.status-badge {
    display: inline-block;
    padding: 0.35rem 0.85rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.unread {
    background: #fee2e2;
    color: #991b1b;
}

.status-badge.read {
    background: #d1fae5;
    color: #065f46;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-action {
    padding: 0.5rem;
    width: 35px;
    height: 35px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 0.9rem;
    color: white;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    color: white;
}

.btn-view {
    background: #3b82f6;
}

.btn-read {
    background: #10b981;
}

.btn-unread {
    background: #f59e0b;
}

.btn-delete {
    background: #ef4444;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6b7280;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

@media (max-width: 900px) {
    .filters-grid {
        grid-template-columns: 1fr 1fr;
    }
    .filter-group:first-child {
        grid-column: 1 / -1;
    }
}

@media (max-width: 600px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    .filters-grid {
        grid-template-columns: 1fr;
    }
    .filter-group:first-child {
        grid-column: auto;
    }
}
</style>

<!-- Statistics Dashboard -->
<div class="stats-grid">
    <div class="stat-card total">
        <div class="stat-icon"><i class="fas fa-inbox"></i></div>
        <div class="stat-value"><?php echo $stats['total']; ?></div>
        <div class="stat-label">Total Messages</div>
    </div>
    
    <div class="stat-card unread">
        <div class="stat-icon"><i class="fas fa-envelope"></i></div>
        <div class="stat-value"><?php echo $stats['unread']; ?></div>
        <div class="stat-label">Unread Messages</div>
    </div>
    
    <div class="stat-card read">
        <div class="stat-icon"><i class="fas fa-envelope-open"></i></div>
        <div class="stat-value"><?php echo $stats['read_count']; ?></div>
        <div class="stat-label">Read Messages</div>
    </div>
    
    <div class="stat-card today">
        <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-value"><?php echo $stats['today']; ?></div>
        <div class="stat-label">Received Today</div>
    </div>
</div>

<!-- Filters Section -->
<div class="filters-section">
    <form method="GET" action="">
        <div class="filters-grid">
            <div class="filter-group">
                <label><i class="fas fa-search"></i> Search</label>
                <input type="text" name="search" class="filter-input" placeholder="Search by name, email, or subject..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-filter"></i> Status</label>
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="0" <?php echo $statusFilter === '0' ? 'selected' : ''; ?>>Unread</option>
                    <option value="1" <?php echo $statusFilter === '1' ? 'selected' : ''; ?>>Read</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-calendar"></i> From Date</label>
                <input type="date" name="date_from" class="filter-input" value="<?php echo htmlspecialchars($dateFrom); ?>">
            </div>
            
            <div class="filter-group">
                <label><i class="fas fa-calendar"></i> To Date</label>
                <input type="date" name="date_to" class="filter-input" value="<?php echo htmlspecialchars($dateTo); ?>">
            </div>
            
            <div class="filter-group">
                <label>&nbsp;</label>
                <button type="submit" class="btn-filter">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
            
            <?php if ($searchQuery || $statusFilter !== '' || $dateFrom || $dateTo): ?>
                <div class="filter-group" style="grid-column: span 1;">
                    <label>&nbsp;</label>
                    <a href="<?php echo BASE_URL; ?>/admin/messages/" class="btn-clear">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Messages Table -->
<div class="messages-table">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Sender</th>
                <th>Subject</th>
                <th>Status</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $msg): ?>
                    <tr class="<?php echo $msg['is_read'] ? '' : 'unread'; ?>">
                        <td>
                            <div style="font-size: 0.9rem; color: #4b5563;">
                                <?php echo date('M d, Y', strtotime($msg['created_at'])); ?>
                                <small style="display: block; opacity: 0.7;"><?php echo date('h:i A', strtotime($msg['created_at'])); ?></small>
                            </div>
                        </td>
                        <td>
                            <div class="sender-info">
                                <span class="sender-name"><?php echo htmlspecialchars($msg['name']); ?></span>
                                <span class="sender-email"><?php echo htmlspecialchars($msg['email']); ?></span>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 500; color: #374151;">
                                <?php echo htmlspecialchars(mb_strimwidth($msg['subject'], 0, 40, "...")); ?>
                            </div>
                            <small style="color: #6b7280;">
                                <?php echo htmlspecialchars(mb_strimwidth(strip_tags($msg['message']), 0, 50, "...")); ?>
                            </small>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $msg['is_read'] ? 'read' : 'unread'; ?>">
                                <?php echo $msg['is_read'] ? 'Read' : 'Unread'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons" style="justify-content: center;">
                                <a href="<?php echo BASE_URL; ?>/admin/messages/view.php?id=<?php echo $msg['message_id']; ?>" 
                                   class="btn-action btn-view" 
                                   title="View Message">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <a href="?action=toggle_read&id=<?php echo $msg['message_id']; ?>" 
                                   class="btn-action <?php echo $msg['is_read'] ? 'btn-unread' : 'btn-read'; ?>" 
                                   title="<?php echo $msg['is_read'] ? 'Mark as Unread' : 'Mark as Read'; ?>">
                                    <i class="fas fa-<?php echo $msg['is_read'] ? 'envelope' : 'envelope-open'; ?>"></i>
                                </a>
                                
                                <a href="?action=delete&id=<?php echo $msg['message_id']; ?>" 
                                   class="btn-action btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this message?');"
                                   title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No Messages Found</h3>
                            <p>You haven't received any messages yet, or none match your filters.</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($messages)): ?>
    <div style="margin-top: 1.5rem; text-align: center; color: #6b7280;">
        <strong>Showing <?php echo count($messages); ?> message(s)</strong>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
