<?php
// Include header
require_once '../includes/header.php';

// Check if user is logged in and is an employee
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../index.php');
    exit;
}

// Include necessary classes
require_once '../classes/Quiz.php';
require_once '../classes/Module.php';

// Initialize classes
$quizObj = new Quiz();
$moduleObj = new Module();

// Get result ID from URL
$resultId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get result details
$result = $quizObj->getQuizResultById($resultId);

if (!$result || $result['user_id'] != $_SESSION['user_id']) {
    // Result not found or doesn't belong to user
    $_SESSION['error'] = 'Quiz result not found.';
    header('Location: index.php');
    exit;
}

// Get user's answers
$userAnswers = $quizObj->getUserAnswersForResult($resultId);

// Get module details
$moduleId = $result['module_id'];
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include '../includes/employee_sidebar.php'; ?>
        
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="module.php?id=<?php echo $moduleId; ?>"><?php echo $result['module_title']; ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Quiz Results</li>
                </ol>
            </nav>
            
            <!-- Result Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Quiz Results: <?php echo $result['quiz_title']; ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <?php if ($result['passed']): ?>
                        <span class="badge bg-success p-2">Passed</span>
                    <?php else: ?>
                        <span class="badge bg-danger p-2">Failed</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Result Summary -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="card-title">Result Summary</h5>
                            <p class="card-text">
                                <strong>Score:</strong> <?php echo $result['score']; ?>%<br>
                                <strong>Passing Threshold:</strong> <?php echo $result['passing_threshold']; ?>%<br>
                                <strong>Attempt:</strong> <?php echo $result['attempt_number']; ?><br>
                                <strong>Completion Time:</strong> <?php echo gmdate("i:s", $result['completion_time']); ?> (mm:ss)<br>
                                <strong>Completed On:</strong> <?php echo date('F d, Y H:i', strtotime($result['completed_at'])); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <div class="position-relative d-inline-block">
                                    <canvas id="scoreChart" width="200" height="200"></canvas>
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <h2 class="mb-0"><?php echo $result['score']; ?>%</h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Detailed Results -->
            <h3 class="h4 mb-3">Detailed Results</h3>
            
            <?php if (count($userAnswers) > 0): ?>
                <?php 
                $questionGroups = [];
                foreach ($userAnswers as $answer) {
                    $questionId = $answer['question_id'];
                    if (!isset($questionGroups[$questionId])) {
                        $questionGroups[$questionId] = [
                            'question_text' => $answer['question_text'],
                            'explanation' => $answer['explanation'],
                            'answer' => $answer
                        ];
                    }
                }
                ?>
                
                <?php foreach ($questionGroups as $index => $group): ?>
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header <?php echo $group['answer']['is_correct'] ? 'bg-success text-white' : 'bg-danger text-white'; ?>">
                            <h5 class="card-title mb-0">
                                <?php echo $group['answer']['is_correct'] ? '<i class="bi bi-check-circle-fill"></i>' : '<i class="bi bi-x-circle-fill"></i>'; ?>
                                Question <?php echo $index + 1; ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><?php echo $group['question_text']; ?></p>
                            
                            <div class="mb-3">
                                <strong>Your Answer:</strong>
                                <p class="<?php echo $group['answer']['is_correct'] ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo $group['answer']['answer_text']; ?>
                                    <?php echo $group['answer']['is_correct'] ? '<i class="bi bi-check-circle-fill"></i>' : '<i class="bi bi-x-circle-fill"></i>'; ?>
                                </p>
                            </div>
                            
                            <?php if (!$group['answer']['is_correct'] && $group['explanation']): ?>
                                <div class="alert alert-info" role="alert">
                                    <strong>Explanation:</strong> <?php echo $group['explanation']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
                    <a href="module.php?id=<?php echo $moduleId; ?>" class="btn btn-primary">Back to Module</a>
                </div>
            <?php else: ?>
                <div class="alert alert-warning" role="alert">
                    No detailed results found for this quiz attempt.
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
                    <a href="module.php?id=<?php echo $moduleId; ?>" class="btn btn-primary">Back to Module</a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Chart.js Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('scoreChart').getContext('2d');
        
        // Score data
        const score = <?php echo $result['score']; ?>;
        const passingThreshold = <?php echo $result['passing_threshold']; ?>;
        const remaining = 100 - score;
        
        // Create chart
        const scoreChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Score', 'Remaining'],
                datasets: [{
                    data: [score, remaining],
                    backgroundColor: [
                        score >= passingThreshold ? 'rgba(40, 167, 69, 0.8)' : 'rgba(220, 53, 69, 0.8)',
                        'rgba(233, 236, 239, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '75%',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                }
            }
        });
    });
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>
