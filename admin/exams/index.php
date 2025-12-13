<?php
/**
 * Admin - Exam Management
 */

require_once __DIR__ . '/../../config.php';

$examModel = new Exam();
$classModel = new ClassModel();

// Handle exam creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_exam') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $classIds = $_POST['class_id'] ?? []; // Expecting array
        $subjectName = $_POST['subject_name'] ?? '';
        $examName = sanitize($_POST['exam_name']);
        $examDate = $_POST['exam_date'];
        $totalMarks = $_POST['total_marks'];
        $assignedTeachers = $_POST['assigned_teachers'] ?? [];

        if (!is_array($classIds)) {
            $classIds = [$classIds];
        }

        // Validate required fields including teachers
        if (!empty($examName) && !empty($classIds) && !empty($subjectName) && !empty($examDate)) {
            // Check if at least one teacher is assigned
            if (empty($assignedTeachers) || !is_array($assignedTeachers) || count($assignedTeachers) === 0) {
                setFlash('danger', 'Please assign at least one teacher as an evaluator for this exam.');
                redirect(BASE_URL . '/admin/exams/');
                exit;
            }
            
            $successCount = 0;
            $failCount = 0;
            $skippedCount = 0;
            
            $conn = Database::getInstance()->getConnection(); // Need DB for subject lookup

            foreach ($classIds as $classId) {
                if (empty($classId)) continue;

                // Find subject_id for this class and subject_name
                // Find subject_id for this class and subject_name (or global subject)
                $sql = "SELECT subject_id FROM subjects WHERE (class_id = :class_id OR class_id IS NULL) AND subject_name = :subject_name ORDER BY class_id DESC LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->execute(['class_id' => $classId, 'subject_name' => $subjectName]);
                $subjectId = $stmt->fetchColumn();

                if ($subjectId) {
                    $data = [
                        'exam_name' => $examName,
                        'class_id' => $classId,
                        'subject_id' => $subjectId,
                        'exam_date' => $examDate,
                        'total_marks' => $totalMarks,
                        'grading_system' => $_POST['grading_system'] ?? null
                    ];

                    $examId = $examModel->create($data);
                    if ($examId) {
                        // Assign teachers
                        if (!empty($assignedTeachers)) {
                            $examModel->assignTeachers($examId, $assignedTeachers);
                        }
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                } else {
                    $skippedCount++;
                }
            }

            if ($successCount > 0) {
                $msg = "Successfully created $successCount exam(s).";
                if ($skippedCount > 0) $msg .= " Skipped $skippedCount class(es) (Subject not found).";
                if ($failCount > 0) $msg .= " Failed to create $failCount exam(s).";
                setFlash('success', $msg);
            } else {
                setFlash('danger', "Failed to create exams. " . ($skippedCount > 0 ? "Subject '$subjectName' not found in selected classes." : ""));
            }
        }
    }
    redirect(BASE_URL . '/admin/exams/');
}

// Handle exam deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    // Verify CSRF token for deletion (safer) - for now just check ID
    if ($examModel->delete($_GET['id'])) {
        setFlash('success', 'Exam deleted successfully!');
    } else {
        setFlash('danger', 'Failed to delete exam.');
    }
    redirect(BASE_URL . '/admin/exams/');
}

// Get all exams
$exams = $examModel->getExamsWithDetails();
$classes = $classModel->findAll('class_name');

// Separate upcoming and past exams
$upcomingExams = array_filter($exams, fn($e) => strtotime($e['exam_date']) >= strtotime('today'));
$pastExams = array_filter($exams, fn($e) => strtotime($e['exam_date']) < strtotime('today'));

$pageTitle = 'Exam Management';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<style>
/* Modern Form & Layout */
:root {
    --primary-soft: #eef2ff;
    --primary-border: #c7d2fe;
    --text-main: #1f2937;
    --text-muted: #6b7280;
}

body {
    background-color: #f3f4f6;
}

