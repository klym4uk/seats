# Social Engineering Awareness Training System (SEATS) - System Architecture

## 1. System Overview

SEATS is a web-based training platform designed to educate employees about social engineering threats and test their awareness through interactive modules. The system provides a structured approach to security awareness training with a focus on contextualization to organizational workflows.

## 2. Architecture Components

### 2.1 High-Level Architecture

The system follows a three-tier architecture:

1. **Presentation Layer (Frontend)**: User interface components built with HTML, CSS, JavaScript, and Bootstrap
2. **Application Layer (Backend)**: Business logic implemented in PHP
3. **Data Layer**: MySQL database for persistent storage

### 2.2 Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: PHP 8.x
- **Database**: MySQL 8.x
- **Development Environment**: XAMPP, VS Code
- **Additional Libraries**: 
  - jQuery for DOM manipulation
  - Chart.js for reporting visualizations
  - Bootstrap Icons for UI elements

## 3. Database Schema

Based on the domain model provided in the requirements, the database will consist of the following tables:

### 3.1 Tables

#### users
- user_id (PK, INT, AUTO_INCREMENT)
- name (VARCHAR(100))
- email (VARCHAR(100), UNIQUE)
- password (VARCHAR(255)) - Hashed
- role (ENUM('employee', 'admin'))
- created_at (TIMESTAMP)
- last_login (TIMESTAMP)

#### modules
- module_id (PK, INT, AUTO_INCREMENT)
- title (VARCHAR(100))
- description (TEXT)
- created_by (INT, FK to users.user_id)
- deadline (DATE)
- status (ENUM('active', 'inactive'))
- created_at (TIMESTAMP)

#### lessons
- lesson_id (PK, INT, AUTO_INCREMENT)
- module_id (INT, FK to modules.module_id)
- title (VARCHAR(100))
- content (TEXT)
- content_type (ENUM('text', 'video', 'image'))
- order_number (INT)
- created_at (TIMESTAMP)

#### quizzes
- quiz_id (PK, INT, AUTO_INCREMENT)
- module_id (INT, FK to modules.module_id)
- title (VARCHAR(100))
- description (TEXT)
- passing_threshold (INT) - Percentage required to pass
- cooldown_period (INT) - Hours before retake allowed
- created_at (TIMESTAMP)

#### questions
- question_id (PK, INT, AUTO_INCREMENT)
- quiz_id (INT, FK to quizzes.quiz_id)
- question_text (TEXT)
- explanation (TEXT) - Explanation for correct answer
- created_at (TIMESTAMP)

#### answers
- answer_id (PK, INT, AUTO_INCREMENT)
- question_id (INT, FK to questions.question_id)
- answer_text (TEXT)
- is_correct (BOOLEAN)
- created_at (TIMESTAMP)

#### user_module_progress
- progress_id (PK, INT, AUTO_INCREMENT)
- user_id (INT, FK to users.user_id)
- module_id (INT, FK to modules.module_id)
- status (ENUM('not_started', 'in_progress', 'completed'))
- completion_date (TIMESTAMP)
- created_at (TIMESTAMP)

#### user_lesson_progress
- progress_id (PK, INT, AUTO_INCREMENT)
- user_id (INT, FK to users.user_id)
- lesson_id (INT, FK to lessons.lesson_id)
- status (ENUM('not_viewed', 'viewed', 'completed'))
- completion_date (TIMESTAMP)
- created_at (TIMESTAMP)

#### quiz_results
- result_id (PK, INT, AUTO_INCREMENT)
- user_id (INT, FK to users.user_id)
- quiz_id (INT, FK to quizzes.quiz_id)
- score (INT) - Percentage score
- passed (BOOLEAN)
- attempt_number (INT)
- completion_time (INT) - Time taken in seconds
- completed_at (TIMESTAMP)
- created_at (TIMESTAMP)

#### user_question_answers
- id (PK, INT, AUTO_INCREMENT)
- result_id (INT, FK to quiz_results.result_id)
- question_id (INT, FK to questions.question_id)
- answer_id (INT, FK to answers.answer_id)
- is_correct (BOOLEAN)
- created_at (TIMESTAMP)

### 3.2 Entity Relationship Diagram (ERD)

The ERD represents the relationships between entities:

- One **User** can have many **Module Progress** records
- One **Module** contains many **Lessons** and one **Quiz**
- One **Quiz** contains many **Questions**
- One **Question** has multiple **Answers**
- One **User** can have many **Quiz Results**
- Each **Quiz Result** contains many **User Question Answers**

## 4. Frontend Structure

### 4.1 Pages and Components

#### Employee Interface
- Login Page
- Dashboard
  - Overview of assigned modules
  - Progress tracking
  - Upcoming deadlines
- Module Page
  - List of lessons
  - Access to quiz (when lessons are completed)
- Lesson Page
  - Lesson content (text, video, images)
  - Navigation between lessons
- Quiz Page
  - Questions with multiple-choice answers
  - Timer (optional)
  - Submit button
