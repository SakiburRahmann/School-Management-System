<?php
/**
 * Admin - Edit Website Content
 * Edit content for a specific page
 */

require_once __DIR__ . '/../../config.php';
requireRole('Admin');

$contentModel = new WebsiteContent();

$pageName = $_GET['page'] ?? null;

// Define page configurations
$pageConfig = [
    'home' => ['title' => 'Home Page', 'icon' => 'fa-home'],
    'about' => ['title' => 'About Us', 'icon' => 'fa-info-circle'],
    'academics' => ['title' => 'Academics', 'icon' => 'fa-graduation-cap'],
    'admissions' => ['title' => 'Admissions', 'icon' => 'fa-user-plus'],
    'events' => ['title' => 'Events', 'icon' => 'fa-calendar-alt'],
    'notices' => ['title' => 'Notices', 'icon' => 'fa-bullhorn'],
    'gallery' => ['title' => 'Gallery', 'icon' => 'fa-images'],
    'contact' => ['title' => 'Contact Us', 'icon' => 'fa-envelope']
];

if (!$pageName || !isset($pageConfig[$pageName])) {
    setFlash('danger', 'Invalid page name.');
    redirect(BASE_URL . '/admin/website/');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $savedCount = 0;
    
    // Process each submitted field
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'content_') === 0) {
            // Extract section and key from field name
            // Format: content_sectionName_contentKey
            $parts = explode('_', $key, 3);
            if (count($parts) === 3) {
                $sectionName = $parts[1];
                $contentKey = $parts[2];
                $contentValue = sanitize($value);
                
                if ($contentModel->saveContent($pageName, $sectionName, $contentKey, $contentValue)) {
                    $savedCount++;
                }
            }
        }
    }
    
    if ($savedCount > 0) {
        setFlash('success', "Content updated successfully! ($savedCount items saved)");
    } else {
        setFlash('warning', 'No changes were made.');
    }
    
    redirect(BASE_URL . '/admin/website/edit.php?page=' . $pageName);
}

// Handle section deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete_section' && isset($_GET['section'])) {
    $sectionToDelete = $_GET['section'];
    if ($contentModel->deleteSection($pageName, $sectionToDelete)) {
        setFlash('success', 'Section deleted successfully!');
    } else {
        setFlash('danger', 'Failed to delete section.');
    }
    redirect(BASE_URL . '/admin/website/edit.php?page=' . $pageName);
}

// Get current page content grouped by sections
$sections = $contentModel->getSectionsByPage($pageName);

// Define common section templates for each page
$sectionTemplates = [
    'hero' => [
        'label' => 'Hero/Banner Section',
        'icon' => 'fa-flag',
        'fields' => [
            'title' => ['label' => 'Main Title', 'type' => 'text', 'placeholder' => 'Welcome to Our School'],
            'subtitle' => ['label' => 'Subtitle', 'type' => 'text', 'placeholder' => 'Building Future Leaders'],
            'description' => ['label' => 'Description', 'type' => 'textarea', 'placeholder' => 'Add a brief description...']
        ]
    ],
    'about' => [
        'label' => 'About Section',
        'icon' => 'fa-info-circle',
        'fields' => [
            'heading' => ['label' => 'Section Heading', 'type' => 'text'],
            'content' => ['label' => 'Content', 'type' => 'textarea']
        ]
    ],
    'features' => [
        'label' => 'Features Section',
        'icon' => 'fa-list',
        'fields' => [
            'heading' => ['label' => 'Section Heading', 'type' => 'text'],
            'feature1_title' => ['label' => 'Feature 1 Title', 'type' => 'text'],
            'feature1_text' => ['label' => 'Feature 1 Text', 'type' => 'textarea'],
            'feature2_title' => ['label' => 'Feature 2 Title', 'type' => 'text'],
            'feature2_text' => ['label' => 'Feature 2 Text', 'type' => 'textarea'],
            'feature3_title' => ['label' => 'Feature 3 Title', 'type' => 'text'],
            'feature3_text' => ['label' => 'Feature 3 Text', 'type' => 'textarea']
        ]
    ]
];

$pageInfo = $pageConfig[$pageName];
$pageTitle = 'Edit ' . $pageInfo['title'];
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<style>
.editor-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.editor-header h1 {
    margin: 0;
    color: white;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: rgba(255,255,255,0.9);
}

