<?php
// Include configuration
require_once '../config/config.php';
require_once '../config/database.php';

class Lesson {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Create lesson
    public function createLesson($data) {
        $sql = "INSERT INTO lessons (module_id, title, content, content_type, order_number) 
                VALUES (:module_id, :title, :content, :content_type, :order_number)";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':module_id', $data['module_id']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':content', $data['content']);
        $stmt->bindParam(':content_type', $data['content_type']);
        $stmt->bindParam(':order_number', $data['order_number']);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    
    // Get lesson by ID
    public function getLessonById($lessonId) {
        $sql = "SELECT * FROM lessons WHERE lesson_id = :lesson_id";
        return $this->db->single($sql, [':lesson_id' => $lessonId]);
    }
    
    // Get lessons by module ID
    public function getLessonsByModuleId($moduleId) {
        $sql = "SELECT * FROM lessons WHERE module_id = :module_id ORDER BY order_number ASC";
        return $this->db->resultSet($sql, [':module_id' => $moduleId]);
    }
    
    // Update lesson
    public function updateLesson($data) {
        $sql = "UPDATE lessons 
                SET title = :title, content = :content, content_type = :content_type, order_number = :order_number 
                WHERE lesson_id = :lesson_id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':content', $data['content']);
        $stmt->bindParam(':content_type', $data['content_type']);
        $stmt->bindParam(':order_number', $data['order_number']);
        $stmt->bindParam(':lesson_id', $data['lesson_id']);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    // Delete lesson
    public function deleteLesson($lessonId) {
        $sql = "DELETE FROM lessons WHERE lesson_id = :lesson_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':lesson_id', $lessonId);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    // Mark lesson as viewed by user
    public function markLessonAsViewed($userId, $lessonId) {
        // Check if progress record exists
        $checkSql = "SELECT * FROM user_lesson_progress 
                    WHERE user_id = :user_id AND lesson_id = :lesson_id";
        
        $exists = $this->db->single($checkSql, [
            ':user_id' => $userId,
            ':lesson_id' => $lessonId
        ]);
        
        if ($exists) {
            // Update existing record
            $sql = "UPDATE user_lesson_progress 
                    SET status = 'viewed' 
                    WHERE user_id = :user_id AND lesson_id = :lesson_id";
        } else {
            // Create new record
            $sql = "INSERT INTO user_lesson_progress (user_id, lesson_id, status) 
                    VALUES (:user_id, :lesson_id, 'viewed')";
        }
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':lesson_id', $lessonId);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    // Mark lesson as completed by user
    public function markLessonAsCompleted($userId, $lessonId) {
        // Check if progress record exists
        $checkSql = "SELECT * FROM user_lesson_progress 
                    WHERE user_id = :user_id AND lesson_id = :lesson_id";
        
        $exists = $this->db->single($checkSql, [
            ':user_id' => $userId,
            ':lesson_id' => $lessonId
        ]);
        
        if ($exists) {
            // Update existing record
            $sql = "UPDATE user_lesson_progress 
                    SET status = 'completed', completion_date = NOW() 
                    WHERE user_id = :user_id AND lesson_id = :lesson_id";
        } else {
            // Create new record
            $sql = "INSERT INTO user_lesson_progress (user_id, lesson_id, status, completion_date) 
                    VALUES (:user_id, :lesson_id, 'completed', NOW())";
        }
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':lesson_id', $lessonId);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    // Get lesson progress for user
    public function getLessonProgressByUser($userId, $lessonId) {
        $sql = "SELECT * FROM user_lesson_progress 
                WHERE user_id = :user_id AND lesson_id = :lesson_id";
        
        return $this->db->single($sql, [
            ':user_id' => $userId,
            ':lesson_id' => $lessonId
        ]);
    }
    
    // Check if all lessons in module are completed by user
    public function areAllLessonsCompletedInModule($userId, $moduleId) {
        $sql = "SELECT COUNT(*) as total_lessons, 
                (SELECT COUNT(*) FROM user_lesson_progress ulp 
                 JOIN lessons l ON ulp.lesson_id = l.lesson_id 
                 WHERE ulp.user_id = :user_id AND l.module_id = :module_id AND ulp.status = 'completed') as completed_lessons 
                FROM lessons 
                WHERE module_id = :module_id";
        
        $result = $this->db->single($sql, [
            ':user_id' => $userId,
            ':module_id' => $moduleId
        ]);
        
        return ($result['total_lessons'] > 0 && $result['total_lessons'] == $result['completed_lessons']);
    }
}
