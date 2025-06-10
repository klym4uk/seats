<?php
// Include configuration
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Module.php';
require_once '../classes/Lesson.php';
require_once '../classes/Quiz.php';

// Helper function to convert YouTube URLs to embed URLs
function convertToEmbedUrl($url) {
    $embedUrl = $url; // Default to original URL
    if (strpos($url, 'youtube.com/watch?v=') !== false) {
        parse_str(parse_url($url, PHP_URL_QUERY), $queryParams);
        if (!empty($queryParams['v'])) {
            $embedUrl = 'https://www.youtube.com/embed/' . $queryParams['v'];
        }
    } elseif (strpos($url, 'youtu.be/') !== false) {
        $path = parse_url($url, PHP_URL_PATH);
        $videoId = ltrim($path, '/');
        if (!empty($videoId)) {
            $embedUrl = 'https://www.youtube.com/embed/' . $videoId;
        }
    } elseif (strpos($url, 'youtube.com/live/') !== false) {
        $path = parse_url($url, PHP_URL_PATH); // e.g., /live/VIDEO_ID
        $parts = explode('/', $path);
        $videoId = end($parts);
        if (!empty($videoId)) {
            $embedUrl = 'https://www.youtube.com/embed/' . $videoId;
        }
    }
    // Optionally, add checks for existing embed URLs to prevent double-embedding
    if (strpos($url, 'youtube.com/embed/') !== false) {
        return $url; // Already an embed URL
    }
    return $embedUrl;
}

// Set page title
$pageTitle = 'Module Details';

// Include header
include_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../employee/index.php');
    exit;
}

// Check if module ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'Module ID is required.';
    header('Location: modules.php');
    exit;
}

$moduleId = $_GET['id'];

// Initialize classes
$moduleObj = new Module();
$lessonObj = new Lesson();
$quizObj = new Quiz();

// Get module details
$module = $moduleObj->getModuleById($moduleId);

if (!$module) {
    $_SESSION['error'] = 'Module not found.';
    header('Location: modules.php');
    exit;
}

// Get lessons for this module
$lessons = $lessonObj->getLessonsByModuleId($moduleId);

// Get quiz for this module
$quiz = $quizObj->getQuizByModuleId($moduleId);

// Process form submissions
$message = '';
$error = '';

// Handle lesson actions (add, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add or update lesson
    if (isset($_POST['save_lesson'])) {
        $lessonData = [
            'module_id' => $moduleId,
            'title' => $_POST['title'],
            'content' => $_POST['content'],
            'content_type' => $_POST['content_type'],
            'order_number' => $_POST['order_number']
        ];

        // If content type is video, convert URL to embed URL
        if (isset($lessonData['content_type']) && $lessonData['content_type'] === 'video' && isset($lessonData['content'])) {
            $lessonData['content'] = convertToEmbedUrl($lessonData['content']);
        }
        
        // Update existing lesson
        if (!empty($_POST['lesson_id'])) {
            $lessonData['lesson_id'] = $_POST['lesson_id'];
            if ($lessonObj->updateLesson($lessonData)) {
                $message = 'Lesson updated successfully.';
            } else {
                $error = 'Failed to update lesson.';
            }
        } 
        // Create new lesson
        else {
            if ($lessonObj->createLesson($lessonData)) {
                $message = 'Lesson created successfully.';
            } else {
                $error = 'Failed to create lesson.';
            }
        }
        
        // Refresh lessons list
        $lessons = $lessonObj->getLessonsByModuleId($moduleId);
    }
    
    // Delete lesson
    if (isset($_POST['delete_lesson'])) {
        $lessonId = $_POST['lesson_id'];
        if ($lessonObj->deleteLesson($lessonId)) {
            $message = 'Lesson deleted successfully.';
            // Refresh lessons list
            $lessons = $lessonObj->getLessonsByModuleId($moduleId);
        } else {
            $error = 'Failed to delete lesson.';
        }
    }
    
    // Add or update quiz
    if (isset($_POST['save_quiz'])) {
        $quizData = [
            'module_id' => $moduleId,
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'passing_threshold' => $_POST['passing_threshold'],
            'cooldown_period' => $_POST['cooldown_period']
        ];
        
        // Update existing quiz
        if (!empty($_POST['quiz_id'])) {
            $quizData['quiz_id'] = $_POST['quiz_id'];
            if ($quizObj->updateQuiz($quizData)) {
                $message = 'Quiz updated successfully.';
            } else {
                $error = 'Failed to update quiz.';
            }
        } 
        // Create new quiz
        else {
            $quizId = $quizObj->createQuiz($quizData);
            if ($quizId) {
                $message = 'Quiz created successfully.';
                // Redirect to quiz detail page
                header("Location: quiz_detail.php?id=$quizId");
                exit;
            } else {
                $error = 'Failed to create quiz.';
            }
        }
        
        // Refresh quiz
        $quiz = $quizObj->getQuizByModuleId($moduleId);
    }
    
    // Delete quiz
    if (isset($_POST['delete_quiz'])) {
        $quizId = $_POST['quiz_id'];
        if ($quizObj->deleteQuiz($quizId)) {
            $message = 'Quiz deleted successfully.';
            // Refresh quiz
            $quiz = $quizObj->getQuizByModuleId($moduleId);
        } else {
            $error = 'Failed to delete quiz.';
        }
    }
}

