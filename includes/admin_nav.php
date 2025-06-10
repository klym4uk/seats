<?php
// Admin navigation bar
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo APP_URL; ?>/admin/index.php">SEATS Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin" aria-controls="navbarAdmin" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarAdmin">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo APP_URL; ?>/admin/index.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo APP_URL; ?>/admin/modules.php">Modules</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo APP_URL; ?>/admin/users.php">Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo APP_URL; ?>/admin/reports.php">Reports</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo $_SESSION['name']; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/profile.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
