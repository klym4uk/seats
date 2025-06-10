<?php
// Set the current page for the navigation
$page = 'employee-dashboard';

// Include the header
require_once 'views/shared/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="card-title mb-0"><?php echo $quiz['title']; ?></h2>
                    <div class="text-muted">
                        Module: <a href="index.php?page=employee-dashboard&action=module-details&id=<?php echo $module['id']; ?>"><?php echo $module['title']; ?></a>
                    </div>
                </div>
                <a href="index.php?page=employee-dashboard&action=module-details&id=<?php echo $module['id']; ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Module
                </a>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Quiz Information:</strong>
                    <ul class="mb-0 mt-2">
                        <li>This quiz has <?php echo count($questions); ?> questions.</li>
                        <li>You need to score at least <?php echo $quiz['passing_threshold']; ?>% to pass.</li>
                        <?php if ($quiz['time_limit']): ?>
                            <li>Time limit: <?php echo $quiz['time_limit']; ?> minutes.</li>
                        <?php endif; ?>
                        <li>Answer all questions and click "Submit Quiz" when you're done.</li>
                    </ul>
                </div>
                
                <form id="quiz-form" action="index.php?page=employee-dashboard&action=submit-quiz" method="post">
                    <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                    
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="card question-card mb-4" data-question-id="<?php echo $question['id']; ?>">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Question <?php echo $index + 1; ?></h4>
                            </div>
                            <div class="card-body">
                                <p class="question-text mb-4"><?php echo $question['question_text']; ?></p>
                                
                                <div class="answers">
                                    <?php foreach ($question['answers'] as $answer): ?>
                                        <div class="answer-option" data-question-id="<?php echo $question['id']; ?>">
                                            <input type="radio" id="answer_<?php echo $answer['id']; ?>" name="question_<?php echo $question['id']; ?>" value="<?php echo $answer['id']; ?>" class="d-none">
                                            <label for="answer_<?php echo $answer['id']; ?>" class="d-block p-3 border rounded mb-2 cursor-pointer">
                                                <?php echo $answer['answer_text']; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="d-grid gap-2 col-md-6 mx-auto mt-5">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i> Submit Quiz
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer
require_once 'views/shared/footer.php';
?>

