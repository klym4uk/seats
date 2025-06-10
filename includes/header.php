<?php
// Header file to be included in all pages
session_start();

// Include configuration
require_once __DIR__ . '/../config/config.php';

// Set cache control headers to prevent back button access
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check for valid session token
if (isset($_SESSION['user_id'])) {
    // Check if session token exists
    if (!isset($_SESSION['session_token']) || !isset($_SESSION['token_time'])) {
        // Invalid session, redirect to logout
        header('Location: ' . APP_URL . '/logout.php');
        exit;
    }
    
    // Check session timeout (optional, set to 2 hours)
    $session_timeout = 7200; // 2 hours in seconds
    if (time() - $_SESSION['token_time'] > $session_timeout) {
        // Session expired, redirect to logout
        header('Location: ' . APP_URL . '/logout.php');
        exit;
    }
    
    // Update token time
    $_SESSION['token_time'] = time();
}

// Check for flash messages
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';

// Clear flash messages
unset($_SESSION['error']);
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

