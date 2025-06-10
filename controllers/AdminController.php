<?php
/**
 * Admin Controller
 */
class AdminController {
    private $userModel;
    private $moduleModel;
    private $lessonModel;
    private $quizModel;
    
    public function __construct() {
        // Check if the user is logged in and is an admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            // Set flash message
            $_SESSION['flash_message'] = 'You do not have permission to access that page';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the login page
            redirect('index.php?page=login');
        }
        
        // Load models
        require_once 'models/User.php';
        require_once 'models/Module.php';
        require_once 'models/Lesson.php';
        require_once 'models/Quiz.php';
        
        $this->userModel = new User();
        $this->moduleModel = new Module();
        $this->lessonModel = new Lesson();
        $this->quizModel = new Quiz();
    }
    
    /**
     * Display the admin dashboard
     */
    public function dashboard() {
        // Get statistics for the dashboard
        $totalUsers = count($this->userModel->getAll());
        $totalModules = count($this->moduleModel->getAll());
        $totalQuizzes = count($this->quizModel->getAll());
        
        // Get recent quiz results
        $recentResults = $this->getRecentQuizResults(5);
        
        $data = [
            'pageTitle' => 'Admin Dashboard',
            'totalUsers' => $totalUsers,
            'totalModules' => $totalModules,
            'totalQuizzes' => $totalQuizzes,
            'recentResults' => $recentResults
        ];
        
        view('admin/dashboard', $data);
    }
    
    /**
     * Display the users page
     */
    public function users() {
        // Get all users
        $users = $this->userModel->getAll();
        
        $data = [
            'pageTitle' => 'Manage Users',
            'users' => $users
        ];
        
        view('admin/users', $data);
    }
    
    /**
     * Display the edit user page
     */
    public function editUser() {
        // Check if the user ID is provided
        if (!isset($_GET['id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'User ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the users page
            redirect('index.php?page=admin-dashboard&action=users');
        }
        
        // Get the user ID
        $userId = (int) $_GET['id'];
        
        // Get the user data
        $user = $this->userModel->getById($userId);
        
        // Check if the user exists
        if (!$user) {
            // Set flash message
            $_SESSION['flash_message'] = 'User not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the users page
            redirect('index.php?page=admin-dashboard&action=users');
        }
        
        // Check if the form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the form data
            $name = sanitize($_POST['name']);
            $email = sanitize($_POST['email']);
            $role = sanitize($_POST['role']);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            
            // Validate the form data
            $errors = [];
            
            if (empty($name)) {
                $errors['name'] = 'Name is required';
            }
            
            if (empty($email)) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            } elseif ($email !== $user['email'] && $this->userModel->emailExists($email)) {
                $errors['email'] = 'Email already exists';
            }
            
            if (empty($role) || !in_array($role, ['admin', 'employee'])) {
                $errors['role'] = 'Invalid role';
            }
            
            // If the admin is changing the user's password
            if (!empty($password) || !empty($confirmPassword)) {
                if (empty($password)) {
                    $errors['password'] = 'Password is required';
                } else {
                    // Validate password strength
                    $passwordValidation = validatePassword($password);
                    
                    if (!$passwordValidation['valid']) {
                        $errors['password'] = $passwordValidation['message'];
                    }
                }
                
                if ($password !== $confirmPassword) {
                    $errors['confirm_password'] = 'Passwords do not match';
                }
            }
            
            // If there are no errors, update the user
            if (empty($errors)) {
                $userData = [
                    'name' => $name,
                    'email' => $email,
                    'role' => $role
                ];
                
                // If the admin is changing the user's password
                if (!empty($password)) {
                    $userData['password'] = $password;
                }
                
                $result = $this->userModel->update($userId, $userData);
                
                if ($result) {
                    // Set flash message
                    $_SESSION['flash_message'] = 'User updated successfully';
                    $_SESSION['flash_message_type'] = 'success';
                    
                    // Redirect to the users page
                    redirect('index.php?page=admin-dashboard&action=users');
                } else {
                    $errors['edit_user'] = 'Failed to update user';
                }
            }
            
            // If we get here, there were errors
            $data = [
                'pageTitle' => 'Edit User',
                'errors' => $errors,
                'user' => $user
            ];
            
            view('admin/edit-user', $data);
        } else {
            // Display the edit user page
            $data = [
                'pageTitle' => 'Edit User',
                'errors' => [],
                'user' => $user
            ];
            
            view('admin/edit-user', $data);
        }
    }
    
    /**
     * Delete a user
     */
    public function deleteUser() {
        // Check if the user ID is provided
        if (!isset($_GET['id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'User ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the users page
            redirect('index.php?page=admin-dashboard&action=users');
        }
        
        // Get the user ID
        $userId = (int) $_GET['id'];
        
        // Check if the user is trying to delete themselves
        if ($userId === (int) $_SESSION['user_id']) {
            // Set flash message
            $_SESSION['flash_message'] = 'You cannot delete your own account';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the users page
            redirect('index.php?page=admin-dashboard&action=users');
        }
        
        // Delete the user
        $result = $this->userModel->delete($userId);
        
        if ($result) {
            // Set flash message
            $_SESSION['flash_message'] = 'User deleted successfully';
            $_SESSION['flash_message_type'] = 'success';
        } else {
            // Set flash message
            $_SESSION['flash_message'] = 'Failed to delete user';
            $_SESSION['flash_message_type'] = 'danger';
        }
        
        // Redirect to the users page
        redirect('index.php?page=admin-dashboard&action=users');
    }
    
    /**
     * Display the modules page
     */
    public function modules() {
        // Get all modules
        $modules = $this->moduleModel->getAll();
        
        $data = [
            'pageTitle' => 'Manage Modules',
            'modules' => $modules
        ];
        
        view('admin/modules', $data);
    }
    
    /**
     * Display the create module page
     */
    public function createModule() {
        // Check if the form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the form data
            $title = sanitize($_POST['title']);
            $description = sanitize($_POST['description']);
            $deadline = sanitize($_POST['deadline']);
            $status = sanitize($_POST['status']);
            
            // Validate the form data
            $errors = [];
            
            if (empty($title)) {
                $errors['title'] = 'Title is required';
            }
            
            if (empty($status) || !in_array($status, ['active', 'inactive'])) {
                $errors['status'] = 'Invalid status';
            }
            
            // If there are no errors, create the module
            if (empty($errors)) {
                $moduleData = [
                    'title' => $title,
                    'description' => $description,
                    'deadline' => $deadline,
                    'status' => $status
                ];
                
                $moduleId = $this->moduleModel->create($moduleData);
                
                if ($moduleId) {
                    // Set flash message
                    $_SESSION['flash_message'] = 'Module created successfully';
                    $_SESSION['flash_message_type'] = 'success';
                    
                    // Redirect to the modules page
                    redirect('index.php?page=admin-dashboard&action=modules');
                } else {
                    $errors['create_module'] = 'Failed to create module';
                }
            }
            
            // If we get here, there were errors
            $data = [
                'pageTitle' => 'Create Module',
                'errors' => $errors,
                'title' => $title,
                'description' => $description,
                'deadline' => $deadline,
                'status' => $status
            ];
            
            view('admin/create-module', $data);
        } else {
            // Display the create module page
            $data = [
                'pageTitle' => 'Create Module',
                'errors' => []
            ];
            
            view('admin/create-module', $data);
        }
    }
    
    /**
     * Display the edit module page
     */
    public function editModule() {
        // Check if the module ID is provided
        if (!isset($_GET['id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the module ID
        $moduleId = (int) $_GET['id'];
        
        // Get the module data
        $module = $this->moduleModel->getById($moduleId);
        
        // Check if the module exists
        if (!$module) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Check if the form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the form data
            $title = sanitize($_POST['title']);
            $description = sanitize($_POST['description']);
            $deadline = sanitize($_POST['deadline']);
            $status = sanitize($_POST['status']);
            
            // Validate the form data
            $errors = [];
            
            if (empty($title)) {
                $errors['title'] = 'Title is required';
            }
            
            if (empty($status) || !in_array($status, ['active', 'inactive'])) {
                $errors['status'] = 'Invalid status';
            }
            
            // If there are no errors, update the module
            if (empty($errors)) {
                $moduleData = [
                    'title' => $title,
                    'description' => $description,
                    'deadline' => $deadline,
                    'status' => $status
                ];
                
                $result = $this->moduleModel->update($moduleId, $moduleData);
                
                if ($result) {
                    // Set flash message
                    $_SESSION['flash_message'] = 'Module updated successfully';
                    $_SESSION['flash_message_type'] = 'success';
                    
                    // Redirect to the modules page
                    redirect('index.php?page=admin-dashboard&action=modules');
                } else {
                    $errors['edit_module'] = 'Failed to update module';
                }
            }
            
            // If we get here, there were errors
            $data = [
                'pageTitle' => 'Edit Module',
                'errors' => $errors,
                'module' => $module
            ];
            
            view('admin/edit-module', $data);
        } else {
            // Display the edit module page
            $data = [
                'pageTitle' => 'Edit Module',
                'errors' => [],
                'module' => $module
            ];
            
            view('admin/edit-module', $data);
        }
    }
    
    /**
     * Delete a module
     */
    public function deleteModule() {
        // Check if the module ID is provided
        if (!isset($_GET['id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the module ID
        $moduleId = (int) $_GET['id'];
        
        // Delete the module
        $result = $this->moduleModel->delete($moduleId);
        
        if ($result) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module deleted successfully';
            $_SESSION['flash_message_type'] = 'success';
        } else {
            // Set flash message
            $_SESSION['flash_message'] = 'Failed to delete module';
            $_SESSION['flash_message_type'] = 'danger';
        }
        
        // Redirect to the modules page
        redirect('index.php?page=admin-dashboard&action=modules');
    }
    
    /**
     * Display the module lessons page
     */
    public function moduleLessons() {
        // Check if the module ID is provided
        if (!isset($_GET['id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the module ID
        $moduleId = (int) $_GET['id'];
        
        // Get the module data
        $module = $this->moduleModel->getById($moduleId);
        
        // Check if the module exists
        if (!$module) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the module lessons
        $lessons = $this->lessonModel->getByModule($moduleId);
        
        $data = [
            'pageTitle' => 'Module Lessons',
            'module' => $module,
            'lessons' => $lessons
        ];
        
        view('admin/module-lessons', $data);
    }
    
    /**
     * Display the create lesson page
     */
    public function createLesson() {
        // Check if the module ID is provided
        if (!isset($_GET['module_id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the module ID
        $moduleId = (int) $_GET['module_id'];
        
        // Get the module data
        $module = $this->moduleModel->getById($moduleId);
        
        // Check if the module exists
        if (!$module) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Check if the form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the form data
            $title = sanitize($_POST['title']);
            $content = $_POST['content']; // Don't sanitize content to allow HTML
            
            // Validate the form data
            $errors = [];
            
            if (empty($title)) {
                $errors['title'] = 'Title is required';
            }
            
            if (empty($content)) {
                $errors['content'] = 'Content is required';
            }
            
            // If there are no errors, create the lesson
            if (empty($errors)) {
                $lessonData = [
                    'module_id' => $moduleId,
                    'title' => $title,
                    'content' => $content
                ];
                
                $lessonId = $this->lessonModel->create($lessonData);
                
                if ($lessonId) {
                    // Set flash message
                    $_SESSION['flash_message'] = 'Lesson created successfully';
                    $_SESSION['flash_message_type'] = 'success';
                    
                    // Redirect to the module lessons page
                    redirect('index.php?page=admin-dashboard&action=module-lessons&id=' . $moduleId);
                } else {
                    $errors['create_lesson'] = 'Failed to create lesson';
                }
            }
            
            // If we get here, there were errors
            $data = [
                'pageTitle' => 'Create Lesson',
                'errors' => $errors,
                'module' => $module,
                'title' => $title,
                'content' => $content
            ];
            
            view('admin/create-lesson', $data);
        } else {
            // Display the create lesson page
            $data = [
                'pageTitle' => 'Create Lesson',
                'errors' => [],
                'module' => $module
            ];
            
            view('admin/create-lesson', $data);
        }
    }
    
    /**
     * Display the edit lesson page
     */
    public function editLesson() {
        // Check if the lesson ID is provided
        if (!isset($_GET['id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Lesson ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the lesson ID
        $lessonId = (int) $_GET['id'];
        
        // Get the lesson data
        $lesson = $this->lessonModel->getById($lessonId);
        
        // Check if the lesson exists
        if (!$lesson) {
            // Set flash message
            $_SESSION['flash_message'] = 'Lesson not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the module data
        $module = $this->moduleModel->getById($lesson['module_id']);
        
        // Check if the form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the form data
            $title = sanitize($_POST['title']);
            $content = $_POST['content']; // Don't sanitize content to allow HTML
            
            // Validate the form data
            $errors = [];
            
            if (empty($title)) {
                $errors['title'] = 'Title is required';
            }
            
            if (empty($content)) {
                $errors['content'] = 'Content is required';
            }
            
            // If there are no errors, update the lesson
            if (empty($errors)) {
                $lessonData = [
                    'title' => $title,
                    'content' => $content
                ];
                
                $result = $this->lessonModel->update($lessonId, $lessonData);
                
                if ($result) {
                    // Set flash message
                    $_SESSION['flash_message'] = 'Lesson updated successfully';
                    $_SESSION['flash_message_type'] = 'success';
                    
                    // Redirect to the module lessons page
                    redirect('index.php?page=admin-dashboard&action=module-lessons&id=' . $lesson['module_id']);
                } else {
                    $errors['edit_lesson'] = 'Failed to update lesson';
                }
            }
            
            // If we get here, there were errors
            $data = [
                'pageTitle' => 'Edit Lesson',
                'errors' => $errors,
                'module' => $module,
                'lesson' => $lesson
            ];
            
            view('admin/edit-lesson', $data);
        } else {
            // Display the edit lesson page
            $data = [
                'pageTitle' => 'Edit Lesson',
                'errors' => [],
                'module' => $module,
                'lesson' => $lesson
            ];
            
            view('admin/edit-lesson', $data);
        }
    }
    
    /**
     * Delete a lesson
     */
    public function deleteLesson() {
        // Check if the lesson ID is provided
        if (!isset($_GET['id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Lesson ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the lesson ID
        $lessonId = (int) $_GET['id'];
        
        // Get the lesson data
        $lesson = $this->lessonModel->getById($lessonId);
        
        // Check if the lesson exists
        if (!$lesson) {
            // Set flash message
            $_SESSION['flash_message'] = 'Lesson not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Delete the lesson
        $result = $this->lessonModel->delete($lessonId);
        
        if ($result) {
            // Set flash message
            $_SESSION['flash_message'] = 'Lesson deleted successfully';
            $_SESSION['flash_message_type'] = 'success';
        } else {
            // Set flash message
            $_SESSION['flash_message'] = 'Failed to delete lesson';
            $_SESSION['flash_message_type'] = 'danger';
        }
        
        // Redirect to the module lessons page
        redirect('index.php?page=admin-dashboard&action=module-lessons&id=' . $lesson['module_id']);
    }
    
    /**
     * Move a lesson up or down in the order
     */
    public function moveLesson() {
        // Check if the lesson ID and direction are provided
        if (!isset($_GET['id']) || !isset($_GET['direction'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Lesson ID and direction are required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the lesson ID and direction
        $lessonId = (int) $_GET['id'];
        $direction = $_GET['direction'];
        
        // Check if the direction is valid
        if ($direction !== 'up' && $direction !== 'down') {
            // Set flash message
            $_SESSION['flash_message'] = 'Invalid direction';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the lesson data
        $lesson = $this->lessonModel->getById($lessonId);
        
        // Check if the lesson exists
        if (!$lesson) {
            // Set flash message
            $_SESSION['flash_message'] = 'Lesson not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Move the lesson
        $result = $this->lessonModel->move($lessonId, $direction);
        
        if ($result) {
            // Set flash message
            $_SESSION['flash_message'] = 'Lesson moved successfully';
            $_SESSION['flash_message_type'] = 'success';
        } else {
            // Set flash message
            $_SESSION['flash_message'] = 'Failed to move lesson';
            $_SESSION['flash_message_type'] = 'danger';
        }
        
        // Redirect to the module lessons page
        redirect('index.php?page=admin-dashboard&action=module-lessons&id=' . $lesson['module_id']);
    }
    
    /**
     * Display the module quiz page
     */
    public function moduleQuiz() {
        // Check if the module ID is provided
        if (!isset($_GET['id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the module ID
        $moduleId = (int) $_GET['id'];
        
        // Get the module data
        $module = $this->moduleModel->getById($moduleId);
        
        // Check if the module exists
        if (!$module) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the module quiz
        $quiz = $this->quizModel->getByModule($moduleId);
        
        // Get the quiz questions if the quiz exists
        $questions = [];
        if ($quiz) {
            $questions = $this->quizModel->getQuestions($quiz['id']);
            
            // Get the answers for each question
            foreach ($questions as &$question) {
                $question['answers'] = $this->quizModel->getAnswers($question['id']);
            }
        }
        
        $data = [
            'pageTitle' => 'Module Quiz',
            'module' => $module,
            'quiz' => $quiz,
            'questions' => $questions
        ];
        
        view('admin/module-quiz', $data);
    }
    
    /**
     * Display the create quiz page
     */
    public function createQuiz() {
        // Check if the module ID is provided
        if (!isset($_GET['module_id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the module ID
        $moduleId = (int) $_GET['module_id'];
        
        // Get the module data
        $module = $this->moduleModel->getById($moduleId);
        
        // Check if the module exists
        if (!$module) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Check if the module already has a quiz
        $existingQuiz = $this->quizModel->getByModule($moduleId);
        
        if ($existingQuiz) {
            // Set flash message
            $_SESSION['flash_message'] = 'This module already has a quiz';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the module quiz page
            redirect('index.php?page=admin-dashboard&action=module-quiz&id=' . $moduleId);
        }
        
        // Check if the form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the form data
            $title = sanitize($_POST['title']);
            $description = sanitize($_POST['description']);
            $passingThreshold = (int) $_POST['passing_threshold'];
            $cooldownPeriod = (int) $_POST['cooldown_period'];
            
            // Validate the form data
            $errors = [];
            
            if (empty($title)) {
                $errors['title'] = 'Title is required';
            }
            
            if ($passingThreshold < 0 || $passingThreshold > 100) {
                $errors['passing_threshold'] = 'Passing threshold must be between 0 and 100';
            }
            
            if ($cooldownPeriod < 0) {
                $errors['cooldown_period'] = 'Cooldown period must be greater than or equal to 0';
            }
            
            // If there are no errors, create the quiz
            if (empty($errors)) {
                $quizData = [
                    'module_id' => $moduleId,
                    'title' => $title,
                    'description' => $description,
                    'passing_threshold' => $passingThreshold,
                    'cooldown_period' => $cooldownPeriod
                ];
                
                $quizId = $this->quizModel->create($quizData);
                
                if ($quizId) {
                    // Set flash message
                    $_SESSION['flash_message'] = 'Quiz created successfully';
                    $_SESSION['flash_message_type'] = 'success';
                    
                    // Redirect to the module quiz page
                    redirect('index.php?page=admin-dashboard&action=module-quiz&id=' . $moduleId);
                } else {
                    $errors['create_quiz'] = 'Failed to create quiz';
                }
            }
            
            // If we get here, there were errors
            $data = [
                'pageTitle' => 'Create Quiz',
                'errors' => $errors,
                'module' => $module,
                'title' => $title,
                'description' => $description,
                'passing_threshold' => $passingThreshold,
                'cooldown_period' => $cooldownPeriod
            ];
            
            view('admin/create-quiz', $data);
        } else {
            // Display the create quiz page
            $data = [
                'pageTitle' => 'Create Quiz',
                'errors' => [],
                'module' => $module,
                'passing_threshold' => DEFAULT_PASSING_THRESHOLD,
                'cooldown_period' => DEFAULT_COOLDOWN_PERIOD
            ];
            
            view('admin/create-quiz', $data);
        }
    }
    
    /**
     * Display the edit quiz page
     */
    public function editQuiz() {
        // Check if the quiz ID is provided
        if (!isset($_GET['id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Quiz ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the quiz ID
        $quizId = (int) $_GET['id'];
        
        // Get the quiz data
        $quiz = $this->quizModel->getById($quizId);
        
        // Check if the quiz exists
        if (!$quiz) {
            // Set flash message
            $_SESSION['flash_message'] = 'Quiz not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the module data
        $module = $this->moduleModel->getById($quiz['module_id']);
        
        // Check if the form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the form data
            $title = sanitize($_POST['title']);
            $description = sanitize($_POST['description']);
            $passingThreshold = (int) $_POST['passing_threshold'];
            $cooldownPeriod = (int) $_POST['cooldown_period'];
            
            // Validate the form data
            $errors = [];
            
            if (empty($title)) {
                $errors['title'] = 'Title is required';
            }
            
            if ($passingThreshold < 0 || $passingThreshold > 100) {
                $errors['passing_threshold'] = 'Passing threshold must be between 0 and 100';
            }
            
            if ($cooldownPeriod < 0) {
                $errors['cooldown_period'] = 'Cooldown period must be greater than or equal to 0';
            }
            
            // If there are no errors, update the quiz
            if (empty($errors)) {
                $quizData = [
                    'title' => $title,
                    'description' => $description,
                    'passing_threshold' => $passingThreshold,
                    'cooldown_period' => $cooldownPeriod
                ];
                
                $result = $this->quizModel->update($quizId, $quizData);
                
                if ($result) {
                    // Set flash message
                    $_SESSION['flash_message'] = 'Quiz updated successfully';
                    $_SESSION['flash_message_type'] = 'success';
                    
                    // Redirect to the module quiz page
                    redirect('index.php?page=admin-dashboard&action=module-quiz&id=' . $quiz['module_id']);
                } else {
                    $errors['edit_quiz'] = 'Failed to update quiz';
                }
            }
            
            // If we get here, there were errors
            $data = [
                'pageTitle' => 'Edit Quiz',
                'errors' => $errors,
                'module' => $module,
                'quiz' => $quiz
            ];
            
            view('admin/edit-quiz', $data);
        } else {
            // Display the edit quiz page
            $data = [
                'pageTitle' => 'Edit Quiz',
                'errors' => [],
                'module' => $module,
                'quiz' => $quiz
            ];
            
            view('admin/edit-quiz', $data);
        }
    }
    
    /**
     * Delete a quiz
     */
    public function deleteQuiz() {
        // Check if the quiz ID is provided
        if (!isset($_GET['id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Quiz ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the quiz ID
        $quizId = (int) $_GET['id'];
        
        // Get the quiz data
        $quiz = $this->quizModel->getById($quizId);
        
        // Check if the quiz exists
        if (!$quiz) {
            // Set flash message
            $_SESSION['flash_message'] = 'Quiz not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Delete the quiz
        $result = $this->quizModel->delete($quizId);
        
        if ($result) {
            // Set flash message
            $_SESSION['flash_message'] = 'Quiz deleted successfully';
            $_SESSION['flash_message_type'] = 'success';
        } else {
            // Set flash message
            $_SESSION['flash_message'] = 'Failed to delete quiz';
            $_SESSION['flash_message_type'] = 'danger';
        }
        
        // Redirect to the module quiz page
        redirect('index.php?page=admin-dashboard&action=module-quiz&id=' . $quiz['module_id']);
    }
    
    /**
     * Display the create question page
     */
    public function createQuestion() {
        // Check if the quiz ID is provided
        if (!isset($_GET['quiz_id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Quiz ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the quiz ID
        $quizId = (int) $_GET['quiz_id'];
        
        // Get the quiz data
        $quiz = $this->quizModel->getById($quizId);
        
        // Check if the quiz exists
        if (!$quiz) {
            // Set flash message
            $_SESSION['flash_message'] = 'Quiz not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the module data
        $module = $this->moduleModel->getById($quiz['module_id']);
        
        // Check if the form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the form data
            $questionText = sanitize($_POST['question_text']);
            $answerTexts = isset($_POST['answer_text']) ? $_POST['answer_text'] : [];
            $correctAnswer = isset($_POST['correct_answer']) ? (int) $_POST['correct_answer'] : -1;
            
            // Validate the form data
            $errors = [];
            
            if (empty($questionText)) {
                $errors['question_text'] = 'Question text is required';
            }
            
            if (count($answerTexts) < 2) {
                $errors['answers'] = 'At least two answer options are required';
            }
            
            if ($correctAnswer < 0 || $correctAnswer >= count($answerTexts)) {
                $errors['correct_answer'] = 'Please select a correct answer';
            }
            
            // Check if all answer texts are provided
            foreach ($answerTexts as $index => $text) {
                if (empty($text)) {
                    $errors['answer_text_' . $index] = 'Answer text is required';
                }
            }
            
            // If there are no errors, create the question
            if (empty($errors)) {
                // Prepare the question data
                $questionData = [
                    'quiz_id' => $quizId,
                    'question_text' => $questionText,
                    'answers' => []
                ];
                
                // Prepare the answer data
                foreach ($answerTexts as $index => $text) {
                    $questionData['answers'][] = [
                        'text' => $text,
                        'is_correct' => $index === $correctAnswer ? 1 : 0
                    ];
                }
                
                // Add the question
                $questionId = $this->quizModel->addQuestion($questionData);
                
                if ($questionId) {
                    // Set flash message
                    $_SESSION['flash_message'] = 'Question added successfully';
                    $_SESSION['flash_message_type'] = 'success';
                    
                    // Redirect to the module quiz page
                    redirect('index.php?page=admin-dashboard&action=module-quiz&id=' . $quiz['module_id']);
                } else {
                    $errors['create_question'] = 'Failed to add question';
                }
            }
            
            // If we get here, there were errors
            $data = [
                'pageTitle' => 'Add Question',
                'errors' => $errors,
                'module' => $module,
                'quiz' => $quiz,
                'question_text' => $questionText,
                'answer_texts' => $answerTexts,
                'correct_answer' => $correctAnswer
            ];
            
            view('admin/create-question', $data);
        } else {
            // Display the create question page
            $data = [
                'pageTitle' => 'Add Question',
                'errors' => [],
                'module' => $module,
                'quiz' => $quiz
            ];
            
            view('admin/create-question', $data);
        }
    }
    
    /**
     * Display the edit question page
     */
    public function editQuestion() {
        // Check if the question ID is provided
        if (!isset($_GET['id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Question ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the question ID
        $questionId = (int) $_GET['id'];
        
        // Get the question data
        $question = $this->db->selectOne("SELECT * FROM questions WHERE id = ?", [$questionId]);
        
        // Check if the question exists
        if (!$question) {
            // Set flash message
            $_SESSION['flash_message'] = 'Question not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the quiz data
        $quiz = $this->quizModel->getById($question['quiz_id']);
        
        // Get the module data
        $module = $this->moduleModel->getById($quiz['module_id']);
        
        // Get the answers for the question
        $answers = $this->quizModel->getAnswers($questionId);
        
        // Find the correct answer
        $correctAnswerIndex = -1;
        foreach ($answers as $index => $answer) {
            if ($answer['is_correct']) {
                $correctAnswerIndex = $index;
                break;
            }
        }
        
        // Check if the form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the form data
            $questionText = sanitize($_POST['question_text']);
            $answerTexts = isset($_POST['answer_text']) ? $_POST['answer_text'] : [];
            $correctAnswer = isset($_POST['correct_answer']) ? (int) $_POST['correct_answer'] : -1;
            
            // Validate the form data
            $errors = [];
            
            if (empty($questionText)) {
                $errors['question_text'] = 'Question text is required';
            }
            
            if (count($answerTexts) < 2) {
                $errors['answers'] = 'At least two answer options are required';
            }
            
            if ($correctAnswer < 0 || $correctAnswer >= count($answerTexts)) {
                $errors['correct_answer'] = 'Please select a correct answer';
            }
            
            // Check if all answer texts are provided
            foreach ($answerTexts as $index => $text) {
                if (empty($text)) {
                    $errors['answer_text_' . $index] = 'Answer text is required';
                }
            }
            
            // If there are no errors, update the question
            if (empty($errors)) {
                // Prepare the question data
                $questionData = [
                    'question_text' => $questionText,
                    'answers' => []
                ];
                
                // Prepare the answer data
                foreach ($answerTexts as $index => $text) {
                    $questionData['answers'][] = [
                        'text' => $text,
                        'is_correct' => $index === $correctAnswer ? 1 : 0
                    ];
                }
                
                // Update the question
                $result = $this->quizModel->updateQuestion($questionId, $questionData);
                
                if ($result) {
                    // Set flash message
                    $_SESSION['flash_message'] = 'Question updated successfully';
                    $_SESSION['flash_message_type'] = 'success';
                    
                    // Redirect to the module quiz page
                    redirect('index.php?page=admin-dashboard&action=module-quiz&id=' . $quiz['module_id']);
                } else {
                    $errors['edit_question'] = 'Failed to update question';
                }
            }
            
            // If we get here, there were errors
            $data = [
                'pageTitle' => 'Edit Question',
                'errors' => $errors,
                'module' => $module,
                'quiz' => $quiz,
                'question' => $question,
                'answers' => $answers,
                'correct_answer_index' => $correctAnswerIndex
            ];
            
            view('admin/edit-question', $data);
        } else {
            // Display the edit question page
            $data = [
                'pageTitle' => 'Edit Question',
                'errors' => [],
                'module' => $module,
                'quiz' => $quiz,
                'question' => $question,
                'answers' => $answers,
                'correct_answer_index' => $correctAnswerIndex
            ];
            
            view('admin/edit-question', $data);
        }
    }
    
    /**
     * Delete a question
     */
    public function deleteQuestion() {
        // Check if the question ID is provided
        if (!isset($_GET['id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Question ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the question ID
        $questionId = (int) $_GET['id'];
        
        // Get the question data
        $question = $this->db->selectOne("SELECT * FROM questions WHERE id = ?", [$questionId]);
        
        // Check if the question exists
        if (!$question) {
            // Set flash message
            $_SESSION['flash_message'] = 'Question not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the quiz data
        $quiz = $this->quizModel->getById($question['quiz_id']);
        
        // Delete the question
        $result = $this->quizModel->deleteQuestion($questionId);
        
        if ($result) {
            // Set flash message
            $_SESSION['flash_message'] = 'Question deleted successfully';
            $_SESSION['flash_message_type'] = 'success';
        } else {
            // Set flash message
            $_SESSION['flash_message'] = 'Failed to delete question';
            $_SESSION['flash_message_type'] = 'danger';
        }
        
        // Redirect to the module quiz page
        redirect('index.php?page=admin-dashboard&action=module-quiz&id=' . $quiz['module_id']);
    }
    
    /**
     * Move a question up or down in the order
     */
    public function moveQuestion() {
        // Check if the question ID and direction are provided
        if (!isset($_GET['id']) || !isset($_GET['direction'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Question ID and direction are required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the question ID and direction
        $questionId = (int) $_GET['id'];
        $direction = $_GET['direction'];
        
        // Check if the direction is valid
        if ($direction !== 'up' && $direction !== 'down') {
            // Set flash message
            $_SESSION['flash_message'] = 'Invalid direction';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the question data
        $question = $this->db->selectOne("SELECT * FROM questions WHERE id = ?", [$questionId]);
        
        // Check if the question exists
        if (!$question) {
            // Set flash message
            $_SESSION['flash_message'] = 'Question not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the quiz data
        $quiz = $this->quizModel->getById($question['quiz_id']);
        
        // Move the question
        $result = $this->quizModel->moveQuestion($questionId, $direction);
        
        if ($result) {
            // Set flash message
            $_SESSION['flash_message'] = 'Question moved successfully';
            $_SESSION['flash_message_type'] = 'success';
        } else {
            // Set flash message
            $_SESSION['flash_message'] = 'Failed to move question';
            $_SESSION['flash_message_type'] = 'danger';
        }
        
        // Redirect to the module quiz page
        redirect('index.php?page=admin-dashboard&action=module-quiz&id=' . $quiz['module_id']);
    }
    
    /**
     * Display the module assignments page
     */
    public function moduleAssignments() {
        // Check if the module ID is provided
        if (!isset($_GET['id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the module ID
        $moduleId = (int) $_GET['id'];
        
        // Get the module data
        $module = $this->moduleModel->getById($moduleId);
        
        // Check if the module exists
        if (!$module) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the assigned users
        $assignedUsers = $this->moduleModel->getAssignedUsers($moduleId);
        
        // Get the unassigned users
        $unassignedUsers = $this->moduleModel->getUnassignedUsers($moduleId);
        
        $data = [
            'pageTitle' => 'Module Assignments',
            'module' => $module,
            'assignedUsers' => $assignedUsers,
            'unassignedUsers' => $unassignedUsers
        ];
        
        view('admin/module-assignments', $data);
    }
    
    /**
     * Assign a module to a user
     */
    public function assignModule() {
        // Check if the module ID and user ID are provided
        if (!isset($_GET['module_id']) || !isset($_GET['user_id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module ID and user ID are required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the module ID and user ID
        $moduleId = (int) $_GET['module_id'];
        $userId = (int) $_GET['user_id'];
        
        // Assign the module to the user
        $result = $this->moduleModel->assignToUser($moduleId, $userId);
        
        if ($result) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module assigned successfully';
            $_SESSION['flash_message_type'] = 'success';
        } else {
            // Set flash message
            $_SESSION['flash_message'] = 'Failed to assign module';
            $_SESSION['flash_message_type'] = 'danger';
        }
        
        // Redirect to the module assignments page
        redirect('index.php?page=admin-dashboard&action=module-assignments&id=' . $moduleId);
    }
    
    /**
     * Unassign a module from a user
     */
    public function unassignModule() {
        // Check if the module ID and user ID are provided
        if (!isset($_GET['module_id']) || !isset($_GET['user_id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module ID and user ID are required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=admin-dashboard&action=modules');
        }
        
        // Get the module ID and user ID
        $moduleId = (int) $_GET['module_id'];
        $userId = (int) $_GET['user_id'];
        
        // Unassign the module from the user
        $result = $this->moduleModel->unassignFromUser($moduleId, $userId);
        
        if ($result) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module unassigned successfully';
            $_SESSION['flash_message_type'] = 'success';
        } else {
            // Set flash message
            $_SESSION['flash_message'] = 'Failed to unassign module';
            $_SESSION['flash_message_type'] = 'danger';
        }
        
        // Redirect to the module assignments page
        redirect('index.php?page=admin-dashboard&action=module-assignments&id=' . $moduleId);
    }
    
    /**
     * Display the reports page
     */
    public function reports() {
        // Get all users
        $users = $this->userModel->getAll();
        
        // Get all modules
        $modules = $this->moduleModel->getAll();
        
        // Get all quizzes
        $quizzes = $this->quizModel->getAll();
        
        $data = [
            'pageTitle' => 'Reports',
            'users' => $users,
            'modules' => $modules,
            'quizzes' => $quizzes
        ];
        
        view('admin/reports', $data);
    }
    
    /**
     * Display the user progress report
     */
    public function userProgressReport() {
        // Check if the user ID is provided
        if (!isset($_GET['user_id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'User ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the reports page
            redirect('index.php?page=admin-dashboard&action=reports');
        }
        
        // Get the user ID
        $userId = (int) $_GET['user_id'];
        
        // Get the user data
        $user = $this->userModel->getById($userId);
        
        // Check if the user exists
        if (!$user) {
            // Set flash message
            $_SESSION['flash_message'] = 'User not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the reports page
            redirect('index.php?page=admin-dashboard&action=reports');
        }
        
        // Get the user's assigned modules
        $modules = $this->moduleModel->getByUser($userId);
        
        // Calculate the overall progress
        $overallProgress = calculateOverallProgress($userId);
        
        $data = [
            'pageTitle' => 'User Progress Report',
            'user' => $user,
            'modules' => $modules,
            'overallProgress' => $overallProgress
        ];
        
        view('admin/user-progress-report', $data);
    }
    
    /**
     * Display the module progress report
     */
    public function moduleProgressReport() {
        // Check if the module ID is provided
        if (!isset($_GET['module_id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the reports page
            redirect('index.php?page=admin-dashboard&action=reports');
        }
        
        // Get the module ID
        $moduleId = (int) $_GET['module_id'];
        
        // Get the module data
        $module = $this->moduleModel->getById($moduleId);
        
        // Check if the module exists
        if (!$module) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the reports page
            redirect('index.php?page=admin-dashboard&action=reports');
        }
        
        // Get the assigned users
        $users = $this->moduleModel->getAssignedUsers($moduleId);
        
        // Get the module quiz
        $quiz = $this->quizModel->getByModule($moduleId);
        
        $data = [
            'pageTitle' => 'Module Progress Report',
            'module' => $module,
            'users' => $users,
            'quiz' => $quiz
        ];
        
        view('admin/module-progress-report', $data);
    }
    
    /**
     * Display the quiz results report
     */
    public function quizResultsReport() {
        // Check if the quiz ID is provided
        if (!isset($_GET['quiz_id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Quiz ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the reports page
            redirect('index.php?page=admin-dashboard&action=reports');
        }
        
        // Get the quiz ID
        $quizId = (int) $_GET['quiz_id'];
        
        // Get the quiz data
        $quiz = $this->quizModel->getById($quizId);
        
        // Check if the quiz exists
        if (!$quiz) {
            // Set flash message
            $_SESSION['flash_message'] = 'Quiz not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the reports page
            redirect('index.php?page=admin-dashboard&action=reports');
        }
        
        // Get the module data
        $module = $this->moduleModel->getById($quiz['module_id']);
        
        // Get all quiz results
        $results = $this->db->select("
            SELECT qr.*, u.name as user_name
            FROM quiz_results qr
            JOIN users u ON qr.user_id = u.id
            WHERE qr.quiz_id = ?
            ORDER BY qr.completed_at DESC
        ", [$quizId]);
        
        $data = [
            'pageTitle' => 'Quiz Results Report',
            'quiz' => $quiz,
            'module' => $module,
            'results' => $results
        ];
        
        view('admin/quiz-results-report', $data);
    }
    
    /**
     * Export a report to CSV
     */
    public function exportReport() {
        // Check if the report type is provided
        if (!isset($_GET['type'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Report type is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the reports page
            redirect('index.php?page=admin-dashboard&action=reports');
        }
        
        // Get the report type
        $reportType = $_GET['type'];
        
        // Generate the report based on the type
        switch ($reportType) {
            case 'user_progress':
                // Check if the user ID is provided
                if (!isset($_GET['user_id'])) {
                    // Set flash message
                    $_SESSION['flash_message'] = 'User ID is required';
                    $_SESSION['flash_message_type'] = 'danger';
                    
                    // Redirect to the reports page
                    redirect('index.php?page=admin-dashboard&action=reports');
                }
                
                // Get the user ID
                $userId = (int) $_GET['user_id'];
                
                // Export the user progress report
                $this->exportUserProgressReport($userId);
                break;
                
            case 'module_progress':
                // Check if the module ID is provided
                if (!isset($_GET['module_id'])) {
                    // Set flash message
                    $_SESSION['flash_message'] = 'Module ID is required';
                    $_SESSION['flash_message_type'] = 'danger';
                    
                    // Redirect to the reports page
                    redirect('index.php?page=admin-dashboard&action=reports');
                }
                
                // Get the module ID
                $moduleId = (int) $_GET['module_id'];
                
                // Export the module progress report
                $this->exportModuleProgressReport($moduleId);
                break;
                
            case 'quiz_results':
                // Check if the quiz ID is provided
                if (!isset($_GET['quiz_id'])) {
                    // Set flash message
                    $_SESSION['flash_message'] = 'Quiz ID is required';
                    $_SESSION['flash_message_type'] = 'danger';
                    
                    // Redirect to the reports page
                    redirect('index.php?page=admin-dashboard&action=reports');
                }
                
                // Get the quiz ID
                $quizId = (int) $_GET['quiz_id'];
                
                // Export the quiz results report
                $this->exportQuizResultsReport($quizId);
                break;
                
            default:
                // Set flash message
                $_SESSION['flash_message'] = 'Invalid report type';
                $_SESSION['flash_message_type'] = 'danger';
                
                // Redirect to the reports page
                redirect('index.php?page=admin-dashboard&action=reports');
        }
    }
    
    /**
     * Export the user progress report to CSV
     * 
     * @param int $userId User ID
     */
    private function exportUserProgressReport($userId) {
        // Get the user data
        $user = $this->userModel->getById($userId);
        
        // Check if the user exists
        if (!$user) {
            // Set flash message
            $_SESSION['flash_message'] = 'User not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the reports page
            redirect('index.php?page=admin-dashboard&action=reports');
        }
        
        // Get the user's assigned modules
        $modules = $this->moduleModel->getByUser($userId);
        
        // Set the CSV headers
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="user_progress_report_' . $userId . '.csv"');
        
        // Create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');
        
        // Add the BOM to make Excel happy
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Set the column headers
        fputcsv($output, ['User Progress Report']);
        fputcsv($output, ['User', $user['name']]);
        fputcsv($output, ['Email', $user['email']]);
        fputcsv($output, ['Role', $user['role']]);
        fputcsv($output, ['Overall Progress', calculateOverallProgress($userId) . '%']);
        fputcsv($output, []);
        fputcsv($output, ['Module', 'Status', 'Completion Date']);
        
        // Add the module data
        foreach ($modules as $module) {
            fputcsv($output, [
                $module['title'],
                $module['progress_status'],
                $module['completion_date'] ? formatDate($module['completion_date'], 'Y-m-d') : ''
            ]);
        }
        
        // Close the file pointer
        fclose($output);
        
        // Exit to prevent any additional output
        exit;
    }
    
    /**
     * Export the module progress report to CSV
     * 
     * @param int $moduleId Module ID
     */
    private function exportModuleProgressReport($moduleId) {
        // Get the module data
        $module = $this->moduleModel->getById($moduleId);
        
        // Check if the module exists
        if (!$module) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the reports page
            redirect('index.php?page=admin-dashboard&action=reports');
        }
        
        // Get the assigned users
        $users = $this->moduleModel->getAssignedUsers($moduleId);
        
        // Set the CSV headers
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="module_progress_report_' . $moduleId . '.csv"');
        
        // Create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');
        
        // Add the BOM to make Excel happy
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Set the column headers
        fputcsv($output, ['Module Progress Report']);
        fputcsv($output, ['Module', $module['title']]);
        fputcsv($output, ['Description', $module['description']]);
        fputcsv($output, ['Deadline', $module['deadline'] ? formatDate($module['deadline'], 'Y-m-d') : 'None']);
        fputcsv($output, ['Status', $module['status']]);
        fputcsv($output, []);
        fputcsv($output, ['User', 'Status', 'Completion Date']);
        
        // Add the user data
        foreach ($users as $user) {
            fputcsv($output, [
                $user['name'],
                $user['progress_status'],
                $user['completion_date'] ? formatDate($user['completion_date'], 'Y-m-d') : ''
            ]);
        }
        
        // Close the file pointer
        fclose($output);
        
        // Exit to prevent any additional output
        exit;
    }
    
    /**
     * Export the quiz results report to CSV
     * 
     * @param int $quizId Quiz ID
     */
    private function exportQuizResultsReport($quizId) {
        // Get the quiz data
        $quiz = $this->quizModel->getById($quizId);
        
        // Check if the quiz exists
        if (!$quiz) {
            // Set flash message
            $_SESSION['flash_message'] = 'Quiz not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the reports page
            redirect('index.php?page=admin-dashboard&action=reports');
        }
        
        // Get the module data
        $module = $this->moduleModel->getById($quiz['module_id']);
        
        // Get all quiz results
        $results = $this->db->select("
            SELECT qr.*, u.name as user_name
            FROM quiz_results qr
            JOIN users u ON qr.user_id = u.id
            WHERE qr.quiz_id = ?
            ORDER BY qr.completed_at DESC
        ", [$quizId]);
        
        // Set the CSV headers
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="quiz_results_report_' . $quizId . '.csv"');
        
        // Create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');
        
        // Add the BOM to make Excel happy
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
        // Set the column headers
        fputcsv($output, ['Quiz Results Report']);
        fputcsv($output, ['Quiz', $quiz['title']]);
        fputcsv($output, ['Module', $module['title']]);
        fputcsv($output, ['Passing Threshold', $quiz['passing_threshold'] . '%']);
        fputcsv($output, []);
        fputcsv($output, ['User', 'Score', 'Passed', 'Attempt', 'Completed At']);
        
        // Add the result data
        foreach ($results as $result) {
            fputcsv($output, [
                $result['user_name'],
                $result['score'] . '%',
                $result['passed'] ? 'Yes' : 'No',
                $result['attempt_number'],
                formatDate($result['completed_at'], 'Y-m-d H:i:s')
            ]);
        }
        
        // Close the file pointer
        fclose($output);
        
        // Exit to prevent any additional output
        exit;
    }
    
    /**
     * Get recent quiz results
     * 
     * @param int $limit Number of results to get
     * @return array Array of recent quiz results
     */
    private function getRecentQuizResults($limit = 5) {
        return $this->db->select("
            SELECT qr.*, u.name as user_name, q.title as quiz_title, m.title as module_title
            FROM quiz_results qr
            JOIN users u ON qr.user_id = u.id
            JOIN quizzes q ON qr.quiz_id = q.id
            JOIN modules m ON q.module_id = m.id
            ORDER BY qr.completed_at DESC
            LIMIT ?
        ", [$limit]);
    }
}

