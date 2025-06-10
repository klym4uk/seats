<?php
// Include configuration
require_once '../config/config.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Check if user is employee
if ($_SESSION['role'] !== 'employee') {
    header('Location: ../admin/index.php');
    exit;
}

// Include necessary files
require_once '../classes/User.php';
require_once '../classes/Module.php';

// Initialize classes
$userObj = new User();
$moduleObj = new Module();

// Get user's modules
$modules = $moduleObj->getModulesByUserId($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - <?php echo APP_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <?php include '../includes/employee_nav.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include '../includes/employee_sidebar.php'; ?>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="profile.php" class="btn btn-sm btn-outline-secondary">Profile</a>
                        </div>
                    </div>
                </div>
                
                <!-- Welcome Message -->
                <div class="alert alert-info" role="alert">
                    <h4 class="alert-heading">Welcome, <?php echo $_SESSION['name']; ?>!</h4>
                    <p>This is your Social Engineering Awareness Training dashboard. Here you can access your assigned training modules, complete lessons, and take quizzes to test your knowledge.</p>
                    <hr>
                    <p class="mb-0">Remember, staying informed about social engineering threats is crucial for maintaining organizational security.</p>
                </div>
                
                <!-- Training Modules -->
                <h2 class="h4 mt-4 mb-3">Your Training Modules</h2>
                
                <?php if (count($modules) > 0): ?>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php foreach ($modules as $module): ?>
                            <div class="col">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $module['title']; ?></h5>
                                        <p class="card-text"><?php echo substr($module['description'], 0, 100) . (strlen($module['description']) > 100 ? '...' : ''); ?></p>
                                        
                                        <?php if ($module['deadline']): ?>
                                            <p class="card-text"><small class="text-muted">Deadline: <?php echo date('M d, Y', strtotime($module['deadline'])); ?></small></p>
                                        <?php endif; ?>
                                        
                                        <?php if ($module['status'] == 'completed'): ?>
                                            <div class="progress mb-3">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">100%</div>
                                            </div>
                                            <span class="badge bg-success">Completed</span>
                                        <?php elseif ($module['status'] == 'in_progress'): ?>
                                            <div class="progress mb-3">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">50%</div>
                                            </div>
                                            <span class="badge bg-info">In Progress</span>
                                        <?php else: ?>
                                            <div class="progress mb-3">
                                                <div class="progress-bar bg-warning" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                            </div>
                                            <span class="badge bg-warning">Not Started</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer bg-transparent border-top-0">
                                        <a href="module.php?id=<?php echo $module['module_id']; ?>" class="btn btn-primary btn-sm">View Module</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning" role="alert">
                        <p class="mb-0">You don't have any assigned training modules yet. Please contact your administrator.</p>
                    </div>
                <?php endif; ?>
                
                <!-- Social Engineering Tips -->
                <div class="card mt-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Social Engineering Awareness Tips</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-shield-lock text-primary fs-2"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5>Verify Before Trusting</h5>
                                        <p class="mb-0">Always verify the identity of individuals requesting sensitive information, even if they appear to be from within your organization.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-envelope-exclamation text-primary fs-2"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5>Be Cautious with Emails</h5>
                                        <p class="mb-0">Check sender addresses carefully, be wary of unexpected attachments, and hover over links before clicking.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-clock-history text-primary fs-2"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5>Resist Urgency</h5>
                                        <p class="mb-0">Be suspicious of messages creating a sense of urgency or fear. Take time to verify requests through official channels.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
