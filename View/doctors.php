<?php
require_once("../Model/Doctor.php");

$doctor = new Doctor();
$doctors = $doctor->getAllDoctors();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Doctors</title>
    <link rel="stylesheet" href="css/doctors.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
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
                <a href="doctors.php" class="active">Doctors</a>
                <a href="contact.html">Contact Us</a>
                </nav>
                <div class="header-actions">
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

    <!-- Page Banner -->
    <div class="page-banner">
        <h1>Our Doctors</h1>
        <div class="breadcrumb">
            <a href="/">Homepage</a>
            <span>doctors</span>
        </div>
    </div>

    <!-- Doctors Section -->
    <section class="doctors-section">
        <div class="doctors-grid">
            <?php foreach ($doctors as $doc): ?>
                <div class="doctor-card">
                    <div class="doctor-image">
                        <img src="<?= !empty($doc['photo']) ? htmlspecialchars($doc['photo']) : 'images/doctor-placeholder.png' ?>" alt="<?= htmlspecialchars($doc['FN'] . ' ' . $doc['LN']) ?>">
                    </div>
                    <h3 class="doctor-name"><?= htmlspecialchars($doc['FN'] . ' ' . $doc['LN']) ?></h3>
                    <p class="doctor-specialty"><?= htmlspecialchars($doc['title']) ?></p>
                    <div class="doctor-details">
                        <span class="specialty-tag"><?= htmlspecialchars($doc['specialty']) ?></span>
                        <span class="gender-tag" data-gender="<?= htmlspecialchars($doc['gender']) ?>"><?= htmlspecialchars($doc['gender']) ?></span>                    </div>
                    <div class="doctor-actions">
                        <a href="book-appointment.php?doctor_id=<?= $doc['id'] ?>" class="book-now">Book Now</a>
                        <a href="doctor-profile.php?doctor_id=<?= $doc['id'] ?>" class="personal-page">Personal Page</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

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
