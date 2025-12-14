<?php
/**
 * Student - Notices (Modern UI)
 */

$pageTitle = 'Notices';
require_once __DIR__ . '/../includes/student_header.php';

$noticeModel = new Notice();
$notices = $noticeModel->getLatest(20); 
?>

<style>
    /* Hero Section */
    .notices-hero {
        background: linear-gradient(135deg, #48bb78 0%, #38b2ac 100%);
        border-radius: 20px;
        padding: 3rem 2rem;
        color: white;
        margin-bottom: 2.5rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(56, 178, 172, 0.4);
    }
    
    .hero-pattern {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 20%),
                          radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 20%);
    }

    .notices-hero h2 {
        font-weight: 800;
        font-size: 2.25rem;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }

    .notices-hero p {
        font-size: 1.1rem;
        opacity: 0.9;
        max-width: 600px;
        position: relative;
        z-index: 1;
        font-weight: 300;
    }

    /* Grid Layout */
    .notices-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 2rem;
    }

    /* Card Design */
    .notice-card {
        background: white;
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
        position: relative;
    }

    .notice-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
        border-color: #cbd5e0;
    }

    .notice-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: #e2e8f0;
        transition: background 0.3s;
    }

    .notice-card:hover::before {
        background: #48bb78;
    }

    .notice-card.priority-High::before {
        background: #f56565;
    }

    .notice-body {
        padding: 2rem;
        flex-grow: 1;
    }

    .notice-header {
        margin-bottom: 1.5rem;
    }

    .notice-date {
        color: #a0aec0;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .notice-title {
        font-size: 1.4rem;
        font-weight: 800;
        color: #2d3748;
        line-height: 1.3;
        margin: 0;
    }

    .notice-content {
        color: #4a5568;
        line-height: 1.6;
        font-size: 1rem;
    }

    .notice-footer {
        padding: 1rem 2rem;
        background: #f8fafc;
        border-top: 1px solid #edf2f7;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .author-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .author-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #f0fff4;
        color: #38b2ac;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.8rem;
    }

    .author-name {
        font-size: 0.9rem;
        font-weight: 600;
        color: #4a5568;
    }

    .priority-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .priority-High .priority-badge {
        background: #fff5f5;
        color: #c53030;
    }

    .priority-Normal .priority-badge {
        background: #edf2f7;
        color: #718096;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    
    .empty-icon {
        width: 100px;
        height: 100px;
        background: #f7fafc;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        color: #cbd5e0;
        font-size: 3rem;
    }

</style>

<!-- Hero -->
<div class="notices-hero">
    <div class="hero-pattern"></div>
    <h2>School Announcements</h2>
    <p>Important updates, events, and news for students.</p>
</div>

<?php if (empty($notices)): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <i class="far fa-bell-slash"></i>
        </div>
        <h3 style="color: #2d3748; font-weight: 700; margin-bottom: 0.5rem;">No Notices Yet</h3>
        <p style="color: #718096;">Check back later for school announcements.</p>
    </div>
<?php else: ?>
    <div class="notices-grid">
        <?php foreach ($notices as $notice): ?>
            <div class="notice-card priority-<?php echo htmlspecialchars($notice['priority']); ?>">
                <div class="notice-body">
                    <div class="notice-header">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                             <div class="notice-date">
                                <i class="far fa-calendar-alt"></i>
                                <?php echo date('M d, Y', strtotime($notice['created_at'])); ?>
                            </div>
                            <?php if ($notice['priority'] === 'High'): ?>
                                <span class="priority-badge">Urgent</span>
                            <?php else: ?>
                                <span class="priority-badge">General</span>
                            <?php endif; ?>
                        </div>
                        <h3 class="notice-title"><?php echo htmlspecialchars($notice['title']); ?></h3>
                    </div>
                    
                    <div class="notice-content">
                        <?php 
                            // Truncate if too long?
                            $content = htmlspecialchars($notice['content']);
                            echo nl2br($content); 
                        ?>
                    </div>
                </div>
                
                <div class="notice-footer">
                    <div class="author-info">
                        <div class="author-avatar">
                            <?php echo strtoupper(substr($notice['created_by_name'] ?? 'A', 0, 1)); ?>
                        </div>
                        <span class="author-name"><?php echo htmlspecialchars($notice['created_by_name'] ?? 'Admin'); ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/student_footer.php'; ?>
