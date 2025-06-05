<?php
require_once '../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Center - Your Health, Our Priority</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header Section -->
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
                    <a href="login.php?type=staff" class="dropdown-item"><i class="fas fa-user-nurse"></i> Staff Login</a>
                    <a href="login.php?type=manager" class="dropdown-item hidden-option"><i class="fas fa-user-tie"></i> Manager Login</a>
                </div>
            </div>
            <a href="registration.php" class="book-now-btn">Register</a>
        </div>
    </div>
</header>
    <title>Top Specialized Hospital In Egypt</title>
    <div class="slider-container">
        <!-- Slides -->
        <div class="slide active" style="background-image: url('images/slider/slide1.jpg.jpg');"></div>
        <div class="slide" style="background-image: url('images/slider/slide2.jpg.jpg');"></div>
        <div class="slide" style="background-image: url('images/slider/slide3.jpg.jpg');"></div>
        <div class="slide" style="background-image: url('images/slider/slide4.jpg.jpg');"></div>
        
        <!-- Info Box -->
        <div class="info-box">
            <h1>Top Specialized Hospital In Egypt</h1>
            <p>Book now with the most qualified physicians with international experience in all disciplines</p>
        </div>
        
        <!-- Arrow Navigation -->
        <div class="nav-arrow prev" onclick="changeSlide(-1)">&lt;</div>
        <div class="nav-arrow next" onclick="changeSlide(1)">&gt;</div>
        
        <!-- Bottom Navigation -->
        <div class="slider-navigation">
            <div class="progress-container">
                <div class="nav-item active" onclick="goToSlide(0)"></div>
                <div class="nav-item" onclick="goToSlide(1)"></div>
                <div class="nav-item" onclick="goToSlide(2)"></div>
                <div class="nav-item" onclick="goToSlide(3)"></div>
                
                <div class="nav-label">Book Your Appointment</div>
                <div class="nav-label">Healing Environment</div>
                <div class="nav-label">Centers Of Excellence</div>
                <div class="nav-label">Luxury Design</div>
            </div>
        </div>
    </div>

    <!-- About Us Section -->
    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-video">
                    <iframe width="560" height="315" src="https://www.youtube.com/embed/YOUR_VIDEO_ID" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
                <div class="about-info">
                    <h2>About Us</h2>
                    <div class="title-underline"></div>
                    <h3>Top Specialized Hospital In Egypt 2025</h3>
                    <p>Setting the health and well-being of the patients as a primary goal, we strive for constant development through the utilization of the latest technology. We also try to blend our global healthcare expertise with the authentic Arabian spirit of the Middle East.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section">
        <div class="container">
            <div class="section-header">
                <h2>Our Services</h2>
                <div class="section-underline"></div>
            </div>
            <div class="services-content">
                <p>We offer a comprehensive range of medical services designed to meet all your healthcare needs. Our modern facilities and expert medical staff ensure you receive the highest quality care in a comfortable environment.</p>
                <div class="services-grid">
                    <div class="service-card">
                        <i class="fas fa-heartbeat"></i>
                        <h3>Cardiology</h3>
                        <p>Expert heart care services</p>
                    </div>
                    <div class="service-card">
                        <i class="fas fa-brain"></i>
                        <h3>Neurology</h3>
                        <p>Specialized brain care</p>
                    </div>
                    <div class="service-card">
                        <i class="fas fa-bone"></i>
                        <h3>Orthopedics</h3>
                        <p>Joint and bone treatments</p>
                    </div>
                    <div class="service-card">
                        <i class="fas fa-stethoscope"></i>
                        <h3>General Medicine</h3>
                        <p>Complete health check-ups</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Doctors Section -->
    <section class="doctors-section">
        <div class="container">
            <div class="section-header">
                <h2>Our Expert Doctors</h2>
                <div class="section-underline"></div>
            </div>
            <div class="doctors-content">
                <p>Meet our distinguished medical professionals who are dedicated to providing exceptional healthcare services.</p>
                <div class="featured-doctors">
                    <?php
                    require_once("../Model/Doctor.php");
                    $doctor = new Doctor();
                    $doctors = $doctor->getAllDoctors();
                    $count = 0;
                    foreach ($doctors as $doc):
                        if ($count < 2): // Show only 2 featured doctors
                    ?>
                        <div class="featured-doctor-card">
                            <div class="doctor-image">
                                <img src="<?= !empty($doc['photo']) ? htmlspecialchars($doc['photo']) : 'images/doctor-placeholder.png' ?>" alt="<?= htmlspecialchars($doc['FN'] . ' ' . $doc['LN']) ?>">
                            </div>
                            <div class="doctor-info">
                                <h3 class="doctor-name"><?= htmlspecialchars($doc['FN'] . ' ' . $doc['LN']) ?></h3>
                                <p class="doctor-specialty"><?= htmlspecialchars($doc['title']) ?></p>
                                <div class="doctor-details">
                                    <span class="specialty-tag"><?= htmlspecialchars($doc['specialty']) ?></span>
                                    <span class="gender-tag" data-gender="<?= htmlspecialchars($doc['gender']) ?>"><?= htmlspecialchars($doc['gender']) ?></span>
                                </div>
                                <a href="doctor-profile.php?doctor_id=<?= $doc['id'] ?>" class="view-profile-btn">View Profile</a>
                            </div>
                        </div>
                    <?php
                        $count++;
                        endif;
                    endforeach;
                    ?>
                </div>
                <div class="section-footer">
                    <a href="doctors.php" class="view-all-btn">View All Doctors</a>
                </div>
            </div>
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
    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const navItems = document.querySelectorAll('.nav-item');
        const totalSlides = slides.length;
        
        function showSlide(index) {
            if (index < 0) index = totalSlides - 1;
            if (index >= totalSlides) index = 0;
            
            // Update current slide
            currentSlide = index;
            
            // Update slides
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                if (i === currentSlide) slide.classList.add('active');
            });
            
            // Update navigation items
            navItems.forEach((item, i) => {
                item.classList.remove('active');
                if (i === currentSlide) item.classList.add('active');
            });
        }
        
        function changeSlide(direction) {
            showSlide(currentSlide + direction);
        }
        
        function goToSlide(index) {
            showSlide(index);
        }
        
        // Auto-slide every 5 seconds
        setInterval(() => {
            changeSlide(1);
        }, 5000);
    </script>
    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const section = document.querySelector(this.getAttribute('href'));
                section.scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Active state on scroll
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('section');
            const navLinks = document.querySelectorAll('.nav-link');
            
            let current = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                
                if (pageYOffset >= (sectionTop - 150)) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href').substring(1) === current) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