- Results Page
  - Score
  - Correct/incorrect answers with explanations
  - Option to retake (if failed and cooldown period passed)
- Profile Page
  - Personal information
  - Password change

#### Admin Interface
- Login Page
- Admin Dashboard
  - Overview of system usage
  - Employee progress statistics
  - Quick access to management functions
- Module Management
  - Create/edit/delete modules
  - Set deadlines
  - Assign to employees
- Lesson Management
  - Create/edit/delete lessons
  - Upload content (text, videos, images)
- Quiz Management
  - Create/edit/delete quizzes
  - Set passing threshold and cooldown period
- Question Management
  - Create/edit/delete questions
  - Add answer options
- User Management
  - Add/edit/delete users
  - Assign roles
- Reports
  - Module completion rates
  - Quiz performance statistics
  - Export options (PDF)

### 4.2 Responsive Design

- Bootstrap 5 grid system for responsive layouts
- Mobile-first approach
- Breakpoints for different device sizes (mobile, tablet, desktop)

## 5. Backend Structure

### 5.1 Directory Structure

```
/seats
  /assets
    /css
    /js
    /images
  /config
    database.php
    config.php
  /includes
    header.php
    footer.php
    functions.php
    auth.php
  /classes
    User.php
    Module.php
    Lesson.php
    Quiz.php
    Question.php
    Result.php
    Report.php
  /admin
    index.php
    modules.php
    lessons.php
    quizzes.php
    questions.php
    users.php
    reports.php
  /employee
    index.php
    modules.php
    lessons.php
    quizzes.php
    results.php
    profile.php
  index.php
  login.php
  logout.php
  register.php (optional)
  reset-password.php
```

### 5.2 API Endpoints

The system will use PHP for server-side processing with the following endpoints:

#### Authentication
- `POST /api/login.php` - User login
- `POST /api/logout.php` - User logout
- `POST /api/reset-password.php` - Password reset

#### Modules
- `GET /api/modules.php` - Get all modules
- `GET /api/modules.php?id={id}` - Get specific module
- `POST /api/modules.php` - Create module (admin only)
- `PUT /api/modules.php?id={id}` - Update module (admin only)
- `DELETE /api/modules.php?id={id}` - Delete module (admin only)

#### Lessons
- `GET /api/lessons.php?module_id={id}` - Get lessons for module
- `GET /api/lessons.php?id={id}` - Get specific lesson
- `POST /api/lessons.php` - Create lesson (admin only)
- `PUT /api/lessons.php?id={id}` - Update lesson (admin only)
- `DELETE /api/lessons.php?id={id}` - Delete lesson (admin only)
- `POST /api/lessons.php?id={id}/complete` - Mark lesson as completed

#### Quizzes
- `GET /api/quizzes.php?module_id={id}` - Get quiz for module
- `GET /api/quizzes.php?id={id}` - Get specific quiz
- `POST /api/quizzes.php` - Create quiz (admin only)
- `PUT /api/quizzes.php?id={id}` - Update quiz (admin only)
- `DELETE /api/quizzes.php?id={id}` - Delete quiz (admin only)
- `POST /api/quizzes.php?id={id}/submit` - Submit quiz answers

#### Users
- `GET /api/users.php` - Get all users (admin only)
- `GET /api/users.php?id={id}` - Get specific user
- `POST /api/users.php` - Create user (admin only)
- `PUT /api/users.php?id={id}` - Update user
- `DELETE /api/users.php?id={id}` - Delete user (admin only)

#### Progress
- `GET /api/progress.php?user_id={id}` - Get progress for user
- `GET /api/progress.php?module_id={id}` - Get progress for module (admin only)

#### Reports
- `GET /api/reports.php?type=module` - Get module completion report (admin only)
- `GET /api/reports.php?type=quiz` - Get quiz performance report (admin only)
- `GET /api/reports.php?type=user` - Get user progress report (admin only)
- `GET /api/reports.php?type=export&format=pdf` - Export report as PDF (admin only)

## 6. Security Considerations

### 6.1 Authentication and Authorization
- Password hashing using PHP's password_hash() function
- Session-based authentication
- CSRF protection with tokens
- Role-based access control (employee vs. admin)

### 6.2 Data Protection
- Input validation and sanitization
- Prepared statements for database queries
- XSS prevention
- HTTPS recommended for production deployment

### 6.3 Error Handling
- Custom error pages
- Logging of errors (without exposing sensitive information)
- Graceful failure handling

## 7. Deployment Considerations

### 7.1 Development Environment
- XAMPP for local development
- PHP 8.x
- MySQL 8.x
- Apache web server

### 7.2 Production Requirements
- Web server with PHP support
- MySQL database
- Minimum 1GB RAM
- 10GB storage space
- Regular backups

## 8. Future Enhancements

Potential future enhancements for the system include:

- Integration with email notification system (PHPMailer)
- Simulated phishing campaigns
- Advanced analytics and reporting
- Gamification elements (badges, leaderboards)
- Mobile application
- Integration with LDAP/Active Directory for authentication
- API for integration with other systems
