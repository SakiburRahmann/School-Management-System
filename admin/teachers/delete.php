<?php
/**
 * Admin - Delete Teacher
 * Handle teacher deletion
 */

require_once __DIR__ . '/../../config.php';

// Check if logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$teacherModel = new Teacher();
$userModel = new User();

// Get teacher ID
$teacherId = $_GET['id'] ?? null;

if (!$teacherId) {
    setFlash('danger', 'Invalid teacher ID.');
    redirect(BASE_URL . '/admin/teachers/');
}

// Check if teacher exists
$teacher = $teacherModel->find($teacherId);
if (!$teacher) {
    setFlash('danger', 'Teacher not found.');
    redirect(BASE_URL . '/admin/teachers/');
}

$db = Database::getInstance()->getConnection();

try {
    $db->beginTransaction();

    // Check for dependencies
    // 1. Check if teacher is assigned to any subjects
    $subjects = $teacherModel->query("SELECT COUNT(*) as count FROM subjects WHERE teacher_id = :id", ['id' => $teacherId]);
    if ($subjects[0]['count'] > 0) {
        throw new Exception("Cannot delete teacher. They are assigned to {$subjects[0]['count']} subjects. Please reassign or delete these subjects first.");
    }

    // 2. Check if teacher is a class teacher
    $sections = $teacherModel->query("SELECT COUNT(*) as count FROM sections WHERE class_teacher_id = :id", ['id' => $teacherId]);
    if ($sections[0]['count'] > 0) {
        throw new Exception("Cannot delete teacher. They are the class teacher for {$sections[0]['count']} sections. Please assign a new class teacher first.");
    }

    // Delete associated user account if exists
    $user = $userModel->queryOne("SELECT * FROM users WHERE related_id = :id AND role = 'Teacher'", ['id' => $teacherId]);
    if ($user) {
        $userModel->delete($user['user_id']);
    }

    // Delete teacher
    if ($teacherModel->delete($teacherId)) {
        $db->commit();
        setFlash('success', 'Teacher deleted successfully.');
    } else {
        $db->rollBack();
        setFlash('danger', 'Failed to delete teacher.');
    }
} catch (Exception $e) {
    $db->rollBack();
    setFlash('danger', $e->getMessage());
}

redirect(BASE_URL . '/admin/teachers/');
