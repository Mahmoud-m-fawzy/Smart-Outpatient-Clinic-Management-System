<?php
// Get any errors or data passed from the controller
$errors = $errors ?? [];
$data = $data ?? [];

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="css/Registration.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/voice-input.css">
    <style>
        /* Toggle Switch Styles */
        .reg-header {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
        }
        
        .reg-sub {
            margin-top: 5px;
        }
        
        .switch-container {
            display: flex;
            align-items: center;
            gap: 8px;
            position: absolute;
            top: 20px;
            right: 20px;
        }
        
        .switch {
            --circle-dim: 1.4em;
            font-size: 17px;
            position: relative;
            display: inline-block;
            width: 3.5em;
            height: 2em;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #f5aeae;
            transition: .4s;
            border-radius: 30px;
        }

        .slider-card {
            position: absolute;
            content: "";
            height: var(--circle-dim);
            width: var(--circle-dim);
            border-radius: 20px;
            left: 0.3em;
            bottom: 0.3em;
            transition: .4s;
            pointer-events: none;
        }

        .slider-card-face {
            position: absolute;
            inset: 0;
            backface-visibility: hidden;
            perspective: 1000px;
            border-radius: 50%;
            transition: .4s transform;
        }

        .slider-card-front {
            background-color: #DC3535;
        }

        .slider-card-back {
            background-color: #379237;
            transform: rotateY(180deg);
        }

        input:checked ~ .slider-card .slider-card-back {
            transform: rotateY(0);
        }

        input:checked ~ .slider-card .slider-card-front {
            transform: rotateY(-180deg);
        }

        input:checked ~ .slider-card {
            transform: translateX(1.5em);
        }

        input:checked ~ .slider {
            background-color: #9ed99c;
        }
        
        .voice-label {
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }
        
        .error {
            background-color: #ffcccc; /* Light red background */
        }
    </style>
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
                    <a href="login.php?type=staff" class="dropdown-item"><i class="fas fa-user-nurse"></i> Staff Login</a>
                    <a href="login.php?type=manager" class="dropdown-item hidden-option"><i class="fas fa-user-tie"></i> Manager Login</a>
                </div>
            </div>
            <a href="registration.php" class="book-now-btn">Register</a>
        </div>
    </div>
</header>
<body>
    <div class="soft-bg">
        <div class="registration-box big-box">
            <div class="reg-header-row">
                <div class="reg-header">
                    <h2>Registration</h2>
                    <div class="reg-sub">Wishing you good health.</div>
                    <div class="switch-container">
                        <span class="voice-label">Fill in voice</span>
                        <label class="switch">
                            <input type="checkbox" id="voiceToggle" >
                            <div class="slider"></div>
                            <div class="slider-card">
                                <div class="slider-card-face slider-card-front"></div>
                                <div class="slider-card-face slider-card-back"></div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <?php if (isset($errors['general'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['general']) ?></div>
            <?php endif; ?>

            <form id="registrationForm" method="POST" action="/MVC/View/process-registration.php">
            <div class="form-row">
                    <div class="form-group">
                        <label for="FN">First Name</label>
                        <input type="text" id="FN" name="FN" value="<?= htmlspecialchars($data['FN'] ?? '') ?>" required>
                        <?php if (isset($errors['FN'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['FN']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="LN">Last Name</label>
                        <input type="text" id="LN" name="LN" value="<?= htmlspecialchars($data['LN'] ?? '') ?>" required>
                        <?php if (isset($errors['LN'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['LN']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($data['phone'] ?? '') ?>" required>
                        <?php if (isset($errors['phone'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['phone']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                        <?php if (isset($errors['confirmPassword'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['confirmPassword']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="age">Age</label>
                        <input type="number" id="age" name="age" min="0" value="<?= htmlspecialchars($data['age'] ?? '') ?>" required>
                        <?php if (isset($errors['age'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['age']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group id-select-group">
                        <label for="idInput">ID / National Number</label>
                        <div class="id-select-input">
                            <select id="idTypeSelect" class="small-select" name="idType">
                                <option value="id" <?= (isset($data['idType']) && $data['idType'] === 'id') ? 'selected' : '' ?>>ID</option>
                                <option value="national" <?= (isset($data['idType']) && $data['idType'] === 'national') ? 'selected' : '' ?>>National Number</option>
                            </select>
                            <input type="text" id="idInput" name="idInput" placeholder="Enter ID or National Number" value="<?= htmlspecialchars($data['idInput'] ?? '') ?>" required>
                        </div>
                        <?php if (isset($data['idType']) && $data['idType'] === 'id' && isset($errors['idnumber'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['idnumber']) ?></div>
                        <?php endif; ?>
                        <?php if (isset($data['idType']) && $data['idType'] === 'national' && isset($errors['NN'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['NN']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($data['address'] ?? '') ?>" required>
                        <?php if (isset($errors['address'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['address']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                    <label for="job">Job</label>
                    <input type="text" id="job" name="job" value="<?= htmlspecialchars($data['job'] ?? '') ?>" required>
                        <?php if (isset($errors['job'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['job']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Gender</label>
                        <div class="segmented-control">
                            <input type="radio" id="male" name="gender" value="male" <?= (isset($data['gender']) && $data['gender'] === 'male') ? 'checked' : '' ?> required>
                            <label for="male">Male</label>
                            <input type="radio" id="female" name="gender" value="female" <?= (isset($data['gender']) && $data['gender'] === 'female') ? 'checked' : '' ?>>
                            <label for="female">Female</label>
                        </div>
                        <?php if (isset($errors['gender'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['gender']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Marital Status</label>
                        <div class="segmented-control">
                            <input type="radio" id="single" name="marital" value="single" <?= (isset($data['marital']) && $data['marital'] === 'single') ? 'checked' : '' ?> required>
                            <label for="single">Single</label>
                            <input type="radio" id="married" name="marital" value="married" <?= (isset($data['marital']) && $data['marital'] === 'married') ? 'checked' : '' ?>>
                            <label for="married">Married</label>
                        </div>
                        <?php if (isset($errors['marital'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['marital']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="divider">
                    <span>OR</span>
                </div>

                <div class="social-login">
                    <button type="button" class="google-btn">
                        <img src="images/12.png" alt="Google">
                    </button>
                    <button type="button" class="facebook-btn">
                        <i class="fab fa-facebook-f"></i>
                    </button>
                </div>

                <div class="already-patient hoverable">
                    <a href="login.php">Already a Patient?</a>
                </div>

                <button type="submit" class="register-btn">Register</button>
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
        <span>All rights reserved for Faculty of Physical Therapy at MSA University 2025</span>
    </div>
    <script src="js/validation.js"></script>
    <script src="js/voice-input.js"></script>
</body>
</html>