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
                <h2 class="card-title mb-0"><?php echo $module['title']; ?></h2>
                <a href="index.php?page=employee-dashboard&action=modules" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Modules
                </a>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="badge bg-<?php echo getProgressStatusClass($module['progress_status']); ?>">
                        <?php echo getProgressStatusText($module['progress_status']); ?>
                    </span>
                    
                    <?php if ($module['progress_status'] === 'completed'): ?>
                        <span class="ms-2 text-muted">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Completed on <?php echo formatDate($module['completion_date']); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <p class="card-text"><?php echo $module['description']; ?></p>
                
                <div class="progress mb-3">
                    <div class="progress-bar" role="progressbar" style="width: <?php echo $module['progress_percentage']; ?>%" aria-valuenow="<?php echo $module['progress_percentage']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Module Information</h4>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-book me-2"></i> Lessons</span>
                                        <span class="badge bg-primary rounded-pill"><?php echo count($lessons); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-tasks me-2"></i> Quiz</span>
                                        <span class="badge bg-<?php echo $quiz ? 'primary' : 'secondary'; ?> rounded-pill"><?php echo $quiz ? 'Yes' : 'No'; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-clock me-2"></i> Estimated Time</span>
                                        <span><?php echo $module['estimated_time']; ?> minutes</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Your Progress</h4>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-book-reader me-2"></i> Lessons Completed</span>
                                        <span class="badge bg-success rounded-pill"><?php echo $module['completed_lessons']; ?> / <?php echo count($lessons); ?></span>
                                    </li>
                                    <?php if ($quiz): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><i class="fas fa-tasks me-2"></i> Quiz Status</span>
                                            <span class="badge bg-<?php echo $module['quiz_passed'] ? 'success' : ($module['quiz_attempted'] ? 'danger' : 'secondary'); ?> rounded-pill">
                                                <?php echo $module['quiz_passed'] ? 'Passed' : ($module['quiz_attempted'] ? 'Failed' : 'Not Attempted'); ?>
                                            </span>
                                        </li>
                                        <?php if ($module['quiz_attempted']): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span><i class="fas fa-chart-bar me-2"></i> Best Score</span>
                                                <span><?php echo $module['best_quiz_score']; ?>%</span>
                                            </li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h3 class="card-title mb-0">Lessons</h3>
            </div>
            <div class="card-body">
                <?php if (empty($lessons)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i> This module doesn't have any lessons yet.
                    </div>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($lessons as $lesson): ?>
                            <a href="index.php?page=employee-dashboard&action=lesson&id=<?php echo $lesson['id']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1"><?php echo $lesson['title']; ?></h5>
                                    <p class="mb-1 text-muted"><?php echo $lesson['description']; ?></p>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-<?php echo getProgressStatusClass($lesson['progress_status']); ?> me-2">
                                        <?php echo getProgressStatusText($lesson['progress_status']); ?>
                                    </span>
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($quiz): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title mb-0">Module Quiz</h3>
                </div>
                <div class="card-body">
                    <h4><?php echo $quiz['title']; ?></h4>
                    <p><?php echo $quiz['description']; ?></p>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Quiz Information:</strong>
                        <ul class="mb-0 mt-2">
                            <li>This quiz has <?php echo $quiz['question_count']; ?> questions.</li>
                            <li>You need to score at least <?php echo $quiz['passing_threshold']; ?>% to pass.</li>
                            <?php if ($quiz['time_limit']): ?>
                                <li>Time limit: <?php echo $quiz['time_limit']; ?> minutes.</li>
                            <?php endif; ?>
                            <?php if ($quiz['attempts_allowed'] > 0): ?>
                                <li>Maximum attempts allowed: <?php echo $quiz['attempts_allowed']; ?>.</li>
                            <?php endif; ?>
                            <?php if ($quiz['cooldown_period']): ?>
                                <li>You must wait <?php echo $quiz['cooldown_period']; ?> hours between attempts.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <?php if ($canAttemptQuiz): ?>
                        <div class="d-grid gap-2 col-md-6 mx-auto mt-4">
                            <a href="index.php?page=employee-dashboard&action=quiz&id=<?php echo $quiz['id']; ?>" class="btn btn-primary btn-lg">
                                <i class="fas fa-tasks me-2"></i> Start Quiz
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $quizMessage; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
// Include the footer
require_once 'views/shared/footer.php';
?>

