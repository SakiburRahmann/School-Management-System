<?php
/**
 * Admin - Delete Subject
 * Handle subject deletion (consistent with student/teacher delete pattern)
 */

require_once __DIR__ . '/../../config.php';

// Check if logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$subjectModel = new Subject();

// Get subject ID
$subjectId = $_GET['id'] ?? null;

if (!$subjectId) {
    setFlash('danger', 'Invalid subject ID.');
    redirect(BASE_URL . '/admin/subjects/');
}

// Check if subject exists
$subject = $subjectModel->find($subjectId);
if (!$subject) {
    setFlash('danger', 'Subject not found.');
    redirect(BASE_URL . '/admin/subjects/');
}

$db = Database::getInstance()->getConnection();

try {
    $db->beginTransaction();

    // Check for dependencies - results
    $results = $subjectModel->query("SELECT COUNT(*) as count FROM results WHERE subject_id = :id", ['id' => $subjectId]);
    if ($results[0]['count'] > 0) {
        throw new Exception("Cannot delete subject. It has {$results[0]['count']} result records. Please delete those results first.");
    }

    // Delete subject (cascade will handle subject_teachers junction table)
    if ($subjectModel->delete($subjectId)) {
        $db->commit();
        setFlash('success', 'Subject "' . $subject['subject_name'] . '" deleted successfully.');
    } else {
        $db->rollBack();
        setFlash('danger', 'Failed to delete subject.');
    }
} catch (Exception $e) {
    $db->rollBack();
    // Check for foreign key constraint violation
    if ($e instanceof PDOException && $e->getCode() == '23000') {
        setFlash('danger', 'Cannot delete subject because it has related records. Please delete those first.');
    } else {
        setFlash('danger', $e->getMessage());
    }
}

redirect(BASE_URL . '/admin/subjects/');
