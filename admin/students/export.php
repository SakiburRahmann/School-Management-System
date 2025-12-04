<?php
/**
 * Admin - Export Students
 * Export students to CSV
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/models/BaseModel.php';
require_once __DIR__ . '/../../core/models/Student.php';
require_once __DIR__ . '/../../core/helpers.php';

// Check if logged in and is admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$studentModel = new Student();

// Get filter parameters
$classId = $_GET['class'] ?? null;
$sectionId = $_GET['section'] ?? null;

// Get students
if ($classId) {
    $students = $studentModel->getByClass($classId, $sectionId);
} else {
    $students = $studentModel->getStudentsWithDetails();
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="students_export_' . date('Y-m-d') . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add header row
fputcsv($output, [
    'Student ID',
    'Name',
    'Roll Number',
    'Class',
    'Section',
    'Gender',
    'Date of Birth',
    'Guardian Name',
    'Guardian Phone',
    'Contact Details',
    'Address'
]);

// Add data rows
foreach ($students as $student) {
    fputcsv($output, [
        $student['student_id'],
        $student['name'],
        $student['roll_number'],
        $student['class_name'],
        $student['section_name'],
        $student['gender'],
        $student['date_of_birth'],
        $student['guardian_name'],
        $student['guardian_phone'],
        $student['contact_details'],
        $student['address']
    ]);
}

fclose($output);
exit;
