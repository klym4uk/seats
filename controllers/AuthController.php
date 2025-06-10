<?php
/**
 * Authentication Controller
 */
class AuthController {
    private $userModel;
    
    public function __construct() {
        // Load the User model
        require_once 'models/User.php';
        $this->userModel = new User();
    }
    
    /**
     * Display the login form
     */
    public function login() {
        // Check if the user is already logged in
        if (isset($_SESSION['user_id'])) {
            // Redirect to the appropriate dashboard
            if ($_SESSION['role'] === 'admin') {
                redirect('index.php?page=admin-dashboard');
            } else {
                redirect('index.php?page=employee-dashboard');
            }
        }
        
        // Check if the form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the form data
            $email = sanitize($_POST['email']);
            $password = $_POST['password'];
            
            // Validate the form data
            $errors = [];
            
            if (empty($email)) {
                $errors['email'] = 'Email is required';
            }
            
            if (empty($password)) {
                $errors['password'] = 'Password is required';
            }
            
            // If there are no errors, attempt to authenticate the user
            if (empty($errors)) {
                $user = $this->userModel->authenticate($email, $password);
                
                if ($user) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Set flash message
                    $_SESSION['flash_message'] = 'Login successful';
                    $_SESSION['flash_message_type'] = 'success';
                    
                    // Redirect to the appropriate dashboard
                    if ($user['role'] === 'admin') {
                        redirect('index.php?page=admin-dashboard');
                    } else {
                        redirect('index.php?page=employee-dashboard');
                    }
                } else {
                    $errors['login'] = 'Invalid email or password';
                }
            }
            
            // If we get here, there were errors
            $data = [
                'pageTitle' => 'Login',
                'errors' => $errors,
                'email' => $email
            ];
            
