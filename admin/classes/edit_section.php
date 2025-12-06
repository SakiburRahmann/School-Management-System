<?php
/**
 * Admin - Edit Section
 * Form to edit section details (name and class teacher)
 */

require_once __DIR__ . '/../../config.php';

$classModel = new ClassModel();
$teacherModel = new Teacher();

// Get section ID
$sectionId = $_GET['id'] ?? null;

if (!$sectionId) {
    setFlash('danger', 'Invalid section ID.');
    redirect(BASE_URL . '/admin/classes/');
}

// Get section details
$section = $classModel->getSectionWithDetails($sectionId);

if (!$section) {
    setFlash('danger', 'Section not found.');
    redirect(BASE_URL . '/admin/classes/');
}

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlash('danger', 'Invalid request.');
        redirect(BASE_URL . '/admin/classes/edit_section.php?id=' . $sectionId);
    }
    
    $sectionName = trim(sanitize($_POST['section_name']));
    $classTeacherId = !empty($_POST['class_teacher_id']) ? $_POST['class_teacher_id'] : null;
    
    // Validate section name
    if (empty($sectionName)) {
        $errors['section_name'] = 'Section name is required.';
    } elseif ($classModel->sectionExists($section['class_id'], $sectionName, $sectionId)) {
        $errors['section_name'] = 'A section with this name already exists in this class.';
    }
    
    if (empty($errors)) {
        $data = [
            'section_name' => $sectionName,
            'class_teacher_id' => $classTeacherId
        ];
        
        if ($classModel->updateSection($sectionId, $data)) {
            setFlash('success', 'Section updated successfully!');
            redirect(BASE_URL . '/admin/classes/view_section.php?id=' . $sectionId);
        } else {
            setFlash('danger', 'Failed to update section.');
        }
    }
}

// Get all teachers for dropdown
$allTeachers = $teacherModel->findAll('name');

// Form data (use POST data if available, otherwise use section data)
$formData = [
    'section_name' => $_POST['section_name'] ?? $section['section_name'],
    'class_teacher_id' => $_POST['class_teacher_id'] ?? $section['class_teacher_id']
];

$pageTitle = 'Edit Section - ' . $section['section_name'];
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<style>
/* Premium Edit Form Styles */
.edit-card {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.edit-card .card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
    border-bottom: 1px solid #eee;
    padding: 1.5rem 2rem;
}

.edit-card .card-header h3 {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 0;
}

.edit-card .card-header h3 i {
    background: linear-gradient(135deg, var(--primary), #667eea);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.edit-card .card-body {
    padding: 2rem;
}

.section-info-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, var(--primary), #667eea);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 10px;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.form-section-title {
    font-size: 1.1rem;
    color: var(--primary);
    margin: 1.5rem 0 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #eee;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-section-title:first-of-type {
    margin-top: 0;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #eee;
}

.form-actions .btn {
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.form-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.class-display {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 10px;
    border: 1px dashed #ddd;
}

.class-display label {
    font-size: 0.85rem;
    color: #666;
    margin-bottom: 0.5rem;
    display: block;
}

.class-display .class-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
}

.class-display small {
    color: #999;
    font-size: 0.8rem;
}

/* Teacher Search Dropdown */
.teacher-select-wrapper {
    position: relative;
}

.teacher-search-container {
    position: relative;
}

.teacher-search-container input {
    padding-right: 2.5rem;
}

.clear-teacher-btn {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    padding: 0.25rem;
    font-size: 0.9rem;
    opacity: 0;
    transition: opacity 0.2s;
}

.teacher-search-container:hover .clear-teacher-btn,
.teacher-search-container input:focus + .clear-teacher-btn {
    opacity: 1;
}

.clear-teacher-btn:hover {
    color: #dc3545;
}
</style>

