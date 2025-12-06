<?php
/**
 * Admin - Classes & Sections Management
 */

require_once __DIR__ . '/../../config.php';

$classModel = new ClassModel();
$teacherModel = new Teacher();

// Handle class creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_class') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $className = sanitize($_POST['class_name']);
        if (!empty($className)) {
            // Check if class name already exists
            if ($classModel->classNameExists($className)) {
                setFlash('danger', 'A class with this name already exists.');
            } else {
                $classId = $classModel->create(['class_name' => $className]);
                if ($classId) {
                    setFlash('success', 'Class added successfully!');
                } else {
                    setFlash('danger', 'Failed to add class.');
                }
            }
        }
    }
    redirect(BASE_URL . '/admin/classes/');
}

// Handle section creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_section') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $data = [
            'class_id' => $_POST['class_id'],
            'section_name' => sanitize($_POST['section_name']),
            'class_teacher_id' => $_POST['class_teacher_id'] ?: null
        ];
        
        if (!empty($data['class_id']) && !empty($data['section_name'])) {
            if ($classModel->sectionExists($data['class_id'], $data['section_name'])) {
                setFlash('danger', 'Section already exists in this class.');
            } else {
                $sectionId = $classModel->createSection($data);
                if ($sectionId) {
                    setFlash('success', 'Section added successfully!');
                } else {
                    setFlash('danger', 'Failed to add section.');
                }
            }
        }
    }
    redirect(BASE_URL . '/admin/classes/');
}

// Get all classes with sections
$classes = $classModel->getClassesWithSections();
$allTeachers = $teacherModel->findAll('name');

