<?php
/**
 * Quiz Model
 */
class Quiz {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get quiz by ID
     * 
     * @param int $id Quiz ID
     * @return array|false Quiz data or false if not found
     */
    public function getById($id) {
        return $this->db->selectOne("SELECT * FROM quizzes WHERE id = ?", [$id]);
    }
    
    /**
     * Get quiz by module ID
     * 
     * @param int $moduleId Module ID
     * @return array|false Quiz data or false if not found
     */
    public function getByModule($moduleId) {
        return $this->db->selectOne("SELECT * FROM quizzes WHERE module_id = ?", [$moduleId]);
    }
    
    /**
     * Get all quizzes
     * 
     * @return array Array of quizzes
     */
    public function getAll() {
        return $this->db->select("
            SELECT q.*, m.title as module_title
            FROM quizzes q
            JOIN modules m ON q.module_id = m.id
            ORDER BY m.title, q.title
        ");
    }
    
    /**
     * Create a new quiz
     * 
     * @param array $data Quiz data
     * @return int|false New quiz ID or false on failure
     */
    public function create($data) {
        return $this->db->insert(
            "INSERT INTO quizzes (module_id, title, description, passing_threshold, cooldown_period) VALUES (?, ?, ?, ?, ?)",
            [
                $data['module_id'],
                $data['title'],
                $data['description'],
                $data['passing_threshold'],
                $data['cooldown_period']
            ]
        );
    }
    
    /**
     * Update a quiz
     * 
     * @param int $id Quiz ID
     * @param array $data Quiz data
     * @return int|false Number of affected rows or false on failure
     */
    public function update($id, $data) {
        $params = [];
        $sql = "UPDATE quizzes SET ";
        
        // Build the SQL query based on the provided data
        if (isset($data['title'])) {
            $sql .= "title = ?, ";
            $params[] = $data['title'];
        }
        
        if (isset($data['description'])) {
            $sql .= "description = ?, ";
            $params[] = $data['description'];
        }
        
        if (isset($data['passing_threshold'])) {
            $sql .= "passing_threshold = ?, ";
            $params[] = $data['passing_threshold'];
        }
        
        if (isset($data['cooldown_period'])) {
            $sql .= "cooldown_period = ?, ";
            $params[] = $data['cooldown_period'];
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
     * Delete a quiz
     * 
     * @param int $id Quiz ID
     * @return int|false Number of affected rows or false on failure
     */
    public function delete($id) {
        return $this->db->delete("DELETE FROM quizzes WHERE id = ?", [$id]);
    }
    
    /**
     * Get questions for a quiz
     * 
     * @param int $quizId Quiz ID
     * @return array Array of questions
     */
    public function getQuestions($quizId) {
        return $this->db->select(
            "SELECT * FROM questions WHERE quiz_id = ? ORDER BY order_number",
            [$quizId]
        );
    }
    
    /**
     * Get answers for a question
     * 
     * @param int $questionId Question ID
     * @return array Array of answers
     */
    public function getAnswers($questionId) {
        return $this->db->select(
            "SELECT * FROM answers WHERE question_id = ?",
            [$questionId]
        );
    }
    
    /**
     * Add a question to a quiz
     * 
     * @param array $data Question data
     * @return int|false New question ID or false on failure
     */
    public function addQuestion($data) {
        // Get the highest order number for the quiz
        $maxOrder = $this->db->selectOne(
            "SELECT MAX(order_number) as max_order FROM questions WHERE quiz_id = ?",
            [$data['quiz_id']]
        );
        
        $orderNumber = $maxOrder ? $maxOrder['max_order'] + 1 : 1;
        
        // Insert the question
        $questionId = $this->db->insert(
            "INSERT INTO questions (quiz_id, question_text, order_number) VALUES (?, ?, ?)",
            [$data['quiz_id'], $data['question_text'], $orderNumber]
        );
        
        if (!$questionId) {
            return false;
        }
        
        // Insert the answers
        foreach ($data['answers'] as $answer) {
            $this->db->insert(
                "INSERT INTO answers (question_id, text, is_correct) VALUES (?, ?, ?)",
                [$questionId, $answer['text'], $answer['is_correct']]
            );
        }
        
        return $questionId;
    }
    
    /**
     * Update a question
     * 
     * @param int $id Question ID
     * @param array $data Question data
     * @return bool True on success, false on failure
     */
    public function updateQuestion($id, $data) {
        // Update the question
        $result = $this->db->update(
            "UPDATE questions SET question_text = ?, updated_at = ? WHERE id = ?",
            [$data['question_text'], date('Y-m-d H:i:s'), $id]
        );
        
        if (!$result) {
            return false;
        }
        
        // Delete existing answers
        $this->db->delete("DELETE FROM answers WHERE question_id = ?", [$id]);
        
        // Insert the new answers
        foreach ($data['answers'] as $answer) {
            $this->db->insert(
                "INSERT INTO answers (question_id, text, is_correct) VALUES (?, ?, ?)",
                [$id, $answer['text'], $answer['is_correct']]
            );
        }
        
        return true;
    }
    
    /**
     * Delete a question
     * 
     * @param int $id Question ID
     * @return int|false Number of affected rows or false on failure
     */
    public function deleteQuestion($id) {
        // Get the question to be deleted
        $question = $this->db->selectOne("SELECT * FROM questions WHERE id = ?", [$id]);
        
        if (!$question) {
            return false;
        }
        
        // Delete the question
        $result = $this->db->delete("DELETE FROM questions WHERE id = ?", [$id]);
        
        if ($result) {
            // Reorder the remaining questions
            $this->reorderQuestions($question['quiz_id']);
        }
        
        return $result;
    }
    
    /**
     * Reorder questions after deletion
     * 
     * @param int $quizId Quiz ID
     * @return void
     */
    private function reorderQuestions($quizId) {
        // Get all questions for the quiz
        $questions = $this->getQuestions($quizId);
        
        // Update order numbers
        foreach ($questions as $index => $question) {
            $orderNumber = $index + 1;
            
            if ($question['order_number'] != $orderNumber) {
                $this->db->update(
                    "UPDATE questions SET order_number = ? WHERE id = ?",
                    [$orderNumber, $question['id']]
                );
            }
        }
    }
    
    /**
     * Move a question up or down in the order
     * 
     * @param int $id Question ID
     * @param string $direction Direction ('up' or 'down')
     * @return bool True on success, false on failure
     */
    public function moveQuestion($id, $direction) {
        // Get the question to be moved
        $question = $this->db->selectOne("SELECT * FROM questions WHERE id = ?", [$id]);
        
        if (!$question) {
            return false;
        }
        
        // Get all questions for the quiz
        $questions = $this->getQuestions($question['quiz_id']);
        
        // Find the current position
        $currentPosition = -1;
        foreach ($questions as $index => $q) {
            if ($q['id'] == $id) {
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
        if ($newPosition < 0 || $newPosition >= count($questions)) {
            return false;
        }
        
        // Swap the order numbers
        $this->db->update(
            "UPDATE questions SET order_number = ? WHERE id = ?",
            [$questions[$newPosition]['order_number'], $question['id']]
        );
        
        $this->db->update(
            "UPDATE questions SET order_number = ? WHERE id = ?",
            [$question['order_number'], $questions[$newPosition]['id']]
        );
        
        return true;
    }
    
    /**
     * Submit a quiz attempt
     * 
     * @param int $quizId Quiz ID
     * @param int $userId User ID
     * @param array $answers User's answers (question_id => answer_id)
     * @return array Result data
     */
    public function submitAttempt($quizId, $userId, $answers) {
        // Get the quiz
        $quiz = $this->getById($quizId);
        
        if (!$quiz) {
            return ['error' => 'Quiz not found'];
        }
        
        // Get the questions
        $questions = $this->getQuestions($quizId);
        
        if (empty($questions)) {
            return ['error' => 'No questions found for this quiz'];
        }
        
        // Calculate the score
        $totalQuestions = count($questions);
        $correctAnswers = 0;
        
        foreach ($questions as $question) {
            if (isset($answers[$question['id']])) {
                // Get the selected answer
                $selectedAnswer = $this->db->selectOne(
                    "SELECT * FROM answers WHERE id = ? AND question_id = ?",
                    [$answers[$question['id']], $question['id']]
                );
                
                if ($selectedAnswer && $selectedAnswer['is_correct']) {
                    $correctAnswers++;
                }
            }
        }
        
        $score = ($correctAnswers / $totalQuestions) * 100;
        $passed = $score >= $quiz['passing_threshold'];
        
        // Get the attempt number
        $lastAttempt = $this->db->selectOne(
            "SELECT MAX(attempt_number) as last_attempt FROM quiz_results WHERE quiz_id = ? AND user_id = ?",
            [$quizId, $userId]
        );
        
        $attemptNumber = $lastAttempt ? $lastAttempt['last_attempt'] + 1 : 1;
        
        // Record the result
        $resultId = $this->db->insert(
            "INSERT INTO quiz_results (user_id, quiz_id, score, passed, attempt_number) VALUES (?, ?, ?, ?, ?)",
            [$userId, $quizId, $score, $passed ? 1 : 0, $attemptNumber]
        );
        
        // Record the answers
        foreach ($answers as $questionId => $answerId) {
            $this->db->insert(
                "INSERT INTO user_question_answers (user_id, question_id, answer_id, quiz_result_id) VALUES (?, ?, ?, ?)",
                [$userId, $questionId, $answerId, $resultId]
            );
        }
        
        // If passed, update the module progress
        if ($passed) {
            $moduleId = $quiz['module_id'];
            
            $this->db->update(
                "UPDATE user_module_progress SET status = ?, completion_date = ? WHERE module_id = ? AND user_id = ?",
                ['completed', date('Y-m-d H:i:s'), $moduleId, $userId]
            );
        }
        
        return [
            'result_id' => $resultId,
            'score' => $score,
            'passed' => $passed,
            'attempt_number' => $attemptNumber,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'passing_threshold' => $quiz['passing_threshold']
        ];
    }
    
    /**
     * Get a user's quiz results
     * 
     * @param int $quizId Quiz ID
     * @param int $userId User ID
     * @return array Array of results
     */
    public function getUserResults($quizId, $userId) {
        return $this->db->select(
            "SELECT * FROM quiz_results WHERE quiz_id = ? AND user_id = ? ORDER BY completed_at DESC",
            [$quizId, $userId]
        );
    }
    
    /**
     * Get a specific quiz result
     * 
     * @param int $resultId Result ID
     * @return array|false Result data or false if not found
     */
    public function getResult($resultId) {
        return $this->db->selectOne("SELECT * FROM quiz_results WHERE id = ?", [$resultId]);
    }
    
    /**
     * Get a user's answers for a specific quiz attempt
     * 
     * @param int $resultId Result ID
     * @return array Array of answers
     */
    public function getUserAnswers($resultId) {
        return $this->db->select("
            SELECT uqa.*, q.question_text, a.text as answer_text, a.is_correct
            FROM user_question_answers uqa
            JOIN questions q ON uqa.question_id = q.id
            JOIN answers a ON uqa.answer_id = a.id
            WHERE uqa.quiz_result_id = ?
            ORDER BY q.order_number
        ", [$resultId]);
    }
    
    /**
     * Check if a user can attempt a quiz
     * 
     * @param int $quizId Quiz ID
     * @param int $userId User ID
     * @return array Result with 'can_attempt' and 'message' keys
     */
    public function canAttempt($quizId, $userId) {
        // Get the quiz
        $quiz = $this->getById($quizId);
        
        if (!$quiz) {
            return [
                'can_attempt' => false,
                'message' => 'Quiz not found'
            ];
        }
        
        // Check if the user has completed all lessons in the module
        $lesson = new Lesson();
        if (!$lesson->allLessonsCompleted($quiz['module_id'], $userId)) {
            return [
                'can_attempt' => false,
                'message' => 'You must complete all lessons in the module before attempting the quiz'
            ];
        }
        
        // Check if the user has already passed the quiz
        $results = $this->getUserResults($quizId, $userId);
        
        foreach ($results as $result) {
            if ($result['passed']) {
                return [
                    'can_attempt' => false,
                    'message' => 'You have already passed this quiz'
                ];
            }
        }
        
        // Check if the user is in a cooldown period
        if (!empty($results)) {
            $lastResult = $results[0]; // Results are ordered by completed_at DESC
            
            if (!$lastResult['passed']) {
                $cooldownPeriod = $quiz['cooldown_period'] * 3600; // Convert hours to seconds
                $lastAttemptTime = strtotime($lastResult['completed_at']);
                $currentTime = time();
                
                if ($currentTime - $lastAttemptTime < $cooldownPeriod) {
                    $nextAttemptTime = date('Y-m-d H:i:s', $lastAttemptTime + $cooldownPeriod);
                    
                    return [
                        'can_attempt' => false,
                        'message' => "You must wait until $nextAttemptTime before attempting this quiz again",
                        'next_attempt_time' => $nextAttemptTime
                    ];
                }
            }
        }
        
        return [
            'can_attempt' => true,
            'message' => 'You can attempt this quiz'
        ];
    }
}

