<?php
/**
 * Home Controller
 */
class HomeController {
    public function __construct() {
        // No models needed for the home page
    }
    
    /**
     * Display the home page
     */
    public function index() {
        // Check if the user is logged in
        if (isset($_SESSION['user_id'])) {
            // Redirect to the appropriate dashboard
            if ($_SESSION['role'] === 'admin') {
                redirect('index.php?page=admin-dashboard');
            } else {
                redirect('index.php?page=employee-dashboard');
            }
        }
        
        // Display the home page
        $data = [
            'pageTitle' => 'Welcome to SEATS',
        ];
        
        view('home/index', $data);
    }
    
    /**
     * Display the about page
     */
    public function about() {
        $data = [
            'pageTitle' => 'About SEATS',
        ];
        
        view('home/about', $data);
    }
}

