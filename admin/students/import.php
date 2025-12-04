<?php
/**
 * Admin - Import Students
 * Import students from CSV
 */

require_once __DIR__ . '/../../config.php';

$studentModel = new Student();
$classModel = new ClassModel();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlash('danger', 'Invalid request.');
        redirect(BASE_URL . '/admin/students/import.php');
    }
    
    $classId = $_POST['class_id'];
    $sectionId = $_POST['section_id'];
    
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        if ($handle) {
            // Skip header row
            fgetcsv($handle);
            
            $successCount = 0;
            $errorCount = 0;
            
            while (($row = fgetcsv($handle)) !== false) {
                // Expected format: Name, Roll No, Guardian Name, Guardian Phone, Gender, DOB (YYYY-MM-DD)
                // Adjust indices based on your CSV structure
                // Let's assume a simple structure for import
                
                $name = $row[0] ?? '';
                $rollNumber = $row[1] ?? null;
                $guardianName = $row[2] ?? '';
                $guardianPhone = $row[3] ?? '';
                $gender = $row[4] ?? '';
                $dob = $row[5] ?? null;
                
                if (empty($name) || empty($guardianName)) {
                    $errorCount++;
                    continue;
                }
                
                $data = [
                    'name' => sanitize($name),
                    'class_id' => $classId,
                    'section_id' => $sectionId,
                    'roll_number' => $rollNumber,
                    'guardian_name' => sanitize($guardianName),
                    'guardian_phone' => sanitize($guardianPhone),
                    'gender' => $gender,
                    'date_of_birth' => $dob
                ];
                
                // Check if roll number exists
                if ($rollNumber && $studentModel->rollNumberExists($classId, $sectionId, $rollNumber)) {
                    // Skip or update? Let's skip for now to avoid overwriting
                    $errorCount++;
                    continue;
                }
                
                if ($studentModel->create($data)) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }
            
            fclose($handle);
            
            if ($successCount > 0) {
                setFlash('success', "Imported {$successCount} students successfully. Failed: {$errorCount}");
            } else {
                setFlash('warning', "No students imported. Failed: {$errorCount}");
            }
            
            redirect(BASE_URL . '/admin/students/');
        } else {
            setFlash('danger', 'Could not open file.');
        }
    } else {
        setFlash('danger', 'Please upload a valid CSV file.');
    }
}

// Get all classes
$classes = $classModel->getClassesWithSections();

$pageTitle = 'Import Students';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Import Students</h3>
        <a href="<?php echo BASE_URL; ?>/admin/students/" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
    
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            Upload a CSV file with the following columns in order: <br>
            <strong>Name, Roll Number, Guardian Name, Guardian Phone, Gender, Date of Birth (YYYY-MM-DD)</strong>
        </div>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label for="class_id">Class <span style="color: red;">*</span></label>
                    <select id="class_id" name="class_id" class="form-control" required onchange="loadSections(this.value)">
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['class_id']; ?>">
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="section_id">Section <span style="color: red;">*</span></label>
                    <select id="section_id" name="section_id" class="form-control" required>
                        <option value="">Select Section</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 1.5rem;">
                <label for="csv_file">CSV File <span style="color: red;">*</span></label>
                <input type="file" id="csv_file" name="csv_file" class="form-control" accept=".csv" required>
            </div>
            
            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-file-import"></i> Import Students
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Load sections when class is selected
function loadSections(classId) {
    const sectionSelect = document.getElementById('section_id');
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
