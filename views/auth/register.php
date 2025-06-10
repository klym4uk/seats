<?php
// Set the current page for the navigation
$page = 'admin-dashboard';

// Include the header
require_once 'views/shared/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-title mb-0">Register New User</h2>
                <a href="index.php?page=admin-dashboard&action=users" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Users
                </a>
            </div>
            <div class="card-body p-4">
                <?php if (isset($errors['register'])): ?>
                    <div class="alert alert-danger">
                        <?php echo $errors['register']; ?>
                    </div>
                <?php endif; ?>
                
                <form action="index.php?page=register" method="post" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo isset($name) ? $name : ''; ?>" required>
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback">
                                <?php echo $errors['name']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback">
                                <?php echo $errors['email']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select <?php echo isset($errors['role']) ? 'is-invalid' : ''; ?>" id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="admin" <?php echo (isset($role) && $role === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="employee" <?php echo (isset($role) && $role === 'employee') ? 'selected' : ''; ?>>Employee</option>
                        </select>
                        <?php if (isset($errors['role'])): ?>
                            <div class="invalid-feedback">
                                <?php echo $errors['role']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password" data-password-strength required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback">
                                <?php echo $errors['password']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" id="confirm_password" name="confirm_password" required>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback">
                                <?php echo $errors['confirm_password']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i> Register User
                        </button>
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

