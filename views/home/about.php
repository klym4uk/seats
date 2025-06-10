<?php
// Set the current page for the navigation
$page = 'about';

// Include the header
require_once 'views/shared/header.php';
?>

<!-- About Hero Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row py-4">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="fw-bold">About SEATS</h1>
                <p class="lead text-muted">
                    Learn more about the Social Engineering Awareness Training System and how it helps protect organizations.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- About Content Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h2 class="mb-4">What is SEATS?</h2>
                <p>
                    The Social Engineering Awareness Training System (SEATS) is a comprehensive platform designed to educate employees about social engineering threats and provide them with the knowledge and skills to identify and prevent such attacks.
                </p>
                <p>
                    Social engineering attacks target the human element of cybersecurity, exploiting psychological vulnerabilities rather than technical ones. These attacks can take many forms, including phishing, pretexting, baiting, and tailgating, among others.
                </p>
                <p>
                    SEATS addresses this critical vulnerability by providing interactive training modules, assessments, and simulations that help employees recognize and respond appropriately to social engineering attempts.
                </p>
                
                <h2 class="mt-5 mb-4">Our Mission</h2>
                <p>
                    Our mission is to strengthen the human firewall within organizations by empowering employees with the knowledge and skills needed to recognize and resist social engineering attacks.
                </p>
                <p>
                    We believe that effective security awareness training should be engaging, relevant, and continuous. SEATS is designed to make security awareness an integral part of your organization's culture, not just a compliance checkbox.
                </p>
                
                <h2 class="mt-5 mb-4">Key Features</h2>
                <div class="row mt-4">
                    <div class="col-md-6 mb-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-book fa-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4>Comprehensive Modules</h4>
                                <p>
                                    Our training modules cover all aspects of social engineering, from basic concepts to advanced techniques used by attackers.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-tasks fa-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4>Interactive Assessments</h4>
                                <p>
                                    Quizzes and practical assessments help reinforce learning and measure understanding of key concepts.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-chart-line fa-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4>Progress Tracking</h4>
                                <p>
                                    Detailed analytics and reporting features allow administrators to monitor employee progress and identify areas for improvement.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users-cog fa-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4>Administrative Controls</h4>
                                <p>
                                    Administrators can manage users, assign modules, and generate reports to track organizational security awareness.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h2 class="mt-5 mb-4">Why Choose SEATS?</h2>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Engaging Content</h5>
                        <p class="card-text">
                            Our training modules use real-world examples, interactive elements, and clear explanations to keep employees engaged and help them retain information.
                        </p>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Comprehensive Coverage</h5>
                        <p class="card-text">
                            SEATS covers all major types of social engineering attacks, providing employees with a thorough understanding of the threats they may face.
                        </p>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Measurable Results</h5>
                        <p class="card-text">
                            Our reporting and analytics features allow you to track progress, measure effectiveness, and demonstrate the value of your security awareness program.
                        </p>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Continuous Learning</h5>
                        <p class="card-text">
                            SEATS supports ongoing education with regular updates and new content to address emerging threats and reinforce key concepts.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="mb-4">Ready to get started?</h2>
                <p class="lead mb-4">
                    Join the thousands of organizations that have strengthened their security posture with SEATS.
                </p>
                <a href="index.php?page=login" class="btn btn-primary btn-lg px-4">
                    <i class="fas fa-sign-in-alt me-2"></i> Login Now
                </a>
            </div>
        </div>
    </div>
</section>

<?php
// Include the footer
require_once 'views/shared/footer.php';
?>

