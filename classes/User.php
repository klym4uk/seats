<?php
// Include configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Register user
    public function register($data) {
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => HASH_COST]);
        
        // Prepare query
        $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
        
        // Prepare statement
        $stmt = $this->db->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $data['password']);
        $stmt->bindParam(':role', $data['role']);
        
        // Execute
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    
    // Login user
    public function login($email, $password) {
        // Prepare query
        $sql = "SELECT * FROM users WHERE email = :email";
        
        // Get user
        $user = $this->db->single($sql, [':email' => $email]);
        
        // Check if user exists
        if ($user) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Update last login
                $this->updateLastLogin($user['user_id']);
                return $user;
            }
        }
        
        return false;
    }
    
    // Update last login
    private function updateLastLogin($userId) {
        $sql = "UPDATE users SET last_login = NOW() WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
    }
    
    // Get user by ID
    public function getUserById($userId) {
        $sql = "SELECT * FROM users WHERE user_id = :user_id";
        return $this->db->single($sql, [':user_id' => $userId]);
    }
    
    // Get all users
    public function getAllUsers() {
        $sql = "SELECT * FROM users ORDER BY name ASC";
        return $this->db->resultSet($sql);
    }
    
    // Update user
    public function updateUser($data) {
        // Check if password is being updated
        if (!empty($data['password'])) {
            $sql = "UPDATE users SET name = :name, email = :email, password = :password, role = :role 
                    WHERE user_id = :user_id";
            
            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT, ['cost' => HASH_COST]);
            
            // Prepare statement
            $stmt = $this->db->prepare($sql);
            
            // Bind parameters
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $data['password']);
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':user_id', $data['user_id']);
        } else {
            $sql = "UPDATE users SET name = :name, email = :email, role = :role 
                    WHERE user_id = :user_id";
            
            // Prepare statement
            $stmt = $this->db->prepare($sql);
            
            // Bind parameters
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':user_id', $data['user_id']);
        }
        
        // Execute
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    // Delete user
    public function deleteUser($userId) {
        $sql = "DELETE FROM users WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    // Check if email exists
    public function emailExists($email, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT * FROM users WHERE email = :email AND user_id != :user_id";
            $params = [':email' => $email, ':user_id' => $excludeId];
        } else {
            $sql = "SELECT * FROM users WHERE email = :email";
            $params = [':email' => $email];
        }
        
        $result = $this->db->single($sql, $params);
        
        return $result ? true : false;
    }
    
    // Change password
    public function changePassword($userId, $newPassword) {
        // Hash password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
        
        $sql = "UPDATE users SET password = :password WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':user_id', $userId);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
