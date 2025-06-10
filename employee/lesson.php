<?php
// Include header
require_once '../includes/header.php';

// Check if user is logged in and is an employee
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../index.php');
    exit;
}

// Include necessary classes
require_once '../classes/Lesson.php';
require_once '../classes/Module.php';

// Initialize classes
$lessonObj = new Lesson();
$moduleObj = new Module();

// Get lesson ID from URL
$lessonId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get lesson details
$lesson = $lessonObj->getLessonById($lessonId);

if (!$lesson) {
    // Lesson not found
    $_SESSION['error'] = 'Lesson not found.';
    header('Location: index.php');
    exit;
}

// Get module details
$module = $moduleObj->getModuleById($lesson['module_id']);

// Check if module is assigned to user
$userModules = $moduleObj->getModulesByUserId($_SESSION['user_id']);
$isAssigned = false;

foreach ($userModules as $userModule) {
    if ($userModule['module_id'] == $lesson['module_id']) {
        $isAssigned = true;
        break;
    }
}

if (!$isAssigned) {
    // Module not assigned to user
    $_SESSION['error'] = 'You do not have access to this lesson.';
    header('Location: index.php');
    exit;
}

// Get all lessons in this module for navigation
$moduleId = $lesson['module_id'];
$allLessons = $lessonObj->getLessonsByModuleId($moduleId);

// Find current lesson index and prev/next lessons
$currentIndex = 0;
$prevLesson = null;
$nextLesson = null;

foreach ($allLessons as $index => $l) {
    if ($l['lesson_id'] == $lessonId) {
        $currentIndex = $index;
        break;
    }
}

if ($currentIndex > 0) {
    $prevLesson = $allLessons[$currentIndex - 1];
}

if ($currentIndex < count($allLessons) - 1) {
    $nextLesson = $allLessons[$currentIndex + 1];
}

// Mark lesson as viewed
$lessonObj->markLessonAsViewed($_SESSION['user_id'], $lessonId);

// Handle lesson completion
if (isset($_POST['complete_lesson'])) {
    $lessonObj->markLessonAsCompleted($_SESSION['user_id'], $lessonId);
    
    // Redirect to next lesson if exists, otherwise to module page
    if ($nextLesson) {
        header('Location: lesson.php?id=' . $nextLesson['lesson_id']);
    } else {
        header('Location: module.php?id=' . $moduleId);
    }
    exit;
}

// Get lesson progress
$lessonProgress = $lessonObj->getLessonProgressByUser($_SESSION['user_id'], $lessonId);
$lessonStatus = $lessonProgress ? $lessonProgress['status'] : 'not_viewed';
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
                    <li class="breadcrumb-item"><a href="module.php?id=<?php echo $moduleId; ?>"><?php echo $module['title']; ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo $lesson['title']; ?></li>
                </ol>
            </nav>
            
            <!-- Lesson Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $lesson['title']; ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <?php if ($lessonStatus === 'completed'): ?>
                        <span class="badge bg-success">Completed</span>
                    <?php elseif ($lessonStatus === 'viewed'): ?>
                        <span class="badge bg-info">In Progress</span>
                    <?php else: ?>
                        <span class="badge bg-light text-dark">Not Started</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Lesson Content -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <?php if ($lesson['content_type'] === 'text'): ?>
                        <div class="lesson-content">
                            <?php echo $lesson['content']; ?>
                        </div>
                    <?php elseif ($lesson['content_type'] === 'video'): ?>
                        <div class="ratio ratio-16x9 mb-4">
                            <iframe src="<?php echo $lesson['content']; ?>" title="<?php echo $lesson['title']; ?>" allowfullscreen></iframe>
                        </div>
                    <?php elseif ($lesson['content_type'] === 'image'): ?>
                        <div class="text-center mb-4">
                            <img src="<?php echo $lesson['content']; ?>" alt="<?php echo $lesson['title']; ?>" class="img-fluid">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Lesson Navigation -->
            <div class="d-flex justify-content-between mb-4">
                <?php if ($prevLesson): ?>
                    <a href="lesson.php?id=<?php echo $prevLesson['lesson_id']; ?>" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left"></i> Previous Lesson
                    </a>
                <?php else: ?>
                    <a href="module.php?id=<?php echo $moduleId; ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Module
                    </a>
                <?php endif; ?>
                
                <?php if ($lessonStatus !== 'completed'): ?>
                    <form method="post">
                        <button type="submit" name="complete_lesson" class="btn btn-success">
                            Mark as Completed <?php echo $nextLesson ? '& Continue' : ''; ?>
                        </button>
                    </form>
                <?php elseif ($nextLesson): ?>
                    <a href="lesson.php?id=<?php echo $nextLesson['lesson_id']; ?>" class="btn btn-primary">
                        Next Lesson <i class="bi bi-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <a href="module.php?id=<?php echo $moduleId; ?>" class="btn btn-primary">
                        Back to Module <i class="bi bi-arrow-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Lesson Progress -->
            <div class="progress mb-4">
                <?php
                $totalLessons = count($allLessons);
                $currentLessonNumber = $currentIndex + 1;
                $progressPercentage = ($currentLessonNumber / $totalLessons) * 100;
                ?>
                <div class="progress-bar" role="progressbar" style="width: <?php echo $progressPercentage; ?>%"
                     aria-valuenow="<?php echo $progressPercentage; ?>" aria-valuemin="0" aria-valuemax="100">
                    Lesson <?php echo $currentLessonNumber; ?> of <?php echo $totalLessons; ?>
                </div>
            </div>
            
            <!-- Lesson Navigation Cards -->
            <h3 class="h5 mb-3">Module Lessons</h3>
            <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
                <?php foreach ($allLessons as $index => $l): 
                    $lProgress = $lessonObj->getLessonProgressByUser($_SESSION['user_id'], $l['lesson_id']);
                    $lStatus = $lProgress ? $lProgress['status'] : 'not_viewed';
                ?>
                    <div class="col">
                        <div class="card h-100 <?php echo ($l['lesson_id'] == $lessonId) ? 'border-primary' : ''; ?>">
                            <div class="card-body">
                                <h5 class="card-title">Lesson <?php echo $index + 1; ?></h5>
                                <p class="card-text"><?php echo $l['title']; ?></p>
                                <?php if ($lStatus === 'completed'): ?>
                                    <span class="badge bg-success">Completed</span>
                                <?php elseif ($lStatus === 'viewed'): ?>
                                    <span class="badge bg-info">In Progress</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark">Not Started</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="lesson.php?id=<?php echo $l['lesson_id']; ?>" class="btn btn-sm <?php echo ($l['lesson_id'] == $lessonId) ? 'btn-primary' : 'btn-outline-primary'; ?> w-100">
                                    <?php echo ($l['lesson_id'] == $lessonId) ? 'Current Lesson' : 'View Lesson'; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</div>

<?php
// Include footer
require_once '../includes/footer.php';
?>