// Get lesson for editing
$editLesson = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit_lesson' && isset($_GET['lesson_id'])) {
    $editLesson = $lessonObj->getLessonById($_GET['lesson_id']);
}
?>

<!-- Navigation -->
<?php include '../includes/admin_nav.php'; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="modules.php">Modules</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($module['title']); ?></li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Module: <?php echo htmlspecialchars($module['title']); ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="modules.php" class="btn btn-sm btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Back to Modules
                    </a>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addLessonModal">
                        <i class="bi bi-plus-circle"></i> Add Lesson
                    </button>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Module Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Module Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Title:</strong> <?php echo htmlspecialchars($module['title']); ?></p>
                            <p><strong>Created By:</strong> <?php echo htmlspecialchars($module['creator_name']); ?></p>
                            <p><strong>Created At:</strong> <?php echo date('M d, Y H:i', strtotime($module['created_at'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> 
                                <?php if ($module['status'] == 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </p>
                            <p><strong>Deadline:</strong> <?php echo $module['deadline'] ? date('M d, Y', strtotime($module['deadline'])) : 'No deadline'; ?></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <p><strong>Description:</strong></p>
                            <p><?php echo nl2br(htmlspecialchars($module['description'])); ?></p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModuleModal">
                                <i class="bi bi-pencil"></i> Edit Module
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Lessons List -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Lessons</h6>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addLessonModal">
                        <i class="bi bi-plus-circle"></i> Add Lesson
                    </button>
                </div>
                <div class="card-body">
                    <?php if (count($lessons) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Title</th>
                                        <th>Content Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lessons as $lesson): ?>
                                        <tr>
                                            <td><?php echo $lesson['order_number']; ?></td>
                                            <td><?php echo htmlspecialchars($lesson['title']); ?></td>
                                            <td><?php echo ucfirst($lesson['content_type']); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewLessonModal<?php echo $lesson['lesson_id']; ?>">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editLessonModal<?php echo $lesson['lesson_id']; ?>">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteLessonModal<?php echo $lesson['lesson_id']; ?>">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                                
                                                <!-- View Lesson Modal -->
                                                <div class="modal fade" id="viewLessonModal<?php echo $lesson['lesson_id']; ?>" tabindex="-1" aria-labelledby="viewLessonModalLabel<?php echo $lesson['lesson_id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="viewLessonModalLabel<?php echo $lesson['lesson_id']; ?>"><?php echo htmlspecialchars($lesson['title']); ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p><strong>Content Type:</strong> <?php echo ucfirst($lesson['content_type']); ?></p>
                                                                <p><strong>Order:</strong> <?php echo $lesson['order_number']; ?></p>
                                                                <hr>
                                                                <div class="lesson-content">
                                                                    <?php if ($lesson['content_type'] === 'text'): ?>
                                                                        <?php echo nl2br(htmlspecialchars($lesson['content'])); ?>
                                                                    <?php elseif ($lesson['content_type'] === 'video'): ?>
                                                                        <div class="ratio ratio-16x9">
                                                                            <iframe src="<?php echo htmlspecialchars($lesson['content']); ?>" title="<?php echo htmlspecialchars($lesson['title']); ?>" allowfullscreen></iframe>
                                                                        </div>
                                                                    <?php elseif ($lesson['content_type'] === 'image'): ?>
                                                                        <img src="<?php echo htmlspecialchars($lesson['content']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($lesson['title']); ?>">
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Edit Lesson Modal -->
                                                <div class="modal fade" id="editLessonModal<?php echo $lesson['lesson_id']; ?>" tabindex="-1" aria-labelledby="editLessonModalLabel<?php echo $lesson['lesson_id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="editLessonModalLabel<?php echo $lesson['lesson_id']; ?>">Edit Lesson</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $moduleId); ?>">
                                                                    <input type="hidden" name="lesson_id" value="<?php echo $lesson['lesson_id']; ?>">
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="title<?php echo $lesson['lesson_id']; ?>" class="form-label">Title</label>
                                                                        <input type="text" class="form-control" id="title<?php echo $lesson['lesson_id']; ?>" name="title" value="<?php echo htmlspecialchars($lesson['title']); ?>" required>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="content_type<?php echo $lesson['lesson_id']; ?>" class="form-label">Content Type</label>
                                                                        <select class="form-select" id="content_type<?php echo $lesson['lesson_id']; ?>" name="content_type" required>
                                                                            <option value="text" <?php echo $lesson['content_type'] == 'text' ? 'selected' : ''; ?>>Text</option>
                                                                            <option value="video" <?php echo $lesson['content_type'] == 'video' ? 'selected' : ''; ?>>Video</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="order_number<?php echo $lesson['lesson_id']; ?>" class="form-label">Order Number</label>
                                                                        <input type="number" class="form-control" id="order_number<?php echo $lesson['lesson_id']; ?>" name="order_number" value="<?php echo $lesson['order_number']; ?>" min="1" required>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="content<?php echo $lesson['lesson_id']; ?>" class="form-label">Content</label>
                                                                        <?php if ($lesson['content_type'] === 'text'): ?>
                                                                            <textarea class="form-control" id="content<?php echo $lesson['lesson_id']; ?>" name="content" rows="10" required><?php echo htmlspecialchars($lesson['content']); ?></textarea>
                                                                        <?php else: ?>
                                                                            <input type="text" class="form-control" id="content<?php echo $lesson['lesson_id']; ?>" name="content" value="<?php echo htmlspecialchars($lesson['content']); ?>" required>
                                                                            <small class="form-text text-muted">
                                                                                <?php if ($lesson['content_type'] === 'video'): ?>
                                                                                    Enter the video URL (YouTube, Vimeo, etc.)
                                                                                <?php elseif ($lesson['content_type'] === 'image'): ?>
                                                                                    Enter the image URL or upload path
                                                                                <?php endif; ?>
                                                                            </small>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="save_lesson" class="btn btn-primary">Save Changes</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Delete Lesson Modal -->
                                                <div class="modal fade" id="deleteLessonModal<?php echo $lesson['lesson_id']; ?>" tabindex="-1" aria-labelledby="deleteLessonModalLabel<?php echo $lesson['lesson_id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteLessonModalLabel<?php echo $lesson['lesson_id']; ?>">Delete Lesson</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Are you sure you want to delete the lesson "<?php echo htmlspecialchars($lesson['title']); ?>"?</p>
                                                                <p class="text-danger">This action cannot be undone.</p>
                                                                
                                                                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $moduleId); ?>">
                                                                    <input type="hidden" name="lesson_id" value="<?php echo $lesson['lesson_id']; ?>">
                                                                    
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="delete_lesson" class="btn btn-danger">Delete Lesson</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center">No lessons found. Add lessons using the "Add Lesson" button.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quiz Section -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Quiz</h6>
                    <?php if (!$quiz): ?>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addQuizModal">
                            <i class="bi bi-plus-circle"></i> Create Quiz
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($quiz): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Title:</strong> <?php echo htmlspecialchars($quiz['title']); ?></p>
                                <p><strong>Passing Threshold:</strong> <?php echo $quiz['passing_threshold']; ?>%</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Cooldown Period:</strong> <?php echo $quiz['cooldown_period']; ?> hour(s)</p>
                                <p><strong>Created At:</strong> <?php echo date('M d, Y H:i', strtotime($quiz['created_at'])); ?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <p><strong>Description:</strong></p>
                                <p><?php echo nl2br(htmlspecialchars($quiz['description'])); ?></p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <a href="quiz_detail.php?id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> Manage Questions
                                </a>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editQuizModal">
                                    <i class="bi bi-pencil"></i> Edit Quiz
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteQuizModal">
                                    <i class="bi bi-trash"></i> Delete Quiz
                                </button>
                            </div>
                        </div>
                        
                        <!-- Edit Quiz Modal -->
                        <div class="modal fade" id="editQuizModal" tabindex="-1" aria-labelledby="editQuizModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editQuizModalLabel">Edit Quiz</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $moduleId); ?>">
                                            <input type="hidden" name="quiz_id" value="<?php echo $quiz['quiz_id']; ?>">
                                            
                                            <div class="mb-3">
                                                <label for="title" class="form-label">Title</label>
                                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($quiz['title']); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($quiz['description']); ?></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="passing_threshold" class="form-label">Passing Threshold (%)</label>
                                                <input type="number" class="form-control" id="passing_threshold" name="passing_threshold" value="<?php echo $quiz['passing_threshold']; ?>" min="0" max="100" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="cooldown_period" class="form-label">Cooldown Period (hours)</label>
                                                <input type="number" class="form-control" id="cooldown_period" name="cooldown_period" value="<?php echo $quiz['cooldown_period']; ?>" min="0" required>
                                            </div>
                                            
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="save_quiz" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Delete Quiz Modal -->
                        <div class="modal fade" id="deleteQuizModal" tabindex="-1" aria-labelledby="deleteQuizModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteQuizModalLabel">Delete Quiz</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete the quiz "<?php echo htmlspecialchars($quiz['title']); ?>"?</p>
                                        <p class="text-danger">This action cannot be undone. All questions, answers, and user results will be deleted.</p>
                                        
                                        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $moduleId); ?>">
                                            <input type="hidden" name="quiz_id" value="<?php echo $quiz['quiz_id']; ?>">
                                            
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="delete_quiz" class="btn btn-danger">Delete Quiz</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-center">No quiz found for this module. Create a quiz using the "Create Quiz" button.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add Lesson Modal -->
<div class="modal fade" id="addLessonModal" tabindex="-1" aria-labelledby="addLessonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLessonModalLabel">Add New Lesson</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $moduleId); ?>">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content_type" class="form-label">Content Type</label>
                        <select class="form-select" id="content_type" name="content_type" required onchange="toggleContentField()">
                            <option value="text" selected>Text</option>
                            <option value="video">Video</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="order_number" class="form-label">Order Number</label>
                        <input type="number" class="form-control" id="order_number" name="order_number" value="<?php echo count($lessons) + 1; ?>" min="1" required>
                    </div>
                    
                    <div class="mb-3" id="text_content_div">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                    </div>
                    
                    <div class="mb-3" id="url_content_div" style="display: none;">
                        <label for="url_content" class="form-label">Content URL</label>
                        <input type="text" class="form-control" id="url_content" name="content">
                        <small class="form-text text-muted" id="url_help_text">Enter the URL for the content</small>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="save_lesson" class="btn btn-primary">Create Lesson</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Quiz Modal -->
