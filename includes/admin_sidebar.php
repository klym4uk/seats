<?php
// Admin sidebar
$current_script_path = $_SERVER['SCRIPT_NAME'];
$app_path_segment = parse_url(APP_URL, PHP_URL_PATH); // Should be '/seats'
if ($app_path_segment === '/' || $app_path_segment === null) {
    $app_path_segment = ''; // Adjust if APP_URL is just 'http://localhost'
}
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <?php $is_active = str_ends_with($current_script_path, $app_path_segment . '/admin/index.php') || str_ends_with($current_script_path, $app_path_segment . '/admin/'); ?>
                <a class="nav-link <?php echo $is_active ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/admin/index.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <?php $is_active = str_ends_with($current_script_path, $app_path_segment . '/admin/modules.php'); ?>
                <a class="nav-link <?php echo $is_active ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/admin/modules.php">
                    <i class="bi bi-journal-text"></i> Modules
                </a>
            </li>
            <li class="nav-item">
                <?php $is_active = str_ends_with($current_script_path, $app_path_segment . '/admin/lessons.php'); ?>
                <a class="nav-link <?php echo $is_active ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/admin/lessons.php">
                    <i class="bi bi-book"></i> Lessons
                </a>
            </li>
            <li class="nav-item">
                <?php $is_active = str_ends_with($current_script_path, $app_path_segment . '/admin/quizzes.php'); ?>
                <a class="nav-link <?php echo $is_active ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/admin/quizzes.php">
                    <i class="bi bi-question-circle"></i> Quizzes
                </a>
            </li>
            <li class="nav-item">
                <?php $is_active = str_ends_with($current_script_path, $app_path_segment . '/admin/users.php'); ?>
                <a class="nav-link <?php echo $is_active ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/admin/users.php">
                    <i class="bi bi-people"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <?php $is_active = str_ends_with($current_script_path, $app_path_segment . '/admin/reports.php'); ?>
                <a class="nav-link <?php echo $is_active ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/admin/reports.php">
                    <i class="bi bi-bar-chart"></i> Reports
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>System</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <?php $is_active = str_ends_with($current_script_path, $app_path_segment . '/admin/profile.php'); ?>
                <a class="nav-link <?php echo $is_active ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/admin/profile.php">
                    <i class="bi bi-person-circle"></i> Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo APP_URL; ?>/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>
