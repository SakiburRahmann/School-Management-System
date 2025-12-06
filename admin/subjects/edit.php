<?php
/**
 * Admin - Edit Subject
 */

require_once __DIR__ . '/../../config.php';

// Check if logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    redirect(BASE_URL . '/login.php');
}

$subjectModel = new Subject();
$teacherModel = new Teacher();
$classModel = new ClassModel();

$errors = [];

// Get subject ID
$subjectId = $_GET['id'] ?? null;

if (!$subjectId) {
    setFlash('danger', 'Subject ID is required.');
    redirect(BASE_URL . '/admin/subjects/');
}

// Get subject details
$subject = $subjectModel->getFullDetails($subjectId);

if (!$subject) {
    setFlash('danger', 'Subject not found.');
    redirect(BASE_URL . '/admin/subjects/');
}

// Get assigned teacher IDs
$assignedTeacherIds = array_column($subject['teachers'], 'teacher_id');

// Form data (from subject or POST)
$formData = [
    'subject_name' => $subject['subject_name'],
    'subject_code' => $subject['subject_code'],
    'description' => $subject['description'] ?? '',
    'credits_hours' => $subject['credits_hours'] ?? 0,
    'subject_type' => $subject['subject_type'] ?? 'Core',
    'status' => $subject['status'] ?? 'Active',
    'class_id' => $subject['class_id'] ?? '',
    'teacher_ids' => $assignedTeacherIds
];

// Handle form submission BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_subject') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $formData = [
            'subject_name' => sanitize($_POST['subject_name'] ?? ''),
            'subject_code' => strtoupper(sanitize($_POST['subject_code'] ?? '')),
            'description' => sanitize($_POST['description'] ?? ''),
            'credits_hours' => (int)($_POST['credits_hours'] ?? 0),
            'subject_type' => sanitize($_POST['subject_type'] ?? 'Core'),
            'status' => sanitize($_POST['status'] ?? 'Active'),
            'class_id' => $_POST['class_id'] ?: null,
            'teacher_ids' => $_POST['teacher_ids'] ?? []
        ];
        
        // Validation
        if (empty($formData['subject_name'])) {
            $errors[] = 'Subject name is required.';
        } elseif ($subjectModel->subjectNameExists($formData['subject_name'], $subjectId)) {
            $errors[] = 'Subject name "' . $formData['subject_name'] . '" already exists. Please use a different name.';
        }
        
        if (empty($formData['subject_code'])) {
            $errors[] = 'Subject code is required.';
        } elseif ($subjectModel->subjectCodeExists($formData['subject_code'], $subjectId)) {
            $errors[] = 'Subject code "' . $formData['subject_code'] . '" already exists. Please use a different code.';
        }
        
        // If no errors, update subject
        if (empty($errors)) {
            $teacherIds = $formData['teacher_ids'];
            unset($formData['teacher_ids']);
            
            $result = $subjectModel->updateWithTeachers($subjectId, $formData, $teacherIds);
            if ($result) {
                setFlash('success', 'Subject "' . $formData['subject_name'] . '" updated successfully!');
                redirect(BASE_URL . '/admin/subjects/view.php?id=' . $subjectId);
            } else {
                $errors[] = 'Failed to update subject. Please try again.';
            }
        }
    }
}

// Now include the header (after POST handling)
$pageTitle = 'Edit Subject';
require_once __DIR__ . '/../../includes/admin_header.php';

// Get all options
$allTeachers = $teacherModel->findAll('name');
$allClasses = $classModel->findAll('class_name');
?>

<style>
/* Edit Form Styles */
.edit-form-container {
    max-width: 900px;
}

.subject-form-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

.subject-form-grid .full-width {
    grid-column: 1 / -1;
}

.subject-form-grid .half-width {
    grid-column: span 2;
}

/* Multi-select styles */
.teacher-checkboxes {
    max-height: 250px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 0.75rem;
    background: #fafafa;
}

.teacher-checkbox-item {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    border-radius: 6px;
    transition: background 0.2s;
}

.teacher-checkbox-item:hover {
    background: #f0f0f0;
}

.teacher-checkbox-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-right: 0.75rem;
    accent-color: var(--primary);
}

.teacher-checkbox-item label {
    margin: 0;
    cursor: pointer;
    flex: 1;
}

.teacher-checkbox-item.checked {
    background: #e8f4ff;
}

/* Teacher ID badge in checkbox list */
.teacher-id-badge {
    display: inline-block;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-right: 0.5rem;
    font-family: monospace;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
    flex-wrap: wrap;
}

