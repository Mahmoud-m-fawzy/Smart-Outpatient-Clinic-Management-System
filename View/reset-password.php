<?php
session_start();
require_once("D:\wamp64\www\MVC\Model\Patient.php");

// Check if user is verified
if (!isset($_SESSION['verified_user']) || !isset($_SESSION['verified_id'])) {
    header('Location: forgot-password.php');
    exit;
}

$user = $_SESSION['verified_user'];
$verifiedId = $_SESSION['verified_id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($newPassword !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match';
    } else {
        try {
            $patient = new Patient();
            
            // Use the resetPassword function to update both password and plain_password
            $result = $patient->resetPassword($verifiedId, $newPassword);

            if ($result) {
                unset($_SESSION['verified_user']); // Clear the session
                unset($_SESSION['verified_id']); // Clear the verified ID
                $_SESSION['success_message'] = 'Password updated successfully';
                header('Location: login.php');
                exit;
            } else {
                $errors['general'] = 'Failed to update password';
            }
        } catch (Exception $e) {
            $errors['general'] = 'An error occurred: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/reset-password.css">
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
            <a href="contact_us.php">Contact Us</a>
            </nav>
            <div class="header-actions">
            <div class="dropdown">
                <a href="login.php" class="sign-in-btn"><i class="fas fa-user"></i> Sign in <i class="fas fa-chevron-down"></i></a>
                <div class="dropdown-menu">
                    <a href="login.php?type=doctor" class="dropdown-item"><i class="fas fa-user-md"></i> Doctor Login</a>
                    <a href="login.php?type=staff" class="dropdown-item"><i class="fas fa-user-nurse"></i> Staff Login</a>
                    <a href="login.php?type=manager" class="dropdown-item hidden-option"><i class="fas fa-user-tie"></i> Manager Login</a>
                </div>
            </div>
            <a href="registration.php" class="book-now-btn">Register</a>
        </div>
    </div>
</header>
<body>
    <div class="reset-container">
        <div class="reset-box">
            <div class="welcome-section">
                <i class="fas fa-user-circle"></i>
                <h2>Hello, <?= htmlspecialchars($user['FN'] . ' ' . $user['LN']) ?></h2>
                <p>Please set your new password</p>
            </div>

            <?php if (isset($errors['general'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['general']) ?></div>
            <?php endif; ?>

            <form method="POST" class="reset-form">
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="new_password" placeholder="New Password" required>
                </div>

                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                    <?php if (isset($errors['confirm_password'])): ?>
                        <div class="error"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="reset-button">
                    Reset Password
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
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
</body>
</html>