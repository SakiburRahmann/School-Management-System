<?php
/**
 * Admin - Promote Students
 * Promote students from one class to another
 */

$classModel = new ClassModel();
$studentModel = new Student();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlash('danger', 'Invalid request.');
        redirect(BASE_URL . '/admin/students/promote.php');
    }
    
    $fromClassId = $_POST['from_class_id'];
    $fromSectionId = $_POST['from_section_id'];
    $toClassId = $_POST['to_class_id'];
    $toSectionId = $_POST['to_section_id'];
    
    if ($fromClassId == $toClassId && $fromSectionId == $toSectionId) {
        setFlash('danger', 'Source and destination class/section cannot be the same.');
    } else {
        if ($studentModel->promoteStudents($fromClassId, $fromSectionId, $toClassId, $toSectionId)) {
            setFlash('success', 'Students promoted successfully! Roll numbers have been reset.');
            redirect(BASE_URL . '/admin/students/');
        } else {
            setFlash('danger', 'Failed to promote students.');
        }
    }
}

// Get all classes
$classes = $classModel->getClassesWithSections();

$pageTitle = 'Promote Students';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Promote Students</h3>
        <a href="<?php echo BASE_URL; ?>/admin/students/" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
    
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            Promoting students will move all students from the source section to the destination section. 
            <strong>Roll numbers will be reset to NULL</strong> and will need to be re-assigned.
        </div>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start;">
                <!-- Source -->
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; border: 1px solid #dee2e6;">
                    <h4 style="margin-top: 0; color: #dc3545;">From (Current Class)</h4>
                    
                    <div class="form-group">
                        <label for="from_class_id">Class</label>
                        <select id="from_class_id" name="from_class_id" class="form-control" required onchange="loadSections(this.value, 'from_section_id')">
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['class_id']; ?>">
                                    <?php echo htmlspecialchars($class['class_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="from_section_id">Section</label>
                        <select id="from_section_id" name="from_section_id" class="form-control" required>
                            <option value="">Select Section</option>
                        </select>
                    </div>
                </div>
                
                <!-- Destination -->
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; border: 1px solid #dee2e6;">
                    <h4 style="margin-top: 0; color: #28a745;">To (Target Class)</h4>
                    
                    <div class="form-group">
                        <label for="to_class_id">Class</label>
                        <select id="to_class_id" name="to_class_id" class="form-control" required onchange="loadSections(this.value, 'to_section_id')">
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['class_id']; ?>">
                                    <?php echo htmlspecialchars($class['class_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="to_section_id">Section</label>
                        <select id="to_section_id" name="to_section_id" class="form-control" required>
                            <option value="">Select Section</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 2rem; text-align: center;">
                <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Are you sure you want to promote these students? This action cannot be undone easily.');">
                    <i class="fas fa-level-up-alt"></i> Promote Students
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Load sections when class is selected
function loadSections(classId, targetSelectId) {
    const sectionSelect = document.getElementById(targetSelectId);
    sectionSelect.innerHTML = '<option value="">Select Section</option>';
    
    if (!classId) return;
    
    // Fetch sections via AJAX
    fetch('<?php echo BASE_URL; ?>/admin/students/get_sections.php?class_id=' + classId)
        .then(response => response.json())
        .then(sections => {
            sections.forEach(section => {
                const option = document.createElement('option');
                option.value = section.section_id;
                option.textContent = section.section_name;
                sectionSelect.appendChild(option);
            });
        });
}
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
