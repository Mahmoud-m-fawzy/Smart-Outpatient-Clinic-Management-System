<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get errors and data from session
$errors = $_SESSION['errors'] ?? [];
$data = $_SESSION['data'] ?? [];
$login_error = '';

// Check for login error message
if (isset($_SESSION['login_error'])) {
    $login_error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

// Clear the session data after retrieving it
unset($_SESSION['errors']);
unset($_SESSION['data']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - Medical Appointment System</title>
    <link rel="stylesheet" href="css/doctor_login1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<header class="main-header">
    <div class="header-top">
        <div class="contact-info">
            <i class="fas fa-phone"></i> 16781
        </div>
        <div class="header-social">
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
            <a href="#"><i class="fab fa-facebook-f"></i></a>
        </div>
    </div>
    <div class="header-bottom">
        <img src="images/logo.png" alt="Logo" class="header-logo">
        <nav class="header-nav">
            <a href="index.php">Home</a>
            <a href="#services" class="nav-link">Services</a>
            <a href="doctors.php">Doctors</a>
            <a href="contact.html">Contact Us</a>
        </nav>
        <div class="header-actions">
            <div class="dropdown">
                <a href="login.php" class="sign-in-btn"><i class="fas fa-user"></i> Sign in <i class="fas fa-chevron-down"></i></a>
                <div class="dropdown-menu">
                    <a href="login.php?type=doctor" class="dropdown-item"><i class="fas fa-user-md"></i> Doctor Login</a>
                    <a href="login.php?type=staff" class="dropdown-item active"><i class="fas fa-user-nurse"></i> Staff Login</a>
                    <a href="login.php?type=manager" class="dropdown-item hidden-option"><i class="fas fa-user-tie"></i> Manager Login</a>
                </div>
            </div>
            <a href="registration.php" class="book-now-btn">Register</a>
        </div>
    </div>
</header>
<div class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-user-nurse login-icon"></i>
                <h2>Staff Login</h2>
                <p>Access the medical staff portal</p>
            </div>
            
            <?php if (!empty($login_error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>
            
            <form action="process-login-staff.php" method="post" class="login-form">
                <input type="hidden" name="action" value="staff_login">
                <div class="form-group">
                    <div class="input-with-icon">
                        <i class="fas fa-id-card"></i>
                        <input type="text" 
                               name="id_number" 
                               id="id_number" 
                               placeholder="ID Number" 
                               required 
                               value="<?php echo htmlspecialchars($data['id_number'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               placeholder="Password" 
                               required>
                    </div>
                </div>
                
                <div class="form-options">
                    <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>
                
                <button type="submit" class="login-button">
                    <span>Sign In</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
                
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            </form>
            
            <div class="login-footer">
                <p>Don't have an account? <a href="register.php">Contact Administrator</a></p>
                <p class="back-to-home">
                    <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add animation class to card
    var card = document.querySelector('.login-card');
    if (card) card.classList.add('animate-in');
});
</script>

<footer class="main-footer">
    <div class="footer-main">
        <div class="footer-col about">
            <img src="images/logo.png" alt="Andalusia Hospital" class="footer-logo">
            <p class="footer-goal">
                Committed to your recovery and well-being — combining expert care with the latest in physical therapy
                technology to help you move better, live stronger.
            </p>
            <div class="footer-social">
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
                <a href="#"><i class="fab fa-facebook-f"></i></a>
            </div>
        </div>
        <div class="footer-col site-content">
            <h3>Site Content</h3>
            <div class="footer-underline"></div>
            <div class="footer-links">
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Services</a></li>
                    <li><a href="#">Doctors</a></li>
                </ul>
                <ul>
                    <li><a href="#">Offers</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">Blog Map</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-col contact-info">
            <h3>Contacts Info</h3>
            <div class="footer-underline"></div>
            <ul>
                <li><i class="fas fa-map-marker-alt"></i> 26 July Mehwar Road intersection with Wahat Road, 6th October
                    City. Egypt.</li>
                <li><i class="fas fa-envelope"></i> info@msa.edu.eg</li>
                <li><i class="fas fa-phone"></i> 16672</li>
            </ul>
        </div>
        <div class="footer-col subscribe">
            <h3>Subscribe Now To The Mailing List</h3>
            <form class="subscribe-form">
                <input type="email" placeholder="Enter Your Email" required>
                <button type="submit">Subscribe</button>
            </form>
            <div class="footer-map">
                <iframe
                    src="https://www.google.com/maps?q=26+July+Mehwar+Road+intersection+with+Wahat+Road,+6th+October+City,+Egypt&output=embed"
                    width="100%" height="120" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <span>All rights reserved for Faculty of Physical Therapy at MSA University ©2025</span>
    </div>
</footer>
</body>
</html>
