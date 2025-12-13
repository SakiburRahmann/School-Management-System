<?php
/**
 * Admin - Subject Management
 * Enhanced with multi-teacher support and responsive UI
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
$formData = [
    'subject_name' => '',
    'subject_code' => '',
    'description' => '',
    'credits_hours' => '',
    'subject_type' => 'Core',
    'class_id' => '',
    'teacher_ids' => []
];

// Handle subject creation BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_subject') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $formData = [
            'subject_name' => sanitize($_POST['subject_name'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'credits_hours' => (int)($_POST['credits_hours'] ?? 0),
            'subject_type' => sanitize($_POST['subject_type'] ?? 'Core'),
            'class_id' => $_POST['class_id'] ?: null,
            'teacher_ids' => $_POST['teacher_ids'] ?? []
        ];
        
        // Validation
        if (empty($formData['subject_name'])) {
            $errors[] = 'Subject name is required.';
        } elseif ($subjectModel->subjectNameExists($formData['subject_name'])) {
            $errors[] = 'Subject name "' . $formData['subject_name'] . '" already exists. Please use a different name.';
        }
        
        // If no errors, create subject
        if (empty($errors)) {
            $teacherIds = $formData['teacher_ids'];
            unset($formData['teacher_ids']);
            
            $subjectId = $subjectModel->createWithTeachers($formData, $teacherIds);
            if ($subjectId) {
                setFlash('success', 'Subject "' . $formData['subject_name'] . '" added successfully!');
                redirect(BASE_URL . '/admin/subjects/');
            } else {
                $errors[] = 'Failed to add subject. Please try again.';
            }
        }
    }
}

// Now include the header (after POST handling)
$pageTitle = 'Manage Subjects';
require_once __DIR__ . '/../../includes/admin_header.php';

// Get all data
$subjects = $subjectModel->getSubjectsWithDetails();
$allTeachers = $teacherModel->findAll('name');
$allClasses = $classModel->findAll('class_name');
?>

<style>
/* Modern Dashboard Styling (consistent with Classes & Exams) */
:root {
    --primary-soft: #eef2ff;
    --primary-border: #c7d2fe;
    --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    --hover-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.page-header {
    background: white;
    padding: 1.5rem 2rem;
    border-radius: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.action-card {
    background: white;
    border: 1px solid #f3f4f6;
    border-radius: 1rem;
    padding: 1.5rem;
    height: 100%;
    transition: transform 0.2s, box-shadow 0.2s;
    box-shadow: var(--card-shadow);
    margin-bottom: 2rem;
}

.action-card:hover {
    box-shadow: var(--hover-shadow);
}

.action-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f3f4f6;
}

.form-control-modern {
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem; /* Slightly larger for touch */
    font-size: 0.95rem;
    width: 100%;
    transition: all 0.2s;
}

.form-control-modern:focus {
    background-color: white;
    border-color: #4e73df;
    outline: none;
    box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
}

.btn-modern {
    width: 100%;
    padding: 0.75rem;
    border-radius: 0.5rem;
    font-weight: 600;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

/* Subject Grid */
.subject-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.subject-card {
    background: white;
    border-radius: 1rem;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    box-shadow: var(--card-shadow);
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-direction: column;
}

.subject-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--hover-shadow);
}

