<?php
// Include configuration
require_once '../config/config.php';
require_once '../config/database.php';

class Module {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Create module
    public function createModule($data) {
        $sql = "INSERT INTO modules (title, description, created_by, deadline, status) 
                VALUES (:title, :description, :created_by, :deadline, :status)";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':created_by', $data['created_by']);
        $stmt->bindParam(':deadline', $data['deadline']);
        $stmt->bindParam(':status', $data['status']);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    
    // Get module by ID
    public function getModuleById($moduleId) {
        $sql = "SELECT m.*, u.name as creator_name 
                FROM modules m 
                LEFT JOIN users u ON m.created_by = u.user_id 
                WHERE m.module_id = :module_id";
        
        return $this->db->single($sql, [':module_id' => $moduleId]);
    }
    
    // Get all modules
    public function getAllModules() {
        $sql = "SELECT m.*, u.name as creator_name 
                FROM modules m 
                LEFT JOIN users u ON m.created_by = u.user_id 
                ORDER BY m.created_at DESC";
        
        return $this->db->resultSet($sql);
    }
    
    // Get active modules
    public function getActiveModules() {
        $sql = "SELECT m.*, u.name as creator_name 
                FROM modules m 
                LEFT JOIN users u ON m.created_by = u.user_id 
                WHERE m.status = 'active' 
                ORDER BY m.created_at DESC";
        
        return $this->db->resultSet($sql);
    }
    
    // Update module
    public function updateModule($data) {
        $sql = "UPDATE modules 
                SET title = :title, description = :description, deadline = :deadline, status = :status 
                WHERE module_id = :module_id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':deadline', $data['deadline']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':module_id', $data['module_id']);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    // Delete module
    public function deleteModule($moduleId) {
        $sql = "DELETE FROM modules WHERE module_id = :module_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':module_id', $moduleId);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    // Assign module to user
    public function assignModuleToUser($moduleId, $userId) {
        // Check if already assigned
        $checkSql = "SELECT * FROM user_module_progress 
                    WHERE module_id = :module_id AND user_id = :user_id";
        
        $exists = $this->db->single($checkSql, [
            ':module_id' => $moduleId,
            ':user_id' => $userId
        ]);
        
        if ($exists) {
            return true; // Already assigned
        }
        
        $sql = "INSERT INTO user_module_progress (user_id, module_id, status) 
                VALUES (:user_id, :module_id, 'not_started')";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':module_id', $moduleId);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    // Get modules assigned to user
    public function getModulesByUserId($userId) {
        $sql = "SELECT m.*, ump.status, ump.completion_date 
                FROM modules m 
                JOIN user_module_progress ump ON m.module_id = ump.module_id 
                WHERE ump.user_id = :user_id AND m.status = 'active' 
                ORDER BY m.deadline ASC";
        
        return $this->db->resultSet($sql, [':user_id' => $userId]);
    }
    
    // Update module progress
    public function updateModuleProgress($userId, $moduleId, $status) {
        $sql = "UPDATE user_module_progress 
                SET status = :status";
        
        // Add completion date if completed
        if ($status == 'completed') {
            $sql .= ", completion_date = NOW()";
        }
        
        $sql .= " WHERE user_id = :user_id AND module_id = :module_id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':module_id', $moduleId);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    // Get module completion statistics
    public function getModuleCompletionStats($moduleId) {
        $sql = "SELECT 
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress,
                COUNT(CASE WHEN status = 'not_started' THEN 1 END) as not_started,
                COUNT(*) as total
                FROM user_module_progress 
                WHERE module_id = :module_id";
        
        return $this->db->single($sql, [':module_id' => $moduleId]);
    }
}
