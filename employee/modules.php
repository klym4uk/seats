<?php
// Include header
require_once '../includes/header.php';

// Check if user is logged in and is an employee
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../index.php');
    exit;
}

// Include necessary classes
require_once '../classes/Module.php';
require_once '../classes/Lesson.php';
require_once '../classes/Quiz.php';

// Initialize classes
$moduleObj = new Module();
$lessonObj = new Lesson();
$quizObj = new Quiz();

// Get module ID from URL
$moduleId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if module exists and is assigned to user
$module = $moduleObj->getModuleById($moduleId);
$userModules = $moduleObj->getModulesByUserId($_SESSION['user_id']);
$isAssigned = false;

foreach ($userModules as $userModule) {
    if ($userModule['module_id'] == $moduleId) {
        $isAssigned = true;
        $moduleStatus = $userModule['status'];
        break;
    }
}

if (!$module || !$isAssigned) {
    // Module not found or not assigned to user
    $_SESSION['error'] = 'Module not found or not assigned to you.';
    header('Location: index.php');
    exit;
}

// Get lessons for this module
$lessons = $lessonObj->getLessonsByModuleId($moduleId);

// Get quiz for this module
$quiz = $quizObj->getQuizByModuleId($moduleId);

// Check if all lessons are completed
$allLessonsCompleted = $lessonObj->areAllLessonsCompletedInModule($_SESSION['user_id'], $moduleId);

// Get user's latest quiz result if exists
$quizResult = null;
if ($quiz) {
    $quizResult = $quizObj->getUserLatestQuizResult($_SESSION['user_id'], $quiz['quiz_id']);
}

// Update module status to in_progress if not already started
if ($moduleStatus === 'not_started') {
    $moduleObj->updateModuleProgress($_SESSION['user_id'], $moduleId, 'in_progress');
    $moduleStatus = 'in_progress';
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
                    <li class="breadcrumb-item active" aria-current="page"><?php echo $module['title']; ?></li>
                </ol>
            </nav>
            
            <!-- Module Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $module['title']; ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <?php if ($moduleStatus === 'completed'): ?>
                        <span class="badge bg-success">Completed</span>
                    <?php elseif ($moduleStatus === 'in_progress'): ?>
                        <span class="badge bg-info">In Progress</span>
                    <?php else: ?>
                        <span class="badge bg-warning">Not Started</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Module Description -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Description</h5>
                    <p class="card-text"><?php echo $module['description']; ?></p>
                    <?php if ($module['deadline']): ?>
                        <p class="card-text"><small class="text-muted">Deadline: <?php echo date('F d, Y', strtotime($module['deadline'])); ?></small></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Lessons -->
            <h3 class="h4 mb-3">Lessons</h3>
            
            <?php if (count($lessons) > 0): ?>
                <div class="list-group mb-4">
                    <?php foreach ($lessons as $index => $lesson): 
                        // Get lesson progress
                        $lessonProgress = $lessonObj->getLessonProgressByUser($_SESSION['user_id'], $lesson['lesson_id']);
                        $lessonStatus = $lessonProgress ? $lessonProgress['status'] : 'not_viewed';
                    ?>
                        <a href="lesson.php?id=<?php echo $lesson['lesson_id']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">Lesson <?php echo $index + 1; ?>: <?php echo $lesson['title']; ?></h5>
                                <small class="text-muted">
                                    <?php if ($lesson['content_type'] === 'text'): ?>
                                        <i class="bi bi-file-text"></i> Text Lesson
                                    <?php elseif ($lesson['content_type'] === 'video'): ?>
                                        <i class="bi bi-camera-video"></i> Video Lesson
                                    <?php else: ?>
                                        <i class="bi bi-image"></i> Image Lesson
                                    <?php endif; ?>
                                </small>
                            </div>
                            <?php if ($lessonStatus === 'completed'): ?>
                                <span class="badge bg-success rounded-pill">Completed</span>
                            <?php elseif ($lessonStatus === 'viewed'): ?>
                                <span class="badge bg-info rounded-pill">In Progress</span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark rounded-pill">Not Started</span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    No lessons found for this module.
                </div>
            <?php endif; ?>
            
            <!-- Quiz -->
            <h3 class="h4 mb-3">Assessment</h3>
            
            <?php if ($quiz): ?>
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $quiz['title']; ?></h5>
                        <p class="card-text"><?php echo $quiz['description']; ?></p>
                        <p class="card-text">
                            <small class="text-muted">
                                Passing threshold: <?php echo $quiz['passing_threshold']; ?>%
                            </small>
                        </p>
                        
                        <?php if ($quizResult): ?>
                            <div class="alert <?php echo $quizResult['passed'] ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                                <h5 class="alert-heading">
                                    <?php echo $quizResult['passed'] ? 'Quiz Passed!' : 'Quiz Failed'; ?>
                                </h5>
                                <p>Your score: <?php echo $quizResult['score']; ?>%</p>
                                <p>Attempt: <?php echo $quizResult['attempt_number']; ?></p>
                                <p>Completed on: <?php echo date('F d, Y H:i', strtotime($quizResult['completed_at'])); ?></p>
                            </div>
                            
                            <?php if (!$quizResult['passed']): ?>
                                <?php if ($quizObj->canUserTakeQuiz($_SESSION['user_id'], $quiz['quiz_id'])): ?>
                                    <a href="quiz.php?id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-primary">Retake Quiz</a>
                                <?php else: ?>
                                    <div class="alert alert-warning" role="alert">
                                        You can retake this quiz after the cooldown period (<?php echo $quiz['cooldown_period']; ?> hours).
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="quiz_result.php?id=<?php echo $quizResult['result_id']; ?>" class="btn btn-info">View Results</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if ($allLessonsCompleted): ?>
                                <a href="quiz.php?id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-primary">Take Quiz</a>
                            <?php else: ?>
                                <div class="alert alert-warning" role="alert">
                                    You need to complete all lessons before taking the quiz.
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    No quiz found for this module.
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php
// Include footer
require_once '../includes/footer.php';
?>