.editor-content {
    max-width: 1200px;
    margin: 0 auto;
}

.section-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f3f4f6;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: #1f2937;
    margin: 0;
    font-size: 1.25rem;
}

.section-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.btn-delete-section {
    padding: 0.5rem 1rem;
    background: #fee2e2;
    color: #dc2626;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
    font-size: 0.85rem;
}

.btn-delete-section:hover {
    background: #dc2626;
    color: white;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.form-input, .form-textarea {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s;
    background: #fafafa;
    font-family: inherit;
}

.form-input:focus, .form-textarea:focus {
    outline: none;
    border-color: var(--primary);
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 120px;
    line-height: 1.6;
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.add-section-card {
    background: linear-gradient(135deg, #f9fafb, #f3f4f6);
    border: 2px dashed #d1d5db;
    border-radius: 16px;
    padding: 3rem 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    margin-bottom: 2rem;
}

.add-section-card:hover {
    border-color: var(--primary);
    background: linear-gradient(135deg, #f0f4ff, #e0e7ff);
}

.add-section-card i {
    font-size: 3rem;
    color: #9ca3af;
    margin-bottom: 1rem;
}

.add-section-card h3 {
    color: #4b5563;
    margin: 0;
}

.save-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    padding: 1.5rem;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
    z-index: 1000;
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.btn {
    padding: 1rem 2.5rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-save {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
}

.btn-cancel {
    background: #6b7280;
    color: white;
}

.btn-cancel:hover {
    background: #4b5563;
    color: white;
    text-decoration: none;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6b7280;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 2000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    max-width: 500px;
    width: 90%;
}

.template-grid {
    display: grid;
    gap: 1rem;
    margin-top: 1.5rem;
}

.template-option {
    padding: 1.25rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
}

.template-option:hover {
    border-color: var(--primary);
    background: #f9fafb;
}

@media (max-width: 768px) {
    .save-bar {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="editor-header">
    <div>
        <div class="breadcrumb">
            <a href="<?php echo BASE_URL; ?>/admin/website/" style="color: rgba(255,255,255,0.9);">Website Content</a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo $pageInfo['title']; ?></span>
        </div>
        <h1>
            <i class="fas <?php echo $pageInfo['icon']; ?>"></i>
            <?php echo $pageInfo['title']; ?>
        </h1>
    </div>
</div>

<div class="editor-content">
    <form method="POST" id="contentForm">
        <?php if (empty($sections)): ?>
            <div class="empty-state">
                <i class="fas fa-file-alt" style="font-size: 4rem; opacity: 0.3; margin-bottom: 1rem;"></i>
                <h3>No Content Yet</h3>
                <p>This page doesn't have any content sections yet. Add your first section below!</p>
            </div>
        <?php else: ?>
            <?php foreach ($sections as $sectionName => $sectionContent): ?>
                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">
                            <div class="section-icon">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <?php echo ucwords(str_replace('_', ' ', $sectionName)); ?>
                        </h3>
                        
                        <button type="button" 
                                class="btn-delete-section"
                                onclick="deleteSection('<?php echo htmlspecialchars($sectionName); ?>')">
                            <i class="fas fa-trash"></i> Delete Section
                        </button>
                    </div>
                    
                    <div class="content-grid">
                        <?php foreach ($sectionContent as $content): ?>
                            <div class="form-group">
                                <label class="form-label">
                                    <?php echo ucwords(str_replace('_', ' ', $content['content_key'])); ?>
                                </label>
                                
                                <?php
                                $fieldName = 'content_' . $sectionName . '_' . $content['content_key'];
                                $fieldValue = htmlspecialchars($content['content_value']);
                                
                                // Determine field type based on content length
                                if (strlen($content['content_value']) > 100 || strpos($content['content_key'], 'description') !== false || strpos($content['content_key'], 'content') !== false):
                                ?>
                                    <textarea name="<?php echo $fieldName; ?>" 
                                              class="form-textarea"><?php echo $fieldValue; ?></textarea>
                                <?php else: ?>
                                    <input type="text" 
                                           name="<?php echo $fieldName; ?>" 
                                           value="<?php echo $fieldValue; ?>"
                                           class="form-input">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- Add Section Card -->
        <div class="add-section-card" onclick="showAddSectionModal()">
            <i class="fas fa-plus-circle"></i>
            <h3>Add New Section</h3>
            <p style="color: #6b7280; margin: 0.5rem 0 0 0;">Click to add a new content section</p>
        </div>
        
        <!-- Save Bar -->
        <div class="save-bar">
            <a href="<?php echo BASE_URL; ?>/admin/website/" class="btn btn-cancel">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-save">
                <i class="fas fa-save"></i> Save All Changes
            </button>
        </div>
    </form>
</div>

<!-- Add Section Modal -->
<div id="addSectionModal" class="modal">
    <div class="modal-content">
        <h3 style="margin: 0 0 1.5rem 0; color: #1f2937;">
            <i class="fas fa-plus"></i> Add New Section
        </h3>
        
        <div class="form-group">
            <label class="form-label">Section Name</label>
            <input type="text" id="newSectionName" class="form-input" 
                   placeholder="e.g., hero, about, features">
        </div>
        
        <div class="template-grid">
            <?php foreach ($sectionTemplates as $templateKey => $template): ?>
                <div class="template-option" onclick="selectTemplate('<?php echo $templateKey; ?>')">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <i class="fas <?php echo $template['icon']; ?>" style="font-size: 1.5rem; color: var(--primary);"></i>
                        <div>
                            <strong><?php echo $template['label']; ?></strong>
                            <div style="font-size: 0.85rem; color: #6b7280;"><?php echo count($template['fields']); ?> fields</div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
            <button type="button" onclick="closeAddSectionModal()" 
                    style="flex: 1; padding: 0.875rem; background: #e5e7eb; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">
                Cancel
            </button>
            <button type="button" onclick="addCustomSection()" 
                    style="flex: 1; padding: 0.875rem; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">
                Add Section
            </button>
        </div>
    </div>
</div>

<script>
const templates = <?php echo json_encode($sectionTemplates); ?>;

function showAddSectionModal() {
    document.getElementById('addSectionModal').classList.add('active');
}

function closeAddSectionModal() {
    document.getElementById('addSectionModal').classList.remove('active');
    document.getElementById('newSectionName').value = '';
}

function selectTemplate(templateKey) {
    document.getElementById('newSectionName').value = templateKey;
}

function addCustomSection() {
    const sectionName = document.getElementById('newSectionName').value.trim();
    if (!sectionName) {
        alert('Please enter a section name');
        return;
    }
    
    const template = templates[sectionName];
    if (!template) {
        alert('Please select a template or create a custom section name');
        return;
    }
    
    // Create new section in the form
    const form = document.getElementById('contentForm');
    const sectionCard = document.createElement('div');
    sectionCard.className = 'section-card';
    
    let fieldsHTML = '';
    for (const [fieldKey, fieldConfig] of Object.entries(template.fields)) {
        const fieldName = `content_${sectionName}_${fieldKey}`;
        const inputType = fieldConfig.type === 'textarea' ? 
            `<textarea name="${fieldName}" class="form-textarea" placeholder="${fieldConfig.placeholder || ''}"></textarea>` :
            `<input type="text" name="${fieldName}" class="form-input" placeholder="${fieldConfig.placeholder || ''}">`;
            
        fieldsHTML += `
            <div class="form-group">
                <label class="form-label">${fieldConfig.label}</label>
                ${inputType}
            </div>
        `;
    }
    
    sectionCard.innerHTML = `
        <div class="section-header">
            <h3 class="section-title">
                <div class="section-icon">
                    <i class="fas ${template.icon}"></i>
                </div>
                ${template.label}
            </h3>
        </div>
        <div class="content-grid">
            ${fieldsHTML}
        </div>
    `;
    
    // Insert before the add section card
    const addCard = document.querySelector('.add-section-card');
    addCard.parentNode.insertBefore(sectionCard, addCard);
    
    closeAddSectionModal();
}

function deleteSection(sectionName) {
    if (confirm('Are you sure you want to delete this section? This action cannot be undone.')) {
        window.location.href = '?page=<?php echo $pageName; ?>&action=delete_section&section=' + sectionName;
    }
}

// Close modal when clicking outside
document.getElementById('addSectionModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAddSectionModal();
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
