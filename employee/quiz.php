<?php
// Include header
require_once '../includes/header.php';

// Check if user is logged in and is an employee
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../index.php');
    exit;
}

// Include necessary classes
require_once '../classes/Quiz.php';
require_once '../classes/Module.php';
require_once '../classes/Lesson.php';

// Initialize classes
$quizObj = new Quiz();
$moduleObj = new Module();
$lessonObj = new Lesson();

// Get quiz ID from URL
$quizId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get quiz details
$quiz = $quizObj->getQuizById($quizId);

if (!$quiz) {
    // Quiz not found
    $_SESSION['error'] = 'Quiz not found.';
    header('Location: index.php');
    exit;
}

// Get module details
$module = $moduleObj->getModuleById($quiz['module_id']);

// Check if module is assigned to user
$userModules = $moduleObj->getModulesByUserId($_SESSION['user_id']);
$isAssigned = false;

foreach ($userModules as $userModule) {
    if ($userModule['module_id'] == $quiz['module_id']) {
        $isAssigned = true;
        break;
    }
}

if (!$isAssigned) {
    // Module not assigned to user
    $_SESSION['error'] = 'You do not have access to this quiz.';
    header('Location: index.php');
    exit;
}

// Check if all lessons are completed
$allLessonsCompleted = $lessonObj->areAllLessonsCompletedInModule($_SESSION['user_id'], $quiz['module_id']);

if (!$allLessonsCompleted) {
    // Not all lessons completed
    $_SESSION['error'] = 'You need to complete all lessons before taking the quiz.';
    header('Location: module.php?id=' . $quiz['module_id']);
    exit;
}

// Check if user can take quiz (cooldown period)
$canTakeQuiz = $quizObj->canUserTakeQuiz($_SESSION['user_id'], $quizId);

if (!$canTakeQuiz) {
    // Cannot take quiz yet
    $_SESSION['error'] = 'You cannot take this quiz yet due to cooldown period.';
    header('Location: module.php?id=' . $quiz['module_id']);
    exit;
}

// Get questions for this quiz
$questions = $quizObj->getQuestionsByQuizId($quizId);

// Process quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    // Get start time from session
    $startTime = isset($_SESSION['quiz_start_time']) ? $_SESSION['quiz_start_time'] : time();
    
    // Calculate completion time in seconds
    $completionTime = time() - $startTime;
    
    // Get answers from form
    $answers = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'question_') === 0) {
            $questionId = substr($key, 9); // Remove 'question_' prefix
            $answers[$questionId] = $value;
        }
    }
    
    // Submit quiz
    $result = $quizObj->submitQuiz($_SESSION['user_id'], $quizId, $answers, $completionTime);
    
    if ($result) {
        // Clear quiz start time
        unset($_SESSION['quiz_start_time']);
        
        // Redirect to results page
        header('Location: quiz_result.php?id=' . $result['result_id']);
        exit;
    } else {
        $_SESSION['error'] = 'An error occurred while submitting the quiz.';
    }
}

// Set quiz start time in session
if (!isset($_SESSION['quiz_start_time'])) {
    $_SESSION['quiz_start_time'] = time();
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/employee_sidebar.php'; ?>
        
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="module.php?id=<?php echo $quiz['module_id']; ?>"><?php echo $module['title']; ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo $quiz['title']; ?></li>
                </ol>
            </nav>
            
            <!-- Quiz Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $quiz['title']; ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div id="quiz-timer" class="badge bg-primary p-2">
                        Time: 00:00
                    </div>
                </div>
            </div>
            
            <!-- Quiz Description -->
            <div class="alert alert-info" role="alert">
                <h4 class="alert-heading">Quiz Instructions</h4>
                <p><?php echo $quiz['description']; ?></p>
                <hr>
                <p class="mb-0">
                    <strong>Passing threshold:</strong> <?php echo $quiz['passing_threshold']; ?>%<br>
                    <strong>Questions:</strong> <?php echo count($questions); ?>
                </p>
            </div>
            
            <!-- Quiz Form -->
            <form method="post" id="quiz-form">
                <?php if (count($questions) > 0): ?>
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">Question <?php echo $index + 1; ?></h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?php echo $question['question_text']; ?></p>
                                
                                <?php 
                                // Get answers for this question
                                $answers = $quizObj->getAnswersByQuestionId($question['question_id']);
                                
                                if (count($answers) > 0):
                                ?>
                                    <div class="list-group">
                                        <?php foreach ($answers as $answer): ?>
                                            <label class="list-group-item">
                                                <input class="form-check-input me-1" type="radio" name="question_<?php echo $question['question_id']; ?>" value="<?php echo $answer['answer_id']; ?>" required>
                                                <?php echo $answer['answer_text']; ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning" role="alert">
                                        No answer options found for this question.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
                        <a href="module.php?id=<?php echo $quiz['module_id']; ?>" class="btn btn-secondary me-md-2">Cancel</a>
                        <button type="submit" name="submit_quiz" class="btn btn-primary">Submit Quiz</button>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning" role="alert">
                        No questions found for this quiz.
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
                        <a href="module.php?id=<?php echo $quiz['module_id']; ?>" class="btn btn-primary">Back to Module</a>
                    </div>
                <?php endif; ?>
            </form>
        </main>
    </div>
</div>

<!-- Quiz Timer Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get start time from session
        const startTime = <?php echo $_SESSION['quiz_start_time']; ?> * 1000; // Convert to milliseconds
        const timerElement = document.getElementById('quiz-timer');
        
        function updateTimer() {
            const currentTime = new Date().getTime();
            const elapsedTime = currentTime - startTime;
            
            // Calculate minutes and seconds
            const minutes = Math.floor(elapsedTime / 60000);
            const seconds = Math.floor((elapsedTime % 60000) / 1000);
            
            // Format time
            const formattedTime = `Time: ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            // Update timer element
            timerElement.textContent = formattedTime;
        }
        
        // Update timer every second
        setInterval(updateTimer, 1000);
        
        // Initial update
        updateTimer();
    });
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>
