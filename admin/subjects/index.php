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
            'subject_code' => strtoupper(sanitize($_POST['subject_code'] ?? '')),
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
        
        if (empty($formData['subject_code'])) {
            $errors[] = 'Subject code is required.';
        } elseif ($subjectModel->subjectCodeExists($formData['subject_code'])) {
            $errors[] = 'Subject code "' . $formData['subject_code'] . '" already exists. Please use a different code.';
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
/* Subjects Page Styles */
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
.teacher-select-container {
    position: relative;
}

.teacher-checkboxes {
    max-height: 200px;
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

/* Subject cards for table on mobile */
.subject-card {
    display: none;
}

/* Subject type badges */
.type-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.type-badge.core { background: #dbeafe; color: #1d4ed8; }
.type-badge.elective { background: #fef3c7; color: #b45309; }
.type-badge.lab { background: #d1fae5; color: #047857; }

/* Status badge */
.status-badge {
    display: inline-block;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}

.status-badge.active { background: #d1fae5; color: #047857; }
.status-badge.inactive { background: #fee2e2; color: #dc2626; }

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

/* Search no results message */
.no-results-message {
    text-align: center;
    padding: 1.5rem;
    color: #999;
}

/* Teacher tags */
.teacher-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
}

.teacher-tag {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.teacher-count {
    background: #e5e7eb;
    color: #374151;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
}

/* Action buttons */
.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.action-buttons .btn-sm {
    padding: 0.4rem 0.6rem;
}

/* Responsive */
@media (max-width: 1024px) {
    .subject-form-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .subject-form-grid .half-width {
        grid-column: 1 / -1;
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
    
    /* Hide table, show cards */
    .subjects-table {
        display: none;
    }
    
    .subject-card {
        display: block;
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-left: 4px solid var(--primary);
    }
    
    .subject-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.75rem;
    }
    
    .subject-card-title {
        font-weight: 600;
        color: var(--dark);
        margin: 0;
    }
    
    .subject-card-code {
        font-family: monospace;
        background: #f3f4f6;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        font-size: 0.85rem;
    }
    
    .subject-card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }
    
    .subject-card-info {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 0.5rem;
    }
    
    .subject-card-actions {
        display: flex;
        gap: 0.5rem;
        padding-top: 0.75rem;
        border-top: 1px solid #eee;
    }
    
    .subject-card-actions .btn {
        flex: 1;
        text-align: center;
    }
}
</style>

<!-- Error Messages (for duplicate validation) -->
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

<!-- Add Subject Form -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h3><i class="fas fa-plus-circle"></i> Add New Subject</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="add_subject">
            
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
                           value="<?php echo htmlspecialchars($formData['credits_hours']); ?>">
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
                
                <!-- Status (hidden, default Active) -->
                <input type="hidden" name="status" value="Active">
                
                <!-- Description -->
                <div class="form-group full-width">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="2"
                              placeholder="Brief description of the subject (optional)"><?php echo htmlspecialchars($formData['description']); ?></textarea>
                </div>
                
                <!-- Assign Teachers (Multi-select with Search) -->
                <div class="form-group full-width">
                    <label>Assign Teachers (Optional - Select Multiple)</label>
                    <!-- Search Input -->
                    <div class="teacher-search-wrapper" style="margin-bottom: 0.5rem;">
                        <input type="text" id="teacherSearchAdd" class="form-control" 
                               placeholder="🔍 Search by teacher name or ID..." 
                               style="border-radius: 8px 8px 0 0;">
                        <small id="teacherSearchResultsAdd" style="color: #666; display: block; padding: 0.25rem 0.5rem; background: #f5f5f5; border-radius: 0 0 8px 8px;">
                            Showing all <?php echo count($allTeachers); ?> teachers
                        </small>
                    </div>
                    <div class="teacher-checkboxes" id="teacherListAdd">
                        <?php if (!empty($allTeachers)): ?>
                            <?php foreach ($allTeachers as $teacher): ?>
                                <div class="teacher-checkbox-item" 
                                     data-name="<?php echo strtolower(htmlspecialchars($teacher['name'])); ?>"
                                     data-id="<?php echo strtolower(htmlspecialchars($teacher['teacher_id_custom'] ?? '')); ?>">
                                    <input type="checkbox" 
                                           id="teacher_<?php echo $teacher['teacher_id']; ?>" 
                                           name="teacher_ids[]" 
                                           value="<?php echo $teacher['teacher_id']; ?>"
                                           <?php echo in_array($teacher['teacher_id'], $formData['teacher_ids']) ? 'checked' : ''; ?>>
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
                            <p style="color: #999; margin: 0;">No teachers available. <a href="<?php echo BASE_URL; ?>/admin/teachers/add.php">Add teachers first</a>.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">
                <i class="fas fa-plus"></i> Add Subject
            </button>
        </form>
    </div>
</div>

<!-- Subjects List -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-book"></i> All Subjects</h3>
        <span class="badge" style="background: var(--primary); color: white; padding: 0.5rem 1rem; border-radius: 20px;">
            <?php echo count($subjects); ?> Total
        </span>
    </div>
    <div class="card-body">
        <!-- Desktop Table -->
        <div class="table-responsive subjects-table">
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Class</th>
                        <th>Teachers</th>
                        <th>Credits</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($subjects)): ?>
                        <?php foreach ($subjects as $subject): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($subject['subject_name']); ?></strong></td>
                                <td><code style="background: #f3f4f6; padding: 0.2rem 0.5rem; border-radius: 4px;"><?php echo htmlspecialchars($subject['subject_code']); ?></code></td>
                                <td>
                                    <span class="type-badge <?php echo strtolower($subject['subject_type'] ?? 'core'); ?>">
                                        <?php echo htmlspecialchars($subject['subject_type'] ?? 'Core'); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($subject['class_name'] ?? 'All Classes'); ?></td>
                                <td>
                                    <?php if (!empty($subject['teacher_names'])): ?>
                                        <div class="teacher-tags">
                                            <?php 
                                            $teachers = explode(', ', $subject['teacher_names']);
                                            $displayTeachers = array_slice($teachers, 0, 2);
                                            foreach ($displayTeachers as $teacher): ?>
                                                <span class="teacher-tag"><?php echo htmlspecialchars($teacher); ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($teachers) > 2): ?>
                                                <span class="teacher-count">+<?php echo count($teachers) - 2; ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #999;">Not assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo (int)($subject['credits_hours'] ?? 0); ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($subject['status'] ?? 'active'); ?>">
                                        <?php echo htmlspecialchars($subject['status'] ?? 'Active'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?php echo BASE_URL; ?>/admin/subjects/view.php?id=<?php echo $subject['subject_id']; ?>" 
                                           class="btn btn-info btn-sm" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/admin/subjects/edit.php?id=<?php echo $subject['subject_id']; ?>" 
                                           class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/admin/subjects/delete.php?id=<?php echo $subject['subject_id']; ?>" 
                                           class="btn btn-danger btn-sm delete-btn"
                                           data-delete-url="<?php echo BASE_URL; ?>/admin/subjects/delete.php?id=<?php echo $subject['subject_id']; ?>"
                                           data-delete-message="Are you sure you want to delete subject '<?php echo htmlspecialchars($subject['subject_name']); ?>'?"
                                           title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem;">
                                <i class="fas fa-book-open" style="font-size: 3rem; color: #ddd; margin-bottom: 1rem;"></i>
                                <p style="color: #999;">No subjects found. Add your first subject above.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Mobile Cards -->
        <?php if (!empty($subjects)): ?>
            <?php foreach ($subjects as $subject): ?>
                <div class="subject-card">
                    <div class="subject-card-header">
                        <h4 class="subject-card-title"><?php echo htmlspecialchars($subject['subject_name']); ?></h4>
                        <span class="subject-card-code"><?php echo htmlspecialchars($subject['subject_code']); ?></span>
                    </div>
                    <div class="subject-card-meta">
                        <span class="type-badge <?php echo strtolower($subject['subject_type'] ?? 'core'); ?>">
                            <?php echo htmlspecialchars($subject['subject_type'] ?? 'Core'); ?>
                        </span>
                        <span class="status-badge <?php echo strtolower($subject['status'] ?? 'active'); ?>">
                            <?php echo htmlspecialchars($subject['status'] ?? 'Active'); ?>
                        </span>
                        <?php if ($subject['credits_hours']): ?>
                            <span style="font-size: 0.85rem; color: #666;">
                                <i class="fas fa-clock"></i> <?php echo (int)$subject['credits_hours']; ?> hrs/week
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="subject-card-info">
                        <i class="fas fa-school"></i> <?php echo htmlspecialchars($subject['class_name'] ?? 'All Classes'); ?>
                    </div>
                    <?php if (!empty($subject['teacher_names'])): ?>
                        <div class="subject-card-info">
                            <i class="fas fa-users"></i> <?php echo htmlspecialchars($subject['teacher_names']); ?>
                        </div>
                    <?php endif; ?>
                    <div class="subject-card-actions">
                        <a href="<?php echo BASE_URL; ?>/admin/subjects/view.php?id=<?php echo $subject['subject_id']; ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/subjects/edit.php?id=<?php echo $subject['subject_id']; ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/subjects/delete.php?id=<?php echo $subject['subject_id']; ?>" 
                           class="btn btn-danger btn-sm delete-btn"
                           data-delete-url="<?php echo BASE_URL; ?>/admin/subjects/delete.php?id=<?php echo $subject['subject_id']; ?>"
                           data-delete-message="Are you sure you want to delete this subject?">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div style="margin-top: 1.5rem; text-align: center;">
            <p><strong>Total Subjects:</strong> <?php echo count($subjects); ?></p>
        </div>
    </div>
</div>

<script>
// Teacher Search Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('teacherSearchAdd');
    const teacherList = document.getElementById('teacherListAdd');
    const resultsInfo = document.getElementById('teacherSearchResultsAdd');
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

