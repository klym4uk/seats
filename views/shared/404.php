<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found - <?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo APP_URL; ?>/assets/css/style.css" rel="stylesheet">
    
    <style>
        .error-template {
            padding: 40px 15px;
            text-align: center;
        }
        .error-actions {
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .error-actions .btn {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="error-template">
                    <h1>Oops!</h1>
                    <h2>404 Not Found</h2>
                    <div class="error-details mb-4">
                        Sorry, the page you requested could not be found.
                    </div>
                    <div class="error-actions">
                        <a href="<?php echo APP_URL; ?>" class="btn btn-primary">
                            <i class="bi bi-house"></i> Take Me Home
                        </a>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <a href="<?php echo APP_URL; ?>?page=admin-dashboard" class="btn btn-secondary">
                                    <i class="bi bi-speedometer"></i> Go to Dashboard
                                </a>
                            <?php else: ?>
                                <a href="<?php echo APP_URL; ?>?page=employee-dashboard" class="btn btn-secondary">
                                    <i class="bi bi-speedometer"></i> Go to Dashboard
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

