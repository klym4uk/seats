<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'seats_db');

// Application configuration
define('APP_NAME', 'SEATS - Social Engineering Awareness Training System');
define('APP_URL', 'http://localhost/seats');
define('APP_VERSION', '1.0.0');

// Session configuration
define('SESSION_NAME', 'seats_session');
define('SESSION_LIFETIME', 3600); // 1 hour

// Security configuration
define('HASH_COST', 10); // For password_hash()

// File upload configuration
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'mp4']);
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
