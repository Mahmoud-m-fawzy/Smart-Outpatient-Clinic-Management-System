<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get errors and data from session
$errors = $_SESSION['errors'] ?? [];
$data = $_SESSION['data'] ?? [];

// Clear the session data after retrieving it
unset($_SESSION['errors']);
unset($_SESSION['data']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="css\loginn.css">
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
                    <a href="doctor_login.php" class="dropdown-item"><i class="fas fa-user-md"></i> Doctor Login</a>
                    <a href="staff_login.php" class="dropdown-item"><i class="fas fa-user-nurse"></i> Staff Login</a>
                    <a href="manager_login.php" class="dropdown-item hidden-option"><i class="fas fa-user-tie"></i> Manager Login</a>
                </div>
            </div>
            <a href="registration.php" class="book-now-btn">Register</a>
        </div>
    </div>
</header>
<body>
    <div class="main-bg">
        <div class="login-container">
            <h1>Login Patient</h1>
            <h2>Wishing you good health.</h2>
            
            <?php if (isset($errors['general'])): ?>
                <div class="error-message" style="color: #dc3545; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?= htmlspecialchars($errors['general']) ?>
                </div>
            <?php endif; ?>

            <form class="login-form" action="/MVC/View/process-login.php" method="POST">
                <div class="input-group">
                    <i class="fas fa-id-card"></i>
                    <input type="text" name="national_id" placeholder="ID / National Number" value="<?= isset($data['national_id']) ? htmlspecialchars($data['national_id']) : '' ?>" required>
                    <?php if (isset($errors['national_id'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['national_id']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                    <?php if (isset($errors['password'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['password']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="forgot-row">
                    <a href="forgot-password.php" class="forgot-link">Forgot Password?</a>
                </div>
                <button type="submit" class="login-btn">Login</button>
                <div class="divider">
                    <span>OR</span>
                </div>
                <div class="social-login">
                    <button type="button" class="google-btn">
                        <img src="images\12.png" alt="Google">
                    </button>
                    <button type="button" class="facebook-btn">
                        <i class="fab fa-facebook-f"></i>
                    </button>
                </div>
                <div class="patient-link">
                    <a href="registration.php">Need to be a patient?</a>
                </div>                
            </form>
        </div>
        <div class="image-container">
            <img src=images\Stow-Fitness-Center-Chiro-practor-1.jpg alt="Login Image">
        </div>
    </div>
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