<?php
/**
 * Employee Controller
 */
class EmployeeController {
    private $userModel;
    private $moduleModel;
    private $lessonModel;
    private $quizModel;
    
    public function __construct() {
        // Check if the user is logged in
        if (!isset($_SESSION['user_id'])) {
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
     * Display the employee dashboard
     */
    public function dashboard() {
        // Get the user's assigned modules
        $modules = $this->moduleModel->getByUser($_SESSION['user_id']);
        
        // Calculate the overall progress
        $overallProgress = calculateOverallProgress($_SESSION['user_id']);
        
        // Get the user's recent quiz results
        $recentResults = $this->getRecentQuizResults($_SESSION['user_id'], 5);
        
        $data = [
            'pageTitle' => 'Employee Dashboard',
            'modules' => $modules,
            'overallProgress' => $overallProgress,
            'recentResults' => $recentResults
        ];
        
        view('employee/dashboard', $data);
    }
    
    /**
     * Display the modules page
     */
    public function modules() {
        // Get the user's assigned modules
        $modules = $this->moduleModel->getByUser($_SESSION['user_id']);
        
        $data = [
            'pageTitle' => 'My Modules',
            'modules' => $modules
        ];
        
        view('employee/modules', $data);
    }
    
    /**
     * Display the module details page
     */
    public function moduleDetails() {
        // Check if the module ID is provided
        if (!isset($_GET['id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Module ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=employee-dashboard&action=modules');
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
            redirect('index.php?page=employee-dashboard&action=modules');
        }
        
        // Check if the user is assigned to this module
        $userModules = $this->moduleModel->getByUser($_SESSION['user_id']);
        $isAssigned = false;
        
        foreach ($userModules as $userModule) {
            if ($userModule['id'] == $moduleId) {
                $isAssigned = true;
                $module['progress_status'] = $userModule['progress_status'];
                $module['completion_date'] = $userModule['completion_date'];
                break;
            }
        }
        
        if (!$isAssigned) {
            // Set flash message
            $_SESSION['flash_message'] = 'You are not assigned to this module';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=employee-dashboard&action=modules');
        }
        
        // Get the module lessons
        $lessons = $this->lessonModel->getByModule($moduleId);
        
        // Get the user's progress on each lesson
        foreach ($lessons as &$lesson) {
            $progress = $this->lessonModel->getUserProgress($lesson['id'], $_SESSION['user_id']);
            $lesson['progress_status'] = $progress ? $progress['status'] : 'not_started';
            $lesson['completion_date'] = $progress ? $progress['completion_date'] : null;
        }
        
        // Get the module quiz
        $quiz = $this->quizModel->getByModule($moduleId);
        
        // Check if the user can attempt the quiz
        $canAttemptQuiz = false;
        $quizMessage = '';
        
        if ($quiz) {
            $canAttemptResult = $this->quizModel->canAttempt($quiz['id'], $_SESSION['user_id']);
            $canAttemptQuiz = $canAttemptResult['can_attempt'];
            $quizMessage = $canAttemptResult['message'];
        }
        
        $data = [
            'pageTitle' => 'Module Details',
            'module' => $module,
            'lessons' => $lessons,
            'quiz' => $quiz,
            'canAttemptQuiz' => $canAttemptQuiz,
            'quizMessage' => $quizMessage
        ];
        
        view('employee/module-details', $data);
    }
    
    /**
     * Display the lesson page
     */
    public function lesson() {
        // Check if the lesson ID is provided
        if (!isset($_GET['id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Lesson ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=employee-dashboard&action=modules');
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
            redirect('index.php?page=employee-dashboard&action=modules');
        }
        
        // Get the module data
        $module = $this->moduleModel->getById($lesson['module_id']);
        
        // Check if the user is assigned to this module
        $userModules = $this->moduleModel->getByUser($_SESSION['user_id']);
        $isAssigned = false;
        
        foreach ($userModules as $userModule) {
            if ($userModule['id'] == $lesson['module_id']) {
                $isAssigned = true;
                break;
            }
        }
        
        if (!$isAssigned) {
            // Set flash message
            $_SESSION['flash_message'] = 'You are not assigned to this module';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=employee-dashboard&action=modules');
        }
        
        // Get the user's progress on this lesson
        $progress = $this->lessonModel->getUserProgress($lessonId, $_SESSION['user_id']);
        $lesson['progress_status'] = $progress ? $progress['status'] : 'not_started';
        $lesson['completion_date'] = $progress ? $progress['completion_date'] : null;
        
        // Get all lessons in the module to show navigation
        $allLessons = $this->lessonModel->getByModule($lesson['module_id']);
        
        // Find the previous and next lessons
        $prevLesson = null;
        $nextLesson = null;
        
        foreach ($allLessons as $index => $l) {
            if ($l['id'] == $lessonId) {
                if ($index > 0) {
                    $prevLesson = $allLessons[$index - 1];
                }
                
                if ($index < count($allLessons) - 1) {
                    $nextLesson = $allLessons[$index + 1];
                }
                
                break;
            }
        }
        
        // Mark the lesson as in progress if it's not already completed
        if ($lesson['progress_status'] !== 'completed') {
            $this->lessonModel->updateUserProgress($lessonId, $_SESSION['user_id'], 'in_progress');
            $lesson['progress_status'] = 'in_progress';
        }
        
        $data = [
            'pageTitle' => $lesson['title'],
            'module' => $module,
            'lesson' => $lesson,
            'prevLesson' => $prevLesson,
            'nextLesson' => $nextLesson
        ];
        
        view('employee/lesson', $data);
    }
    
    /**
     * Mark a lesson as completed
     */
    public function completeLesson() {
        // Check if the lesson ID is provided
        if (!isset($_GET['id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Lesson ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=employee-dashboard&action=modules');
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
            redirect('index.php?page=employee-dashboard&action=modules');
        }
        
        // Mark the lesson as completed
        $result = $this->lessonModel->updateUserProgress($lessonId, $_SESSION['user_id'], 'completed');
        
        if ($result) {
            // Set flash message
            $_SESSION['flash_message'] = 'Lesson marked as completed';
            $_SESSION['flash_message_type'] = 'success';
            
            // Check if all lessons in the module are completed
            if ($this->lessonModel->allLessonsCompleted($lesson['module_id'], $_SESSION['user_id'])) {
                // Update the module progress
                $this->moduleModel->updateUserProgress($lesson['module_id'], $_SESSION['user_id'], 'lessons_completed');
                
                // Check if there's a quiz for this module
                $quiz = $this->quizModel->getByModule($lesson['module_id']);
                
                if (!$quiz) {
                    // If there's no quiz, mark the module as completed
                    $this->moduleModel->updateUserProgress($lesson['module_id'], $_SESSION['user_id'], 'completed');
                }
            }
        } else {
            // Set flash message
            $_SESSION['flash_message'] = 'Failed to mark lesson as completed';
            $_SESSION['flash_message_type'] = 'danger';
        }
        
        // Redirect to the module details page
        redirect('index.php?page=employee-dashboard&action=module-details&id=' . $lesson['module_id']);
    }
    
    /**
     * Display the quiz page
     */
    public function quiz() {
        // Check if the quiz ID is provided
        if (!isset($_GET['id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Quiz ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=employee-dashboard&action=modules');
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
            redirect('index.php?page=employee-dashboard&action=modules');
        }
        
        // Get the module data
        $module = $this->moduleModel->getById($quiz['module_id']);
        
        // Check if the user is assigned to this module
        $userModules = $this->moduleModel->getByUser($_SESSION['user_id']);
        $isAssigned = false;
        
        foreach ($userModules as $userModule) {
            if ($userModule['id'] == $quiz['module_id']) {
                $isAssigned = true;
                break;
            }
        }
        
        if (!$isAssigned) {
            // Set flash message
            $_SESSION['flash_message'] = 'You are not assigned to this module';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=employee-dashboard&action=modules');
        }
        
        // Check if the user can attempt the quiz
        $canAttemptResult = $this->quizModel->canAttempt($quizId, $_SESSION['user_id']);
        
        if (!$canAttemptResult['can_attempt']) {
            // Set flash message
            $_SESSION['flash_message'] = $canAttemptResult['message'];
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the module details page
            redirect('index.php?page=employee-dashboard&action=module-details&id=' . $quiz['module_id']);
        }
        
        // Get the quiz questions
        $questions = $this->quizModel->getQuestions($quizId);
        
        // Get the answers for each question
        foreach ($questions as &$question) {
            $question['answers'] = $this->quizModel->getAnswers($question['id']);
            
            // Shuffle the answers
            shuffle($question['answers']);
        }
        
        $data = [
            'pageTitle' => $quiz['title'],
            'module' => $module,
            'quiz' => $quiz,
            'questions' => $questions
        ];
        
        view('employee/quiz', $data);
    }
    
    /**
     * Submit a quiz
     */
    public function submitQuiz() {
        // Check if the quiz ID is provided
        if (!isset($_POST['quiz_id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Quiz ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=employee-dashboard&action=modules');
        }
        
        // Get the quiz ID
        $quizId = (int) $_POST['quiz_id'];
        
        // Get the quiz data
        $quiz = $this->quizModel->getById($quizId);
        
        // Check if the quiz exists
        if (!$quiz) {
            // Set flash message
            $_SESSION['flash_message'] = 'Quiz not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=employee-dashboard&action=modules');
        }
        
        // Check if the user can attempt the quiz
        $canAttemptResult = $this->quizModel->canAttempt($quizId, $_SESSION['user_id']);
        
        if (!$canAttemptResult['can_attempt']) {
            // Set flash message
            $_SESSION['flash_message'] = $canAttemptResult['message'];
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the module details page
            redirect('index.php?page=employee-dashboard&action=module-details&id=' . $quiz['module_id']);
        }
        
        // Get the user's answers
        $answers = [];
        
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'question_') === 0) {
                $questionId = (int) substr($key, 9);
                $answers[$questionId] = (int) $value;
            }
        }
        
        // Submit the quiz
        $result = $this->quizModel->submitAttempt($quizId, $_SESSION['user_id'], $answers);
        
        if (isset($result['error'])) {
            // Set flash message
            $_SESSION['flash_message'] = $result['error'];
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the module details page
            redirect('index.php?page=employee-dashboard&action=module-details&id=' . $quiz['module_id']);
        }
        
        // Store the result in the session for the results page
        $_SESSION['quiz_result'] = $result;
        
        // Redirect to the quiz results page
        redirect('index.php?page=employee-dashboard&action=quiz-results&id=' . $result['result_id']);
    }
    
    /**
     * Display the quiz results page
     */
    public function quizResults() {
        // Check if the result ID is provided
        if (!isset($_GET['id'])) {
            // Set flash message
            $_SESSION['flash_message'] = 'Result ID is required';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=employee-dashboard&action=modules');
        }
        
        // Get the result ID
        $resultId = (int) $_GET['id'];
        
        // Get the result data
        $result = $this->quizModel->getResult($resultId);
        
        // Check if the result exists and belongs to the user
        if (!$result || $result['user_id'] != $_SESSION['user_id']) {
            // Set flash message
            $_SESSION['flash_message'] = 'Result not found';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the modules page
            redirect('index.php?page=employee-dashboard&action=modules');
        }
        
        // Get the quiz data
        $quiz = $this->quizModel->getById($result['quiz_id']);
        
        // Get the module data
        $module = $this->moduleModel->getById($quiz['module_id']);
        
        // Get the user's answers
        $userAnswers = $this->quizModel->getUserAnswers($resultId);
        
        // Group the answers by question
        $questions = [];
        
        foreach ($userAnswers as $answer) {
            if (!isset($questions[$answer['question_id']])) {
                $questions[$answer['question_id']] = [
                    'id' => $answer['question_id'],
                    'question_text' => $answer['question_text'],
                    'user_answer' => [
                        'id' => $answer['answer_id'],
                        'text' => $answer['answer_text'],
                        'is_correct' => $answer['is_correct']
                    ]
                ];
            }
        }
        
        // Get all possible answers for each question
        foreach ($questions as &$question) {
            $question['all_answers'] = $this->quizModel->getAnswers($question['id']);
        }
        
        // If the result was just submitted, get it from the session
        $quizResult = isset($_SESSION['quiz_result']) ? $_SESSION['quiz_result'] : [
            'score' => $result['score'],
            'passed' => $result['passed'],
            'attempt_number' => $result['attempt_number']
        ];
        
        // Clear the session variable
        unset($_SESSION['quiz_result']);
        
        $data = [
            'pageTitle' => 'Quiz Results',
            'module' => $module,
            'quiz' => $quiz,
            'result' => $result,
            'quizResult' => $quizResult,
            'questions' => $questions
        ];
        
        view('employee/quiz-results', $data);
    }
    
    /**
     * Display the progress page
     */
    public function progress() {
        // Get the user's assigned modules
        $modules = $this->moduleModel->getByUser($_SESSION['user_id']);
        
        // Calculate the overall progress
        $overallProgress = calculateOverallProgress($_SESSION['user_id']);
        
        // Get the user's quiz results
        $quizResults = $this->getUserQuizResults($_SESSION['user_id']);
        
        $data = [
            'pageTitle' => 'My Progress',
            'modules' => $modules,
            'overallProgress' => $overallProgress,
            'quizResults' => $quizResults
        ];
        
        view('employee/progress', $data);
    }
    
    /**
     * Get a user's recent quiz results
     * 
     * @param int $userId User ID
     * @param int $limit Number of results to get
     * @return array Array of recent quiz results
     */
    private function getRecentQuizResults($userId, $limit = 5) {
        return $this->db->select("
            SELECT qr.*, q.title as quiz_title, m.title as module_title
            FROM quiz_results qr
            JOIN quizzes q ON qr.quiz_id = q.id
            JOIN modules m ON q.module_id = m.id
            WHERE qr.user_id = ?
            ORDER BY qr.completed_at DESC
            LIMIT ?
        ", [$userId, $limit]);
    }
    
    /**
     * Get all of a user's quiz results
     * 
     * @param int $userId User ID
     * @return array Array of quiz results
     */
    private function getUserQuizResults($userId) {
        return $this->db->select("
            SELECT qr.*, q.title as quiz_title, m.title as module_title
            FROM quiz_results qr
            JOIN quizzes q ON qr.quiz_id = q.id
            JOIN modules m ON q.module_id = m.id
            WHERE qr.user_id = ?
            ORDER BY qr.completed_at DESC
        ", [$userId]);
    }
}

