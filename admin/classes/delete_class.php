<?php
/**
 * Admin - Delete Class
 * Handle class deletion
 */

require_once __DIR__ . '/../../config.php';

// Check if logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$classModel = new ClassModel();

// Get class ID
$classId = $_GET['id'] ?? null;

if (!$classId) {
    setFlash('danger', 'Invalid class ID.');
    redirect(BASE_URL . '/admin/classes/');
}

// Check if class exists
$class = $classModel->find($classId);
if (!$class) {
    setFlash('danger', 'Class not found.');
    redirect(BASE_URL . '/admin/classes/');
}

$db = Database::getInstance()->getConnection();

try {
    $db->beginTransaction();

    // Check for dependencies
    // 1. Check if class has students
    $students = $classModel->query("SELECT COUNT(*) as count FROM students WHERE class_id = :id", ['id' => $classId]);
    if ($students[0]['count'] > 0) {
        throw new Exception("Cannot delete class. It has {$students[0]['count']} students enrolled. Please reassign or delete these students first.");
    }

    // 2. Check if class has sections
    $sections = $classModel->query("SELECT COUNT(*) as count FROM sections WHERE class_id = :id", ['id' => $classId]);
    if ($sections[0]['count'] > 0) {
        throw new Exception("Cannot delete class. It has {$sections[0]['count']} sections. Please delete these sections first.");
    }

    // 3. Check if class has subjects
    $subjects = $classModel->query("SELECT COUNT(*) as count FROM subjects WHERE class_id = :id", ['id' => $classId]);
    if ($subjects[0]['count'] > 0) {
        throw new Exception("Cannot delete class. It has {$subjects[0]['count']} subjects assigned. Please delete these subjects first.");
    }

    // Delete class
    if ($classModel->delete($classId)) {
        $db->commit();
        setFlash('success', 'Class deleted successfully.');
    } else {
        $db->rollBack();
        setFlash('danger', 'Failed to delete class.');
    }
} catch (Exception $e) {
    $db->rollBack();
    setFlash('danger', $e->getMessage());
}

redirect(BASE_URL . '/admin/classes/');
