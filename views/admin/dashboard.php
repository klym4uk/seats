<?php
// Set the current page for the navigation
$page = 'admin-dashboard';

// Include the header
require_once 'views/shared/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title">Welcome, <?php echo $_SESSION['name']; ?>!</h2>
                <p class="card-text">
                    This is the admin dashboard for the Social Engineering Awareness Training System. Here you can manage users, modules, and view reports.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-4 mb-md-0">
        <div class="card shadow-sm dashboard-widget h-100">
            <div class="card-body text-center">
                <div class="feature-icon bg-primary bg-gradient text-white mb-3 mx-auto" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-users fa-2x"></i>
                </div>
                <h3 class="dashboard-widget-title">Users</h3>
                <div class="display-4 fw-bold text-primary mb-3"><?php echo $userCount; ?></div>
                <p class="text-muted">Total registered users</p>
                <a href="index.php?page=admin-dashboard&action=users" class="btn btn-outline-primary">
                    <i class="fas fa-user-cog me-1"></i> Manage Users
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4 mb-md-0">
        <div class="card shadow-sm dashboard-widget h-100">
            <div class="card-body text-center">
                <div class="feature-icon bg-primary bg-gradient text-white mb-3 mx-auto" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-book fa-2x"></i>
                </div>
                <h3 class="dashboard-widget-title">Modules</h3>
                <div class="display-4 fw-bold text-primary mb-3"><?php echo $moduleCount; ?></div>
                <p class="text-muted">Training modules available</p>
                <a href="index.php?page=admin-dashboard&action=modules" class="btn btn-outline-primary">
                    <i class="fas fa-book-open me-1"></i> Manage Modules
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm dashboard-widget h-100">
            <div class="card-body text-center">
                <div class="feature-icon bg-primary bg-gradient text-white mb-3 mx-auto" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-chart-bar fa-2x"></i>
                </div>
                <h3 class="dashboard-widget-title">Completion Rate</h3>
                <div class="display-4 fw-bold text-primary mb-3"><?php echo $completionRate; ?>%</div>
                <p class="text-muted">Overall module completion rate</p>
                <a href="index.php?page=admin-dashboard&action=reports" class="btn btn-outline-primary">
                    <i class="fas fa-chart-line me-1"></i> View Reports
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-4 mb-md-0">
        <div class="card shadow-sm dashboard-widget">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="dashboard-widget-title mb-0">Recent User Activity</h3>
                <a href="index.php?page=admin-dashboard&action=reports" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-chart-line me-1"></i> View All
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recentActivity)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i> No recent activity to display.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Activity</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentActivity as $activity): ?>
                                    <tr>
                                        <td><?php echo $activity['user_name']; ?></td>
                                        <td><?php echo $activity['activity']; ?></td>
                                        <td><?php echo formatDate($activity['created_at'], 'M j, Y g:i A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm dashboard-widget">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="dashboard-widget-title mb-0">Recent Quiz Results</h3>
                <a href="index.php?page=admin-dashboard&action=reports" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-chart-line me-1"></i> View All
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recentQuizResults)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i> No recent quiz results to display.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Quiz</th>
                                    <th>Score</th>
                                    <th>Result</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentQuizResults as $result): ?>
                                    <tr>
                                        <td><?php echo $result['user_name']; ?></td>
                                        <td><?php echo $result['quiz_title']; ?></td>
                                        <td><?php echo $result['score']; ?>%</td>
                                        <td>
                                            <span class="badge bg-<?php echo $result['passed'] ? 'success' : 'danger'; ?>">
                                                <?php echo $result['passed'] ? 'Passed' : 'Failed'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($result['completed_at'], 'M j, Y g:i A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
                <h3 class="dashboard-widget-title mb-0">Module Completion Statistics</h3>
            </div>
            <div class="card-body">
                <?php if (empty($moduleStats)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i> No module statistics to display.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Assigned Users</th>
                                    <th>Completed</th>
                                    <th>In Progress</th>
                                    <th>Not Started</th>
                                    <th>Completion Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($moduleStats as $stat): ?>
                                    <tr>
                                        <td><?php echo $stat['title']; ?></td>
                                        <td><?php echo $stat['assigned_users']; ?></td>
                                        <td><?php echo $stat['completed']; ?></td>
                                        <td><?php echo $stat['in_progress']; ?></td>
                                        <td><?php echo $stat['not_started']; ?></td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" style="width: <?php echo $stat['completion_rate']; ?>%" aria-valuenow="<?php echo $stat['completion_rate']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <small class="text-muted"><?php echo $stat['completion_rate']; ?>%</small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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

