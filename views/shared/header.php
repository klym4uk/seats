<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-shield-alt me-2"></i>
                <span>SEATS</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'admin-dashboard' && !isset($_GET['action']) ? 'active' : ''; ?>" href="index.php?page=admin-dashboard">
                                    <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'admin-dashboard' && isset($_GET['action']) && $_GET['action'] === 'users' ? 'active' : ''; ?>" href="index.php?page=admin-dashboard&action=users">
                                    <i class="fas fa-users me-1"></i> Users
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'admin-dashboard' && isset($_GET['action']) && $_GET['action'] === 'modules' ? 'active' : ''; ?>" href="index.php?page=admin-dashboard&action=modules">
                                    <i class="fas fa-book me-1"></i> Modules
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'admin-dashboard' && isset($_GET['action']) && $_GET['action'] === 'reports' ? 'active' : ''; ?>" href="index.php?page=admin-dashboard&action=reports">
                                    <i class="fas fa-chart-bar me-1"></i> Reports
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'employee-dashboard' && !isset($_GET['action']) ? 'active' : ''; ?>" href="index.php?page=employee-dashboard">
                                    <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'employee-dashboard' && isset($_GET['action']) && $_GET['action'] === 'modules' ? 'active' : ''; ?>" href="index.php?page=employee-dashboard&action=modules">
                                    <i class="fas fa-book me-1"></i> My Modules
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'employee-dashboard' && isset($_GET['action']) && $_GET['action'] === 'progress' ? 'active' : ''; ?>" href="index.php?page=employee-dashboard&action=progress">
                                    <i class="fas fa-chart-line me-1"></i> My Progress
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i> <?php echo $_SESSION['name']; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li>
                                    <a class="dropdown-item" href="index.php?page=profile">
                                        <i class="fas fa-id-card me-1"></i> Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="index.php?page=logout">
                                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === 'home' ? 'active' : ''; ?>" href="index.php">
                                <i class="fas fa-home me-1"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === 'about' ? 'active' : ''; ?>" href="index.php?page=about">
                                <i class="fas fa-info-circle me-1"></i> About
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === 'login' ? 'active' : ''; ?>" href="index.php?page=login">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <?php if (isset($flashMessage)): ?>
                <div class="alert alert-<?php echo $flashMessage['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $flashMessage['text']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

