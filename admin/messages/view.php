<?php
/**
 * Admin - View Message Details
 */

require_once __DIR__ . '/../../config.php';
requireRole('Admin');

$messageId = $_GET['id'] ?? null;

if (!$messageId) {
    setFlash('danger', 'Invalid message ID.');
    redirect(BASE_URL . '/admin/messages/');
}

$messageModel = new ContactMessage();
$message = $messageModel->find($messageId);

if (!$message) {
    setFlash('danger', 'Message not found.');
    redirect(BASE_URL . '/admin/messages/');
}

// Mark as read automatically if currently unread
if ($message['is_read'] == 0) {
    $messageModel->markAsRead($messageId);
    $message['is_read'] = 1; // Update local state
}

// Handle Actions
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'delete') {
        if ($messageModel->delete($messageId)) {
            setFlash('success', 'Message deleted successfully!');
            redirect(BASE_URL . '/admin/messages/');
        } else {
            setFlash('danger', 'Failed to delete message.');
        }
    } elseif ($_GET['action'] === 'unread') {
        if ($messageModel->markAsUnread($messageId)) {
            setFlash('success', 'Message marked as unread.');
            redirect(BASE_URL . '/admin/messages/');
        }
    }
}

$pageTitle = 'View Message';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<style>
.message-view-container {
    max-width: 900px;
    margin: 0 auto;
}

.message-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    overflow: hidden;
}

.message-header {
    padding: 2rem;
    background: #f9fafb;
    border-bottom: 2px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
}

.sender-details {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.sender-avatar {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    font-weight: 600;
}

.sender-meta h2 {
    margin: 0 0 0.25rem 0;
    color: #1f2937;
    font-size: 1.25rem;
}

.sender-meta .email {
    color: var(--primary);
    font-weight: 500;
    text-decoration: none;
}

.sender-meta .phone {
    color: #6b7280;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.25rem;
}

.message-date {
    text-align: right;
    color: #6b7280;
    font-size: 0.9rem;
}

.message-date .time {
    font-weight: 600;
    color: #374151;
    font-size: 1.1rem;
    display: block;
    margin-bottom: 0.25rem;
}

.message-body {
    padding: 2.5rem;
}

.subject-line {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.message-content {
    font-size: 1.05rem;
    line-height: 1.8;
    color: #4b5563;
    white-space: pre-wrap;
    min-height: 200px;
}

.message-footer {
    padding: 1.5rem 2rem;
    background: #f9fafb;
    border-top: 2px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn-action-view {
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
}

.btn-reply {
    background: var(--primary);
    color: white;
}

.btn-reply:hover {
    background: var(--secondary);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.btn-outline {
    background: white;
    border: 2px solid #e5e7eb;
    color: #6b7280;
}

.btn-outline:hover {
    border-color: #d1d5db;
    background: #f3f4f6;
    color: #374151;
    text-decoration: none;
}

.btn-outline.delete:hover {
    border-color: #fee2e2;
    background: #fef2f2;
    color: #dc2626;
}

@media (max-width: 600px) {
    .message-header {
        flex-direction: column;
    }
    .message-date {
        text-align: left;
    }
    .sender-details {
        flex-direction: column;
        align-items: flex-start;
    }
    .sender-avatar {
        width: 48px;
        height: 48px;
        font-size: 1.25rem;
    }
    .message-footer {
        flex-direction: column-reverse;
        gap: 1rem;
    }
    .btn-action-view {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="message-view-container">
    <div style="margin-bottom: 1.5rem;">
        <a href="<?php echo BASE_URL; ?>/admin/messages/" class="btn-action-view btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Messages
        </a>
    </div>

    <div class="message-card">
        <div class="message-header">
            <div class="sender-details">
                <div class="sender-avatar">
                    <?php echo strtoupper(substr($message['name'], 0, 1)); ?>
                </div>
                <div class="sender-meta">
                    <h2><?php echo htmlspecialchars($message['name']); ?></h2>
                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="email">
                        <?php echo htmlspecialchars($message['email']); ?>
                    </a>
                    <?php if (!empty($message['phone'])): ?>
                        <div class="phone">
                            <i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($message['phone']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="message-date">
                <span class="time"><?php echo date('h:i A', strtotime($message['created_at'])); ?></span>
                <span><?php echo date('l, F j, Y', strtotime($message['created_at'])); ?></span>
            </div>
        </div>
        
        <div class="message-body">
            <div class="subject-line">
                <?php echo htmlspecialchars($message['subject'] ?: '(No Subject)'); ?>
            </div>
            
            <div class="message-content">
                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
            </div>
        </div>
        
        <div class="message-footer">
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="?id=<?php echo $messageId; ?>&action=unread" class="btn-action-view btn-outline">
                    <i class="fas fa-envelope"></i> Mark as Unread
                </a>
                <a href="?id=<?php echo $messageId; ?>&action=delete" 
                   class="btn-action-view btn-outline delete"
                   onclick="return confirm('Are you sure you want to delete this message?');">
                    <i class="fas fa-trash"></i> Delete
                </a>
            </div>
            
            <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo urlencode($message['subject']); ?>" class="btn-action-view btn-reply">
                <i class="fas fa-reply"></i> Reply via Email
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
