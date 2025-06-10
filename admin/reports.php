<?php
// Include configuration
require_once '../config/config.php';
require_once '../config/database.php';

class Report {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Get overall system statistics
    public function getSystemStatistics() {
        $stats = [];
        
        // Total users
        $userSql = "SELECT COUNT(*) as total FROM users WHERE role = 'employee'";
        $stats['total_users'] = $this->db->single($userSql)['total'];
        
        // Total modules
        $moduleSql = "SELECT COUNT(*) as total FROM modules";
        $stats['total_modules'] = $this->db->single($moduleSql)['total'];
        
        // Total quizzes
        $quizSql = "SELECT COUNT(*) as total FROM quizzes";
        $stats['total_quizzes'] = $this->db->single($quizSql)['total'];
        
        // Total quiz attempts
        $attemptSql = "SELECT COUNT(*) as total FROM quiz_results";
        $stats['total_attempts'] = $this->db->single($attemptSql)['total'];
        
        // Average quiz score
        $scoreSql = "SELECT AVG(score) as average FROM quiz_results";
        $stats['average_score'] = $this->db->single($scoreSql)['average'];
        
        // Module completion rate
        $completionSql = "SELECT 
                          COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                          COUNT(*) as total
                          FROM user_module_progress";
        $completion = $this->db->single($completionSql);
        $stats['completion_rate'] = $completion['total'] > 0 ? 
                                   round(($completion['completed'] / $completion['total']) * 100, 2) : 0;
        
        return $stats;
    }
    
    // Get module completion report
    public function getModuleCompletionReport() {
        $sql = "SELECT 
                m.module_id, m.title,
                COUNT(CASE WHEN ump.status = 'completed' THEN 1 END) as completed,
                COUNT(CASE WHEN ump.status = 'in_progress' THEN 1 END) as in_progress,
                COUNT(CASE WHEN ump.status = 'not_started' THEN 1 END) as not_started,
                COUNT(ump.user_id) as total_assigned
                FROM modules m
                LEFT JOIN user_module_progress ump ON m.module_id = ump.module_id
                GROUP BY m.module_id
                ORDER BY m.created_at DESC";
        
        return $this->db->resultSet($sql);
    }
    
    // Get quiz performance report
    public function getQuizPerformanceReport() {
        $sql = "SELECT 
                q.quiz_id, q.title, m.title as module_title,
                COUNT(qr.result_id) as total_attempts,
                COUNT(DISTINCT qr.user_id) as total_users,
                COUNT(CASE WHEN qr.passed = 1 THEN 1 END) as passed_attempts,
                AVG(qr.score) as average_score,
                AVG(qr.completion_time) as average_time
                FROM quizzes q
                JOIN modules m ON q.module_id = m.module_id
                LEFT JOIN quiz_results qr ON q.quiz_id = qr.quiz_id
                GROUP BY q.quiz_id
                ORDER BY m.created_at DESC";
        
        return $this->db->resultSet($sql);
    }
    
    // Get user progress report
    public function getUserProgressReport() {
        $sql = "SELECT 
                u.user_id, u.name, u.email,
                COUNT(DISTINCT ump.module_id) as assigned_modules,
                COUNT(DISTINCT CASE WHEN ump.status = 'completed' THEN ump.module_id END) as completed_modules,
                COUNT(DISTINCT qr.quiz_id) as attempted_quizzes,
                COUNT(DISTINCT CASE WHEN qr.passed = 1 THEN qr.quiz_id END) as passed_quizzes,
                AVG(qr.score) as average_score
                FROM users u
                LEFT JOIN user_module_progress ump ON u.user_id = ump.user_id
                LEFT JOIN quiz_results qr ON u.user_id = qr.user_id
                WHERE u.role = 'employee'
                GROUP BY u.user_id
                ORDER BY u.name ASC";
        
        return $this->db->resultSet($sql);
    }
    
    // Get detailed user progress report
    public function getDetailedUserProgressReport($userId) {
        $result = [];
        
        // User info
        $userSql = "SELECT user_id, name, email, last_login FROM users WHERE user_id = :user_id";
        $result['user'] = $this->db->single($userSql, [':user_id' => $userId]);
        
        // Module progress
        $moduleSql = "SELECT 
                     m.module_id, m.title, m.description, m.deadline,
                     ump.status, ump.completion_date
                     FROM modules m
                     JOIN user_module_progress ump ON m.module_id = ump.module_id
                     WHERE ump.user_id = :user_id
                     ORDER BY m.deadline ASC";
        $result['modules'] = $this->db->resultSet($moduleSql, [':user_id' => $userId]);
        
        // Quiz results
        $quizSql = "SELECT 
                   qr.result_id, qr.quiz_id, q.title as quiz_title, m.title as module_title,
                   qr.score, qr.passed, qr.attempt_number, qr.completion_time, qr.completed_at
                   FROM quiz_results qr
                   JOIN quizzes q ON qr.quiz_id = q.quiz_id
                   JOIN modules m ON q.module_id = m.module_id
                   WHERE qr.user_id = :user_id
                   ORDER BY qr.completed_at DESC";
        $result['quizzes'] = $this->db->resultSet($quizSql, [':user_id' => $userId]);
        
        return $result;
    }
    
    // Generate PDF report
    public function generatePdfReport($type, $id = null) {
        // Implementation would depend on PDF library
        // This is a placeholder for the actual implementation
        return "Report generated: " . $type . ($id ? " for ID: " . $id : "");
    }
}
