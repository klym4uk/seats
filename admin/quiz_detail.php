<?php
// Include configuration
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Quiz.php';
require_once '../classes/Module.php';

// Set page title
$pageTitle = 'Quiz Details';

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

// Check if quiz ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = 'Quiz ID is required.';
    header('Location: modules.php');
    exit;
}

$quizId = $_GET['id'];

// Initialize classes
$quizObj = new Quiz();
$moduleObj = new Module();

// Get quiz details
$quiz = $quizObj->getQuizById($quizId);

if (!$quiz) {
    $_SESSION['error'] = 'Quiz not found.';
    header('Location: modules.php');
    exit;
}

// Get module details
$module = $moduleObj->getModuleById($quiz['module_id']);

// Get questions for this quiz
$questions = $quizObj->getQuestionsByQuizId($quizId);

// Process form submissions
$message = '';
$error = '';

// Handle question actions (add, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add question
    if (isset($_POST['add_question'])) {
        $questionData = [
            'quiz_id' => $quizId,
            'question_text' => $_POST['question_text'],
            'explanation' => $_POST['explanation']
        ];
        
        $questionId = $quizObj->addQuestion($questionData);
        
        if ($questionId) {
            // Add answers
            $answerTexts = $_POST['answer_text'];
            $correctAnswer = $_POST['correct_answer'];
            
            $allAnswersAdded = true;
            
            foreach ($answerTexts as $key => $text) {
                if (!empty($text)) {
                    $answerData = [
                        'question_id' => $questionId,
                        'answer_text' => $text,
                        'is_correct' => ($key == $correctAnswer) ? 1 : 0
                    ];
                    
                    if (!$quizObj->addAnswer($answerData)) {
                        $allAnswersAdded = false;
                    }
                }
            }
            
            if ($allAnswersAdded) {
                $message = 'Question and answers added successfully.';
            } else {
                $error = 'Question added but some answers could not be saved.';
            }
            
            // Refresh questions list
            $questions = $quizObj->getQuestionsByQuizId($quizId);
        } else {
            $error = 'Failed to add question.';
        }
    }
    
    // Edit question
    if (isset($_POST['edit_question'])) {
        $questionId = $_POST['question_id'];
        
        // Update question
        $questionData = [
            'question_id' => $questionId,
            'question_text' => $_POST['question_text'],
            'explanation' => $_POST['explanation']
        ];
        
        if ($quizObj->updateQuestion($questionData)) {
            // Delete existing answers
            $quizObj->deleteAnswersByQuestionId($questionId);
            
            // Add new answers
            $answerTexts = $_POST['answer_text'];
            $correctAnswer = $_POST['correct_answer'];
            
            $allAnswersAdded = true;
            
            foreach ($answerTexts as $key => $text) {
                if (!empty($text)) {
                    $answerData = [
                        'question_id' => $questionId,
                        'answer_text' => $text,
                        'is_correct' => ($key == $correctAnswer) ? 1 : 0
                    ];
                    
                    if (!$quizObj->addAnswer($answerData)) {
                        $allAnswersAdded = false;
                    }
                }
            }
            
            if ($allAnswersAdded) {
                $message = 'Question and answers updated successfully.';
            } else {
                $error = 'Question updated but some answers could not be saved.';
            }
            
            // Refresh questions list
            $questions = $quizObj->getQuestionsByQuizId($quizId);
        } else {
            $error = 'Failed to update question.';
        }
    }
    
    // Delete question
    if (isset($_POST['delete_question'])) {
        $questionId = $_POST['question_id'];
        
        if ($quizObj->deleteQuestion($questionId)) {
            $message = 'Question deleted successfully.';
            // Refresh questions list
            $questions = $quizObj->getQuestionsByQuizId($quizId);
        } else {
            $error = 'Failed to delete question.';
        }
    }
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
                    <li class="breadcrumb-item"><a href="module_detail.php?id=<?php echo $module['module_id']; ?>"><?php echo htmlspecialchars($module['title']); ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($quiz['title']); ?></li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Quiz: <?php echo htmlspecialchars($quiz['title']); ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="module_detail.php?id=<?php echo $module['module_id']; ?>" class="btn btn-sm btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Back to Module
                    </a>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                        <i class="bi bi-plus-circle"></i> Add Question
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
            
            <!-- Quiz Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quiz Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Title:</strong> <?php echo htmlspecialchars($quiz['title']); ?></p>
                            <p><strong>Module:</strong> <?php echo htmlspecialchars($module['title']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Passing Threshold:</strong> <?php echo $quiz['passing_threshold']; ?>%</p>
                            <p><strong>Cooldown Period:</strong> <?php echo $quiz['cooldown_period']; ?> hour(s)</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <p><strong>Description:</strong></p>
                            <p><?php echo nl2br(htmlspecialchars($quiz['description'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Questions List -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Questions</h6>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                        <i class="bi bi-plus-circle"></i> Add Question
                    </button>
                </div>
                <div class="card-body">
                    <?php if (count($questions) > 0): ?>
                        <div class="accordion" id="questionsAccordion">
                            <?php foreach ($questions as $index => $question): ?>
                                <?php 
                                $answers = $quizObj->getAnswersByQuestionId($question['question_id']);
                                $correctAnswer = null;
                                foreach ($answers as $key => $answer) {
                                    if ($answer['is_correct']) {
                                        $correctAnswer = $key;
                                    }
                                }
                                ?>
                                <div class="accordion-item mb-3">
                                    <h2 class="accordion-header" id="heading<?php echo $question['question_id']; ?>">
                                        <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $question['question_id']; ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $question['question_id']; ?>">
                                            <div class="d-flex justify-content-between w-100 me-3">
                                                <span>Question <?php echo $index + 1; ?>: <?php echo htmlspecialchars(substr($question['question_text'], 0, 100)) . (strlen($question['question_text']) > 100 ? '...' : ''); ?></span>
                                                <span class="badge bg-info"><?php echo count($answers); ?> answers</span>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $question['question_id']; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $question['question_id']; ?>" data-bs-parent="#questionsAccordion">
                                        <div class="accordion-body">
                                            <div class="mb-3">
                                                <h5>Question:</h5>
                                                <p><?php echo nl2br(htmlspecialchars($question['question_text'])); ?></p>
                                            </div>
                                            
                                            <?php if (!empty($question['explanation'])): ?>
                                                <div class="mb-3">
                                                    <h5>Explanation:</h5>
                                                    <p><?php echo nl2br(htmlspecialchars($question['explanation'])); ?></p>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="mb-3">
                                                <h5>Answers:</h5>
                                                <ul class="list-group">
                                                    <?php foreach ($answers as $answer): ?>
                                                        <li class="list-group-item <?php echo $answer['is_correct'] ? 'list-group-item-success' : ''; ?>">
                                                            <?php echo htmlspecialchars($answer['answer_text']); ?>
                                                            <?php if ($answer['is_correct']): ?>
                                                                <span class="badge bg-success ms-2">Correct</span>
                                                            <?php endif; ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                            
                                            <div class="d-flex justify-content-end">
                                                <button type="button" class="btn btn-sm btn-primary me-2" data-bs-toggle="modal" data-bs-target="#editQuestionModal<?php echo $question['question_id']; ?>">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteQuestionModal<?php echo $question['question_id']; ?>">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </div>
                                            
                                            <!-- Edit Question Modal -->
                                            <div class="modal fade" id="editQuestionModal<?php echo $question['question_id']; ?>" tabindex="-1" aria-labelledby="editQuestionModalLabel<?php echo $question['question_id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="editQuestionModalLabel<?php echo $question['question_id']; ?>">Edit Question</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $quizId); ?>">
                                                                <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
                                                                
                                                                <div class="mb-3">
                                                                    <label for="question_text<?php echo $question['question_id']; ?>" class="form-label">Question Text</label>
                                                                    <textarea class="form-control" id="question_text<?php echo $question['question_id']; ?>" name="question_text" rows="3" required><?php echo htmlspecialchars($question['question_text']); ?></textarea>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label for="explanation<?php echo $question['question_id']; ?>" class="form-label">Explanation (Optional)</label>
                                                                    <textarea class="form-control" id="explanation<?php echo $question['question_id']; ?>" name="explanation" rows="2"><?php echo htmlspecialchars($question['explanation']); ?></textarea>
                                                                    <small class="form-text text-muted">Explanation will be shown to users after they answer the question</small>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Answers</label>
                                                                    <small class="form-text text-muted d-block mb-2">Select the correct answer using the radio buttons</small>
                                                                    
                                                                    <?php foreach ($answers as $key => $answer): ?>
                                                                        <div class="input-group mb-2">
                                                                            <div class="input-group-text">
                                                                                <input class="form-check-input mt-0" type="radio" name="correct_answer" value="<?php echo $key; ?>" <?php echo $answer['is_correct'] ? 'checked' : ''; ?> required>
                                                                            </div>
                                                                            <input type="text" class="form-control" name="answer_text[<?php echo $key; ?>]" value="<?php echo htmlspecialchars($answer['answer_text']); ?>" required>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                    
                                                                    <?php for ($i = count($answers); $i < 4; $i++): ?>
                                                                        <div class="input-group mb-2">
                                                                            <div class="input-group-text">
                                                                                <input class="form-check-input mt-0" type="radio" name="correct_answer" value="<?php echo $i; ?>">
                                                                            </div>
                                                                            <input type="text" class="form-control" name="answer_text[<?php echo $i; ?>]" placeholder="Answer option">
                                                                        </div>
                                                                    <?php endfor; ?>
                                                                </div>
                                                                
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" name="edit_question" class="btn btn-primary">Save Changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Delete Question Modal -->
                                            <div class="modal fade" id="deleteQuestionModal<?php echo $question['question_id']; ?>" tabindex="-1" aria-labelledby="deleteQuestionModalLabel<?php echo $question['question_id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="deleteQuestionModalLabel<?php echo $question['question_id']; ?>">Delete Question</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete this question?</p>
                                                            <p class="text-danger">This action cannot be undone. All associated answers will be deleted.</p>
                                                            
                                                            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $quizId); ?>">
                                                                <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
                                                                
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" name="delete_question" class="btn btn-danger">Delete Question</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center">No questions found. Add questions using the "Add Question" button.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add Question Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1" aria-labelledby="addQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addQuestionModalLabel">Add New Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $quizId); ?>">
                    <div class="mb-3">
                        <label for="question_text" class="form-label">Question Text</label>
                        <textarea class="form-control" id="question_text" name="question_text" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="explanation" class="form-label">Explanation (Optional)</label>
                        <textarea class="form-control" id="explanation" name="explanation" rows="2"></textarea>
                        <small class="form-text text-muted">Explanation will be shown to users after they answer the question</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Answers</label>
                        <small class="form-text text-muted d-block mb-2">Select the correct answer using the radio buttons. At least 2 answers are required.</small>
                        
                        <div class="input-group mb-2">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="radio" name="correct_answer" value="0" checked required>
                            </div>
                            <input type="text" class="form-control" name="answer_text[0]" placeholder="Correct answer" required>
                        </div>
                        
                        <div class="input-group mb-2">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="radio" name="correct_answer" value="1">
                            </div>
                            <input type="text" class="form-control" name="answer_text[1]" placeholder="Incorrect answer" required>
                        </div>
                        
                        <div class="input-group mb-2">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="radio" name="correct_answer" value="2">
                            </div>
                            <input type="text" class="form-control" name="answer_text[2]" placeholder="Incorrect answer (optional)">
                        </div>
                        
                        <div class="input-group mb-2">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="radio" name="correct_answer" value="3">
                            </div>
                            <input type="text" class="form-control" name="answer_text[3]" placeholder="Incorrect answer (optional)">
                        </div>
                        
                        <div class="input-group mb-2">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="radio" name="correct_answer" value="4">
                            </div>
                            <input type="text" class="form-control" name="answer_text[4]" placeholder="Incorrect answer (optional)">
                        </div>
                        
                        <div class="input-group mb-2">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="radio" name="correct_answer" value="5">
                            </div>
                            <input type="text" class="form-control" name="answer_text[5]" placeholder="Incorrect answer (optional)">
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_question" class="btn btn-primary">Add Question</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<?php include '../includes/footer.php'; ?>

