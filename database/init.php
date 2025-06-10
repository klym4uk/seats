<?php
/**
 * SEATS - Social Engineering Awareness Training System
 * Database initialization script
 */

// Load configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Get database connection
$db = Database::getInstance()->getConnection();

// Create tables
try {
    // Drop existing tables if they exist
    $db->exec('DROP TABLE IF EXISTS user_question_answers');
    $db->exec('DROP TABLE IF EXISTS quiz_results');
    $db->exec('DROP TABLE IF EXISTS user_lesson_progress');
    $db->exec('DROP TABLE IF EXISTS user_module_progress');
    $db->exec('DROP TABLE IF EXISTS answers');
    $db->exec('DROP TABLE IF EXISTS questions');
    $db->exec('DROP TABLE IF EXISTS quizzes');
    $db->exec('DROP TABLE IF EXISTS lessons');
    $db->exec('DROP TABLE IF EXISTS modules');
    $db->exec('DROP TABLE IF EXISTS users');
    
    echo "Existing tables dropped successfully.\n";
    
    // Enable foreign keys
    $db->exec('PRAGMA foreign_keys = ON;');
    
    // Create users table
    $db->exec('
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT "employee",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME
        )
    ');
    
    // Create modules table
    $db->exec('
        CREATE TABLE IF NOT EXISTS modules (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT,
            deadline DATETIME,
            status TEXT NOT NULL DEFAULT "active",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ');
    
    // Create lessons table
    $db->exec('
        CREATE TABLE IF NOT EXISTS lessons (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            module_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            content TEXT NOT NULL,
            order_number INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
        )
    ');
    
    // Create quizzes table
    $db->exec('
        CREATE TABLE IF NOT EXISTS quizzes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            module_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            description TEXT,
            passing_threshold INTEGER NOT NULL DEFAULT 70,
            cooldown_period INTEGER NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
        )
    ');
    
    // Create questions table
    $db->exec('
        CREATE TABLE IF NOT EXISTS questions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            quiz_id INTEGER NOT NULL,
            question_text TEXT NOT NULL,
            order_number INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
        )
    ');
    
    // Create answers table
    $db->exec('
        CREATE TABLE IF NOT EXISTS answers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            question_id INTEGER NOT NULL,
            text TEXT NOT NULL,
            is_correct INTEGER NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
        )
    ');
    
    // Create user_module_progress table
    $db->exec('
        CREATE TABLE IF NOT EXISTS user_module_progress (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            module_id INTEGER NOT NULL,
            status TEXT NOT NULL DEFAULT "not_started",
            completion_date DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
        )
    ');
    
    // Create user_lesson_progress table
    $db->exec('
        CREATE TABLE IF NOT EXISTS user_lesson_progress (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            lesson_id INTEGER NOT NULL,
            status TEXT NOT NULL DEFAULT "not_viewed",
            completion_date DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
        )
    ');
    
    // Create quiz_results table
    $db->exec('
        CREATE TABLE IF NOT EXISTS quiz_results (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            quiz_id INTEGER NOT NULL,
            score INTEGER NOT NULL,
            passed INTEGER NOT NULL DEFAULT 0,
            attempt_number INTEGER NOT NULL DEFAULT 1,
            completed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
        )
    ');
    
    // Create user_question_answers table
    $db->exec('
        CREATE TABLE IF NOT EXISTS user_question_answers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            question_id INTEGER NOT NULL,
            answer_id INTEGER NOT NULL,
            quiz_result_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
            FOREIGN KEY (answer_id) REFERENCES answers(id) ON DELETE CASCADE,
            FOREIGN KEY (quiz_result_id) REFERENCES quiz_results(id) ON DELETE CASCADE
        )
    ');
    
    echo "Database tables created successfully.\n";
    
    // Create admin user
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("
        INSERT INTO users (name, email, password, role) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute(['Admin User', 'admin@example.com', $adminPassword, 'admin']);
    
    echo "Admin user created successfully.\n";
    
    // Create test employee user
    $employeePassword = password_hash('employee123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("
        INSERT INTO users (name, email, password, role) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute(['Employee User', 'employee@example.com', $employeePassword, 'employee']);
    
    echo "Employee user created successfully.\n";
    
    // Create sample module
    $stmt = $db->prepare("
        INSERT INTO modules (title, description, status) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute(['Introduction to Social Engineering', 'Learn the basics of social engineering and how to identify common attacks.', 'active']);
    
    $moduleId = $db->lastInsertId();
    
    echo "Sample module created successfully.\n";
    
    // Create sample lessons
    $lesson1Content = 'Social engineering is the art of manipulating people so they give up confidential information. The types of information these criminals are seeking can vary, but when individuals are targeted the criminals are usually trying to trick you into giving them your passwords or bank information, or access your computer to secretly install malicious software that will give them access to your passwords and bank information as well as giving them control over your computer.';
    
    $stmt = $db->prepare("
        INSERT INTO lessons (module_id, title, content, order_number) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$moduleId, 'What is Social Engineering?', $lesson1Content, 1]);
    
    $lesson2Content = 'Phishing: Phishing attacks are the practice of sending fraudulent communications that appear to come from a reputable source. The goal is to steal sensitive data like credit card and login information, or to install malware on the victim\'s machine.

Pretexting: Pretexting is when one of the parties creates a fabricated scenario to engage the targeted victim and make them release information or perform an action that they normally would not.

Baiting: Baiting is like the real-world Trojan Horse that uses physical media and relies on the curiosity or greed of the victim.

Quid Pro Quo: Quid pro quo attacks promise a benefit in exchange for information. This benefit usually assumes the form of a service, whereas baiting frequently takes the form of a good.';
    
    $stmt = $db->prepare("
        INSERT INTO lessons (module_id, title, content, order_number) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$moduleId, 'Common Social Engineering Attacks', $lesson2Content, 2]);
    
    $lesson3Content = 'Be suspicious of unsolicited phone calls, visits, or email messages from individuals asking about employees or other internal information. If an unknown individual claims to be from a legitimate organization, try to verify his or her identity directly with the company.

Do not provide personal information or information about your organization, including its structure or networks, unless you are certain of a person\'s authority to have the information.

Do not reveal personal or financial information in email, and do not respond to email solicitations for this information. This includes following links sent in email.

Don\'t send sensitive information over the Internet before checking a website\'s security.

Pay attention to the URL of a website. Malicious websites may look identical to a legitimate site, but the URL may use a variation in spelling or a different domain (e.g., .com vs. .net).';
    
    $stmt = $db->prepare("
        INSERT INTO lessons (module_id, title, content, order_number) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$moduleId, 'How to Prevent Social Engineering Attacks', $lesson3Content, 3]);
    
    echo "Sample lessons created successfully.\n";
    
    // Create sample quiz
    $stmt = $db->prepare("
        INSERT INTO quizzes (module_id, title, description, passing_threshold, cooldown_period) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$moduleId, 'Social Engineering Basics Quiz', 'Test your knowledge of social engineering basics.', 70, 1]);
    
    $quizId = $db->lastInsertId();
    
    echo "Sample quiz created successfully.\n";
    
    // Create sample questions and answers
    $stmt = $db->prepare("
        INSERT INTO questions (quiz_id, question_text, order_number) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$quizId, 'What is social engineering?', 1]);
    
    $questionId = $db->lastInsertId();
    
    $stmt = $db->prepare("
        INSERT INTO answers (question_id, text, is_correct) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$questionId, 'The art of manipulating people to give up confidential information', 1]);
    $stmt->execute([$questionId, 'A type of computer virus', 0]);
    $stmt->execute([$questionId, 'A method of encrypting data', 0]);
    $stmt->execute([$questionId, 'A way to secure networks', 0]);
    
    $stmt = $db->prepare("
        INSERT INTO questions (quiz_id, question_text, order_number) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$quizId, 'Which of the following is a common type of social engineering attack?', 2]);
    
    $questionId = $db->lastInsertId();
    
    $stmt = $db->prepare("
        INSERT INTO answers (question_id, text, is_correct) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$questionId, 'Phishing', 1]);
    $stmt->execute([$questionId, 'Defragmentation', 0]);
    $stmt->execute([$questionId, 'Compression', 0]);
    $stmt->execute([$questionId, 'Partitioning', 0]);
    
    $stmt = $db->prepare("
        INSERT INTO questions (quiz_id, question_text, order_number) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$quizId, 'What should you do if you receive an email asking for your password?', 3]);
    
    $questionId = $db->lastInsertId();
    
    $stmt = $db->prepare("
        INSERT INTO answers (question_id, text, is_correct) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$questionId, 'Never provide your password via email', 1]);
    $stmt->execute([$questionId, 'Reply with your password if it looks legitimate', 0]);
    $stmt->execute([$questionId, 'Forward the email to all your colleagues', 0]);
    $stmt->execute([$questionId, 'Change your password and send the new one', 0]);
    
    echo "Sample questions and answers created successfully.\n";
    
    // Assign module to employee
    $stmt = $db->prepare("
        INSERT INTO user_module_progress (user_id, module_id, status) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([2, $moduleId, 'not_started']);
    
    echo "Module assigned to employee successfully.\n";
    
    echo "Database initialization completed successfully.\n";
    
} catch (PDOException $e) {
    die("Database initialization failed: " . $e->getMessage());
}

