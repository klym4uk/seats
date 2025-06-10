# SEATS: Social Engineering Awareness Training System
## User Documentation

### Table of Contents
1. [Introduction](#introduction)
2. [System Overview](#system-overview)
3. [Installation Guide](#installation-guide)
4. [User Guides](#user-guides)
   - [Employee User Guide](#employee-user-guide)
   - [Admin User Guide](#admin-user-guide)
5. [Technical Documentation](#technical-documentation)
6. [Troubleshooting](#troubleshooting)

## Introduction

The Social Engineering Awareness Training System (SEATS) is a web-based platform designed to educate employees about social engineering threats and test their awareness through interactive modules. This system provides a structured training program with study materials, assessments, progress tracking, and reporting capabilities.

## System Overview

SEATS consists of the following core components:

1. **User Authentication System**: Secure login for employees and administrators
2. **Training Modules**: Interactive lessons on various social engineering topics
3. **Assessment System**: Quizzes to test knowledge retention
4. **Progress Tracking**: Monitoring of employee completion rates and scores
5. **Reporting and Analytics**: Tools for administrators to evaluate training effectiveness

The system is built using PHP, MySQL, HTML, CSS, JavaScript, and Bootstrap, making it lightweight and easy to deploy in most web environments.

## Installation Guide

### System Requirements

- Web server with PHP 8.x support (Apache recommended)
- MySQL 8.x database
- Modern web browser (Chrome, Firefox, Edge)

### Installation Steps

1. **Database Setup**:
   - Create a MySQL database named `seats_db`
   - Import the `database.sql` file to create the necessary tables
   - Default admin credentials: Email: `admin@example.com`, Password: `admin123` (change immediately after first login)

2. **Web Server Configuration**:
   - Copy the entire `seats` directory to your web server's document root
   - Ensure the web server has write permissions to the `assets/uploads` directory
   - Configure your web server to point to the `seats` directory

3. **Configuration**:
   - Edit `config/config.php` to update database connection details and application settings
   - Update `APP_URL` to match your server's URL

4. **First Login**:
   - Access the system through your web browser
   - Log in with the default admin credentials
   - Change the default password immediately

## User Guides

### Employee User Guide

#### Logging In
1. Navigate to the SEATS login page
2. Enter your email and password
3. Click "Sign in"

#### Dashboard
- The dashboard displays your assigned training modules
- View your progress and upcoming deadlines
- Access social engineering awareness tips

#### Taking Training Modules
1. Click on a module from your dashboard
2. Complete each lesson in sequence
3. Mark lessons as completed to progress
4. After completing all lessons, take the assessment quiz

#### Completing Quizzes
1. Answer all questions in the quiz
2. Submit your answers
3. Review your results
4. Retake the quiz if necessary (subject to cooldown period)

#### Tracking Progress
- View your overall progress on the dashboard
- See detailed progress for each module
- Check your quiz scores and completion status

### Admin User Guide

#### Logging In
1. Navigate to the SEATS login page
2. Enter your admin email and password
3. Click "Sign in"

#### Dashboard
- View system statistics and employee progress
- Access quick links to management functions
- Monitor completion rates and quiz performance

#### Managing Users
1. Navigate to the Users section
2. Add new employees with the "Add User" button
3. Edit or delete existing users as needed
4. Assign roles (employee or admin)

#### Creating Training Modules
1. Go to the Modules section
2. Click "Add Module"
3. Fill in the module details (title, description, deadline)
4. Save the module

#### Adding Lessons
1. Select a module
2. Click "Add Lesson"
3. Enter lesson title and content
4. Set the lesson order
5. Save the lesson

#### Creating Quizzes
1. Select a module
2. Click "Add Quiz"
3. Enter quiz details (title, description, passing threshold)
4. Add questions and answer options
5. Mark correct answers
6. Save the quiz

#### Generating Reports
1. Go to the Reports section
2. Select the report type (module completion, quiz performance, user progress)
3. View the report data
4. Export to PDF if needed

## Technical Documentation

### System Architecture

SEATS follows a three-tier architecture:

1. **Presentation Layer**: User interfaces built with HTML, CSS, and JavaScript
2. **Application Layer**: Business logic implemented in PHP
3. **Data Layer**: MySQL database for persistent storage

### Database Schema

The database consists of the following main tables:

- `users`: Stores user account information
- `modules`: Contains training module details
- `lessons`: Stores lesson content for each module
- `quizzes`: Contains quiz information for modules
- `questions`: Stores quiz questions
- `answers`: Contains answer options for questions
- `user_module_progress`: Tracks user progress through modules
- `user_lesson_progress`: Tracks user progress through lessons
- `quiz_results`: Stores quiz attempt results
- `user_question_answers`: Records user answers to quiz questions

### Directory Structure

```
/seats
  /assets
    /css
    /js
    /images
    /uploads
  /config
    database.php
    config.php
  /includes
    header.php
    footer.php
    admin_nav.php
    employee_nav.php
    admin_sidebar.php
    employee_sidebar.php
  /classes
    User.php
    Module.php
    Lesson.php
    Quiz.php
    Report.php
  /admin
    index.php
    modules.php
    lessons.php
    quizzes.php
    users.php
    reports.php
  /employee
    index.php
    module.php
    lesson.php
    quiz.php
    quiz_result.php
  index.php
  login.php
  logout.php
```

## Troubleshooting

### Common Issues

1. **Login Problems**:
   - Verify database connection settings in `config.php`
   - Check that the user account exists in the database
   - Ensure the password is correct

2. **Module Access Issues**:
   - Confirm that modules are assigned to the user
   - Verify that modules are set to "active" status

3. **Quiz Submission Errors**:
   - Ensure all questions have been answered
   - Check database connection
   - Verify that the quiz is properly linked to the module

4. **Display Problems**:
   - Clear browser cache
   - Update to the latest browser version
   - Check for JavaScript errors in the browser console

### Support

For additional support, please contact your system administrator.
