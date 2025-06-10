<?php
// Set the current page for the navigation
$page = 'login';

// Include the header
require_once 'views/shared/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="card-title text-center mb-4">Login</h2>
                
                <?php if (isset($errors['login'])): ?>
                    <div class="alert alert-danger">
                        <?php echo $errors['login']; ?>
                    </div>
                <?php endif; ?>
                
                <form action="index.php?page=login" method="post" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['email']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password" required>
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['password']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i> Login
                        </button>
                    </div>
                </form>
                
                <div class="mt-3 text-center">
                    <a href="index.php?page=forgot-password" class="text-decoration-none">Forgot your password?</a>
                </div>
            </div>
        </div>
        
        <div class="card mt-4 shadow-sm">
            <div class="card-body p-4">
                <h5 class="card-title">Demo Accounts</h5>
                <p class="card-text">Use these credentials to test the system:</p>
                
                <div class="mb-2">
                    <strong>Admin:</strong><br>
                    Email: admin@example.com<br>
                    Password: Admin123!
                </div>
                
                <div>
                    <strong>Employee:</strong><br>
                    Email: employee@example.com<br>
                    Password: Employee123!
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer
require_once 'views/shared/footer.php';
?>

