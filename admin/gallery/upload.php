<?php
/**
 * Admin - Gallery Upload
 * Upload new images to gallery
 */

require_once __DIR__ . '/../../config.php';
requireRole('Admin');

$galleryModel = new Gallery();

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
    $uploadedCount = 0;
    $errors = [];
    
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $newCategory = sanitize($_POST['new_category'] ?? '');
    
    // Use new category if provided
    if (!empty($newCategory)) {
        $category = $newCategory;
    }
    
    $files = $_FILES['images'];
    $fileCount = count($files['name']);
    
    for ($i = 0; $i < $fileCount; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $tmpName = $files['tmp_name'][$i];
            $originalName = $files['name'][$i];
            $fileSize = $files['size'][$i];
            $fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            
            // Validate file type
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($fileExt, $allowedTypes)) {
                $errors[] = "$originalName: Invalid file type. Only JPG, PNG, GIF, and WebP allowed.";
                continue;
            }
            
            // Validate file size (5MB max)
            if ($fileSize > 5 * 1024 * 1024) {
                $errors[] = "$originalName: File too large. Maximum 5MB allowed.";
                continue;
            }
            
            // Generate unique filename
            $newFileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $fileExt;
            $uploadPath = __DIR__ . '/../../uploads/gallery/' . $newFileName;
            $dbPath = 'uploads/gallery/' . $newFileName;
            
            // Move uploaded file
            if (move_uploaded_file($tmpName, $uploadPath)) {
                // Save to database
                $imageTitle = $fileCount > 1 ? ($title . ' ' . ($i + 1)) : $title;
                $result = $galleryModel->uploadImage(
                    $imageTitle,
                    $description,
                    $dbPath,
                    $category,
                    getUserId()
                );
                
                if ($result) {
                    $uploadedCount++;
                } else {
                    $errors[] = "$originalName: Failed to save to database.";
                    if (file_exists($uploadPath)) {
                        unlink($uploadPath);
                    }
                }
            } else {
                $errors[] = "$originalName: Failed to upload file.";
            }
        } else {
            $errors[] = "$originalName: Upload error (code: {$files['error'][$i]}).";
        }
    }
    
    if ($uploadedCount > 0) {
        setFlash('success', "$uploadedCount image(s) uploaded successfully!");
    }
    if (!empty($errors)) {
        setFlash('danger', implode('<br>', $errors));
    }
    
    redirect(BASE_URL . '/admin/gallery/');
}

// Get existing categories
$categories = $galleryModel->getCategories();

$pageTitle = 'Upload Images';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<style>
.upload-container {
    max-width: 800px;
    margin: 0 auto;
}

.upload-card {
    background: white;
    border-radius: 16px;
    padding: 2.5rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

.upload-header {
    margin-bottom: 2rem;
}

.upload-header h2 {
    color: #1f2937;
    margin: 0 0 0.5rem 0;
}

.upload-header p {
    color: #6b7280;
    margin: 0;
}

.drop-zone {
    border: 3px dashed #d1d5db;
    border-radius: 16px;
    padding: 3rem;
    text-align: center;
    transition: all 0.3s;
    cursor: pointer;
    background: #fafafa;
    margin-bottom: 2rem;
}

.drop-zone.dragover {
    border-color: var(--primary);
    background: #f0f4ff;
}

.drop-zone-icon {
    font-size: 4rem;
    color: #9ca3af;
    margin-bottom: 1rem;
}

.drop-zone-text {
    font-size: 1.1rem;
    color: #4b5563;
    margin-bottom: 0.5rem;
}

.drop-zone-hint {
    font-size: 0.9rem;
    color: #9ca3af;
}

.file-input-hidden {
    display: none;
}

.file-list {
    margin: 1.5rem 0;
}

.file-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 10px;
    margin-bottom: 0.75rem;
}

.file-preview {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}

.file-info {
    flex: 1;
}

.file-name {
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 0.25rem 0;
}

.file-size {
    font-size: 0.85rem;
    color: #6b7280;
}

.file-remove {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #fee2e2;
    color: #dc2626;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
}

