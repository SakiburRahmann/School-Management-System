<?php
/**
 * Admin - Delete Section
 * Handle section deletion
 */

require_once __DIR__ . '/../../config.php';

// Check if logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$classModel = new ClassModel();

// Get section ID
$sectionId = $_GET['id'] ?? null;

if (!$sectionId) {
    setFlash('danger', 'Invalid section ID.');
    redirect(BASE_URL . '/admin/classes/');
}

// Check if section exists
$section = $classModel->queryOne("SELECT * FROM sections WHERE section_id = :id", ['id' => $sectionId]);
if (!$section) {
    setFlash('danger', 'Section not found.');
    redirect(BASE_URL . '/admin/classes/');
}

$db = Database::getInstance()->getConnection();

try {
    $db->beginTransaction();

    // Check for dependencies
    // 1. Check if section has students
    $students = $classModel->query("SELECT COUNT(*) as count FROM students WHERE section_id = :id", ['id' => $sectionId]);
    if ($students[0]['count'] > 0) {
        throw new Exception("Cannot delete section. It has {$students[0]['count']} students enrolled. Please reassign or delete these students first.");
    }

    // 2. Check if section has routines/schedules
    $routines = $classModel->query("SELECT COUNT(*) as count FROM routines WHERE section_id = :id", ['id' => $sectionId]);
    if ($routines[0]['count'] > 0) {
        throw new Exception("Cannot delete section. It has {$routines[0]['count']} routine entries. Please delete these first.");
    }

    // Delete section
    $stmt = $db->prepare("DELETE FROM sections WHERE section_id = :id");
    if ($stmt->execute(['id' => $sectionId])) {
        $db->commit();
        setFlash('success', 'Section deleted successfully.');
    } else {
        $db->rollBack();
        setFlash('danger', 'Failed to delete section.');
    }
} catch (Exception $e) {
    $db->rollBack();
    setFlash('danger', $e->getMessage());
}

redirect(BASE_URL . '/admin/classes/');