            view('auth/login', $data);
        } else {
            // Display the login form
            $data = [
                'pageTitle' => 'Login',
                'errors' => []
            ];
            
            view('auth/login', $data);
        }
    }
    
    /**
     * Log the user out
     */
    public function logout() {
        // Unset all session variables
        $_SESSION = [];
        
        // Destroy the session
        session_destroy();
        
        // Redirect to the login page
        redirect('index.php?page=login');
    }
    
    /**
     * Display the forgot password form
     */
    public function forgotPassword() {
        // Check if the user is already logged in
        if (isset($_SESSION['user_id'])) {
            // Redirect to the appropriate dashboard
            if ($_SESSION['role'] === 'admin') {
                redirect('index.php?page=admin-dashboard');
            } else {
                redirect('index.php?page=employee-dashboard');
            }
        }
        
        // Check if the form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the form data
            $email = sanitize($_POST['email']);
            
            // Validate the form data
            $errors = [];
            
            if (empty($email)) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            } elseif (!$this->userModel->emailExists($email)) {
                $errors['email'] = 'Email not found';
            }
            
            // If there are no errors, send the password reset email
            if (empty($errors)) {
                // In a real application, we would generate a token, store it in the database,
                // and send an email with a link to reset the password.
                // For this demo, we'll just display a success message.
                
                // Set flash message
                $_SESSION['flash_message'] = 'Password reset instructions have been sent to your email';
                $_SESSION['flash_message_type'] = 'success';
                
                // Redirect to the login page
                redirect('index.php?page=login');
            }
            
            // If we get here, there were errors
            $data = [
                'pageTitle' => 'Forgot Password',
                'errors' => $errors,
                'email' => $email
            ];
            
            view('auth/forgot-password', $data);
        } else {
            // Display the forgot password form
            $data = [
                'pageTitle' => 'Forgot Password',
                'errors' => []
            ];
            
            view('auth/forgot-password', $data);
        }
    }
    
    /**
     * Display the register form (admin only)
     */
    public function register() {
        // Check if the user is logged in and is an admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            // Set flash message
            $_SESSION['flash_message'] = 'You do not have permission to access that page';
            $_SESSION['flash_message_type'] = 'danger';
            
            // Redirect to the login page
            redirect('index.php?page=login');
        }
        
        // Check if the form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the form data
            $name = sanitize($_POST['name']);
            $email = sanitize($_POST['email']);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            $role = sanitize($_POST['role']);
            
            // Validate the form data
            $errors = [];
            
            if (empty($name)) {
                $errors['name'] = 'Name is required';
            }
            
            if (empty($email)) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            } elseif ($this->userModel->emailExists($email)) {
                $errors['email'] = 'Email already exists';
            }
            
            if (empty($password)) {
                $errors['password'] = 'Password is required';
            } else {
                // Validate password strength
                $passwordValidation = validatePassword($password);
                
                if (!$passwordValidation['valid']) {
                    $errors['password'] = $passwordValidation['message'];
                }
            }
            
            if ($password !== $confirmPassword) {
                $errors['confirm_password'] = 'Passwords do not match';
            }
            
            if (empty($role) || !in_array($role, ['admin', 'employee'])) {
                $errors['role'] = 'Invalid role';
            }
            
            // If there are no errors, create the user
            if (empty($errors)) {
                $userData = [
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'role' => $role
                ];
                
                $userId = $this->userModel->create($userData);
                
                if ($userId) {
                    // Set flash message
                    $_SESSION['flash_message'] = 'User created successfully';
                    $_SESSION['flash_message_type'] = 'success';
                    
                    // Redirect to the users page
                    redirect('index.php?page=admin-dashboard&action=users');
                } else {
                    $errors['register'] = 'Failed to create user';
                }
            }
            
            // If we get here, there were errors
            $data = [
                'pageTitle' => 'Register User',
                'errors' => $errors,
                'name' => $name,
                'email' => $email,
                'role' => $role
            ];
            
            view('auth/register', $data);
        } else {
            // Display the register form
            $data = [
                'pageTitle' => 'Register User',
                'errors' => []
            ];
            
            view('auth/register', $data);
        }
    }
    
    /**
     * Display the profile page
     */
    public function profile() {
        // Check if the user is logged in
        if (!isset($_SESSION['user_id'])) {
            // Redirect to the login page
            redirect('index.php?page=login');
        }
        
        // Get the user data
        $user = $this->userModel->getById($_SESSION['user_id']);
        
        // Check if the form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the form data
            $name = sanitize($_POST['name']);
            $email = sanitize($_POST['email']);
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            // Validate the form data
            $errors = [];
            
            if (empty($name)) {
                $errors['name'] = 'Name is required';
            }
            
            if (empty($email)) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            } elseif ($email !== $user['email'] && $this->userModel->emailExists($email)) {
                $errors['email'] = 'Email already exists';
            }
            
            // If the user is changing their password
            if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
                if (empty($currentPassword)) {
                    $errors['current_password'] = 'Current password is required';
                } elseif (!password_verify($currentPassword, $user['password'])) {
                    $errors['current_password'] = 'Current password is incorrect';
                }
                
                if (empty($newPassword)) {
                    $errors['new_password'] = 'New password is required';
                } else {
                    // Validate password strength
                    $passwordValidation = validatePassword($newPassword);
                    
                    if (!$passwordValidation['valid']) {
                        $errors['new_password'] = $passwordValidation['message'];
                    }
                }
                
                if ($newPassword !== $confirmPassword) {
                    $errors['confirm_password'] = 'Passwords do not match';
                }
            }
            
            // If there are no errors, update the user
            if (empty($errors)) {
                $userData = [
                    'name' => $name,
                    'email' => $email
                ];
                
                // If the user is changing their password
                if (!empty($newPassword)) {
                    $userData['password'] = $newPassword;
                }
                
                $result = $this->userModel->update($user['id'], $userData);
                
                if ($result) {
                    // Update session variables
                    $_SESSION['name'] = $name;
                    $_SESSION['email'] = $email;
                    
                    // Set flash message
                    $_SESSION['flash_message'] = 'Profile updated successfully';
                    $_SESSION['flash_message_type'] = 'success';
                    
                    // Redirect to the profile page
                    redirect('index.php?page=profile');
                } else {
                    $errors['profile'] = 'Failed to update profile';
                }
            }
            
            // If we get here, there were errors
            $data = [
                'pageTitle' => 'Profile',
                'errors' => $errors,
                'user' => $user
            ];
            
            view('auth/profile', $data);
        } else {
            // Display the profile page
            $data = [
                'pageTitle' => 'Profile',
                'errors' => [],
                'user' => $user
            ];
            
            view('auth/profile', $data);
        }
    }
}

