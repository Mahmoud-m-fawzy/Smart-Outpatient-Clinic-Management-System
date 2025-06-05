<?php
// Include the Manager model for session check
require_once __DIR__ . '/../Model/Manager.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (Manager::isManagerLoggedIn()) {
    header('Location: /MVC/View/manager_dashboard.php');
    exit();
}

// Get error messages if any
$errors = $_SESSION['login_errors'] ?? [];
unset($_SESSION['login_errors']); // Clear errors after displaying
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { 
            font: 14px sans-serif; 
            background: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .wrapper { 
            width: 360px; 
            padding: 20px; 
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .btn-primary {
            width: 100%;
            padding: 10px;
            position: relative;
        }
        .btn-primary .spinner-border {
            display: none;
            width: 1rem;
            height: 1rem;
            margin-right: 0.5rem;
        }
        .btn-primary.loading .spinner-border {
            display: inline-block;
        }
        .invalid-feedback {
            display: block;
            margin-top: 0.25rem;
            color: #dc3545;
        }
        #errorAlert {
            display: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2 class="text-center mb-4">Manager Login</h2>
        <p class="text-center mb-4">Please fill in your credentials to login.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form id="loginForm" action="/MVC/Controller/ManagerController.php" method="POST">
            <input type="hidden" name="login" value="1">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required>
                <div class="invalid-feedback" id="usernameError"></div>
            </div>    
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
                <div class="invalid-feedback" id="passwordError"></div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" id="loginButton">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    <span class="btn-text">Login</span>
                </button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Clear validation on input
            $('input').on('input', function() {
                $(this).removeClass('is-invalid');
                $(`#${this.id}Error`).text('');
            });
            
            // Handle form submission
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                const $button = $('#loginButton');
                const $buttonText = $button.find('.btn-text');
                const $spinner = $button.find('.spinner-border');
                
                $button.prop('disabled', true);
                $buttonText.text('Logging in...');
                $spinner.show();
                
                // Clear previous errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                
                // Submit the form
                this.submit();
            });
            
            function showError(message) {
                const $alert = $('<div class="alert alert-danger mt-3">').text(message);
                $('.wrapper').prepend($alert);
                setTimeout(() => $alert.fadeOut(1000, () => $alert.remove()), 3000);
            }
            
            // Focus the username field on page load
            $('#username').focus();
        });
    </script>
</body>
</html>