<?php
// Set the current page for the navigation
$page = 'forgot-password';

// Include the header
require_once 'views/shared/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="card-title text-center mb-4">Forgot Password</h2>
                
                <p class="text-muted mb-4">
                    Enter your email address and we'll send you instructions to reset your password.
                </p>
                
                <form action="index.php?page=forgot-password" method="post" class="needs-validation" novalidate>
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
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i> Send Reset Link
                        </button>
                    </div>
                </form>
                
                <div class="mt-3 text-center">
                    <a href="index.php?page=login" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-1"></i> Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer
require_once 'views/shared/footer.php';
?>

