<?php
/**
 * Admin - Website Content Management
 * Manage content for all public pages
 */

require_once __DIR__ . '/../../config.php';
requireRole('Admin');

$contentModel = new WebsiteContent();

// Define available pages with their descriptions
$availablePages = [
    'home' => [
        'title' => 'Home Page',
        'description' => 'Main landing page with hero section, features, and call-to-action',
        'icon' => 'fa-home'
    ],
    'about' => [
        'title' => 'About Us',
        'description' => 'School history, mission, vision, and values',
        'icon' => 'fa-info-circle'
    ],
    'academics' => [
        'title' => 'Academics',
        'description' => 'Academic programs, curriculum, and educational approach',
        'icon' => 'fa-graduation-cap'
    ],
    'admissions' => [
        'title' => 'Admissions',
        'description' => 'Admission process, requirements, and application information',
        'icon' => 'fa-user-plus'
    ],
    'events' => [
        'title' => 'Events',
        'description' => 'Upcoming events and school calendar',
        'icon' => 'fa-calendar-alt'
    ],
    'notices' => [
        'title' => 'Notices',
        'description' => 'Important announcements and notifications',
        'icon' => 'fa-bullhorn'
    ],
    'gallery' => [
        'title' => 'Gallery',
        'description' => 'Photo gallery and visual content',
        'icon' => 'fa-images'
    ],
    'contact' => [
        'title' => 'Contact Us',
        'description' => 'Contact information and inquiry form',
        'icon' => 'fa-envelope'
    ]
];

// Get existing pages with counts
$pagesWithData = [];
foreach ($contentModel->getAllPagesWithCounts() as $page) {
    $pagesWithData[$page['page_name']] = $page;
}

$pageTitle = 'Website Content';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<style>
.cms-header {
    text-align: center;
    margin-bottom: 3rem;
}

.cms-header h1 {
    color: #1f2937;
    margin: 0 0 0.5rem 0;
    font-size: 2.5rem;
}

.cms-header p {
    color: #6b7280;
    font-size: 1.1rem;
}

.pages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.page-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}

.page-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    transform: scaleX(0);
    transition: transform 0.3s;
}

.page-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.page-card:hover::before {
    transform: scaleX(1);
}

.page-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    margin-bottom: 1.5rem;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.page-card h3 {
    color: #1f2937;
    margin: 0 0 0.75rem 0;
    font-size: 1.5rem;
}

.page-description {
    color: #6b7280;
    margin: 0 0 1.5rem 0;
    line-height: 1.6;
    font-size: 0.95rem;
}

.page-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
    margin-bottom: 1.5rem;
}

.page-meta-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.meta-label {
    font-size: 0.75rem;
    color: #9ca3af;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.meta-value {
    font-size: 1.1rem;
    color: #1f2937;
    font-weight: 700;
}

.meta-date {
    font-size: 0.85rem;
    color: #6b7280;
}

.btn-edit-page {
    width: 100%;
    padding: 0.875rem;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: block;
    text-align: center;
}

.btn-edit-page:hover {
    color: white;
    text-decoration: none;
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.empty-badge {
    display: inline-block;
    padding: 0.35rem 0.85rem;
    background: #fef3c7;
    color: #92400e;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.info-banner {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    text-align: center;
}

.info-banner h3 {
    margin: 0 0 0.5rem 0;
    color: white;
}

.info-banner p {
    margin: 0;
    opacity: 0.95;
}

@media (max-width: 768px) {
    .pages-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="cms-header">
    <h1><i class="fas fa-edit"></i> Website Content Management</h1>
    <p>Edit and manage content for all pages on your website</p>
</div>

<div class="info-banner">
    <h3><i class="fas fa-info-circle"></i> How It Works</h3>
    <p>Click on any page below to edit its content. You can modify text, images, and sections to customize your website.</p>
</div>

<div class="pages-grid">
    <?php foreach ($availablePages as $pageKey => $pageInfo): ?>
        <?php 
        $pageData = $pagesWithData[$pageKey] ?? null;
        $hasContent = $pageData !== null;
        $contentCount = $hasContent ? $pageData['content_count'] : 0;
        $lastUpdated = $hasContent ? $pageData['last_updated'] : null;
        ?>
        
        <div class="page-card">
            <div class="page-icon">
                <i class="fas <?php echo $pageInfo['icon']; ?>"></i>
            </div>
            
            <h3><?php echo htmlspecialchars($pageInfo['title']); ?></h3>
            <p class="page-description"><?php echo htmlspecialchars($pageInfo['description']); ?></p>
            
            <div class="page-meta">
                <div class="page-meta-item">
                    <span class="meta-label">Content Items</span>
                    <span class="meta-value"><?php echo $contentCount; ?></span>
                </div>
                
                <div class="page-meta-item" style="text-align: right;">
                    <span class="meta-label">Last Updated</span>
                    <?php if ($lastUpdated): ?>
                        <span class="meta-date"><?php echo date('M d, Y', strtotime($lastUpdated)); ?></span>
                    <?php else: ?>
                        <span class="empty-badge">Not Set</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <a href="<?php echo BASE_URL; ?>/admin/website/edit.php?page=<?php echo $pageKey; ?>" 
               class="btn-edit-page">
                <i class="fas fa-edit"></i> Edit Page Content
            </a>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
