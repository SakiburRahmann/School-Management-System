<?php
/**
 * Admin - Delete Student
 * Handle student deletion
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/models/BaseModel.php';
require_once __DIR__ . '/../../core/models/Student.php';
require_once __DIR__ . '/../../core/models/User.php';
require_once __DIR__ . '/../../core/helpers.php';

// Check if logged in and is admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$studentModel = new Student();
$userModel = new User();

// Get student ID
$studentId = $_GET['id'] ?? null;

if (!$studentId) {
    setFlash('danger', 'Invalid student ID.');
    redirect(BASE_URL . '/admin/students/');
}

// Check if student exists
$student = $studentModel->find($studentId);
if (!$student) {
    setFlash('danger', 'Student not found.');
    redirect(BASE_URL . '/admin/students/');
}

$db = Database::getInstance()->getConnection();

try {
    $db->beginTransaction();

    // Delete associated user account if exists
    $user = $userModel->queryOne("SELECT * FROM users WHERE related_id = :id AND role = 'Student'", ['id' => $studentId]);
    if ($user) {
        $userModel->delete($user['user_id']);
    }

    // Delete student
    if ($studentModel->delete($studentId)) {
        $db->commit();
        setFlash('success', 'Student deleted successfully.');
    } else {
        $db->rollBack();
        setFlash('danger', 'Failed to delete student.');
    }
} catch (Exception $e) {
    $db->rollBack();
    // Check for foreign key constraint violation
    if ($e instanceof PDOException && $e->getCode() == '23000') {
        setFlash('danger', 'Cannot delete student because they have related records (e.g., results, fees). Please delete those first.');
    } else {
        setFlash('danger', 'Error deleting student: ' . $e->getMessage());
    }
}

redirect(BASE_URL . '/admin/students/');
