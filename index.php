<?php
// Include configuration
require_once 'config/config.php';
require_once 'config/database.php';

// Start session
session_start();

// Set cache control headers to prevent back button access
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if user is already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['session_token'])) {
    // Redirect based on role
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/index.php');
    } else {
        header('Location: employee/index.php');
    }
    exit;
}

// Process login form
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    if (!$email) {
        $error = 'Please enter a valid email address.';
    } elseif (empty($password)) {
        $error = 'Please enter your password.';
    } else {
        // Include User class
        require_once 'classes/User.php';
        
        // Attempt login
        $user = new User();
        $userData = $user->login($email, $password);
        
        if ($userData) {
            // Set session variables
            $_SESSION['user_id'] = $userData['user_id'];
            $_SESSION['name'] = $userData['name'];
            $_SESSION['email'] = $userData['email'];
            $_SESSION['role'] = $userData['role'];
            
            // Generate and store session token
            $_SESSION['session_token'] = bin2hex(random_bytes(32));
            $_SESSION['token_time'] = time();
            
            // Redirect based on role
            if ($userData['role'] === 'admin') {
                header('Location: admin/index.php');
            } else {
                header('Location: employee/index.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h1 class="h3 mb-3 fw-normal">SEATS</h1>
                            <p class="text-muted">Social Engineering Awareness Training System</p>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                                <label for="email">Email address</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                <label for="password">Password</label>
                            </div>
                            <div class="d-grid">
                                <button class="btn btn-primary btn-lg" type="submit">Sign in</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <p class="text-muted">&copy; <?php echo date('Y'); ?> SEATS. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