.create-exam-header {
    background: white;
    padding: 1.5rem 2rem;
    border-radius: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.create-exam-title h3 {
    font-weight: 700;
    color: #111827;
    margin: 0;
    font-size: 1.5rem;
}

.create-exam-title p {
    color: #6b7280;
    margin: 0.25rem 0 0 0;
    font-size: 0.95rem;
}

/* Sections */
.form-section {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid #f3f4f6;
}

.section-label {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.section-number {
    background: var(--primary);
    color: white;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
    margin-right: 0.75rem;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #374151;
    margin: 0;
}

/* Modern Inputs */
.modern-input-group label {
    display: block;
    font-size: 0.9rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-control {
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    padding: 0.625rem 0.875rem;
    font-size: 0.95rem;
    transition: all 0.2s;
    background-color: #f9fafb;
}

.form-control:focus {
    background-color: white;
    border-color: #4e73df;
    box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
}

/* Hide Number Spinner */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
}
input[type=number] {
    -moz-appearance: textfield;
}

/* Search Bar Modern */
.modern-search {
    position: relative;
    margin-bottom: 1rem;
}

.modern-search input {
    padding-left: 2.5rem;
    background: white;
    border-radius: 2rem; /* Pill shape */
}

.modern-search i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

/* Selection Cards (Classes & Subjects) */
.selection-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 0.75rem;
    max-height: 300px;
    overflow-y: auto;
    padding: 0.25rem; /* For focus ring space */
}

.selection-card {
    position: relative;
    cursor: pointer;
}

.selection-card input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.card-content {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1rem;
    display: flex;
    align-items: center;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Checked State */
.selection-card input:checked + .card-content {
    background: var(--primary-soft);
    border-color: var(--primary);
    box-shadow: 0 0 0 2px var(--primary);
    transform: translateY(-1px);
}

.selection-card input:checked + .card-content .sc-icon {
    background: white;
    color: var(--primary);
}

.sc-icon {
    width: 36px;
    height: 36px;
    background: #f3f4f6;
    color: #6b7280;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    margin-right: 0.75rem;
    transition: all 0.2s;
}

.sc-info {
    flex: 1;
    overflow: hidden;
}

.sc-title {
    font-weight: 600;
    font-size: 0.95rem;
    color: #1f2937;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
}

.sc-subtitle {
    font-size: 0.8rem;
    color: #6b7280;
    display: block;
    margin-top: 2px;
}

/* Teachers List */
.teacher-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 0.75rem;
}

.teacher-card {
    display: flex;
    align-items: center;
    background: white;
    border: 1px solid #e5e7eb;
    padding: 0.75rem;
    border-radius: 0.5rem;
    transition: all 0.2s;
    cursor: pointer;
}

.teacher-card:hover {
    border-color: #d1d5db;
    background: #f9fafb;
}

.tc-checkbox {
    width: 20px;
    height: 20px;
    margin-right: 0.75rem;
    accent-color: var(--primary);
    border-radius: 4px;
    cursor: pointer;
}

.tc-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e0e7ff;
    color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    margin-right: 0.75rem;
}

.empty-state {
    text-align: center;
    padding: 2rem;
    color: #9ca3af;
    background: #f9fafb;
    border-radius: 0.75rem;
    border: 2px dashed #e5e7eb;
}

/* Floating Action Bar */
.action-bar {
    position: sticky;
    bottom: 20px;
    background: white;
    padding: 1rem 2rem;
    border-radius: 1rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 1rem;
    border: 1px solid #e5e7eb;
    margin-top: 2rem;
    z-index: 100;
}