/* Responsive */
@media (max-width: 1024px) {
    .subject-form-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .subject-form-grid {
        grid-template-columns: 1fr;
    }
    
    .subject-form-grid .half-width,
    .subject-form-grid .full-width {
        grid-column: 1;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}
</style>

<!-- Breadcrumb -->
<div style="margin-bottom: 1.5rem;">
    <a href="<?php echo BASE_URL; ?>/admin/subjects/" style="color: var(--primary); text-decoration: none;">
        <i class="fas fa-arrow-left"></i> Back to Subjects
    </a>
</div>

<!-- Error Messages (using validation-alert for consistency with student/teacher) -->
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

<!-- Edit Form -->
<div class="card edit-form-container">
    <div class="card-header">
        <h3><i class="fas fa-edit"></i> Edit Subject: <?php echo htmlspecialchars($subject['subject_name']); ?></h3>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="edit_subject">
            
            <div class="subject-form-grid">
                <!-- Subject Name -->
                <div class="form-group">
                    <label for="subject_name">Subject Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="subject_name" name="subject_name" class="form-control" 
                           placeholder="e.g., Mathematics" required
                           value="<?php echo htmlspecialchars($formData['subject_name']); ?>">
                </div>
                
                <!-- Subject Code -->
                <div class="form-group">
                    <label for="subject_code">Subject Code <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="subject_code" name="subject_code" class="form-control" 
                           placeholder="e.g., MATH101" required style="text-transform: uppercase;"
                           value="<?php echo htmlspecialchars($formData['subject_code']); ?>">
                </div>
                
                <!-- Subject Type -->
                <div class="form-group">
                    <label for="subject_type">Subject Type</label>
                    <select id="subject_type" name="subject_type" class="form-control">
                        <option value="Core" <?php echo $formData['subject_type'] === 'Core' ? 'selected' : ''; ?>>Core Subject</option>
                        <option value="Elective" <?php echo $formData['subject_type'] === 'Elective' ? 'selected' : ''; ?>>Elective</option>
                        <option value="Lab" <?php echo $formData['subject_type'] === 'Lab' ? 'selected' : ''; ?>>Lab/Practical</option>
                    </select>
                </div>
                
                <!-- Credits/Hours -->
                <div class="form-group">
                    <label for="credits_hours">Credits / Hours per Week</label>
                    <input type="number" id="credits_hours" name="credits_hours" class="form-control" 
                           placeholder="e.g., 4" min="0" max="20"
                           value="<?php echo (int)$formData['credits_hours']; ?>">
                </div>
                
                <!-- Class -->
                <div class="form-group">
                    <label for="class_id">Assign to Class</label>
                    <select id="class_id" name="class_id" class="form-control">
                        <option value="">All Classes / No Specific Class</option>
                        <?php foreach ($allClasses as $class): ?>
                            <option value="<?php echo $class['class_id']; ?>" 
                                    <?php echo $formData['class_id'] == $class['class_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Status -->
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="Active" <?php echo $formData['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo $formData['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <!-- Description -->
                <div class="form-group full-width">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3"
                              placeholder="Brief description of the subject (optional)"><?php echo htmlspecialchars($formData['description']); ?></textarea>
                </div>
                
                <!-- Assign Teachers (Multi-select with Search) -->
                <div class="form-group full-width">
                    <label>Assign Teachers (Select Multiple)</label>
                    <!-- Search Input -->
                    <div class="teacher-search-wrapper" style="margin-bottom: 0.5rem;">
                        <input type="text" id="teacherSearchEdit" class="form-control" 
                               placeholder="🔍 Search by teacher name or ID..." 
                               style="border-radius: 8px 8px 0 0;">
                        <small id="teacherSearchResultsEdit" style="color: #666; display: block; padding: 0.25rem 0.5rem; background: #f5f5f5; border-radius: 0 0 8px 8px;">
                            Showing all <?php echo count($allTeachers); ?> teachers
                        </small>
                    </div>
                    <div class="teacher-checkboxes" id="teacherListEdit">
                        <?php if (!empty($allTeachers)): ?>
                            <?php foreach ($allTeachers as $teacher): 
                                $isChecked = in_array($teacher['teacher_id'], $formData['teacher_ids']);
                            ?>
                                <div class="teacher-checkbox-item <?php echo $isChecked ? 'checked' : ''; ?>"
                                     data-name="<?php echo strtolower(htmlspecialchars($teacher['name'])); ?>"
                                     data-id="<?php echo strtolower(htmlspecialchars($teacher['teacher_id_custom'] ?? '')); ?>">
                                    <input type="checkbox" 
                                           id="teacher_<?php echo $teacher['teacher_id']; ?>" 
                                           name="teacher_ids[]" 
                                           value="<?php echo $teacher['teacher_id']; ?>"
                                           <?php echo $isChecked ? 'checked' : ''; ?>>
                                    <label for="teacher_<?php echo $teacher['teacher_id']; ?>">
                                        <?php if (!empty($teacher['teacher_id_custom'])): ?>
                                            <span class="teacher-id-badge"><?php echo htmlspecialchars($teacher['teacher_id_custom']); ?></span>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($teacher['name']); ?>
                                        <?php if (!empty($teacher['email'])): ?>
                                            <small style="color: #888;">- <?php echo htmlspecialchars($teacher['email']); ?></small>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: #999; margin: 0;">No teachers available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="<?php echo BASE_URL; ?>/admin/subjects/view.php?id=<?php echo $subjectId; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Teacher Search Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('teacherSearchEdit');
    const teacherList = document.getElementById('teacherListEdit');
    const resultsInfo = document.getElementById('teacherSearchResultsEdit');
    const totalTeachers = <?php echo count($allTeachers); ?>;
    
    if (searchInput && teacherList) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const items = teacherList.querySelectorAll('.teacher-checkbox-item');
            let visibleCount = 0;
            
            items.forEach(item => {
                const name = item.getAttribute('data-name') || '';
                const teacherId = item.getAttribute('data-id') || '';
                
                if (searchTerm === '' || name.includes(searchTerm) || teacherId.includes(searchTerm)) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Update results info
            if (searchTerm === '') {
                resultsInfo.textContent = `Showing all ${totalTeachers} teachers`;
            } else if (visibleCount === 0) {
                resultsInfo.innerHTML = `<span style="color: #dc2626;">No teachers found matching "${this.value}"</span>`;
            } else {
                resultsInfo.textContent = `Found ${visibleCount} teacher${visibleCount !== 1 ? 's' : ''} matching "${this.value}"`;
            }
        });
        
        // Clear search on escape
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                this.dispatchEvent(new Event('input'));
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
