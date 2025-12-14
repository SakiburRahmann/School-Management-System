<?php
/**
 * Teacher - My Subjects
 */

$pageTitle = 'My Subjects';
require_once __DIR__ . '/../includes/teacher_header.php';

$teacherId = $teacherInfo['teacher_id'];
$subjectModel = new Subject();

// Get subjects assigned to the teacher
$mySubjects = $subjectModel->getByTeacher($teacherId);
?>

<style>
    /* Hero Section */
    .hero-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .hero-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        border: 1px solid rgba(226, 232, 240, 0.6);
        transition: transform 0.2s;
    }
    
    .hero-icon {
        width: 60px;
        height: 60px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        margin-right: 1.25rem;
        flex-shrink: 0;
    }
    
    .icon-blue { background: #ebf8ff; color: #3182ce; }
    
    .hero-info h3 {
        margin: 0;
        font-size: 2rem;
        font-weight: 800;
        color: #2d3748;
        line-height: 1.1;
    }
    
    .hero-info p {
        margin: 0;
        color: #718096;
        font-size: 0.95rem;
        font-weight: 500;
        margin-top: 0.25rem;
    }

    /* Subject Grid */
    .subject-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
    }

    .subject-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #edf2f7;
        position: relative;
        overflow: hidden;
    }

    .subject-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
        border-color: #cbd5e0;
    }

    .card-gradient {
        position: absolute;
        top: 0;
        right: 0;
        width: 150px;
        height: 150px;
        background: linear-gradient(135deg, rgba(66, 153, 225, 0.1), rgba(102, 126, 234, 0.05));
        border-radius: 0 0 0 100%;
        z-index: 0;
    }

    .card-content {
        position: relative;
        z-index: 1;
    }
    
    .subject-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
        color: white;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .subject-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.25rem;
    }
    
    .class-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        background: #f7fafc;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #718096;
        margin-bottom: 1rem;
        border: 1px solid #edf2f7;
    }
    
    .subject-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px dashed #e2e8f0;
    }
    
    .meta-box {
        display: flex;
        flex-direction: column;
    }
    
    .meta-label { font-size: 0.75rem; color: #a0aec0; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; }
    .meta-value { font-weight: 600; color: #4a5568; font-size: 0.95rem; }

</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 style="font-weight: 800; color: #1a202c; margin-bottom: 0.5rem;">My Subjects</h2>
        <p class="text-muted mb-0">Overview of subjects you are teaching</p>
    </div>
</div>

<?php if (empty($mySubjects)): ?>
    <div style="text-align: center; padding: 5rem 2rem; background: white; border-radius: 20px;">
        <div style="background: #ebf8ff; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: #3182ce; font-size: 2rem;">
            <i class="fas fa-book-open"></i>
        </div>
        <h3 style="color: #2d3748; margin-bottom: 1rem;">No Subjects Assigned</h3>
        <p style="color: #718096; max-width: 500px; margin: 0 auto;">
            You have not been assigned to teach any subjects yet. 
            <br>Please contact the administrator or principal for your course schedule.
        </p>
    </div>
<?php else: ?>
    <!-- Hero Stats -->
    <div class="hero-stats">
        <div class="hero-card">
            <div class="hero-icon icon-blue">
                <i class="fas fa-book"></i>
            </div>
            <div class="hero-info">
                <h3><?php echo count($mySubjects); ?></h3>
                <p>Total Subjects</p>
            </div>
        </div>
    </div>

    <!-- Subjects Grid -->
    <div class="subject-grid">
        <?php foreach ($mySubjects as $subject): ?>
            <?php 
                // Generate color based on subject name
                $seed = crc32($subject['subject_name']); // No class in subject list sometimes?
                $hue = $seed % 360;
                $color = "hsl($hue, 70%, 50%)";
                $bgLight = "hsl($hue, 70%, 95%)";
            ?>
            <div class="subject-card">
                <div class="card-gradient" style="background: radial-gradient(circle at top right, <?php echo $bgLight; ?>, transparent);"></div>
                
                <div class="card-content">
                    <div class="subject-icon" style="background: <?php echo $color; ?>;">
                        <i class="fas fa-book"></i>
                    </div>
                    
                    <h3 class="subject-name"><?php echo htmlspecialchars($subject['subject_name']); ?></h3>
                    
                    <div class="class-badge">
                        <i class="fas fa-chalkboard mr-2"></i>
                        <?php echo htmlspecialchars($subject['class_name'] ?? 'General'); ?>
                    </div>
                    
                    <div class="subject-meta">
                        <div class="meta-box">
                            <span class="meta-label">Code</span>
                            <span class="meta-value"><?php echo htmlspecialchars($subject['subject_code']); ?></span>
                        </div>
                        <div class="meta-box">
                            <span class="meta-label">Type</span>
                            <span class="meta-value"><?php echo htmlspecialchars($subject['type'] ?? 'Theory'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/teacher_footer.php'; ?>