.subject-header {
    padding: 1.25rem;
    background: linear-gradient(to right, #f9fafb, #fff);
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.subject-icon {
    width: 42px;
    height: 42px;
    background: #e0f2fe;
    color: #0369a1;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.subject-title-area {
    margin-left: 1rem;
    flex-grow: 1;
}

.subject-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.2rem;
    line-height: 1.3;
}

.subject-meta {
    font-size: 0.85rem;
    color: #6b7280;
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.subject-body {
    padding: 1.25rem;
    flex-grow: 1;
}

.info-row {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
    color: #4b5563;
}

.info-row i {
    width: 24px;
    color: #9ca3af;
    text-align: center;
    margin-right: 0.5rem;
}

.teachers-list {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px dashed #e5e7eb;
}

.teacher-tag {
    display: inline-flex;
    align-items: center;
    background: #f3f4f6;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.8rem;
    font-weight: 500;
    color: #374151;
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
}

.teacher-tag i {
    font-size: 0.7rem;
    margin-right: 0.4rem;
    width: auto;
}

.subject-footer {
    padding: 1rem 1.25rem;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.badge-modern {
    padding: 0.25rem 0.6rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}
.badge-core { background: #dbeafe; color: #1e40af; }
.badge-elective { background: #fef3c7; color: #92400e; }
.badge-lab { background: #d1fae5; color: #065f46; }

/* Custom teacher selector styling */
.teacher-select-box {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    overflow: hidden;
    background: #fff;
}
.teacher-search-input {
    border: none;
    border-bottom: 1px solid #e5e7eb;
    padding: 0.75rem;
    width: 100%;
    outline: none;
    background: #f9fafb;
}
.teacher-search-input:focus {
    background: #fff;
}
.teacher-list-scroll {
    max-height: 200px;
    overflow-y: auto;
}
.teacher-option {
    padding: 0.5rem 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    transition: background 0.1s;
}
.teacher-option:hover {
    background: #f3f4f6;
}
.teacher-option input[type="checkbox"] {
    width: 1.1rem;
    height: 1.1rem;
    accent-color: #4e73df;
    cursor: pointer;
}
.teacher-label {
    flex-grow: 1;
    font-size: 0.9rem;
    color: #374151;
    cursor: pointer;
}
</style>

<!-- Error Messages (for duplicate validation) -->
<?php if (!empty($errors)): ?>
    <div class="validation-alert validation-alert-danger" style="margin-bottom: 2rem;">
        <div class="validation-alert-icon">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div class="validation-alert-content">
            <strong>Check these errors:</strong>
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

<div class="page-header">
    <div>
        <h3 style="margin:0; font-weight: 700; color: #111827;">Academic Subjects</h3>
        <p style="margin: 0.25rem 0 0 0; color: #6b7280;">Manage curriculum, credits, and teacher assignments.</p>
    </div>
</div>

<!-- Add Subject Form Card -->
<div class="action-card">
    <div class="action-title">
        <div style="background: #e0f2fe; padding: 8px; border-radius: 8px; color: #0369a1;">
            <i class="fas fa-book-open"></i>
        </div>
        Add New Subject
    </div>
    
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="action" value="add_subject">
        
        <div class="row">
            <!-- Left Column: Basic Info -->
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label class="form-label" style="font-weight: 600; color: #374151;">Subject Name <span class="text-danger">*</span></label>
                    <input type="text" name="subject_name" class="form-control-modern" 
                           placeholder="e.g. Advanced Mathematics" required
                           value="<?php echo htmlspecialchars($formData['subject_name']); ?>">
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label" style="font-weight: 600; color: #374151;">Type</label>
                            <select name="subject_type" class="form-control-modern">
                                <option value="Core" <?php echo $formData['subject_type'] === 'Core' ? 'selected' : ''; ?>>Core</option>
                                <option value="Elective" <?php echo $formData['subject_type'] === 'Elective' ? 'selected' : ''; ?>>Elective</option>
                                <option value="Lab" <?php echo $formData['subject_type'] === 'Lab' ? 'selected' : ''; ?>>Lab</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label" style="font-weight: 600; color: #374151;">Credits/Hours</label>
                            <input type="number" name="credits_hours" class="form-control-modern" 
                                   placeholder="e.g. 4" min="0" max="20"
                                   value="<?php echo htmlspecialchars($formData['credits_hours']); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" style="font-weight: 600; color: #374151;">Assign to Class</label>
                    <select name="class_id" class="form-control-modern">
                        <option value="">All Classes / No Specific Class</option>
                        <?php foreach ($allClasses as $class): ?>
                            <option value="<?php echo $class['class_id']; ?>" 
                                    <?php echo $formData['class_id'] == $class['class_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Right Column: Teachers & Description -->
            <div class="col-md-6">
                 <div class="form-group mb-3">
                    <label class="form-label" style="font-weight: 600; color: #374151;">Assign Teachers (Optional)</label>
                    <div class="teacher-select-box">
                        <input type="text" id="teacherSearchAdd" class="teacher-search-input" placeholder="🔍 Search by name...">
                        <div class="teacher-list-scroll" id="teacherListAdd">
                            <?php if (!empty($allTeachers)): ?>
                                <?php foreach ($allTeachers as $teacher): ?>
                                    <label class="teacher-option" data-name="<?php echo strtolower(htmlspecialchars($teacher['name'])); ?>">
                                        <input type="checkbox" name="teacher_ids[]" value="<?php echo $teacher['teacher_id']; ?>"
                                               <?php echo in_array($teacher['teacher_id'], $formData['teacher_ids']) ? 'checked' : ''; ?>>
                                        <span class="teacher-label">
                                            <?php echo htmlspecialchars($teacher['name']); ?>
                                            <?php if (!empty($teacher['teacher_id_custom'])): ?>
                                                <small class="text-muted">(<?php echo htmlspecialchars($teacher['teacher_id_custom']); ?>)</small>
                                            <?php endif; ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="padding: 1rem; text-align: center; color: #9ca3af;">No teachers found</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <small class="text-muted" id="teacherSearchResultsAdd">Select multiple teachers if needed.</small>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" style="font-weight: 600; color: #374151;">Description (Optional)</label>
                     <textarea name="description" class="form-control-modern" rows="1" style="resize: none;"
                               placeholder="Brief details..."><?php echo htmlspecialchars($formData['description']); ?></textarea>
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary btn-modern" style="margin-top: 1rem;">
            <i class="fas fa-plus-circle"></i> Create Subject
        </button>
    </form>
</div>

<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
    <h4 style="font-weight: 700; color: #374151; margin: 0;">Existing Subjects</h4>
    <span class="badge" style="background: #e5e7eb; color: #374151; padding: 0.4rem 0.8rem; border-radius: 20px;">
        <?php echo count($subjects); ?> Total
    </span>
</div>

<?php if (!empty($subjects)): ?>
    <div class="subject-grid">
        <?php foreach ($subjects as $subject): ?>
            <div class="subject-card">
                <div class="subject-header">
                    <div class="subject-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="subject-title-area">
                        <div class="subject-name"><?php echo htmlspecialchars($subject['subject_name']); ?></div>
                        <div class="subject-meta">
                            <span>Code: <?php echo htmlspecialchars($subject['subject_code']); ?></span>
                        </div>
                    </div>
                    <span class="badge-modern badge-<?php echo strtolower($subject['subject_type']); ?>">
                        <?php echo htmlspecialchars($subject['subject_type']); ?>
                    </span>
                </div>
                
                <div class="subject-body">
                    <div class="info-row">
                        <i class="fas fa-chalkboard"></i>
                        <span style="font-weight: 500;">
                            <?php echo htmlspecialchars($subject['class_name'] ?? 'All Classes'); ?>
                        </span>
                    </div>
                    
                    <?php if ($subject['credits_hours'] > 0): ?>
                    <div class="info-row">
                        <i class="fas fa-clock"></i>
                        <span><?php echo (int)$subject['credits_hours']; ?> Credits / Hours</span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="teachers-list">
                         <?php if (!empty($subject['teacher_names'])): 
                            $teachers = explode(', ', $subject['teacher_names']);
                            $displayTeachers = array_slice($teachers, 0, 3);
                         ?>
                            <?php foreach ($displayTeachers as $teacher): ?>
                                <span class="teacher-tag">
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($teacher); ?>
                                </span>
                            <?php endforeach; ?>
                            <?php if (count($teachers) > 3): ?>
                                <span class="teacher-tag" style="background: #e5e7eb;">+<?php echo count($teachers) - 3; ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color: #9ca3af; font-size: 0.9rem; font-style: italic;">No teachers assigned</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="subject-footer">
                    <div>
                         <!-- Status Dot -->
                         <div style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; color: #059669; font-weight: 600;">
                             <div style="width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></div>
                             Active
                         </div>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                         <a href="<?php echo BASE_URL; ?>/admin/subjects/edit.php?id=<?php echo $subject['subject_id']; ?>" 
                           class="btn btn-warning btn-sm" style="border-radius: 6px;" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/subjects/delete.php?id=<?php echo $subject['subject_id']; ?>" 
                           class="btn btn-danger btn-sm delete-btn" style="border-radius: 6px;"
                           data-delete-url="<?php echo BASE_URL; ?>/admin/subjects/delete.php?id=<?php echo $subject['subject_id']; ?>"
                           data-delete-message="Delete subject '<?php echo htmlspecialchars($subject['subject_name']); ?>'?"
                           title="Delete">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div style="text-align: center; padding: 4rem; background: white; border-radius: 1rem; color: #6b7280; box-shadow: var(--card-shadow);">
        <i class="fas fa-book-reader fa-3x mb-3" style="color: #e5e7eb;"></i>
        <h4>No Subjects Yet</h4>
        <p>Use the form above to add your first subject to the curriculum.</p>
    </div>
<?php endif; ?>

<script>
// Teacher Search Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('teacherSearchAdd');
    const teacherList = document.getElementById('teacherListAdd');
    const resultsInfo = document.getElementById('teacherSearchResultsAdd');
    
    if (searchInput && teacherList) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const items = teacherList.querySelectorAll('.teacher-option');
            let visibleCount = 0;
            
            items.forEach(item => {
                const name = item.getAttribute('data-name') || '';
                if (searchTerm === '' || name.includes(searchTerm)) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Update results info
            if (searchTerm !== '') {
                resultsInfo.textContent = visibleCount > 0 
                    ? `Found ${visibleCount} match(es)` 
                    : 'No matches found';
                resultsInfo.style.color = visibleCount > 0 ? '#666' : '#dc2626';
            } else {
                resultsInfo.textContent = 'Select multiple teachers if needed.';
                resultsInfo.style.color = '#666';
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>

