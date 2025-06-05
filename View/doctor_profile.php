<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor/TA Profile</title>
    <link rel="stylesheet" href="css/doctor_profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-image">
                <img src="<?php echo isset($profile['image']) ? $profile['image'] : 'View/assets/default-avatar.png'; ?>" alt="Profile Picture">
            </div>
            <div class="profile-info">
                <h1><?php echo isset($profile['name']) ? htmlspecialchars($profile['name']) : 'Name'; ?></h1>
                <div class="profile-details">
                    <div class="detail-item">
                        <i class="fas fa-stethoscope"></i>
                        <span class="label">Specialty:</span>
                        <span class="value"><?php echo isset($profile['specialty']) ? htmlspecialchars($profile['specialty']) : 'Not specified'; ?></span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-graduation-cap"></i>
                        <span class="label">Academic Degree:</span>
                        <span class="value"><?php echo isset($profile['academic_degree']) ? htmlspecialchars($profile['academic_degree']) : 'Not specified'; ?></span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-clock"></i>
                        <span class="label">Years of Experience:</span>
                        <span class="value"><?php echo isset($profile['experience_years']) ? htmlspecialchars($profile['experience_years']) : 'Not specified'; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="profile-content">
            <div class="section">
                <h2>Contact Information</h2>
                <div class="info-grid">
                    <div class="info-item email-item">
                        <i class="fas fa-envelope"></i>
                        <a href="https://mail.google.com/mail/?view=cm&fs=1&to=habibaadel528@gmail.com" target="_blank" class="email-link">
                            habibaadel528@gmail.com
                        </a>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <p><?php echo isset($profile['phone']) ? htmlspecialchars($profile['phone']) : 'Phone'; ?></p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <p><?php echo isset($profile['office']) ? htmlspecialchars($profile['office']) : 'Office Location'; ?></p>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2>Qualifications</h2>
                <div class="qualifications">
                    <?php if(isset($profile['qualifications']) && is_array($profile['qualifications'])): ?>
                        <?php foreach($profile['qualifications'] as $qual): ?>
                            <div class="qualification-item">
                                <h3><?php echo htmlspecialchars($qual['degree']); ?></h3>
                                <p><?php echo htmlspecialchars($qual['institution']); ?></p>
                                <p class="year"><?php echo htmlspecialchars($qual['year']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="section">
                <h2>Office Hours</h2>
                <div class="office-hours">
                    <?php if(isset($profile['office_hours']) && is_array($profile['office_hours'])): ?>
                        <?php foreach($profile['office_hours'] as $day => $hours): ?>
                            <div class="hours-item">
                                <span class="day"><?php echo htmlspecialchars($day); ?></span>
                                <span class="time"><?php echo htmlspecialchars($hours); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="section">
                <h2>Courses</h2>
                <div class="courses">
                    <?php if(isset($profile['courses']) && is_array($profile['courses'])): ?>
                        <?php foreach($profile['courses'] as $course): ?>
                            <div class="course-item">
                                <h3><?php echo htmlspecialchars($course['code']); ?></h3>
                                <p><?php echo htmlspecialchars($course['name']); ?></p>
                                <p class="semester"><?php echo htmlspecialchars($course['semester']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
</body>
</html> 