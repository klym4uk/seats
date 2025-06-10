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

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../employee/index.php');
    exit;
}

// Include necessary files
require_once '../classes/User.php';
require_once '../classes/Module.php';
require_once '../classes/Report.php';

// Initialize classes
$userObj = new User();
$moduleObj = new Module();
$reportObj = new Report();

// Get system statistics
$stats = $reportObj->getSystemStatistics();

// Get recent modules
$modules = $moduleObj->getAllModules();
$recentModules = array_slice($modules, 0, 5);

// Get user progress report
$userProgress = $reportObj->getUserProgressReport();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <?php include '../includes/admin_nav.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include '../includes/admin_sidebar.php'; ?>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="reports.php" class="btn btn-sm btn-outline-secondary">Reports</a>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Employees</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_users']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-people-fill fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Modules</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_modules']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-journal-text fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Completion Rate</div>
                                        <div class="row no-gutters align-items-center">
                                            <div class="col-auto">
                                                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?php echo $stats['completion_rate']; ?>%</div>
                                            </div>
                                            <div class="col">
                                                <div class="progress progress-sm mr-2">
                                                    <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $stats['completion_rate']; ?>%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-clipboard-check fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Average Score</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo round($stats['average_score'], 1); ?>%</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-graph-up fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Content Row -->
                <div class="row">
                    <!-- Recent Modules -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Modules</h6>
                                <a href="modules.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if (count($recentModules) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Deadline</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recentModules as $module): ?>
                                                    <tr>
                                                        <td><a href="module_detail.php?id=<?php echo $module['module_id']; ?>"><?php echo $module['title']; ?></a></td>
                                                        <td><?php echo $module['deadline'] ? date('M d, Y', strtotime($module['deadline'])) : 'No deadline'; ?></td>
                                                        <td>
                                                            <?php if ($module['status'] == 'active'): ?>
                                                                <span class="badge bg-success">Active</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Inactive</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-center">No modules found. <a href="modules.php?action=add">Create one</a>.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Completion Chart -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Module Completion</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="completionChart" width="100%" height="50"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Employee Progress -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Employee Progress</h6>
                                <a href="reports.php?type=user" class="btn btn-sm btn-primary">Detailed Report</a>
                            </div>
                            <div class="card-body">
                                <?php if (count($userProgress) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Assigned Modules</th>
                                                    <th>Completed Modules</th>
                                                    <th>Average Score</th>
                                                    <th>Progress</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($userProgress as $user): ?>
                                                    <tr>
                                                        <td><?php echo $user['name']; ?></td>
                                                        <td><?php echo $user['email']; ?></td>
                                                        <td><?php echo $user['assigned_modules']; ?></td>
                                                        <td><?php echo $user['completed_modules']; ?></td>
                                                        <td><?php echo $user['average_score'] ? round($user['average_score'], 1) . '%' : 'N/A'; ?></td>
                                                        <td>
                                                            <?php 
                                                            $progress = $user['assigned_modules'] > 0 ? 
                                                                        round(($user['completed_modules'] / $user['assigned_modules']) * 100) : 0;
                                                            ?>
                                                            <div class="progress">
                                                                <div class="progress-bar" role="progressbar" style="width: <?php echo $progress; ?>%"
                                                                    aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100">
                                                                    <?php echo $progress; ?>%
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-center">No employee data found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js initialization -->
    <script>
        // Sample data for chart - in a real implementation, this would come from the database
        const moduleData = {
            labels: ['Module 1', 'Module 2', 'Module 3', 'Module 4', 'Module 5'],
            datasets: [{
                label: 'Completion Rate (%)',
                data: [85, 72, 63, 45, 92],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(153, 102, 255, 0.2)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        };
        
        // Chart configuration
        const config = {
            type: 'bar',
            data: moduleData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        };
        
        // Initialize chart
        const completionChart = new Chart(
            document.getElementById('completionChart'),
            config
        );
    </script>
</body>
</html>