.btn-create {
    padding: 0.75rem 2rem;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 0.5rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
</style>

<div class="create-exam-header">
    <div class="create-exam-title">
        <h3>Create New Exam</h3>
        <p>Set up a new examination, define subjects, and configure grading logic.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/admin/exams/" class="btn btn-outline-secondary">
        <i class="fas fa-list"></i> View All Exams
    </a>
</div>

<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <input type="hidden" name="action" value="create_exam">

    <!-- STEP 1: BASIC INFO -->
    <div class="form-section">
        <div class="section-label">
            <span class="section-number">1</span>
            <h4 class="section-title">Exam Information</h4>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group modern-input-group">
                    <label for="exam_name">Exam Name <span class="text-danger">*</span></label>
                    <input type="text" id="exam_name" name="exam_name" class="form-control" 
                           placeholder="Mid-Term Examination 2025" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group modern-input-group">
                    <label for="exam_date">Date <span class="text-danger">*</span></label>
                    <input type="date" id="exam_date" name="exam_date" class="form-control" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group modern-input-group">
                    <label for="total_marks">Total Marks</label>
                    <!-- type="number" with styling hook for removing spinner -->
                    <input type="number" id="total_marks" name="total_marks" class="form-control" 
                           placeholder="100" min="0">
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 2: PARTICIPANTS -->
    <div class="form-section">
        <div class="section-label">
            <span class="section-number">2</span>
            <h4 class="section-title">Select Participants</h4>
        </div>

        <div class="row">
            <!-- Classes -->
            <div class="col-md-6">
                <div class="form-group modern-input-group">
                    <label>Target Classes <span class="text-danger">*</span></label>
                    
                    <div class="modern-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="classSearchAdd" class="form-control" placeholder="Search classes...">
                    </div>
                    
                    <div class="selection-grid" id="classListAdd">
                        <?php if (!empty($classes)): ?>
                            <?php foreach ($classes as $class): ?>
                                <label class="selection-card" data-name="<?php echo strtolower(htmlspecialchars($class['class_name'])); ?>">
                                    <input type="checkbox" name="class_id[]" class="class-checkbox" 
                                           value="<?php echo $class['class_id']; ?>">
                                    <div class="card-content">
                                        <div class="sc-icon"><i class="fas fa-users"></i></div>
                                        <div class="sc-info">
                                            <span class="sc-title"><?php echo htmlspecialchars($class['class_name']); ?></span>
                                            <span class="sc-subtitle">Class ID: <?php echo $class['class_id']; ?></span>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">No classes available.</div>
                        <?php endif; ?>
                    </div>
                    <small id="classSearchResultsAdd" class="text-muted mt-2 d-block pl-2">
                        Showing all <?php echo count($classes); ?> classes
                    </small>
                </div>
            </div>

            <!-- Subjects (Dynamic) -->
            <div class="col-md-6">
                <div class="form-group modern-input-group">
                    <label>Subject <span class="text-danger">*</span></label>
                    
                    <div class="modern-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="subjectSearch" class="form-control" placeholder="Search subject...">
                    </div>
                    
                    <div class="selection-grid" id="subject_selection_container">
                        <div class="empty-state">
                            <i class="fas fa-arrow-left mr-2"></i> Select a class first
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 3: EVALUATORS -->
    <div class="form-section">
        <div class="section-label">
            <span class="section-number">3</span>
            <h4 class="section-title">Assign Evaluators</h4>
        </div>

        <div class="form-group modern-input-group">
             <div class="modern-search" style="max-width: 400px;">
                <i class="fas fa-search"></i>
                <input type="text" id="teacher_search" class="form-control" placeholder="Filter teachers by name...">
            </div>
            
            <div class="teacher-grid" id="teacher_selection_container">
                <div class="empty-state" style="grid-column: 1/-1;">
                    <i class="fas fa-arrow-up mr-2"></i> Select a subject first to load qualified teachers
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 4: GRADING CONFIGURATION (Imported UI) -->
    <?php require_once __DIR__ . '/grading_system_ui.php'; ?>

    <!-- SUBMIT BAR -->
    <div class="action-bar">
        <button type="submit" class="btn btn-primary btn-create">
            <i class="fas fa-check-circle"></i> Create Exam
        </button>
    </div>

</form>

<!-- Upcoming Exams List (Cleaned Up) -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="m-0 font-weight-bold text-primary">Scheduled Exams</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="pl-4">Exam Name</th>
                                <th>Class</th>
                                <th>Date</th>
                                <th>Total Marks</th>
                                <th class="text-right pr-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($upcomingExams)): ?>
                                <?php foreach ($upcomingExams as $exam): ?>
                                    <tr>
                                        <td class="pl-4">
                                            <div class="font-weight-bold text-dark"><?php echo htmlspecialchars($exam['exam_name']); ?></div>
                                        </td>
                                        <td><span class="badge badge-light border"><?php echo htmlspecialchars($exam['class_name']); ?></span></td>
                                        <td><?php echo formatDate($exam['exam_date']); ?></td>
                                        <td><?php echo $exam['total_marks'] ?? '-'; ?></td>
                                        <td class="text-right pr-4">
                                            <a href="<?php echo BASE_URL; ?>/admin/exams/marks.php?exam_id=<?php echo $exam['exam_id']; ?>" 
                                               class="btn btn-info btn-sm btn-icon-split shadow-sm">
                                                <span class="icon text-white-50"><i class="fas fa-clipboard-list"></i></span>
                                                <span class="text">Marks</span>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/admin/exams/edit.php?id=<?php echo $exam['exam_id']; ?>" 
                                               class="btn btn-warning btn-sm shadow-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/admin/exams/?delete=1&id=<?php echo $exam['exam_id']; ?>" 
                                               class="btn btn-danger btn-sm shadow-sm delete-btn" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No upcoming exams.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    /* -------------------------------------------------------------------------- */
    /*                                CLASS SEARCH                                */
    /* -------------------------------------------------------------------------- */
    
    const classSearchInput = document.getElementById('classSearchAdd');
    const classList = document.getElementById('classListAdd');
    const classResultsInfo = document.getElementById('classSearchResultsAdd');
    const totalClasses = <?php echo count($classes); ?>;
    
    if (classSearchInput && classList) {
        classSearchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const items = classList.querySelectorAll('.selection-card'); // Updated selector
            let visibleCount = 0;
            
            items.forEach(item => {
                const name = item.getAttribute('data-name') || '';
                
                if (searchTerm === '' || name.includes(searchTerm)) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Update results info
            if (searchTerm === '') {
                classResultsInfo.textContent = `Showing all ${totalClasses} classes`;
            } else {
                classResultsInfo.innerHTML = visibleCount > 0 
                    ? `Found ${visibleCount} class${visibleCount !== 1 ? 'es' : ''}`
                    : `<span class="text-danger">No classes found</span>`;
            }
        });
    }

    /* -------------------------------------------------------------------------- */
    /*                       DYNAMIC SUBJECT & TEACHER LOADING                    */
    /* -------------------------------------------------------------------------- */
    
    const classCheckboxes = document.querySelectorAll('.class-checkbox');
    const subjectContainer = document.getElementById('subject_selection_container');
    const teacherContainer = document.getElementById('teacher_selection_container');
    
    function loadSubjects() {
        // Get selected class IDs
        const classIds = Array.from(classCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
            
        // Reset Containers with Empty States
        const emptySubject = '<div class="empty-state"><i class="fas fa-arrow-left mr-2"></i> Select a class first</div>';
        const emptyTeacher = '<div class="empty-state" style="grid-column: 1/-1;"><i class="fas fa-arrow-up mr-2"></i> Select a subject first</div>';
        
        if (classIds.length === 0) {
            subjectContainer.innerHTML = emptySubject;
            teacherContainer.innerHTML = emptyTeacher;
            return;
        }
        
        // Loading State
        subjectContainer.innerHTML = '<div class="text-center py-3 w-100"><i class="fas fa-spinner fa-spin text-primary"></i> Loading subjects...</div>';
        
        const classIdsParam = classIds.join(',');
        
        fetch('<?php echo BASE_URL; ?>/admin/exams/get_subjects_by_class.php?class_id=' + classIdsParam)
            .then(response => response.json())
            .then(subjects => {
                if (subjects.length === 0) {
                    subjectContainer.innerHTML = '<div class="empty-state">No subjects found for selected classes.</div>';
                } else {
                    let html = '';
                    subjects.forEach(subject => {
                        const safeId = 'subject_' + subject.subject_name.replace(/[^a-zA-Z0-9]/g, '_');
                        // Modern Selection Card for Subject
                        html += `
                            <label class="selection-card subject-radio-item" data-name="${subject.subject_name.toLowerCase()}">
                                <input type="radio" name="subject_name" value="${subject.subject_name}" id="${safeId}" required>
                                <div class="card-content">
                                    <div class="sc-icon" style="background: #e0f2fe; color: #0284c7;"><i class="fas fa-book"></i></div>
                                    <div class="sc-info">
                                        <span class="sc-title">${subject.subject_name}</span>
                                        <span class="sc-subtitle">Subject Code: ${subject.subject_code || 'N/A'}</span>
                                    </div>
                                </div>
                            </label>
                        `;
                    });
                    subjectContainer.innerHTML = html;
                    attachSubjectChangeListeners(); // Re-attach listeners
                }
            })
            .catch(error => {
                console.error(error);
                subjectContainer.innerHTML = '<div class="text-danger p-3">Error loading subjects.</div>';
            });
    }

    function loadTeachersBySubject(subjectName) {
        teacherContainer.innerHTML = '<div class="text-center py-3 w-100" style="grid-column: 1/-1;"><i class="fas fa-spinner fa-spin text-primary"></i> Loading teachers...</div>';
        
        fetch('<?php echo BASE_URL; ?>/admin/exams/get_teachers_by_subject.php?subject_name=' + encodeURIComponent(subjectName))
            .then(response => response.json())
            .then(teachers => {
                if (teachers.length === 0) {
                    teacherContainer.innerHTML = '<div class="empty-state" style="grid-column: 1/-1;">No qualified teachers found for this subject.</div>';
                    return;
                }
                
                let html = '';
                teachers.forEach(teacher => {
                    const initials = teacher.name.match(/\b\w/g) || [];
                    const avatarText = ((initials.shift() || '') + (initials.pop() || '')).toUpperCase();
                    
                    // Modern Teacher Card
                    html += `
                        <label class="teacher-card teacher-checkbox-item">
                            <input type="checkbox" name="assigned_teachers[]" class="tc-checkbox" 
                                   value="${teacher.teacher_id}" required>
                            <div class="tc-avatar">${avatarText}</div>
                            <div class="tc-info">
                                <strong class="d-block text-dark">${teacher.name}</strong>
                                ${teacher.subject_speciality ? `<small class="text-muted">${teacher.subject_speciality}</small>` : ''}
                            </div>
                        </label>
                    `;
                });
                teacherContainer.innerHTML = html;
            })
            .catch(error => {
                console.error(error);
                teacherContainer.innerHTML = '<div class="text-danger p-3 style="grid-column: 1/-1;"">Error loading teachers.</div>';
            });
    }

    /* -------------------------------------------------------------------------- */
    /*                              EVENT LISTENERS                               */
    /* -------------------------------------------------------------------------- */

    function attachSubjectChangeListeners() {
        const subjectRadios = document.querySelectorAll('input[name="subject_name"]');
        subjectRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    loadTeachersBySubject(this.value);
                }
            });
        });
    }

    // Class selection change
    classCheckboxes.forEach(cb => {
        cb.addEventListener('change', loadSubjects);
    });

    // Subject Search
    const subjectSearchInput = document.getElementById('subjectSearch');
    if (subjectSearchInput) {
        subjectSearchInput.addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            const items = document.querySelectorAll('.subject-radio-item');
            items.forEach(item => {
                const name = item.getAttribute('data-name') || '';
                if (name.includes(searchText)) {
                    item.style.display = ''; // default display (inline-block/flex)
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
    
    // Teacher Search
    const teacherSearchInput = document.getElementById('teacher_search');
    if (teacherSearchInput) {
        teacherSearchInput.addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            const items = document.querySelectorAll('.teacher-card'); // Updated selector
            items.forEach(item => {
                // Find name within the card (in <strong>)
                const nameEl = item.querySelector('strong'); 
                if (nameEl && nameEl.textContent.toLowerCase().includes(searchText)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
    
    // Submit Validation
    const examForm = document.querySelector('form[action=""]');
    if (examForm) {
        examForm.addEventListener('submit', function(e) {
            const selectedTeachers = document.querySelectorAll('input[name="assigned_teachers[]"]:checked');
            if (selectedTeachers.length === 0) {
                e.preventDefault();
                // Custom Toast or Alert could be better, but standard alert is safe
                alert('⚠️ Please assign at least one evaluator/teacher.'); 
                // Scroll to teacher section
                document.getElementById('teacher_selection_container').scrollIntoView({behavior: 'smooth'});
                return false;
            }
        });
    }
});
</script>

