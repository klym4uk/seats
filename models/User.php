<?php
/**
 * User Model
 */
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get user by ID
     * 
     * @param int $id User ID
     * @return array|false User data or false if not found
     */
    public function getById($id) {
        return $this->db->selectOne("SELECT * FROM users WHERE id = ?", [$id]);
    }
    
    /**
     * Get user by email
     * 
     * @param string $email User email
     * @return array|false User data or false if not found
     */
    public function getByEmail($email) {
        return $this->db->selectOne("SELECT * FROM users WHERE email = ?", [$email]);
    }
    
    /**
     * Get all users
     * 
     * @return array Array of users
     */
    public function getAll() {
        return $this->db->select("SELECT * FROM users ORDER BY name");
    }
    
    /**
     * Create a new user
     * 
     * @param array $data User data
     * @return int|false New user ID or false on failure
     */
    public function create($data) {
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        return $this->db->insert(
            "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)",
            [$data['name'], $data['email'], $data['password'], $data['role']]
        );
    }
    
    /**
     * Update a user
     * 
     * @param int $id User ID
     * @param array $data User data
     * @return int|false Number of affected rows or false on failure
     */
    public function update($id, $data) {
        $params = [];
        $sql = "UPDATE users SET ";
        
        // Build the SQL query based on the provided data
        if (isset($data['name'])) {
            $sql .= "name = ?, ";
            $params[] = $data['name'];
        }
        
        if (isset($data['email'])) {
            $sql .= "email = ?, ";
            $params[] = $data['email'];
        }
        
        if (isset($data['password'])) {
            $sql .= "password = ?, ";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (isset($data['role'])) {
            $sql .= "role = ?, ";
            $params[] = $data['role'];
        }
        
        if (isset($data['last_login'])) {
            $sql .= "last_login = ?, ";
            $params[] = $data['last_login'];
        }
        
        // Remove trailing comma and space
        $sql = rtrim($sql, ", ");
        
        // Add WHERE clause
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        return $this->db->update($sql, $params);
    }
    
    /**
     * Delete a user
     * 
     * @param int $id User ID
     * @return int|false Number of affected rows or false on failure
     */
    public function delete($id) {
        return $this->db->delete("DELETE FROM users WHERE id = ?", [$id]);
    }
    
    /**
     * Authenticate a user
     * 
     * @param string $email User email
     * @param string $password User password
     * @return array|false User data or false if authentication fails
     */
    public function authenticate($email, $password) {
        $user = $this->getByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            // Update last login time
            $this->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
            
            return $user;
        }
        
        return false;
    }
    
    /**
     * Check if a user exists with the given email
     * 
     * @param string $email User email
     * @return bool True if user exists, false otherwise
     */
    public function emailExists($email) {
        $user = $this->getByEmail($email);
        return $user !== false;
    }
}