<div class="card edit-card">
    <div class="card-header">
        <h3><i class="fas fa-edit"></i> Edit Section</h3>
        <a href="<?php echo BASE_URL; ?>/admin/classes/view_section.php?id=<?php echo $sectionId; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Section
        </a>
    </div>
    
    <div class="card-body">
        <!-- Section Info Badge -->
        <div class="section-info-badge">
            <i class="fas fa-layer-group"></i>
            Editing: <?php echo htmlspecialchars($section['class_name']); ?> - Section <?php echo htmlspecialchars($section['section_name']); ?>
        </div>
        
        <?php if (!empty($errors)): ?>
        <div class="validation-alert validation-alert-danger">
            <div class="validation-alert-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="validation-alert-content">
                <strong>Please fix the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <button class="validation-alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="editSectionForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <h4 class="form-section-title"><i class="fas fa-info-circle"></i> Section Details</h4>
            
            <div class="form-grid">
                <!-- Class (Read-only) -->
                <div class="class-display">
                    <label>Class</label>
                    <div class="class-name">
                        <i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($section['class_name']); ?>
                    </div>
                    <small>Class cannot be changed. Create a new section in the target class instead.</small>
                </div>
                
                <!-- Section Name -->
                <div class="form-group <?php echo isset($errors['section_name']) ? 'has-error' : ''; ?>">
                    <label for="section_name">Section Name <span class="required-star">*</span></label>
                    <input type="text" id="section_name" name="section_name" class="form-control"
                           value="<?php echo htmlspecialchars($formData['section_name']); ?>"
                           placeholder="e.g., A, B, C" required>
                    <div class="field-error" id="section_name-error">
                        <?php echo isset($errors['section_name']) ? htmlspecialchars($errors['section_name']) : ''; ?>
                    </div>
                </div>
            </div>
            
            <h4 class="form-section-title"><i class="fas fa-chalkboard-teacher"></i> Class Teacher Assignment</h4>
            
            <div class="form-group teacher-select-wrapper">
                <label for="class_teacher_id">Class Teacher (Optional)</label>
                <div class="teacher-search-container">
                    <input type="text" id="teacher_search" class="form-control" 
                           placeholder="Type to search teachers..."
                           autocomplete="off">
                    <button type="button" class="clear-teacher-btn" id="clearTeacher" title="Clear selection">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <select id="class_teacher_id" name="class_teacher_id" class="form-control" style="margin-top: 5px; display: none;" size="8">
                    <option value="">-- No Class Teacher --</option>
                    <?php foreach ($allTeachers as $teacher): ?>
                        <option value="<?php echo $teacher['teacher_id']; ?>"
                                data-name="<?php echo htmlspecialchars($teacher['name']); ?>"
                                data-custom-id="<?php echo htmlspecialchars($teacher['teacher_id_custom'] ?? ''); ?>"
                                <?php echo ($formData['class_teacher_id'] == $teacher['teacher_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($teacher['name']); ?>
                            <?php if (!empty($teacher['teacher_id_custom'])): ?>
                                (ID: <?php echo htmlspecialchars($teacher['teacher_id_custom']); ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Start typing to search, or leave empty to remove the class teacher</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Section
                </button>
                <a href="<?php echo BASE_URL; ?>/admin/classes/view_section.php?id=<?php echo $sectionId; ?>" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const teacherSelect = document.getElementById('class_teacher_id');
    const searchInput = document.getElementById('teacher_search');
    const clearBtn = document.getElementById('clearTeacher');
    const originalOptions = Array.from(teacherSelect.options);
    
    let validSelection = true;
    
    // Initialize with current selection
    const selectedOption = teacherSelect.options[teacherSelect.selectedIndex];
    if (selectedOption && selectedOption.value) {
        searchInput.value = selectedOption.text;
        searchInput.style.borderColor = '#28a745';
    }
    
    // Show dropdown on focus
    searchInput.addEventListener('focus', function() {
        teacherSelect.style.display = 'block';
        filterOptions('');
    });
    
    // Filter as user types
    searchInput.addEventListener('input', function() {
        filterOptions(this.value);
        if (this.value.trim() !== '') {
            validSelection = false;
            this.style.borderColor = '#ffc107';
        } else {
            validSelection = true;
            teacherSelect.value = '';
            this.style.borderColor = '';
        }
    });
    
    // Handle option selection
    teacherSelect.addEventListener('change', function() {
        selectTeacher(this);
    });
    
    teacherSelect.addEventListener('click', function() {
        selectTeacher(this);
    });
    
    function selectTeacher(select) {
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption.value) {
            searchInput.value = selectedOption.text;
            validSelection = true;
            searchInput.style.borderColor = '#28a745';
        } else {
            searchInput.value = '';
            validSelection = true;
            searchInput.style.borderColor = '';
        }
        teacherSelect.style.display = 'none';
    }
    
    // Clear button handler
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        teacherSelect.value = '';
        validSelection = true;
        searchInput.style.borderColor = '';
        searchInput.focus();
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.teacher-select-wrapper')) {
            teacherSelect.style.display = 'none';
        }
    });
    
    // Filter options function
    function filterOptions(searchTerm) {
        const term = searchTerm.toLowerCase();
        teacherSelect.innerHTML = '';
        
        originalOptions.forEach(option => {
            const text = option.text.toLowerCase();
            const customId = option.getAttribute('data-custom-id') || '';
            const name = option.getAttribute('data-name') || '';
            
            if (option.value === '' || 
                text.includes(term) || 
                customId.toLowerCase().includes(term) ||
                name.toLowerCase().includes(term)) {
                teacherSelect.appendChild(option.cloneNode(true));
            }
        });
    }
    
    // Form validation
    const form = document.getElementById('editSectionForm');
    form.addEventListener('submit', function(e) {
        const sectionName = document.getElementById('section_name');
        
        if (sectionName.value.trim() === '') {
            e.preventDefault();
            const formGroup = sectionName.closest('.form-group');
            formGroup.classList.add('has-error');
            document.getElementById('section_name-error').textContent = 'Section name is required';
            formGroup.classList.add('shake');
            setTimeout(() => formGroup.classList.remove('shake'), 500);
            showToast('Please fill in the section name', 'danger');
            return false;
        }
        
        // If search input has text but no valid selection
        if (searchInput.value.trim() !== '' && !validSelection) {
            e.preventDefault();
            searchInput.style.borderColor = '#dc3545';
            showToast('Please select a teacher from the dropdown, or clear the field', 'warning');
            searchInput.focus();
            teacherSelect.style.display = 'block';
            filterOptions(searchInput.value);
            return false;
        }
        
        if (searchInput.value.trim() === '') {
            teacherSelect.value = '';
        }
    });
    
    // Clear error on input
    document.getElementById('section_name').addEventListener('input', function() {
        const formGroup = this.closest('.form-group');
        if (this.value.trim()) {
            formGroup.classList.remove('has-error');
            formGroup.classList.add('is-valid');
            document.getElementById('section_name-error').textContent = '';
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