$pageTitle = 'Classes & Sections';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<script>
// Test toast on page load
setTimeout(function() {
    console.log('Testing toast...');
    console.log('showToast function exists:', typeof showToast);
    if (typeof showToast === 'function') {
        console.log('Calling showToast...');
        showToast('Page loaded successfully!', 'info');
    } else {
        console.error('showToast function not found!');
    }
}, 500);
</script>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- Add Class Card -->
    <div class="card">
        <div class="card-header">
            <h3>Add New Class</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="" id="addClassForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="add_class">
                
                <div class="form-group">
                    <label for="class_name">Class Name</label>
                    <input type="text" id="class_name" name="class_name" class="form-control" 
                           placeholder="e.g., Class 11" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Class
                </button>
            </form>
        </div>
    </div>
    
    <!-- Add Section Card -->
    <div class="card">
        <div class="card-header">
            <h3>Add New Section</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="" id="addSectionForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="add_section">
                
                <div class="form-group">
                    <label for="class_id">Class</label>
                    <select id="class_id" name="class_id" class="form-control" required>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['class_id']; ?>">
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="section_name">Section Name</label>
                    <input type="text" id="section_name" name="section_name" class="form-control" 
                           placeholder="e.g., A, B, C" required>
                </div>
                
                <div class="form-group">
                    <label for="class_teacher_id">Class Teacher (Optional)</label>
                    <select id="class_teacher_id" name="class_teacher_id" class="form-control">
                        <option value="">Select Teacher</option>
                        <?php foreach ($allTeachers as $teacher): ?>
                            <option value="<?php echo $teacher['teacher_id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($teacher['name']); ?>"
                                    data-custom-id="<?php echo htmlspecialchars($teacher['teacher_id_custom'] ?? ''); ?>">
                                <?php echo htmlspecialchars($teacher['name']); ?>
                                <?php if (!empty($teacher['teacher_id_custom'])): ?>
                                    (ID: <?php echo htmlspecialchars($teacher['teacher_id_custom']); ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Start typing to search for a teacher</small>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Section
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Classes List -->
<div class="card">
    <div class="card-header">
        <h3>All Classes & Sections</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($classes)): ?>
            <div style="display: grid; gap: 1.5rem;">
                <?php foreach ($classes as $class): ?>
                    <?php $classDetails = $classModel->getClassDetails($class['class_id']); ?>
                    <div data-class-id="<?php echo $class['class_id']; ?>" data-class-name="<?php echo htmlspecialchars($class['class_name']); ?>" style="border: 2px solid var(--light); border-radius: 10px; padding: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h4 style="margin: 0; color: var(--primary);">
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </h4>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <span class="badge badge-info">
                                    <?php echo $class['section_count']; ?> Section<?php echo $class['section_count'] != 1 ? 's' : ''; ?>
                                </span>
                                <a href="<?php echo BASE_URL; ?>/admin/classes/delete_class.php?id=<?php echo $class['class_id']; ?>" 
                                   class="btn btn-danger btn-sm delete-btn"
                                   data-delete-url="<?php echo BASE_URL; ?>/admin/classes/delete_class.php?id=<?php echo $class['class_id']; ?>"
                                   data-delete-message="Are you sure you want to delete class '<?php echo htmlspecialchars($class['class_name']); ?>'? All sections must be deleted first.">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        
                        <?php if (!empty($classDetails['sections'])): ?>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
                                <?php foreach ($classDetails['sections'] as $section): ?>
                                    <div data-section-name="<?php echo htmlspecialchars($section['section_name']); ?>" style="background: var(--light); padding: 1rem; border-radius: 8px; transition: all 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';" onmouseout="this.style.transform=''; this.style.boxShadow='';">
                                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                            <strong style="font-size: 1.1rem;">Section <?php echo htmlspecialchars($section['section_name']); ?></strong>
                                            <div style="display: flex; gap: 0.25rem;">
                                                <a href="<?php echo BASE_URL; ?>/admin/classes/view_section.php?id=<?php echo $section['section_id']; ?>" 
                                                   class="btn btn-info btn-sm" title="View Details" style="padding: 0.25rem 0.5rem;">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?php echo BASE_URL; ?>/admin/classes/edit_section.php?id=<?php echo $section['section_id']; ?>" 
                                                   class="btn btn-warning btn-sm" title="Edit" style="padding: 0.25rem 0.5rem;">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo BASE_URL; ?>/admin/classes/delete_section.php?id=<?php echo $section['section_id']; ?>" 
                                                   class="btn btn-danger btn-sm delete-btn" title="Delete" style="padding: 0.25rem 0.5rem;"
                                                   data-delete-url="<?php echo BASE_URL; ?>/admin/classes/delete_section.php?id=<?php echo $section['section_id']; ?>"
                                                   data-delete-message="Are you sure you want to delete Section '<?php echo htmlspecialchars($section['section_name']); ?>' from <?php echo htmlspecialchars($class['class_name']); ?>?">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <p style="margin: 0; color: #666; font-size: 0.9rem;">
                                            <i class="fas fa-user"></i> 
                                            <?php echo $section['class_teacher_name'] ? htmlspecialchars($section['class_teacher_name']) : 'No class teacher'; ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="color: #999; margin: 0;">No sections created yet.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; padding: 2rem; color: #999;">No classes found.</p>
        <?php endif; ?>
    </div>
</div>

