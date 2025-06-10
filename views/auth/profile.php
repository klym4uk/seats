<?php
// Set the current page for the navigation
$page = 'profile';

// Include the header
require_once 'views/shared/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h2 class="card-title mb-0">My Profile</h2>
            </div>
            <div class="card-body p-4">
                <?php if (isset($errors['profile'])): ?>
                    <div class="alert alert-danger">
                        <?php echo $errors['profile']; ?>
                    </div>
                <?php endif; ?>
                
                <form action="index.php?page=profile" method="post" class="needs-validation" novalidate>
                    <div class="row mb-3">
                        <label for="name" class="col-sm-3 col-form-label">Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo $user['name']; ?>" required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['name']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label for="email" class="col-sm-3 col-form-label">Email</label>
                        <div class="col-sm-9">
                            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['email']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label for="role" class="col-sm-3 col-form-label">Role</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="role" value="<?php echo ucfirst($user['role']); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label for="last_login" class="col-sm-3 col-form-label">Last Login</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="last_login" value="<?php echo $user['last_login'] ? formatDate($user['last_login'], 'M j, Y g:i A') : 'Never'; ?>" readonly>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h4 class="mb-3">Change Password</h4>
                    <p class="text-muted mb-3">Leave these fields blank if you don't want to change your password.</p>
                    
                    <div class="row mb-3">
                        <label for="current_password" class="col-sm-3 col-form-label">Current Password</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control <?php echo isset($errors['current_password']) ? 'is-invalid' : ''; ?>" id="current_password" name="current_password">
                            <?php if (isset($errors['current_password'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['current_password']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label for="new_password" class="col-sm-3 col-form-label">New Password</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control <?php echo isset($errors['new_password']) ? 'is-invalid' : ''; ?>" id="new_password" name="new_password" data-password-strength>
                            <?php if (isset($errors['new_password'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['new_password']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label for="confirm_password" class="col-sm-3 col-form-label">Confirm Password</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" id="confirm_password" name="confirm_password">
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['confirm_password']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-9 offset-sm-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer
require_once 'views/shared/footer.php';
?>

