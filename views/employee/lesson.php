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
                    <h2 class="card-title mb-0"><?php echo $lesson['title']; ?></h2>
                    <div class="text-muted">
                        Module: <a href="index.php?page=employee-dashboard&action=module-details&id=<?php echo $module['id']; ?>"><?php echo $module['title']; ?></a>
                    </div>
                </div>
                <a href="index.php?page=employee-dashboard&action=module-details&id=<?php echo $module['id']; ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Module
                </a>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="badge bg-<?php echo getProgressStatusClass($lesson['progress_status']); ?>">
                        <?php echo getProgressStatusText($lesson['progress_status']); ?>
                    </span>
                    
                    <?php if ($lesson['progress_status'] === 'completed'): ?>
                        <span class="ms-2 text-muted">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Completed on <?php echo formatDate($lesson['completion_date']); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="lesson-content mb-4">
                    <?php echo $lesson['content']; ?>
                </div>
                
                <?php if ($lesson['progress_status'] !== 'completed'): ?>
                    <div class="d-grid gap-2 col-md-6 mx-auto mt-5">
                        <a href="index.php?page=employee-dashboard&action=complete-lesson&id=<?php echo $lesson['id']; ?>" class="btn btn-success btn-lg">
                            <i class="fas fa-check-circle me-2"></i> Mark as Completed
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="lesson-nav mt-5">
                    <?php if ($prevLesson): ?>
                        <a href="index.php?page=employee-dashboard&action=lesson&id=<?php echo $prevLesson['id']; ?>" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i> Previous Lesson
                        </a>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>
                    
                    <?php if ($nextLesson): ?>
                        <a href="index.php?page=employee-dashboard&action=lesson&id=<?php echo $nextLesson['id']; ?>" class="btn btn-outline-primary">
                            Next Lesson <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    <?php else: ?>
                        <a href="index.php?page=employee-dashboard&action=module-details&id=<?php echo $module['id']; ?>" class="btn btn-outline-primary">
                            Back to Module <i class="fas fa-arrow-right ms-2"></i>
                        </a>
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

