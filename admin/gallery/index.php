<?php
/**
 * Admin - Gallery Management
 * Upload and manage gallery images
 */

require_once __DIR__ . '/../../config.php';
requireRole('Admin');

$galleryModel = new Gallery();

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($galleryModel->deleteWithFile($id)) {
        setFlash('success', 'Image deleted successfully!');
    } else {
        setFlash('danger', 'Failed to delete image.');
    }
    redirect(BASE_URL . '/admin/gallery/');
}

// Get filter parameters
$searchQuery = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';

// Get images based on filters
if ($searchQuery || $categoryFilter) {
    $images = $galleryModel->search($searchQuery, $categoryFilter ?: null);
} else {
    $images = $galleryModel->getAll();
}

// Get statistics and categories
$stats = $galleryModel->getStatistics();
$categories = $galleryModel->getCategories();

$pageTitle = 'Gallery Management';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<style>
/* Gallery Management Styles */
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
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card.total { background: linear-gradient(135deg, #667eea, #764ba2); }
.stat-card.categories { background: linear-gradient(135deg, #f093fb, #f5576c); }
.stat-card.today { background: linear-gradient(135deg, #4facfe, #00f2fe); }
.stat-card.week { background: linear-gradient(135deg, #43e97b, #38f9d7); }

.stat-icon {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    opacity: 0.9;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0.5rem 0;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.95;
}

.toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 1.5rem;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.search-filter {
    display: flex;
    gap: 1rem;
    flex: 1;
    min-width: 300px;
}

.search-input, .filter-select {
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.95rem;
    background: #fafafa;
    transition: all 0.3s;
}

.search-input {
    flex: 1;
}

.search-input:focus, .filter-select:focus {
    outline: none;
    border-color: var(--primary);
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.btn-upload {
    padding: 0.875rem 2rem;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-upload:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
    color: white;
    text-decoration: none;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

.gallery-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s;
}

.gallery-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.gallery-image {
    width: 100%;
    height: 220px;
    object-fit: cover;
    background: #f3f4f6;
}

.gallery-content {
    padding: 1.25rem;
}

.gallery-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 0.5rem 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.gallery-description {
    font-size: 0.9rem;
    color: #6b7280;
    margin: 0 0 1rem 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.gallery-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid #f3f4f6;
}

.category-badge {
    display: inline-block;
    padding: 0.35rem 0.85rem;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.gallery-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-action-small {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
}

.btn-edit {
    background: #3b82f6;
}

.btn-delete {
    background: #ef4444;
}

.btn-action-small:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6b7280;
    background: white;
    border-radius: 16px;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

@media (max-width: 768px) {
    .toolbar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-filter {
        flex-direction: column;
    }
    
    .btn-upload {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- Statistics Dashboard -->
<div class="stats-grid">
    <div class="stat-card total">
        <div class="stat-icon"><i class="fas fa-images"></i></div>
        <div class="stat-value"><?php echo $stats['total']; ?></div>
        <div class="stat-label">Total Images</div>
    </div>
    
    <div class="stat-card categories">
        <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
        <div class="stat-value"><?php echo $stats['categories']; ?></div>
        <div class="stat-label">Categories</div>
    </div>
    
    <div class="stat-card today">
        <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-value"><?php echo $stats['today']; ?></div>
        <div class="stat-label">Added Today</div>
    </div>
    
    <div class="stat-card week">
        <div class="stat-icon"><i class="fas fa-calendar-week"></i></div>
        <div class="stat-value"><?php echo $stats['this_week']; ?></div>
        <div class="stat-label">This Week</div>
    </div>
</div>

<!-- Toolbar -->
<div class="toolbar">
    <form method="GET" class="search-filter">
        <input type="text" name="search" class="search-input" placeholder="Search by title or description..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        
        <select name="category" class="filter-select">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <?php if (!empty($cat['category'])): ?>
                    <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $categoryFilter === $cat['category'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category']); ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
        
        <button type="submit" class="btn-filter" style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">
            <i class="fas fa-search"></i>
        </button>
    </form>
    
    <a href="<?php echo BASE_URL; ?>/admin/gallery/upload.php" class="btn-upload">
        <i class="fas fa-cloud-upload-alt"></i> Upload Images
    </a>
</div>

<!-- Gallery Grid -->
<?php if (!empty($images)): ?>
    <div class="gallery-grid">
        <?php foreach ($images as $image): ?>
            <div class="gallery-card">
                <img src="<?php echo BASE_URL . '/' . htmlspecialchars($image['image_path']); ?>" 
                     alt="<?php echo htmlspecialchars($image['title']); ?>" 
                     class="gallery-image">
                <div class="gallery-content">
                    <h3 class="gallery-title"><?php echo htmlspecialchars($image['title'] ?: 'Untitled'); ?></h3>
                    <?php if (!empty($image['description'])): ?>
                        <p class="gallery-description"><?php echo htmlspecialchars($image['description']); ?></p>
                    <?php endif; ?>
                    
                    <div class="gallery-meta">
                        <?php if (!empty($image['category'])): ?>
                            <span class="category-badge"><?php echo htmlspecialchars($image['category']); ?></span>
                        <?php else: ?>
                            <span style="color: #9ca3af; font-size: 0.85rem;">No category</span>
                        <?php endif; ?>
                        
                        <div class="gallery-actions">
                            <a href="<?php echo BASE_URL; ?>/admin/gallery/edit.php?id=<?php echo $image['gallery_id']; ?>" 
                               class="btn-action-small btn-edit" 
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?action=delete&id=<?php echo $image['gallery_id']; ?>" 
                               class="btn-action-small btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this image?');"
                               title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <i class="fas fa-images"></i>
        <h3>No Images Found</h3>
        <p>Upload some images to get started!</p>
        <br>
        <a href="<?php echo BASE_URL; ?>/admin/gallery/upload.php" class="btn-upload">
            <i class="fas fa-cloud-upload-alt"></i> Upload Your First Image
        </a>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
