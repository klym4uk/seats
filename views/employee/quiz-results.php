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
                    <h2 class="card-title mb-0">Quiz Results</h2>
                    <div class="text-muted">
                        Module: <a href="index.php?page=employee-dashboard&action=module-details&id=<?php echo $module['id']; ?>"><?php echo $module['title']; ?></a>
                    </div>
                </div>
                <a href="index.php?page=employee-dashboard&action=module-details&id=<?php echo $module['id']; ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Module
                </a>
            </div>
            <div class="card-body">
                <div class="result-summary <?php echo $result['passed'] ? 'result-passed' : 'result-failed'; ?>">
                    <h3><?php echo $result['passed'] ? 'Congratulations!' : 'Better luck next time!'; ?></h3>
                    <div class="result-score"><?php echo $result['score']; ?>%</div>
                    <p>
                        <?php if ($result['passed']): ?>
                            You have passed the quiz for this module.
                        <?php else: ?>
                            You did not pass the quiz. The passing threshold is <?php echo $quiz['passing_threshold']; ?>%.
                            <?php if ($quizResult['attempt_number'] < $quiz['attempts_allowed'] || $quiz['attempts_allowed'] === 0): ?>
                                You can try again after the cooldown period.
                            <?php else: ?>
                                You have reached the maximum number of attempts allowed.
                            <?php endif; ?>
                        <?php endif; ?>
                    </p>
                    <div class="mt-3">
                        <strong>Attempt:</strong> <?php echo $quizResult['attempt_number']; ?>
                        <?php if ($quiz['attempts_allowed'] > 0): ?>
                            of <?php echo $quiz['attempts_allowed']; ?>
                        <?php endif; ?>
                    </div>
                    <div class="mt-2">
                        <strong>Completed:</strong> <?php echo formatDate($result['completed_at'], 'M j, Y g:i A'); ?>
                    </div>
                </div>
                
                <h3 class="mt-5 mb-4">Question Review</h3>
                
                <?php foreach ($questions as $index => $question): ?>
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Question <?php echo $index + 1; ?></h4>
                            <span class="badge bg-<?php echo $question['user_answer']['is_correct'] ? 'success' : 'danger'; ?>">
                                <?php echo $question['user_answer']['is_correct'] ? 'Correct' : 'Incorrect'; ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <p class="question-text mb-4"><?php echo $question['question_text']; ?></p>
                            
                            <div class="answers">
                                <?php foreach ($question['all_answers'] as $answer): ?>
                                    <div class="answer-option <?php echo $answer['id'] === $question['user_answer']['id'] ? ($answer['is_correct'] ? 'answer-correct' : 'answer-incorrect') : ($answer['is_correct'] ? 'answer-correct' : ''); ?>">
                                        <div class="d-flex align-items-center p-3 border rounded mb-2">
                                            <div class="me-3">
                                                <?php if ($answer['id'] === $question['user_answer']['id'] && $answer['is_correct']): ?>
                                                    <i class="fas fa-check-circle text-success fa-lg"></i>
                                                <?php elseif ($answer['id'] === $question['user_answer']['id'] && !$answer['is_correct']): ?>
                                                    <i class="fas fa-times-circle text-danger fa-lg"></i>
                                                <?php elseif ($answer['is_correct']): ?>
                                                    <i class="fas fa-check-circle text-success fa-lg"></i>
                                                <?php else: ?>
                                                    <i class="far fa-circle text-muted fa-lg"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <?php echo $answer['answer_text']; ?>
                                                <?php if ($answer['id'] === $question['user_answer']['id']): ?>
                                                    <span class="ms-2 badge bg-info">Your Answer</span>
                                                <?php endif; ?>
                                                <?php if ($answer['is_correct']): ?>
                                                    <span class="ms-2 badge bg-success">Correct Answer</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php if (isset($question['explanation']) && !empty($question['explanation'])): ?>
                                <div class="mt-3 p-3 bg-light rounded">
                                    <strong>Explanation:</strong>
                                    <p class="mb-0 mt-2"><?php echo $question['explanation']; ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="d-flex justify-content-between mt-5">
                    <a href="index.php?page=employee-dashboard&action=module-details&id=<?php echo $module['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Module
                    </a>
                    
                    <?php if (!$result['passed'] && ($quizResult['attempt_number'] < $quiz['attempts_allowed'] || $quiz['attempts_allowed'] === 0)): ?>
                        <?php if ($canAttemptQuiz): ?>
                            <a href="index.php?page=employee-dashboard&action=quiz&id=<?php echo $quiz['id']; ?>" class="btn btn-success">
                                <i class="fas fa-redo me-2"></i> Try Again
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-clock me-2"></i> Cooldown Period Active
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer
require_once 'views/shared/footer.php';
?>

