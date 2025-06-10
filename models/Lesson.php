<?php
/**
 * Lesson Model
 */
class Lesson {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get lesson by ID
     * 
     * @param int $id Lesson ID
     * @return array|false Lesson data or false if not found
     */
    public function getById($id) {
        return $this->db->selectOne("SELECT * FROM lessons WHERE id = ?", [$id]);
    }
    
    /**
     * Get lessons by module ID
     * 
     * @param int $moduleId Module ID
     * @return array Array of lessons
     */
    public function getByModule($moduleId) {
        return $this->db->select(
            "SELECT * FROM lessons WHERE module_id = ? ORDER BY order_number",
            [$moduleId]
        );
    }
    
    /**
     * Create a new lesson
     * 
     * @param array $data Lesson data
     * @return int|false New lesson ID or false on failure
     */
    public function create($data) {
        // Get the highest order number for the module
        $maxOrder = $this->db->selectOne(
            "SELECT MAX(order_number) as max_order FROM lessons WHERE module_id = ?",
            [$data['module_id']]
        );
        
        $orderNumber = $maxOrder ? $maxOrder['max_order'] + 1 : 1;
        
        return $this->db->insert(
            "INSERT INTO lessons (module_id, title, content, order_number) VALUES (?, ?, ?, ?)",
            [$data['module_id'], $data['title'], $data['content'], $orderNumber]
        );
    }
    
    /**
     * Update a lesson
     * 
     * @param int $id Lesson ID
     * @param array $data Lesson data
     * @return int|false Number of affected rows or false on failure
     */
    public function update($id, $data) {
        $params = [];
        $sql = "UPDATE lessons SET ";
        
        // Build the SQL query based on the provided data
        if (isset($data['title'])) {
            $sql .= "title = ?, ";
            $params[] = $data['title'];
        }
        
        if (isset($data['content'])) {
            $sql .= "content = ?, ";
            $params[] = $data['content'];
        }
        
        if (isset($data['order_number'])) {
            $sql .= "order_number = ?, ";
            $params[] = $data['order_number'];
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
     * Delete a lesson
     * 
     * @param int $id Lesson ID
     * @return int|false Number of affected rows or false on failure
     */
    public function delete($id) {
        // Get the lesson to be deleted
        $lesson = $this->getById($id);
        
        if (!$lesson) {
            return false;
        }
        
        // Delete the lesson
        $result = $this->db->delete("DELETE FROM lessons WHERE id = ?", [$id]);
        
        if ($result) {
            // Reorder the remaining lessons
            $this->reorderLessons($lesson['module_id']);
        }
        
        return $result;
    }
    
    /**
     * Reorder lessons after deletion
     * 
     * @param int $moduleId Module ID
     * @return void
     */
    private function reorderLessons($moduleId) {
        // Get all lessons for the module
        $lessons = $this->getByModule($moduleId);
        
        // Update order numbers
        foreach ($lessons as $index => $lesson) {
            $orderNumber = $index + 1;
            
            if ($lesson['order_number'] != $orderNumber) {
                $this->update($lesson['id'], ['order_number' => $orderNumber]);
            }
        }
    }
    
    /**
     * Move a lesson up or down in the order
     * 
     * @param int $id Lesson ID
     * @param string $direction Direction ('up' or 'down')
     * @return bool True on success, false on failure
     */
    public function move($id, $direction) {
        // Get the lesson to be moved
        $lesson = $this->getById($id);
        
        if (!$lesson) {
            return false;
        }
        
        // Get all lessons for the module
        $lessons = $this->getByModule($lesson['module_id']);
        
        // Find the current position
        $currentPosition = -1;
        foreach ($lessons as $index => $l) {
            if ($l['id'] == $id) {
                $currentPosition = $index;
                break;
            }
        }
        
        if ($currentPosition === -1) {
            return false;
        }
        
        // Calculate the new position
        $newPosition = $direction === 'up' ? $currentPosition - 1 : $currentPosition + 1;
        
        // Check if the new position is valid
        if ($newPosition < 0 || $newPosition >= count($lessons)) {
            return false;
        }
        
        // Swap the order numbers
        $this->update($lesson['id'], ['order_number' => $lessons[$newPosition]['order_number']]);
        $this->update($lessons[$newPosition]['id'], ['order_number' => $lesson['order_number']]);
        
        return true;
    }
    
    /**
     * Get a user's progress on a lesson
     * 
     * @param int $lessonId Lesson ID
     * @param int $userId User ID
     * @return array|false Progress data or false if not found
     */
    public function getUserProgress($lessonId, $userId) {
        return $this->db->selectOne(
            "SELECT * FROM user_lesson_progress WHERE lesson_id = ? AND user_id = ?",
            [$lessonId, $userId]
        );
    }
    
    /**
     * Update a user's progress on a lesson
     * 
     * @param int $lessonId Lesson ID
     * @param int $userId User ID
     * @param string $status New status
     * @param string $completionDate Completion date (optional)
     * @return int|false Number of affected rows or false on failure
     */
    public function updateUserProgress($lessonId, $userId, $status, $completionDate = null) {
        // Check if progress record exists
        $progress = $this->getUserProgress($lessonId, $userId);
        
        if ($progress) {
            // Update existing record
            $params = [$status];
            
            $sql = "UPDATE user_lesson_progress SET status = ?";
            
            if ($completionDate || $status === 'completed') {
                $sql .= ", completion_date = ?";
                $params[] = $completionDate ?: date('Y-m-d H:i:s');
            }
            
            $sql .= ", updated_at = ? WHERE lesson_id = ? AND user_id = ?";
            $params[] = date('Y-m-d H:i:s');
            $params[] = $lessonId;
            $params[] = $userId;
            
            return $this->db->update($sql, $params);
        } else {
            // Create new record
            $params = [$userId, $lessonId, $status];
            
            $sql = "INSERT INTO user_lesson_progress (user_id, lesson_id, status";
            
            if ($completionDate || $status === 'completed') {
                $sql .= ", completion_date";
                $params[] = $completionDate ?: date('Y-m-d H:i:s');
            }
            
            $sql .= ") VALUES (?, ?, ?";
            
            if ($completionDate || $status === 'completed') {
                $sql .= ", ?";
            }
            
            $sql .= ")";
            
            return $this->db->insert($sql, $params);
        }
    }
    
    /**
     * Check if all lessons in a module are completed by a user
     * 
     * @param int $moduleId Module ID
     * @param int $userId User ID
     * @return bool True if all lessons are completed, false otherwise
     */
    public function allLessonsCompleted($moduleId, $userId) {
        // Get all lessons for the module
        $lessons = $this->getByModule($moduleId);
        
        if (empty($lessons)) {
            return false;
        }
        
        // Check if all lessons are completed
        foreach ($lessons as $lesson) {
            $progress = $this->getUserProgress($lesson['id'], $userId);
            
            if (!$progress || $progress['status'] !== 'completed') {
                return false;
            }
        }
        
        return true;
    }
}

