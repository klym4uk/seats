<?php
/**
 * Helper functions for the SEATS application
 */

/**
 * Redirect to a URL
 * 
 * @param string $url URL to redirect to
 * @return void
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Sanitize user input
 * 
 * @param string $input Input to sanitize
 * @return string Sanitized input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Format a date
 * 
 * @param string $date Date to format
 * @param string $format Format to use
 * @return string Formatted date
 */
function formatDate($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

/**
 * Check if a string starts with a specific substring
 * 
 * @param string $haystack String to check
 * @param string $needle Substring to check for
 * @return bool True if the string starts with the substring, false otherwise
 */
function startsWith($haystack, $needle) {
    return substr($haystack, 0, strlen($needle)) === $needle;
}

/**
 * Check if a string ends with a specific substring
 * 
 * @param string $haystack String to check
 * @param string $needle Substring to check for
 * @return bool True if the string ends with the substring, false otherwise
 */
function endsWith($haystack, $needle) {
    return substr($haystack, -strlen($needle)) === $needle;
}

/**
 * Get a flash message from the session
 * 
 * @return array|null Flash message or null if none exists
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'text' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_message_type'] ?? 'info'
        ];
        
        // Clear the flash message
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_message_type']);
        
        return $message;
    }
    
    return null;
}

/**
 * Load a view
 * 
 * @param string $view View to load
 * @param array $data Data to pass to the view
 * @return void
 */
function view($view, $data = []) {
    // Extract the data to make it available in the view
    extract($data);
    
    // Get the flash message
    $flashMessage = getFlashMessage();
    
    // Load the view
    require_once __DIR__ . '/../views/' . $view . '.php';
}

/**
 * Validate a password
 * 
 * @param string $password Password to validate
 * @return array Validation result with 'valid' and 'message' keys
 */
function validatePassword($password) {
    // Check length
    if (strlen($password) < 8) {
        return [
            'valid' => false,
            'message' => 'Password must be at least 8 characters long'
        ];
    }
    
    // Check for at least one uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one uppercase letter'
        ];
    }
    
    // Check for at least one lowercase letter
    if (!preg_match('/[a-z]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one lowercase letter'
        ];
    }
    
    // Check for at least one number
    if (!preg_match('/[0-9]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one number'
        ];
    }
    
    // Check for at least one special character
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one special character'
        ];
    }
    
    return [
        'valid' => true,
        'message' => 'Password is valid'
    ];
}

/**
 * Calculate the overall progress for a user
 * 
 * @param int $userId User ID
 * @return int Overall progress percentage
 */
function calculateOverallProgress($userId) {
    // Get the database instance
    $db = Database::getInstance();
    
    // Get the user's assigned modules
    $modules = $db->select("
        SELECT m.*, ump.status as progress_status
        FROM modules m
        JOIN user_module_progress ump ON m.id = ump.module_id
        WHERE ump.user_id = ?
    ", [$userId]);
    
    if (empty($modules)) {
        return 0;
    }
    
    $totalModules = count($modules);
    $completedModules = 0;
    $inProgressModules = 0;
    
    foreach ($modules as $module) {
        if ($module['progress_status'] === 'completed') {
            $completedModules++;
        } elseif ($module['progress_status'] === 'in_progress' || $module['progress_status'] === 'lessons_completed') {
            $inProgressModules++;
            
            // Get the module's lessons
            $lessons = $db->select("
                SELECT l.*
                FROM lessons l
                WHERE l.module_id = ?
            ", [$module['id']]);
            
            if (!empty($lessons)) {
                $totalLessons = count($lessons);
                $completedLessons = 0;
                
                // Get the user's progress on each lesson
                foreach ($lessons as $lesson) {
                    $progress = $db->selectOne("
                        SELECT *
                        FROM user_lesson_progress
                        WHERE lesson_id = ? AND user_id = ?
                    ", [$lesson['id'], $userId]);
                    
                    if ($progress && $progress['status'] === 'completed') {
                        $completedLessons++;
                    }
                }
                
                // Add the lesson progress to the overall progress
                $completedModules += ($completedLessons / $totalLessons) * 0.5;
            }
        }
    }
    
    return round(($completedModules / $totalModules) * 100);
}

/**
 * Get the progress status text
 * 
 * @param string $status Progress status
 * @return string Progress status text
 */
function getProgressStatusText($status) {
    switch ($status) {
        case 'not_started':
            return 'Not Started';
        case 'in_progress':
            return 'In Progress';
        case 'lessons_completed':
            return 'Lessons Completed';
        case 'completed':
            return 'Completed';
        default:
            return 'Unknown';
    }
}

/**
 * Get the progress status class
 * 
 * @param string $status Progress status
 * @return string Progress status class
 */
function getProgressStatusClass($status) {
    switch ($status) {
        case 'not_started':
            return 'danger';
        case 'in_progress':
            return 'warning';
        case 'lessons_completed':
            return 'info';
        case 'completed':
            return 'success';
        default:
            return 'secondary';
    }
}

