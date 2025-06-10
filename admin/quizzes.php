<?php
// Include configuration
require_once '../config/config.php';
require_once '../config/database.php';

class Quiz {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Create quiz
    public function createQuiz($data) {
        $sql = "INSERT INTO quizzes (module_id, title, description, passing_threshold, cooldown_period) 
                VALUES (:module_id, :title, :description, :passing_threshold, :cooldown_period)";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':module_id', $data['module_id']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':passing_threshold', $data['passing_threshold']);
        $stmt->bindParam(':cooldown_period', $data['cooldown_period']);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    
    // Get quiz by ID
    public function getQuizById($quizId) {
        $sql = "SELECT * FROM quizzes WHERE quiz_id = :quiz_id";
        return $this->db->single($sql, [':quiz_id' => $quizId]);
    }
    
    // Get quiz by module ID
    public function getQuizByModuleId($moduleId) {
        $sql = "SELECT * FROM quizzes WHERE module_id = :module_id";
        return $this->db->single($sql, [':module_id' => $moduleId]);
    }
    
    // Update quiz
    public function updateQuiz($data) {
        $sql = "UPDATE quizzes 
                SET title = :title, description = :description, 
                    passing_threshold = :passing_threshold, cooldown_period = :cooldown_period 
                WHERE quiz_id = :quiz_id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':passing_threshold', $data['passing_threshold']);
        $stmt->bindParam(':cooldown_period', $data['cooldown_period']);
        $stmt->bindParam(':quiz_id', $data['quiz_id']);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    // Delete quiz
    public function deleteQuiz($quizId) {
        $sql = "DELETE FROM quizzes WHERE quiz_id = :quiz_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':quiz_id', $quizId);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    // Add question to quiz
    public function addQuestion($data) {
        $sql = "INSERT INTO questions (quiz_id, question_text, explanation) 
                VALUES (:quiz_id, :question_text, :explanation)";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':quiz_id', $data['quiz_id']);
        $stmt->bindParam(':question_text', $data['question_text']);
        $stmt->bindParam(':explanation', $data['explanation']);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    
    // Add answer to question
    public function addAnswer($data) {
        $sql = "INSERT INTO answers (question_id, answer_text, is_correct) 
                VALUES (:question_id, :answer_text, :is_correct)";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':question_id', $data['question_id']);
        $stmt->bindParam(':answer_text', $data['answer_text']);
        $stmt->bindParam(':is_correct', $data['is_correct']);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    
    // Get questions by quiz ID
    public function getQuestionsByQuizId($quizId) {
        $sql = "SELECT * FROM questions WHERE quiz_id = :quiz_id";
        return $this->db->resultSet($sql, [':quiz_id' => $quizId]);
    }
    
    // Get answers by question ID
    public function getAnswersByQuestionId($questionId) {
        $sql = "SELECT * FROM answers WHERE question_id = :question_id";
        return $this->db->resultSet($sql, [':question_id' => $questionId]);
    }
    
    // Submit quiz
    public function submitQuiz($userId, $quizId, $answers, $completionTime) {
        try {
            $this->db->beginTransaction();
            
            // Get quiz details
            $quiz = $this->getQuizById($quizId);
            
            // Calculate score
            $totalQuestions = count($answers);
            $correctAnswers = 0;
            
            // Get attempt number
            $attemptSql = "SELECT COUNT(*) as attempts FROM quiz_results 
                          WHERE user_id = :user_id AND quiz_id = :quiz_id";
            $attemptResult = $this->db->single($attemptSql, [
                ':user_id' => $userId,
                ':quiz_id' => $quizId
            ]);
            $attemptNumber = $attemptResult['attempts'] + 1;
            
            // Insert quiz result
            $resultSql = "INSERT INTO quiz_results 
                         (user_id, quiz_id, score, passed, attempt_number, completion_time) 
                         VALUES (:user_id, :quiz_id, :score, :passed, :attempt_number, :completion_time)";
            
            $stmt = $this->db->prepare($resultSql);
            
            // Set initial score to 0, will update after processing answers
            $score = 0;
            $passed = false;
            
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':quiz_id', $quizId);
            $stmt->bindParam(':score', $score);
            $stmt->bindParam(':passed', $passed);
            $stmt->bindParam(':attempt_number', $attemptNumber);
            $stmt->bindParam(':completion_time', $completionTime);
            
            $stmt->execute();
            $resultId = $this->db->lastInsertId();
            
            // Process each answer
            foreach ($answers as $questionId => $answerId) {
                // Get correct answer
                $answerSql = "SELECT * FROM answers 
                             WHERE question_id = :question_id AND answer_id = :answer_id";
                $answer = $this->db->single($answerSql, [
                    ':question_id' => $questionId,
                    ':answer_id' => $answerId
                ]);
                
                $isCorrect = $answer && $answer['is_correct'] ? 1 : 0;
                
                if ($isCorrect) {
                    $correctAnswers++;
                }
                
                // Save user's answer
                $userAnswerSql = "INSERT INTO user_question_answers 
                                 (result_id, question_id, answer_id, is_correct) 
                                 VALUES (:result_id, :question_id, :answer_id, :is_correct)";
                
                $answerStmt = $this->db->prepare($userAnswerSql);
                
                $answerStmt->bindParam(':result_id', $resultId);
                $answerStmt->bindParam(':question_id', $questionId);
                $answerStmt->bindParam(':answer_id', $answerId);
                $answerStmt->bindParam(':is_correct', $isCorrect);
                
                $answerStmt->execute();
            }
            
            // Calculate final score
            $score = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100) : 0;
            $passed = $score >= $quiz['passing_threshold'];
            
            // Update quiz result with final score
            $updateSql = "UPDATE quiz_results 
                         SET score = :score, passed = :passed 
                         WHERE result_id = :result_id";
            
            $updateStmt = $this->db->prepare($updateSql);
            
            $updateStmt->bindParam(':score', $score);
            $updateStmt->bindParam(':passed', $passed);
            $updateStmt->bindParam(':result_id', $resultId);
            
            $updateStmt->execute();
            
            // If passed, update module progress
            if ($passed) {
                // Get module ID
                $moduleSql = "SELECT module_id FROM quizzes WHERE quiz_id = :quiz_id";
                $module = $this->db->single($moduleSql, [':quiz_id' => $quizId]);
                
                if ($module) {
                    $moduleProgressSql = "UPDATE user_module_progress 
                                         SET status = 'completed', completion_date = NOW() 
                                         WHERE user_id = :user_id AND module_id = :module_id";
                    
                    $moduleStmt = $this->db->prepare($moduleProgressSql);
                    
                    $moduleStmt->bindParam(':user_id', $userId);
                    $moduleStmt->bindParam(':module_id', $module['module_id']);
                    
                    $moduleStmt->execute();
                }
            }
            
            $this->db->commit();
            
            return [
                'result_id' => $resultId,
                'score' => $score,
                'passed' => $passed,
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    // Get quiz result by ID
    public function getQuizResultById($resultId) {
        $sql = "SELECT qr.*, q.title as quiz_title, q.passing_threshold, 
                m.title as module_title, m.module_id, u.name as user_name 
                FROM quiz_results qr 
                JOIN quizzes q ON qr.quiz_id = q.quiz_id 
                JOIN modules m ON q.module_id = m.module_id 
                JOIN users u ON qr.user_id = u.user_id 
                WHERE qr.result_id = :result_id";
        
        return $this->db->single($sql, [':result_id' => $resultId]);
    }
    
    // Get user's quiz results
    public function getUserQuizResults($userId, $quizId) {
        $sql = "SELECT * FROM quiz_results 
                WHERE user_id = :user_id AND quiz_id = :quiz_id 
                ORDER BY completed_at DESC";
        
        return $this->db->resultSet($sql, [
            ':user_id' => $userId,
            ':quiz_id' => $quizId
        ]);
    }
    
    // Get user's latest quiz result
    public function getUserLatestQuizResult($userId, $quizId) {
        $sql = "SELECT * FROM quiz_results 
                WHERE user_id = :user_id AND quiz_id = :quiz_id 
                ORDER BY completed_at DESC LIMIT 1";
        
        return $this->db->single($sql, [
            ':user_id' => $userId,
            ':quiz_id' => $quizId
        ]);
    }
    
    // Check if user can take quiz
    public function canUserTakeQuiz($userId, $quizId) {
        // Get quiz details
        $quiz = $this->getQuizById($quizId);
        
        // Get latest attempt
        $latestResult = $this->getUserLatestQuizResult($userId, $quizId);
        
        // If no previous attempts or passed, user can take quiz
        if (!$latestResult || $latestResult['passed']) {
            return true;
        }
        
        // Check cooldown period
        $completedAt = strtotime($latestResult['completed_at']);
        $cooldownSeconds = $quiz['cooldown_period'] * 3600; // Convert hours to seconds
        $currentTime = time();
        
        return ($currentTime - $completedAt) >= $cooldownSeconds;
    }
    
    // Get user's answers for a quiz result
    public function getUserAnswersForResult($resultId) {
        $sql = "SELECT uqa.*, q.question_text, q.explanation, a.answer_text 
                FROM user_question_answers uqa 
                JOIN questions q ON uqa.question_id = q.question_id 
                JOIN answers a ON uqa.answer_id = a.answer_id 
                WHERE uqa.result_id = :result_id";
        
        return $this->db->resultSet($sql, [':result_id' => $resultId]);
    }
    
    // Get quiz statistics
    public function getQuizStatistics($quizId) {
        $sql = "SELECT 
                COUNT(*) as total_attempts,
                COUNT(DISTINCT user_id) as total_users,
                COUNT(CASE WHEN passed = 1 THEN 1 END) as passed_attempts,
                AVG(score) as average_score,
                AVG(completion_time) as average_time
                FROM quiz_results 
                WHERE quiz_id = :quiz_id";
        
        return $this->db->single($sql, [':quiz_id' => $quizId]);
    }
}
