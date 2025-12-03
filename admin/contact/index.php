<?php
/**
 * Admin - Contact Messages
 */

$pageTitle = 'Contact Messages';
require_once __DIR__ . '/../../includes/admin_header.php';

$contactModel = new ContactMessage();

// Handle delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    if ($contactModel->delete($_GET['id'])) {
        setFlash('success', 'Message deleted successfully!');
    } else {
        setFlash('danger', 'Failed to delete message.');
    }
    redirect(BASE_URL . '/admin/contact/');
}

// Mark as read if viewing details (simplified for now, just list view)
// In a full implementation, clicking view would mark as read

// Get all messages
$messages = $contactModel->getAll();
?>

<div class="card">
    <div class="card-header">
        <h3>Inbox</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $message): ?>
                            <tr style="<?php echo $message['status'] === 'Unread' ? 'font-weight: bold; background: #f9f9f9;' : ''; ?>">
                                <td><?php echo formatDate($message['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($message['name']); ?></td>
                                <td><?php echo htmlspecialchars($message['email']); ?></td>
                                <td><?php echo htmlspecialchars($message['subject'] ?? '(No Subject)'); ?></td>
                                <td>
                                    <?php echo htmlspecialchars(substr($message['message'], 0, 50)); ?>...
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/admin/contact/?delete=1&id=<?php echo $message['message_id']; ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirmDelete('Delete this message?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">
                                No messages found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
