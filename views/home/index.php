<?php
// Set the current page for the navigation
$page = 'home';

// Include the header
require_once 'views/shared/header.php';
?>

<!-- Hero Section -->
<section class="py-5 text-center">
    <div class="container">
        <div class="row py-lg-5">
            <div class="col-lg-8 col-md-10 mx-auto">
                <h1 class="fw-bold">Social Engineering Awareness Training System</h1>
                <p class="lead text-muted">
                    Protect your organization from social engineering attacks through comprehensive training and awareness.
                </p>
                <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-4">
                    <a href="index.php?page=login" class="btn btn-primary btn-lg px-4 gap-3">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </a>
                    <a href="index.php?page=about" class="btn btn-outline-secondary btn-lg px-4">
                        <i class="fas fa-info-circle me-2"></i> Learn More
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4 py-5">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary bg-gradient text-white mb-3 mx-auto" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </div>
                        <h3 class="card-title">Comprehensive Training</h3>
                        <p class="card-text">
                            Learn about various social engineering techniques and how to identify and prevent them through interactive modules.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary bg-gradient text-white mb-3 mx-auto" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-tasks fa-2x"></i>
                        </div>
                        <h3 class="card-title">Interactive Assessments</h3>
                        <p class="card-text">
                            Test your knowledge with quizzes and practical assessments designed to reinforce learning and measure progress.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon bg-primary bg-gradient text-white mb-3 mx-auto" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                        <h3 class="card-title">Progress Tracking</h3>
                        <p class="card-text">
                            Monitor your learning progress and performance with detailed analytics and reporting features.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="display-4 fw-bold text-primary mb-2">
                    <i class="fas fa-users me-2"></i> 1000+
                </div>
                <h4>Employees Trained</h4>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="display-4 fw-bold text-primary mb-2">
                    <i class="fas fa-building me-2"></i> 50+
                </div>
                <h4>Organizations Protected</h4>
            </div>
            <div class="col-md-4">
                <div class="display-4 fw-bold text-primary mb-2">
                    <i class="fas fa-shield-alt me-2"></i> 95%
                </div>
                <h4>Success Rate</h4>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">What Our Users Say</h2>
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <i class="fas fa-quote-left fa-2x text-primary me-3"></i>
                            <div>
                                <h5 class="card-title mb-1">John Smith</h5>
                                <p class="text-muted small">IT Manager, Tech Solutions Inc.</p>
                            </div>
                        </div>
                        <p class="card-text">
                            "SEATS has transformed our security awareness culture. Our employees are now much more vigilant about potential social engineering attacks."
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <i class="fas fa-quote-left fa-2x text-primary me-3"></i>
                            <div>
                                <h5 class="card-title mb-1">Sarah Johnson</h5>
                                <p class="text-muted small">Security Officer, Financial Services Ltd.</p>
                            </div>
                        </div>
                        <p class="card-text">
                            "The interactive modules and real-world examples make the training engaging and effective. We've seen a significant reduction in successful phishing attempts."
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex mb-3">
                            <i class="fas fa-quote-left fa-2x text-primary me-3"></i>
                            <div>
                                <h5 class="card-title mb-1">Michael Brown</h5>
                                <p class="text-muted small">CEO, Secure Solutions</p>
                            </div>
                        </div>
                        <p class="card-text">
                            "SEATS provides comprehensive training with minimal administrative overhead. The reporting features help us identify areas where additional training is needed."
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 text-center">
    <div class="container">
        <div class="p-5 bg-primary text-white rounded">
            <h2 class="fw-bold mb-3">Ready to protect your organization?</h2>
            <p class="lead mb-4">
                Start training your employees today and build a strong defense against social engineering attacks.
            </p>
            <a href="index.php?page=login" class="btn btn-light btn-lg px-4">
                <i class="fas fa-sign-in-alt me-2"></i> Get Started
            </a>
        </div>
    </div>
</section>

<?php
// Include the footer
require_once 'views/shared/footer.php';
?>

