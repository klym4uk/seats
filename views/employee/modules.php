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
                <h2 class="card-title mb-0">My Modules</h2>
                <a href="index.php?page=employee-dashboard" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
            <div class="card-body">
                <p class="card-text">
                    Below are all the training modules assigned to you. Click on a module to view its details and access the lessons.
                </p>
            </div>
        </div>
    </div>
</div>

<?php if (empty($modules)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i> You don't have any assigned modules yet.
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($modules as $module): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h3 class="card-title"><?php echo $module['title']; ?></h3>
                        
                        <div class="mb-3">
                            <span class="badge bg-<?php echo getProgressStatusClass($module['progress_status']); ?>">
                                <?php echo getProgressStatusText($module['progress_status']); ?>
                            </span>
                        </div>
                        
                        <p class="card-text flex-grow-1"><?php echo $module['description']; ?></p>
                        
                        <div class="progress mb-3">
                            <div class="progress-bar" role="progressbar" style="width: <?php echo $module['progress_percentage']; ?>%" aria-valuenow="<?php echo $module['progress_percentage']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <?php echo $module['lesson_count']; ?> lessons
                            </small>
                            <a href="index.php?page=employee-dashboard&action=module-details&id=<?php echo $module['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-eye me-1"></i> View Module
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
// Include the footer
require_once 'views/shared/footer.php';
?>

