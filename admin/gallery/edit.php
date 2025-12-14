<?php
/**
 * Admin - Edit Gallery Image
 */

require_once __DIR__ . '/../../config.php';
requireRole('Admin');

$galleryModel = new Gallery();
$imageId = $_GET['id'] ?? null;

if (!$imageId) {
    setFlash('danger', 'Invalid image ID.');
    redirect(BASE_URL . '/admin/gallery/');
}

$image = $galleryModel->find($imageId);

if (!$image) {
    setFlash('danger', 'Image not found.');
    redirect(BASE_URL . '/admin/gallery/');
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $newCategory = sanitize($_POST['new_category'] ?? '');
    
    // Use new category if provided
    if (!empty($newCategory)) {
        $category = $newCategory;
    }
    
    // Handle image replacement
    if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['new_image']['tmp_name'];
        $originalName = $_FILES['new_image']['name'];
        $fileSize = $_FILES['new_image']['size'];
        $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        
        // Validate
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($fileExt, $allowedTypes) && $fileSize <= 5 * 1024 * 1024) {
            // Delete old image
            $oldPath = __DIR__ . '/../../' . $image['image_path'];
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
            
            // Upload new image
            $newFileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $fileExt;
            $uploadPath = __DIR__ . '/../../uploads/gallery/' . $newFileName;
            $dbPath = 'uploads/gallery/' . $newFileName;
            
            if (move_uploaded_file($tmpName, $uploadPath)) {
                $image['image_path'] = $dbPath;
            }
        } else {
            setFlash('warning', 'Image not updated: Invalid file type or size too large.');
        }
    }
    
    // Update database
    $result = $galleryModel->update($imageId, [
        'title' => $title,
        'description' => $description,
        'category' => $category,
        'image_path' => $image['image_path']
    ]);
    
    if ($result) {
        setFlash('success', 'Image updated successfully!');
        redirect(BASE_URL . '/admin/gallery/');
    } else {
        setFlash('danger', 'Failed to update image.');
    }
}

// Get categories
$categories = $galleryModel->getCategories();

$pageTitle = 'Edit Image';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<style>
.edit-container {
    max-width: 900px;
    margin: 0 auto;
}

.edit-card {
    background: white;
    border-radius: 16px;
    padding: 2.5rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.current-image {
    text-align: center;
    margin-bottom: 2rem;
    padding: 2rem;
    background: #f9fafb;
    border-radius: 12px;
}

.current-image img {
    max-width: 100%;
    max-height: 400px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.current-image-label {
    font-size: 0.85rem;
    color: #6b7280;
    margin-bottom: 1rem;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-input, .form-textarea, .form-select {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s;
    background: #fafafa;
}

.form-input:focus, .form-textarea:focus, .form-select:focus {
    outline: none;
    border-color: var(--primary);
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 120px;
}

.file-input-label {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background: #f3f4f6;
    color: #374151;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 500;
}

.file-input-label:hover {
    background: #e5e7eb;
}

.file-input-hidden {
    display: none;
}

.file-chosen {
    margin-left: 1rem;
    color: #6b7280;
    font-size: 0.9rem;
}

.category-flex {
    display: flex;
    gap: 1rem;
}

.category-flex > div {
    flex: 1;
}

.form-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
    flex-wrap: wrap;
}

.btn {
    padding: 1rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: none;
}

.btn-submit {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    flex: 1;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
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

.btn-delete {
    background: #ef4444;
    color: white;
}

.btn-delete:hover {
    background: #dc2626;
    color: white;
    text-decoration: none;
}

@media (max-width: 600px) {
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="edit-container">
    <div class="edit-card">
        <h2 style="margin: 0 0 2rem 0; color: #1f2937;">
            <i class="fas fa-edit"></i> Edit Image
        </h2>
        
        <!-- Current Image -->
        <div class="current-image">
            <div class="current-image-label">Current Image</div>
            <img src="<?php echo BASE_URL . '/' . htmlspecialchars($image['image_path']); ?>" 
                 alt="<?php echo htmlspecialchars($image['title']); ?>">
        </div>
        
        <!-- Edit Form -->
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-input" 
                       value="<?php echo htmlspecialchars($image['title']); ?>" 
                       placeholder="Enter image title">
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-textarea" 
                          placeholder="Enter image description"><?php echo htmlspecialchars($image['description']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Category</label>
                <div class="category-flex">
                    <div>
                        <select name="category" id="categorySelect" class="form-select">
                            <option value="">No category</option>
                            <?php foreach ($categories as $cat): ?>
                                <?php if (!empty($cat['category'])): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category']); ?>"
                                            <?php echo $image['category'] === $cat['category'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category']); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <input type="text" name="new_category" class="form-input" 
                               placeholder="Or create new category">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Replace Image (optional)</label>
                <label for="newImage" class="file-input-label">
                    <i class="fas fa-image"></i> Choose New Image
                </label>
                <span class="file-chosen" id="fileChosen">No file chosen</span>
                <input type="file" name="new_image" id="newImage" class="file-input-hidden" 
                       accept="image/*" onchange="updateFileName(this)">
                <small style="display: block; color: #6b7280; margin-top: 0.5rem;">
                    JPG, PNG, GIF, or WebP - Max 5MB
                </small>
            </div>
            
            <!-- Actions -->
            <div class="form-actions">
                <a href="<?php echo BASE_URL; ?>/admin/gallery/" class="btn btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="<?php echo BASE_URL; ?>/admin/gallery/index.php?action=delete&id=<?php echo $imageId; ?>" 
                   class="btn btn-delete"
                   onclick="return confirm('Are you sure you want to delete this image? This action cannot be undone.');">
                    <i class="fas fa-trash"></i> Delete Image
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function updateFileName(input) {
    const fileChosen = document.getElementById('fileChosen');
    if (input.files.length > 0) {
        fileChosen.textContent = input.files[0].name;
    } else {
        fileChosen.textContent = 'No file chosen';
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
