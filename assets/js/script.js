// Main JavaScript file for SEATS
document.addEventListener('DOMContentLoaded', function() {
    // Enable tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Enable popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    });
    
    // Password strength meter
    const passwordInput = document.getElementById('password');
    const passwordStrength = document.getElementById('password-strength');
    
    if (passwordInput && passwordStrength) {
        passwordInput.addEventListener('input', function() {
            const password = passwordInput.value;
            let strength = 0;
            
            if (password.length >= 8) strength += 1;
            if (password.match(/[a-z]+/)) strength += 1;
            if (password.match(/[A-Z]+/)) strength += 1;
            if (password.match(/[0-9]+/)) strength += 1;
            if (password.match(/[^a-zA-Z0-9]+/)) strength += 1;
            
            switch (strength) {
                case 0:
                case 1:
                    passwordStrength.className = 'progress-bar bg-danger';
                    passwordStrength.style.width = '20%';
                    passwordStrength.textContent = 'Very Weak';
                    break;
                case 2:
                    passwordStrength.className = 'progress-bar bg-warning';
                    passwordStrength.style.width = '40%';
                    passwordStrength.textContent = 'Weak';
                    break;
                case 3:
                    passwordStrength.className = 'progress-bar bg-info';
                    passwordStrength.style.width = '60%';
                    passwordStrength.textContent = 'Medium';
                    break;
                case 4:
                    passwordStrength.className = 'progress-bar bg-primary';
                    passwordStrength.style.width = '80%';
                    passwordStrength.textContent = 'Strong';
                    break;
                case 5:
                    passwordStrength.className = 'progress-bar bg-success';
                    passwordStrength.style.width = '100%';
                    passwordStrength.textContent = 'Very Strong';
                    break;
            }
        });
    }
    
    // Confirm delete modals
    const deleteButtons = document.querySelectorAll('.btn-delete');
    
    if (deleteButtons.length > 0) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const confirmModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                const confirmButton = document.getElementById('confirmDelete');
                const deleteUrl = this.getAttribute('href');
                
                confirmButton.addEventListener('click', function() {
                    window.location.href = deleteUrl;
                });
                
                confirmModal.show();
            });
        });
    }
    
    // Quiz timer
    const quizTimer = document.getElementById('quiz-timer');
    const quizForm = document.getElementById('quiz-form');
    
    if (quizTimer && quizForm) {
        let seconds = 0;
        let minutes = 0;
        
        setInterval(function() {
            seconds++;
            
            if (seconds >= 60) {
                seconds = 0;
                minutes++;
            }
            
            quizTimer.textContent = `Time: ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }, 1000);
    }
});
