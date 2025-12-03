<?php
/**
 * Admin - Classes & Sections Management
 */

$pageTitle = 'Classes & Sections';
require_once __DIR__ . '/../../includes/admin_header.php';

$classModel = new ClassModel();
$teacherModel = new Teacher();

// Handle class creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_class') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $className = sanitize($_POST['class_name']);
        if (!empty($className)) {
            $classId = $classModel->create(['class_name' => $className]);
            if ($classId) {
                setFlash('success', 'Class added successfully!');
            } else {
                setFlash('danger', 'Failed to add class.');
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
?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- Add Class Card -->
    <div class="card">
        <div class="card-header">
            <h3>Add New Class</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
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
            <form method="POST" action="">
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
                            <option value="<?php echo $teacher['teacher_id']; ?>">
                                <?php echo htmlspecialchars($teacher['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
                    <div style="border: 2px solid var(--light); border-radius: 10px; padding: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h4 style="margin: 0; color: var(--primary);">
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </h4>
                            <span class="badge badge-info">
                                <?php echo $class['section_count']; ?> Section<?php echo $class['section_count'] != 1 ? 's' : ''; ?>
                            </span>
                        </div>
                        
                        <?php if (!empty($classDetails['sections'])): ?>
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
                                <?php foreach ($classDetails['sections'] as $section): ?>
                                    <div style="background: var(--light); padding: 1rem; border-radius: 8px;">
                                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                            <strong style="font-size: 1.1rem;">Section <?php echo htmlspecialchars($section['section_name']); ?></strong>
                                            <a href="<?php echo BASE_URL; ?>/admin/classes/delete_section.php?id=<?php echo $section['section_id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirmDelete('Delete this section?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
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

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
