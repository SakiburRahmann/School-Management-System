# Test User Credentials

## Admin Portal
- **URL**: http://localhost:8000/login.php
- **Username**: `admin`
- **Password**: `admin123`
- **Access**: Full system administration

## Teacher Portal
- **URL**: http://localhost:8000/login.php
- **Username**: `teacher1`
- **Password**: `teacher123`
- **Teacher Name**: John Smith
- **Specialization**: Mathematics
- **Access**: Teacher dashboard, attendance, marks entry

## Student Portal
- **URL**: http://localhost:8000/login.php
- **Username**: `student1`
- **Password**: `student123`
- **Student Name**: Alice Johnson
- **Class**: Class 1
- **Roll Number**: 001
- **Access**: Student dashboard, attendance history, results, fees

---

## Quick Test Guide

### 1. Test Admin Portal
1. Login as `admin` / `admin123`
2. Go to Dashboard - see statistics
3. Add a new student via Students → Add New Student
4. Add a new teacher via Teachers → Add New Teacher
5. Create classes/sections via Classes & Sections
6. Add subjects via Subjects
7. Mark attendance via Attendance
8. Create fee invoice via Fees
9. Post a notice via Notices

### 2. Test Teacher Portal
1. Login as `teacher1` / `teacher123`
2. View dashboard with assigned subjects
3. Check class teacher sections
4. View latest notices

### 3. Test Student Portal
1. Login as `student1` / `student123`
2. View dashboard with student info
3. Check attendance percentage
4. View unpaid fees
5. See upcoming exams
6. Read latest notices

---

## Notes
- All passwords are hashed using bcrypt
- Users are linked to their respective teacher/student records
- Role-based access control is enforced on all pages
- CSRF protection is active on all forms
