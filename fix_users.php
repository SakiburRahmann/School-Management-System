<?php
/**
 * Fix Users Script (v4)
 * Creates test users with properly hashed passwords
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/models/BaseModel.php';
require_once __DIR__ . '/core/models/User.php';
require_once __DIR__ . '/core/models/Teacher.php';
require_once __DIR__ . '/core/models/Student.php';
require_once __DIR__ . '/core/models/ClassModel.php';

$db = Database::getInstance()->getConnection();
$userModel = new User();
$teacherModel = new Teacher();
$studentModel = new Student();
$classModel = new ClassModel();

echo "Starting user fix...\n";

// 1. Clear existing test users
$db->exec("DELETE FROM users WHERE username IN ('teacher1', 'student1')");
$db->exec("DELETE FROM teachers WHERE email = 'teacher1@school.com'");

// Safe delete for student
try {
    $stmt = $db->prepare("SELECT student_id FROM students WHERE roll_number = ?");
    $stmt->execute(['STU001']);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($student) {
        $db->exec("DELETE FROM students WHERE student_id = " . $student['student_id']);
        echo "Deleted existing student record.\n";
    }
} catch (Exception $e) {
    echo "Warning: Could not delete student: " . $e->getMessage() . "\n";
}

echo "Cleared old test users.\n";

// 3. Create Teacher
$teacherData = [
    'name' => 'John Smith',
    'email' => 'teacher1@school.com',
    'phone' => '1234567890',
    'subject_speciality' => 'Mathematics',
    'status' => 'Active'
];

$teacherId = $teacherModel->create($teacherData);

if ($teacherId) {
    $teacherUser = [
        'username' => 'teacher1',
        'password' => 'teacher123',
        'role' => 'Teacher',
        'related_id' => $teacherId,
        'is_active' => 1
    ];
    
    if ($userModel->createUser($teacherUser)) {
        echo "Teacher user created successfully.\n";
    } else {
        echo "Failed to create Teacher user.\n";
    }
} else {
    echo "Failed to create Teacher profile.\n";
}

// 4. Create Student
$classes = $classModel->findAll();
if (empty($classes)) {
    $classId = $classModel->create(['class_name' => 'Class 10', 'status' => 'Active']);
} else {
    $classId = $classes[0]['class_id'];
}

$studentData = [
    'name' => 'Alice Johnson',
    'roll_number' => 1001,
    'class_id' => $classId,
    'date_of_birth' => '2008-05-15',
    'gender' => 'Female',
    'status' => 'Active'
];

$studentId = $studentModel->create($studentData);

if ($studentId) {
    $studentUser = [
        'username' => 'student1',
        'password' => 'student123',
        'role' => 'Student',
        'related_id' => $studentId,
        'is_active' => 1
    ];
    
    if ($userModel->createUser($studentUser)) {
        echo "Student user created successfully.\n";
    } else {
        echo "Failed to create Student user.\n";
    }
} else {
    echo "Failed to create Student profile.\n";
}

echo "Fix complete.\n";
