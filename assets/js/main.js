/**
 * SEATS - Social Engineering Awareness Training System
 * Main JavaScript file
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Progress circles
    initProgressCircles();
    
    // Quiz functionality
    initQuiz();
    
    // Form validation
    initFormValidation();
});

/**
 * Initialize progress circles
 */
function initProgressCircles() {
    var progressCircles = document.querySelectorAll('.progress-circle');
    
    progressCircles.forEach(function(circle) {
        var value = circle.getAttribute('data-value');
        var radius = circle.querySelector('circle').getAttribute('r');
        var circumference = 2 * Math.PI * radius;
        
        var valueCircle = circle.querySelector('.progress-circle-value');
        var textElement = circle.querySelector('.progress-circle-text');
        
        valueCircle.style.strokeDasharray = circumference;
        valueCircle.style.strokeDashoffset = circumference - (value / 100) * circumference;
        
        if (textElement) {
            textElement.textContent = value + '%';
        }
    });
}

/**
 * Initialize quiz functionality
 */
function initQuiz() {
    var quizForm = document.getElementById('quiz-form');
    
    if (quizForm) {
        // Handle answer selection
        var answerOptions = document.querySelectorAll('.answer-option');
        
        answerOptions.forEach(function(option) {
            option.addEventListener('click', function() {
                var questionId = this.getAttribute('data-question-id');
                var radioInput = this.querySelector('input[type="radio"]');
                
                // Deselect all options for this question
                var questionOptions = document.querySelectorAll('.answer-option[data-question-id="' + questionId + '"]');
                questionOptions.forEach(function(opt) {
                    opt.classList.remove('selected');
                });
                
                // Select this option
                this.classList.add('selected');
                radioInput.checked = true;
            });
        });
        
        // Form submission validation
        quizForm.addEventListener('submit', function(e) {
            var questions = document.querySelectorAll('.question-card');
            var unansweredQuestions = [];
            
            questions.forEach(function(question) {
                var questionId = question.getAttribute('data-question-id');
                var answered = false;
                
                var options = question.querySelectorAll('input[type="radio"]');
                options.forEach(function(option) {
                    if (option.checked) {
                        answered = true;
                    }
                });
                
                if (!answered) {
                    unansweredQuestions.push(questionId);
                }
            });
            
            if (unansweredQuestions.length > 0) {
                e.preventDefault();
                alert('Please answer all questions before submitting the quiz.');
                
                // Scroll to the first unanswered question
                var firstUnanswered = document.querySelector('.question-card[data-question-id="' + unansweredQuestions[0] + '"]');
                if (firstUnanswered) {
                    firstUnanswered.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    }
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    var forms = document.querySelectorAll('.needs-validation');
    
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Password strength meter
    var passwordInputs = document.querySelectorAll('input[type="password"][data-password-strength]');
    
    passwordInputs.forEach(function(input) {
        var strengthMeter = document.createElement('div');
        strengthMeter.className = 'progress mt-2';
        strengthMeter.style.height = '5px';
        
        var strengthBar = document.createElement('div');
        strengthBar.className = 'progress-bar';
        strengthBar.style.width = '0%';
        
        strengthMeter.appendChild(strengthBar);
        input.parentNode.insertBefore(strengthMeter, input.nextSibling);
        
        var strengthText = document.createElement('small');
        strengthText.className = 'form-text text-muted mt-1';
        input.parentNode.insertBefore(strengthText, strengthMeter.nextSibling);
        
        input.addEventListener('input', function() {
            var strength = calculatePasswordStrength(this.value);
            
            strengthBar.style.width = strength.score + '%';
            strengthText.textContent = strength.message;
            
            if (strength.score < 40) {
                strengthBar.className = 'progress-bar bg-danger';
            } else if (strength.score < 70) {
                strengthBar.className = 'progress-bar bg-warning';
            } else {
                strengthBar.className = 'progress-bar bg-success';
            }
        });
    });
}

/**
 * Calculate password strength
 * 
 * @param {string} password Password to check
 * @return {object} Object with score and message
 */
function calculatePasswordStrength(password) {
    var score = 0;
    var message = '';
    
    if (password.length === 0) {
        return { score: 0, message: '' };
    }
    
    // Length check
    if (password.length < 8) {
        message = 'Password is too short';
    } else {
        score += 20;
    }
    
    // Complexity checks
    if (/[A-Z]/.test(password)) {
        score += 20;
    }
    
    if (/[a-z]/.test(password)) {
        score += 20;
    }
    
    if (/[0-9]/.test(password)) {
        score += 20;
    }
    
    if (/[^A-Za-z0-9]/.test(password)) {
        score += 20;
    }
    
    // Set message based on score
    if (score === 100) {
        message = 'Password strength: Strong';
    } else if (score >= 60) {
        message = 'Password strength: Good';
    } else if (score >= 40) {
        message = 'Password strength: Fair';
    } else {
        message = 'Password strength: Weak';
    }
    
    return { score: score, message: message };
}

/**
 * Confirm action with a modal
 * 
 * @param {string} message Message to display
 * @param {function} callback Function to call if confirmed
 */
function confirmAction(message, callback) {
    var confirmModal = document.getElementById('confirm-modal');
    
    if (!confirmModal) {
        // Create modal if it doesn't exist
        confirmModal = document.createElement('div');
        confirmModal.className = 'modal fade';
        confirmModal.id = 'confirm-modal';
        confirmModal.setAttribute('tabindex', '-1');
        confirmModal.setAttribute('aria-hidden', 'true');
        
        confirmModal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Action</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="confirm-message"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirm-button">Confirm</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(confirmModal);
    }
    
    var modal = new bootstrap.Modal(confirmModal);
    var confirmMessage = document.getElementById('confirm-message');
    var confirmButton = document.getElementById('confirm-button');
    
    confirmMessage.textContent = message;
    
    // Remove any existing event listeners
    var newConfirmButton = confirmButton.cloneNode(true);
    confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);
    confirmButton = newConfirmButton;
    
    // Add new event listener
    confirmButton.addEventListener('click', function() {
        modal.hide();
        callback();
    });
    
    modal.show();
}