<script>
// Make teacher select searchable
document.addEventListener('DOMContentLoaded', function() {
    const teacherSelect = document.getElementById('class_teacher_id');
    
    if (teacherSelect) {
        // Store original options
        const originalOptions = Array.from(teacherSelect.options);
        
        // Create a search input wrapper
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        teacherSelect.parentNode.insertBefore(wrapper, teacherSelect);
        wrapper.appendChild(teacherSelect);
        
        // Create search input
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.className = 'form-control';
        searchInput.placeholder = 'Type to search teachers...';
        searchInput.style.marginBottom = '5px';
        
        // Insert search input before select
        wrapper.insertBefore(searchInput, teacherSelect);
        
        // Hide select initially, show on focus
        teacherSelect.style.display = 'none';
        teacherSelect.size = 8;
        
        // Track if user has made a valid selection
        let validSelection = true;
        
        // Show/hide select on search input focus
        searchInput.addEventListener('focus', function() {
            teacherSelect.style.display = 'block';
            filterOptions('');
        });
        
        // Filter options as user types
        searchInput.addEventListener('input', function() {
            filterOptions(this.value);
            // Mark as invalid selection when user types
            if (this.value.trim() !== '') {
                validSelection = false;
                searchInput.style.borderColor = '#ffc107'; // Warning color
            } else {
                validSelection = true;
                teacherSelect.value = ''; // Clear selection
                searchInput.style.borderColor = '';
            }
        });
        
        // Update search input when option is selected
        teacherSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                searchInput.value = selectedOption.text;
                validSelection = true;
                searchInput.style.borderColor = '#28a745'; // Success color
            } else {
                searchInput.value = '';
                validSelection = true;
                searchInput.style.borderColor = '';
            }
            teacherSelect.style.display = 'none';
        });
        
        // Handle clicking on an option
        teacherSelect.addEventListener('click', function() {
            const selectedOption = this.options[this.selectedIndex];
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
        });
        
        // Hide select when clicking outside
        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) {
                teacherSelect.style.display = 'none';
            }
        });
        
        // Form validation
        const form = teacherSelect.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                // If there's text in search input but no valid selection
                if (searchInput.value.trim() !== '' && !validSelection) {
                    e.preventDefault();
                    searchInput.style.borderColor = '#dc3545'; // Danger color
                    showToast('Please select a teacher from the dropdown list, or clear the field to leave it empty.', 'warning');
                    searchInput.focus();
                    teacherSelect.style.display = 'block';
                    filterOptions(searchInput.value);
                    return false;
                }
                
                // If search input is empty, make sure select is also empty
                if (searchInput.value.trim() === '') {
                    teacherSelect.value = '';
                }
            });
        }
        
        function filterOptions(searchTerm) {
            const term = searchTerm.toLowerCase();
            
            // Clear current options
            teacherSelect.innerHTML = '';
            
            // Filter and add matching options
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
    }
    
    // Validate duplicate sections
    const addSectionForm = document.getElementById('addSectionForm');
    if (addSectionForm) {
        addSectionForm.addEventListener('submit', function(e) {
            const classId = document.getElementById('class_id').value;
            const sectionName = document.getElementById('section_name').value.trim();
            
            if (!classId || !sectionName) {
                return; // Let HTML5 validation handle this
            }
            
            // Get all existing sections for the selected class
            const classCards = document.querySelectorAll('[data-class-id]');
            let isDuplicate = false;
            
            classCards.forEach(card => {
                if (card.getAttribute('data-class-id') === classId) {
                    const sections = card.querySelectorAll('[data-section-name]');
                    sections.forEach(section => {
                        const existingSectionName = section.getAttribute('data-section-name').trim();
                        if (existingSectionName.toLowerCase() === sectionName.toLowerCase()) {
                            isDuplicate = true;
                        }
                    });
                }
            });
            
            if (isDuplicate) {
                e.preventDefault();
                const sectionInput = document.getElementById('section_name');
                sectionInput.style.borderColor = '#dc3545';
                showToast('A section with this name already exists in the selected class. Please use a different name.', 'warning');
                sectionInput.focus();
                return false;
            }
        });
        
        // Reset border color when user types
        document.getElementById('section_name').addEventListener('input', function() {
            this.style.borderColor = '';
        });
    }
    
    // Validate duplicate class names
    const addClassForm = document.getElementById('addClassForm');
    if (addClassForm) {
        addClassForm.addEventListener('submit', function(e) {
            const className = document.getElementById('class_name').value.trim();
            
            if (!className) {
                return; // Let HTML5 validation handle this
            }
            
            // Get all existing class names
            const classCards = document.querySelectorAll('[data-class-name]');
            let isDuplicate = false;
            
            classCards.forEach(card => {
                const existingClassName = card.getAttribute('data-class-name').trim();
                if (existingClassName.toLowerCase() === className.toLowerCase()) {
                    isDuplicate = true;
                }
            });
            
            if (isDuplicate) {
                e.preventDefault();
                const classInput = document.getElementById('class_name');
                classInput.style.borderColor = '#dc3545';
                showToast('A class with this name already exists. Please use a different name.', 'warning');
                classInput.focus();
                return false;
            }
        });
        
        // Reset border color when user types
        document.getElementById('class_name').addEventListener('input', function() {
            this.style.borderColor = '';
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
