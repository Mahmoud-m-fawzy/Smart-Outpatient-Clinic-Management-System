document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registrationForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            let hasError = false;

            // Check password length
            if (password.length < 12) {
                alert('Password must be at least 12 characters long!');
                document.getElementById('password').classList.add('error');
                hasError = true;
            } else {
                document.getElementById('password').classList.remove('error');
            }

            // Check if passwords match
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                document.getElementById('confirmPassword').classList.add('error');
                hasError = true;
            } else {
                document.getElementById('confirmPassword').classList.remove('error');
            }

            // If there are any errors, prevent form submission
            if (hasError) {
                event.preventDefault();
            }
        });

        // Add real-time validation for password length
        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                if (this.value.length < 12) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }
            });
        }

        // Add real-time validation for password matching
        const confirmPasswordInput = document.getElementById('confirmPassword');
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                const password = document.getElementById('password').value;
                if (this.value !== password) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }
            });
        }
    }
}); 