.file-remove:hover {
    background: #dc2626;
    color: white;
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
    min-height: 100px;
}

.form-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.btn-submit {
    flex: 1;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.btn-submit:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-cancel {
    padding: 1rem 2rem;
    background: #6b7280;
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}

.btn-cancel:hover {
    background: #4b5563;
    color: white;
    text-decoration: none;
}

.category-flex {
    display: flex;
    gap: 1rem;
}

.category-flex > div {
    flex: 1;
}
</style>

<div class="upload-container">
    <div class="upload-card">
        <div class="upload-header">
            <h2><i class="fas fa-cloud-upload-alt"></i> Upload Images</h2>
            <p>Upload one or multiple images to your gallery</p>
        </div>
        
        <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <!-- Drop Zone -->
            <div class="drop-zone" id="dropZone">
                <i class="fas fa-cloud-upload-alt drop-zone-icon"></i>
                <div class="drop-zone-text">Drag & drop images here</div>
                <div class="drop-zone-hint">or click to browse (JPG, PNG, GIF, WebP - Max 5MB each)</div>
            </div>
            
            <input type="file" name="images[]" id="fileInput" class="file-input-hidden" multiple accept="image/*">
            
            <!-- File List -->
            <div id="fileList" class="file-list" style="display: none;"></div>
            
            <!-- Details -->
            <div class="form-group">
                <label>Title (optional)</label>
                <input type="text" name="title" class="form-input" placeholder="Enter image title">
                <small style="color: #6b7280;">If uploading multiple images, numbers will be appended</small>
            </div>
            
            <div class="form-group">
                <label>Description (optional)</label>
                <textarea name="description" class="form-textarea" placeholder="Enter image description"></textarea>
            </div>
            
            <div class="form-group">
                <label>Category</label>
                <div class="category-flex">
                    <div>
                        <select name="category" id="categorySelect" class="form-select">
                            <option value="">Select existing category</option>
                            <?php foreach ($categories as $cat): ?>
                                <?php if (!empty($cat['category'])): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category']); ?>">
                                        <?php echo htmlspecialchars($cat['category']); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <input type="text" name="new_category" id="newCategory" class="form-input" placeholder="Or create new category">
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="form-actions">
                <a href="<?php echo BASE_URL; ?>/admin/gallery/" class="btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" id="submitBtn" class="btn-submit" disabled>
                    <i class="fas fa-upload"></i> Upload Images
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const fileList = document.getElementById('fileList');
const submitBtn = document.getElementById('submitBtn');
let selectedFiles = [];

// Click to browse
dropZone.addEventListener('click', () => fileInput.click());

// Drag and drop
dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('dragover');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('dragover');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
});

// File input change
fileInput.addEventListener('change', (e) => {
    handleFiles(e.target.files);
});

function handleFiles(files) {
    selectedFiles = Array.from(files);
    displayFiles();
    submitBtn.disabled = selectedFiles.length === 0;
}

function displayFiles() {
    if (selectedFiles.length === 0) {
        fileList.style.display = 'none';
        return;
    }
    
    fileList.style.display = 'block';
    fileList.innerHTML = '';
    
    selectedFiles.forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        
        const preview = document.createElement('img');
        preview.className = 'file-preview';
        preview.src = URL.createObjectURL(file);
        
        const info = document.createElement('div');
        info.className = 'file-info';
        info.innerHTML = `
            <div class="file-name">${file.name}</div>
            <div class="file-size">${(file.size / 1024 / 1024).toFixed(2)} MB</div>
        `;
        
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'file-remove';
        removeBtn.innerHTML = '<i class="fas fa-times"></i>';
        removeBtn.onclick = () => removeFile(index);
        
        fileItem.appendChild(preview);
        fileItem.appendChild(info);
        fileItem.appendChild(removeBtn);
        fileList.appendChild(fileItem);
    });
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    
    // Update file input
    const dt = new DataTransfer();
    selectedFiles.forEach(file => dt.items.add(file));
    fileInput.files = dt.files;
    
    displayFiles();
    submitBtn.disabled = selectedFiles.length === 0;
}
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
