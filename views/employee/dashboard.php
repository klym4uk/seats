<?php
// Set the current page for the navigation
$page = 'employee-dashboard';

// Include the header
require_once 'views/shared/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title">Welcome, <?php echo $_SESSION['name']; ?>!</h2>
                <p class="card-text">
                    This is your dashboard for the Social Engineering Awareness Training System. Here you can view your assigned modules, track your progress, and access training materials.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm dashboard-widget">
            <div class="card-body text-center">
                <h3 class="dashboard-widget-title">Overall Progress</h3>
                <div class="progress-circle" data-value="<?php echo $overallProgress; ?>">
                    <svg width="120" height="120" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="54" fill="none" stroke="#dce4ec" stroke-width="12" />
                        <circle cx="60" cy="60" r="54" fill="none" stroke="#3498db" stroke-width="12" stroke-dasharray="339.292" stroke-dashoffset="<?php echo 339.292 - ($overallProgress / 100) * 339.292; ?>" class="progress-circle-value" />
                    </svg>
                    <div class="progress-circle-text"><?php echo $overallProgress; ?>%</div>
                </div>
                <p class="text-muted">Your overall training completion</p>
                <a href="index.php?page=employee-dashboard&action=progress" class="btn btn-outline-primary">
                    <i class="fas fa-chart-line me-1"></i> View Detailed Progress
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card shadow-sm dashboard-widget">
            <div class="card-header">
                <h3 class="dashboard-widget-title mb-0">Assigned Modules</h3>
            </div>
            <div class="card-body">
                <?php if (empty($modules)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i> You don't have any assigned modules yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modules as $module): ?>
                                    <tr>
                                        <td><?php echo $module['title']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo getProgressStatusClass($module['progress_status']); ?>">
                                                <?php echo getProgressStatusText($module['progress_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="index.php?page=employee-dashboard&action=module-details&id=<?php echo $module['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye me-1"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <a href="index.php?page=employee-dashboard&action=modules" class="btn btn-outline-primary">
                            <i class="fas fa-book me-1"></i> View All Modules
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm dashboard-widget">
            <div class="card-header">
                <h3 class="dashboard-widget-title mb-0">Recent Quiz Results</h3>
            </div>
            <div class="card-body">
                <?php if (empty($recentResults)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i> You haven't taken any quizzes yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Quiz</th>
                                    <th>Module</th>
                                    <th>Score</th>
                                    <th>Result</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentResults as $result): ?>
                                    <tr>
                                        <td><?php echo $result['quiz_title']; ?></td>
                                        <td><?php echo $result['module_title']; ?></td>
                                        <td><?php echo $result['score']; ?>%</td>
                                        <td>
                                            <span class="badge bg-<?php echo $result['passed'] ? 'success' : 'danger'; ?>">
                                                <?php echo $result['passed'] ? 'Passed' : 'Failed'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($result['completed_at'], 'M j, Y g:i A'); ?></td>
                                        <td>
                                            <a href="index.php?page=employee-dashboard&action=quiz-results&id=<?php echo $result['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye me-1"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <a href="index.php?page=employee-dashboard&action=progress" class="btn btn-outline-primary">
                            <i class="fas fa-chart-bar me-1"></i> View All Results
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer
require_once 'views/shared/footer.php';
?>

