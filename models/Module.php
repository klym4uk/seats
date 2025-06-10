<?php
/**
 * Module Model
 */
class Module {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get module by ID
     * 
     * @param int $id Module ID
     * @return array|false Module data or false if not found
     */
    public function getById($id) {
        return $this->db->selectOne("SELECT * FROM modules WHERE id = ?", [$id]);
    }
    
    /**
     * Get all modules
     * 
     * @param string $status Filter by status (optional)
     * @return array Array of modules
     */
    public function getAll($status = null) {
        if ($status) {
            return $this->db->select("SELECT * FROM modules WHERE status = ? ORDER BY title", [$status]);
        }
        
        return $this->db->select("SELECT * FROM modules ORDER BY title");
    }
    
    /**
     * Create a new module
     * 
     * @param array $data Module data
     * @return int|false New module ID or false on failure
     */
    public function create($data) {
        return $this->db->insert(
            "INSERT INTO modules (title, description, deadline, status) VALUES (?, ?, ?, ?)",
            [$data['title'], $data['description'], $data['deadline'], $data['status']]
        );
    }
    
    /**
     * Update a module
     * 
     * @param int $id Module ID
     * @param array $data Module data
     * @return int|false Number of affected rows or false on failure
     */
    public function update($id, $data) {
        $params = [];
        $sql = "UPDATE modules SET ";
        
        // Build the SQL query based on the provided data
        if (isset($data['title'])) {
            $sql .= "title = ?, ";
            $params[] = $data['title'];
        }
        
        if (isset($data['description'])) {
            $sql .= "description = ?, ";
            $params[] = $data['description'];
        }
        
        if (isset($data['deadline'])) {
            $sql .= "deadline = ?, ";
            $params[] = $data['deadline'];
        }
        
        if (isset($data['status'])) {
            $sql .= "status = ?, ";
            $params[] = $data['status'];
        }
        
        // Add updated_at timestamp
        $sql .= "updated_at = ? ";
        $params[] = date('Y-m-d H:i:s');
        
        // Add WHERE clause
        $sql .= "WHERE id = ?";
        $params[] = $id;
        
        return $this->db->update($sql, $params);
    }
    
    /**
     * Delete a module
     * 
     * @param int $id Module ID
     * @return int|false Number of affected rows or false on failure
     */
    public function delete($id) {
        return $this->db->delete("DELETE FROM modules WHERE id = ?", [$id]);
    }
    
    /**
     * Get modules assigned to a user
     * 
     * @param int $userId User ID
     * @param string $status Filter by status (optional)
     * @return array Array of modules
     */
    public function getByUser($userId, $status = null) {
        $sql = "
            SELECT m.*, ump.status as progress_status, ump.completion_date
            FROM modules m
            JOIN user_module_progress ump ON m.id = ump.module_id
            WHERE ump.user_id = ?
        ";
        
        $params = [$userId];
        
        if ($status) {
            $sql .= " AND ump.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY m.title";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Assign a module to a user
     * 
     * @param int $moduleId Module ID
     * @param int $userId User ID
     * @return int|false New assignment ID or false on failure
     */
    public function assignToUser($moduleId, $userId) {
        // Check if already assigned
        $existing = $this->db->selectOne(
            "SELECT * FROM user_module_progress WHERE module_id = ? AND user_id = ?",
            [$moduleId, $userId]
        );
        
        if ($existing) {
            return false; // Already assigned
        }
        
        return $this->db->insert(
            "INSERT INTO user_module_progress (module_id, user_id, status) VALUES (?, ?, ?)",
            [$moduleId, $userId, 'not_started']
        );
    }
    
    /**
     * Unassign a module from a user
     * 
     * @param int $moduleId Module ID
     * @param int $userId User ID
     * @return int|false Number of affected rows or false on failure
     */
    public function unassignFromUser($moduleId, $userId) {
        return $this->db->delete(
            "DELETE FROM user_module_progress WHERE module_id = ? AND user_id = ?",
            [$moduleId, $userId]
        );
    }
    
    /**
     * Update a user's progress on a module
     * 
     * @param int $moduleId Module ID
     * @param int $userId User ID
     * @param string $status New status
     * @param string $completionDate Completion date (optional)
     * @return int|false Number of affected rows or false on failure
     */
    public function updateUserProgress($moduleId, $userId, $status, $completionDate = null) {
        $params = [$status];
        
        $sql = "UPDATE user_module_progress SET status = ?";
        
        if ($completionDate || $status === 'completed') {
            $sql .= ", completion_date = ?";
            $params[] = $completionDate ?: date('Y-m-d H:i:s');
        }
        
        $sql .= ", updated_at = ? WHERE module_id = ? AND user_id = ?";
        $params[] = date('Y-m-d H:i:s');
        $params[] = $moduleId;
        $params[] = $userId;
        
        return $this->db->update($sql, $params);
    }
    
    /**
     * Get users assigned to a module
     * 
     * @param int $moduleId Module ID
     * @return array Array of users
     */
    public function getAssignedUsers($moduleId) {
        return $this->db->select("
            SELECT u.*, ump.status as progress_status, ump.completion_date
            FROM users u
            JOIN user_module_progress ump ON u.id = ump.user_id
            WHERE ump.module_id = ?
            ORDER BY u.name
        ", [$moduleId]);
    }
    
    /**
     * Get users not assigned to a module
     * 
     * @param int $moduleId Module ID
     * @return array Array of users
     */
    public function getUnassignedUsers($moduleId) {
        return $this->db->select("
            SELECT u.*
            FROM users u
            WHERE u.id NOT IN (
                SELECT user_id FROM user_module_progress WHERE module_id = ?
            )
            ORDER BY u.name
        ", [$moduleId]);
    }
}