<div class="modal fade" id="addQuizModal" tabindex="-1" aria-labelledby="addQuizModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addQuizModalLabel">Create Quiz</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $moduleId); ?>">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="passing_threshold" class="form-label">Passing Threshold (%)</label>
                        <input type="number" class="form-control" id="passing_threshold" name="passing_threshold" value="70" min="0" max="100" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cooldown_period" class="form-label">Cooldown Period (hours)</label>
                        <input type="number" class="form-control" id="cooldown_period" name="cooldown_period" value="24" min="0" required>
                        <small class="form-text text-muted">Time users must wait before retaking a failed quiz</small>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="save_quiz" class="btn btn-primary">Create Quiz</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Module Modal -->
<div class="modal fade" id="editModuleModal" tabindex="-1" aria-labelledby="editModuleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModuleModalLabel">Edit Module</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="modules.php">
                    <input type="hidden" name="module_id" value="<?php echo $module['module_id']; ?>">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($module['title']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($module['description']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deadline" class="form-label">Deadline (optional)</label>
                        <input type="date" class="form-control" id="deadline" name="deadline" value="<?php echo $module['deadline'] ? date('Y-m-d', strtotime($module['deadline'])) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active" <?php echo $module['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $module['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="save_module" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JavaScript -->
<script>
    function toggleContentField() {
        const contentType = document.getElementById('content_type').value;
        const textContentDiv = document.getElementById('text_content_div');
        const urlContentDiv = document.getElementById('url_content_div');
        const urlHelpText = document.getElementById('url_help_text');
        
        if (contentType === 'text') {
            textContentDiv.style.display = 'block';
            urlContentDiv.style.display = 'none';
            document.getElementById('content').required = true;
            document.getElementById('url_content').required = false;
        } else {
            textContentDiv.style.display = 'none';
            urlContentDiv.style.display = 'block';
            document.getElementById('content').required = false;
            document.getElementById('url_content').required = true;
            
            if (contentType === 'video') {
                urlHelpText.textContent = 'Enter the video URL (YouTube, Vimeo, etc.)';
            } else if (contentType === 'image') {
                urlHelpText.textContent = 'Enter the image URL or upload path';
            }
        }
    }
</script>

<?php include '../includes/footer.php'; ?>

