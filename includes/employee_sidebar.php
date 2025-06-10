<?php
// Employee sidebar
$current_script_path = $_SERVER['SCRIPT_NAME'];
$app_path_segment = parse_url(APP_URL, PHP_URL_PATH);
if ($app_path_segment === null || $app_path_segment === false || $app_path_segment === '/') {
    $app_path_segment = ''; // Handle cases where APP_URL might not have a path or is just '/'
}
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <?php $is_active = str_ends_with($current_script_path, $app_path_segment . '/employee/index.php') || str_ends_with($current_script_path, $app_path_segment . '/employee/'); ?>
                <a class="nav-link <?php echo $is_active ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/employee/index.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <?php $is_active = str_ends_with($current_script_path, $app_path_segment . '/employee/modules.php') || str_ends_with($current_script_path, $app_path_segment . '/employee/module.php'); ?>
                <a class="nav-link <?php echo $is_active ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/employee/modules.php">
                    <i class="bi bi-journal-text"></i> My Modules
                </a>
            </li>
            <li class="nav-item">
                <?php $is_active = str_ends_with($current_script_path, $app_path_segment . '/employee/profile.php'); ?>
                <a class="nav-link <?php echo $is_active ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/employee/profile.php">
                    <i class="bi bi-person-circle"></i> Profile
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Resources</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <?php $is_active = str_ends_with($current_script_path, $app_path_segment . '/employee/resources.php'); ?>
                <a class="nav-link <?php echo $is_active ? 'active' : ''; ?>" href="<?php echo APP_URL; ?>/employee/resources.php">
                    <i class="bi bi-file-earmark-text"></i> Security Resources
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
