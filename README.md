# School Management System - README

## Overview
A comprehensive web-based School Management System with two main components:
1. **Public Website** - For visitors, parents, and prospective students
2. **Internal Portal** - Role-based system for Admin, Teachers, and Students

## Features

### Public Website
- Homepage with school information
- About Us page
- Academic information
- Online admission form
- Events and news
- Photo gallery
- Notice board
- Contact form

### Admin Panel
- Dashboard with statistics
- Student management (CRUD, promotion)
- Teacher management
- Class & section management
- Subject management
- Attendance tracking
- Exam management
- Results management
- Fee/payment system
- User account management
- Website content management (CMS)
- Admission request handling
- Contact message viewer

### Teacher Panel
- Personal dashboard
- Attendance taking
- Marks entry
- View assigned subjects
- Class routine
- Notice board

### Student Panel
- Personal dashboard
- View attendance history
- View results with PDF download
- Fee invoices and payment history
- Class routine
- Profile information
- Notice board

## Tech Stack
- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP (OOP with MVC pattern)
- **Database**: MySQL
- **Authentication**: PHP Sessions with password hashing (bcrypt)
- **Security**: CSRF protection, prepared statements, input sanitization

## Installation

### 1. Database Setup
```bash
mysql -u root -p
CREATE DATABASE school_management;
USE school_management;
SOURCE database.sql;
```

### 2. Configuration
Edit `config.php` and update database credentials if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'school_management');
```

### 3. File Permissions
```bash
chmod 755 public/uploads
```

### 4. Start Server
```bash
# Using PHP built-in server
php -S localhost:8000

# Or configure Apache/Nginx to point to the project directory
```

### 5. Access the System
- **Public Website**: http://localhost:8000/
- **Login**: http://localhost:8000/login.php
- **Default Admin**: username: `admin`, password: `admin123`

## Directory Structure
```
SchoolMS/
├── admin/              # Admin panel pages
│   ├── dashboard.php
│   ├── students/
│   ├── teachers/
│   ├── classes/
│   └── ...
├── teacher/            # Teacher panel pages
├── student/            # Student panel pages
├── public/             # Public website pages
│   ├── index.php
│   ├── about.php
│   ├── css/
│   ├── js/
│   └── uploads/
├── core/               # Core application files
│   ├── Database.php
│   ├── helpers.php
│   └── models/
├── includes/           # Shared components
│   ├── admin_header.php
│   └── admin_footer.php
├── config.php          # Configuration file
├── database.sql        # Database schema
├── login.php           # Login page
└── logout.php          # Logout handler
```

## Database Schema
The system uses 16 tables:
- `students` - Student information
- `teachers` - Teacher information
- `classes` - Class definitions
- `sections` - Class sections
- `subjects` - Subject information
- `attendance` - Attendance records
- `exams` - Exam definitions
- `results` - Exam results
- `fees` - Fee invoices
- `users` - User accounts
- `notices` - Announcements
- `events` - School events
- `gallery` - Photo gallery
- `contact_messages` - Contact form submissions
- `admission_requests` - Online admission applications
- `website_content` - CMS content
- `routines` - Class timetables

## Security Features
- Password hashing using bcrypt
- CSRF token protection on all forms
- SQL injection prevention using prepared statements
- XSS prevention through input sanitization
- Role-based access control
- Session management

## Default Credentials
**Admin Account:**
- Username: `admin`
- Password: `admin123`

**Important:** Change the default admin password immediately after first login!

## Key Features Implemented

### Authentication & Authorization
- Secure login with password hashing
- Role-based access control (Admin, Teacher, Student)
- Session management
- CSRF protection

### Student Management
- Add/Edit/Delete students
- Search and filter functionality
- Class promotion system
- Student profiles with guardian information
- Automatic user account creation

### Modern UI/UX
- Responsive design for mobile and desktop
- Gradient color schemes
- Smooth animations
- Card-based layouts
- Modern typography (Inter font)

### Database Architecture
- Normalized database design
- Foreign key constraints
- Indexes for performance
- Comprehensive data relationships

## Development Notes
- All models extend `BaseModel` for common CRUD operations
- Helper functions available in `core/helpers.php`
- CSS uses CSS custom properties for theming
- JavaScript includes form validation and AJAX functionality

## Future Enhancements
- PDF report generation for results
- Email notifications
- SMS integration
- Online payment gateway
- Mobile app
- Advanced analytics dashboard
- Bulk import/export functionality
- Multi-language support

## Support
For issues or questions, please contact the development team.

## License
This project is developed for educational purposes.
