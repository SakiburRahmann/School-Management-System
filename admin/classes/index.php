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

<style>
/* Modern Dashboard Styling */
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
}

.action-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--hover-shadow);
}

.action-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-control-modern {
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 0.625rem 0.875rem;
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
    padding: 0.625rem;
    border-radius: 0.5rem;
    font-weight: 600;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

/* Class Cards Grid */
.class-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.class-card {
    background: white;
    border-radius: 1rem;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    box-shadow: var(--card-shadow);
}

.class-header {
    background: linear-gradient(to right, #f9fafb, #fff);
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.class-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #374151;
}

.class-body {
    padding: 1.5rem;
}

/* Section Chips */
.section-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.section-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.2s;
}

.section-item:hover {
    background: white;
    border-color: #cbd5e1;
    transform: translateX(4px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.section-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.section-badge {
    background: #e0f2fe;
    color: #0369a1;
    font-weight: 700;
    padding: 0.25rem 0.6rem;
    border-radius: 0.375rem;
    font-size: 0.85rem;
    min-width: 32px;
    text-align: center;
}

.teacher-info {
    font-size: 0.9rem;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.action-buttons {
    opacity: 0.5;
    transition: opacity 0.2s;
}

.section-item:hover .action-buttons {
    opacity: 1;
}

.btn-icon {
    padding: 0.25rem;
    color: #94a3b8;
    transition: color 0.2s;
}
.btn-icon:hover { color: #4e73df; }
.btn-icon.delete:hover { color: #ef4444; }

/* Custom Search Input override */
.teacher-search-wrapper {
    position: relative;
    margin-bottom: 0.5rem;
}
</style>

<div class="page-header">
    <div>
        <h3 style="margin:0; font-weight: 700; color: #111827;">Academic Structure</h3>
        <p style="margin: 0.25rem 0 0 0; color: #6b7280;">Manage classes, sections, and class teacher assignments.</p>
    </div>
</div>

<div class="row mb-5">
    <!-- Add Class -->
    <div class="col-md-5 mb-4">
        <div class="action-card">
            <div class="action-title">
                <div style="background: #eff6ff; padding: 8px; border-radius: 8px; color: #2563eb;">
                    <i class="fas fa-layer-group"></i>
                </div>
                Create New Class
            </div>
            <form method="POST" action="" id="addClassForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="add_class">
                
                <div class="form-group mb-3">
                    <input type="text" id="class_name" name="class_name" class="form-control-modern" 
                           placeholder="Enter Class Name (e.g. Class 10)" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-modern">
                    <i class="fas fa-plus"></i> Add Class
                </button>
            </form>
        </div>
    </div>

    <!-- Add Section -->
    <div class="col-md-7 mb-4">
        <div class="action-card" style="border-left: 4px solid #4e73df;">
            <div class="action-title">
                <div style="background: #f0fdf4; padding: 8px; border-radius: 8px; color: #16a34a;">
                    <i class="fas fa-puzzle-piece"></i>
                </div>
                Add Section to Class
            </div>
            <form method="POST" action="" id="addSectionForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="add_section">
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <select id="class_id" name="class_id" class="form-control-modern" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['class_id']; ?>">
                                        <?php echo htmlspecialchars($class['class_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <input type="text" id="section_name" name="section_name" class="form-control-modern" 
                                   placeholder="Section Name (e.g. A)" required>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                     <!-- Teacher Select will be enhanced by JS -->
                    <select id="class_teacher_id" name="class_teacher_id" class="form-control-modern">
                        <option value="">Select Class Teacher (Optional)</option>
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
                </div>
                
                <button type="submit" class="btn btn-success btn-modern">
                    <i class="fas fa-plus"></i> Create Section
                </button>
            </form>
        </div>
    </div>
</div>

<h4 style="margin-bottom: 1.5rem; font-weight: 700; color: #374151; padding-left: 0.5rem; border-left: 4px solid #4e73df;">
    Active Classes & Sections
</h4>

<?php if (!empty($classes)): ?>
    <div class="class-grid">
        <?php foreach ($classes as $class): ?>
            <?php $classDetails = $classModel->getClassDetails($class['class_id']); ?>
            <div class="class-card" 
                 data-class-id="<?php echo $class['class_id']; ?>" 
                 data-class-name="<?php echo htmlspecialchars($class['class_name']); ?>">
                
                <div class="class-header">
                    <span class="class-title"><?php echo htmlspecialchars($class['class_name']); ?></span>
                    <a href="<?php echo BASE_URL; ?>/admin/classes/delete_class.php?id=<?php echo $class['class_id']; ?>" 
                       class="btn btn-icon delete delete-btn"
                       data-delete-url="<?php echo BASE_URL; ?>/admin/classes/delete_class.php?id=<?php echo $class['class_id']; ?>"
                       data-delete-message="Delete class '<?php echo htmlspecialchars($class['class_name']); ?>'?">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                </div>
                
                <div class="class-body">
                    <?php if (!empty($classDetails['sections'])): ?>
                        <div class="section-list">
                            <?php foreach ($classDetails['sections'] as $section): ?>
                                <div class="section-item" data-section-name="<?php echo htmlspecialchars($section['section_name']); ?>">
                                    <div class="section-info">
                                        <div class="section-badge"><?php echo htmlspecialchars($section['section_name']); ?></div>
                                        <div>
                                            <div style="font-weight: 600; font-size: 0.9rem;">Section</div>
                                            <div class="teacher-info">
                                                <i class="fas fa-chalkboard-teacher" style="font-size: 0.8rem;"></i>
                                                <?php echo $section['class_teacher_name'] ? htmlspecialchars($section['class_teacher_name']) : '<span style="color:#9ca3af; font-style:italic;">No Teacher</span>'; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="action-buttons">
                                        <a href="<?php echo BASE_URL; ?>/admin/classes/view_section.php?id=<?php echo $section['section_id']; ?>" 
                                           class="btn-icon" title="View Students">
                                            <i class="fas fa-users"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/admin/classes/edit_section.php?id=<?php echo $section['section_id']; ?>" 
                                           class="btn-icon" title="Edit Section">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/admin/classes/delete_section.php?id=<?php echo $section['section_id']; ?>" 
                                           class="btn-icon delete delete-btn" title="Delete"
                                           data-delete-url="<?php echo BASE_URL; ?>/admin/classes/delete_section.php?id=<?php echo $section['section_id']; ?>"
                                           data-delete-message="Delete section '<?php echo htmlspecialchars($section['section_name']); ?>'?">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; color: #9ca3af; padding: 1.5rem 0; border: 2px dashed #f3f4f6; border-radius: 0.5rem;">
                            <small>No sections added yet</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div style="text-align: center; padding: 4rem; background: white; border-radius: 1rem; color: #6b7280;">
        <i class="fas fa-school fa-3x mb-3" style="color: #e5e7eb;"></i>
        <h4>No Classes Found</h4>
        <p>Use the form above to create your first class.</p>
    </div>
<?php endif; ?>

<script>
// Validations and Teacher Search Logic
document.addEventListener('DOMContentLoaded', function() {
    
    // --- Enhanced Teacher Search ---
    const teacherSelect = document.getElementById('class_teacher_id');
    if (teacherSelect) {
        const originalOptions = Array.from(teacherSelect.options);
        
        const wrapper = document.createElement('div');
        wrapper.className = 'teacher-search-wrapper';
        teacherSelect.parentNode.insertBefore(wrapper, teacherSelect);
        wrapper.appendChild(teacherSelect);
        
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.className = 'form-control-modern';
        searchInput.placeholder = '🔍 Type to search teacher...';
        searchInput.style.marginBottom = '5px';
        
        wrapper.insertBefore(searchInput, teacherSelect);
        
        teacherSelect.style.display = 'none';
        teacherSelect.size = 5; // Show 5 items roughly
        teacherSelect.classList.add('form-control-modern'); // Add styling
        
        let validSelection = true;
        
        const showDropdown = () => {
            teacherSelect.style.display = 'block';
            filterOptions(searchInput.value);
        };
        
        const hideDropdown = () => {
             // Delay slightly to allow click event to register
             setTimeout(() => {
                 teacherSelect.style.display = 'none';
             }, 200);
        };

        searchInput.addEventListener('focus', showDropdown);
        // searchInput.addEventListener('blur', hideDropdown); // Handled by document click
        
        searchInput.addEventListener('input', function() {
            filterOptions(this.value);
            validSelection = (this.value.trim() === '');
            this.style.borderColor = validSelection ? '' : '#f59e0b';
        });
        
        teacherSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                searchInput.value = selectedOption.text.trim();
                validSelection = true;
                searchInput.style.borderColor = '#10b981';
            } else {
                searchInput.value = '';
                validSelection = true;
            }
            teacherSelect.style.display = 'none';
        });
        
        // Hide when clicking outside
        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) {
                teacherSelect.style.display = 'none';
            }
        });

        function filterOptions(term) {
            const t = term.toLowerCase();
            teacherSelect.innerHTML = '';
            
            // Allow empty selection
            if(t === '') {
                 const emptyOpt = document.createElement('option');
                 emptyOpt.value = '';
                 emptyOpt.text = 'Select Class Teacher (Optional)';
                 teacherSelect.appendChild(emptyOpt);
            }

            let count = 0;
            originalOptions.forEach(opt => {
                if (opt.value === '') return;
                const text = opt.text.toLowerCase();
                if (text.includes(t)) {
                    teacherSelect.appendChild(opt.cloneNode(true));
                    count++;
                }
            });
            
            if(count === 0 && t !== '') {
                 const noOpt = document.createElement('option');
                 noOpt.disabled = true;
                 noOpt.text = 'No teachers found';
                 teacherSelect.appendChild(noOpt);
            }
        }
    }

    // --- Duplicate Checks ---
    
    // Class Name Check
    const addClassForm = document.getElementById('addClassForm');
    if (addClassForm) {
        addClassForm.addEventListener('submit', function(e) {
            const name = document.getElementById('class_name').value.trim();
            const existing = document.querySelectorAll(`[data-class-name]`);
            let isDup = false;
            
            existing.forEach(el => {
                if(el.getAttribute('data-class-name').toLowerCase() === name.toLowerCase()) isDup = true;
            });
            
            if (isDup) {
                e.preventDefault();
                alert('⚠️ A class with this name already exists.');
            }
        });
    }

    // Section Name Check
    const addSectionForm = document.getElementById('addSectionForm');
    if (addSectionForm) {
        addSectionForm.addEventListener('submit', function(e) {
            const classId = document.getElementById('class_id').value;
            const secName = document.getElementById('section_name').value.trim();
            
            if(!classId || !secName) return;
            
            const classCard = document.querySelector(`.class-card[data-class-id="${classId}"]`);
            if(classCard) {
                const existingSec = classCard.querySelectorAll(`[data-section-name]`);
                let isDup = false;
                existingSec.forEach(el => {
                    if(el.getAttribute('data-section-name').toLowerCase() === secName.toLowerCase()) isDup = true;
                });
                
                if(isDup) {
                    e.preventDefault();
                    alert('⚠️ This section already exists in the selected class.');
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
