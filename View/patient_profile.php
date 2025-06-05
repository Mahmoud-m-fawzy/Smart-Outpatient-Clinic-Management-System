<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if patient is logged in
if (!isset($_SESSION['patient_id'])) {
    header('Location: login.php');
    exit();
}

// Include required files
require_once __DIR__ . '/../Model/Patient.php';

// Get patient data
$patientModel = new Patient();
$patient = $patientModel->getPatientById($_SESSION['patient_id']);

// If patient not found, redirect to login
if (!$patient) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Profile - <?php echo htmlspecialchars($patient['FN'] . ' ' . $patient['LN']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/patientprofile.css">
    <style>
        .profile-header {
            background-color: #4e73df;
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .profile-photo {
            width: 150px;
            height: 150px;
            object-fit: cover;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.3);
            border: 2px solid #fff;
        }
        .clinic-logo {
            max-height: 160px;
            max-width: 100%;
            height: auto;
        }
        .profile-card {
            margin-bottom: 2rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: none;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .card-header {
            font-weight: 600;
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
        }
        .info-label {
            font-weight: 600;
            color: #5a5c69;
        }
        .btn-upload {
            position: relative;
            overflow: hidden;
        }
        .btn-upload input[type=file] {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 100%;
            min-height: 100%;
            font-size: 100px;
            text-align: right;
            filter: alpha(opacity=0);
            opacity: 0;
            outline: none;
            background: white;
            cursor: inherit;
            display: block;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .profile-photo {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="profile-header">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between gap-4">
                <div class="d-flex align-items-center gap-4">
                    <!-- Patient Profile Photo -->
                    <div class="position-relative">
                        <?php
                        // Default avatar path
                        $defaultAvatar = '/MVC/assets/img/default-avatar.png';
                        $photoPath = $defaultAvatar;
                        
                        // Debug: Log the patient data and environment
                        error_log('Patient data: ' . print_r($patient, true));
                        error_log('Session data: ' . print_r($_SESSION, true));
                        error_log('Current directory: ' . __DIR__);
                        error_log('Document root: ' . $_SERVER['DOCUMENT_ROOT']);
                        
                        // Check if we have a photo in the patient record
                        if (!empty($patient['photo'])) {
                            $photoFile = $patient['photo'];
                            $uploadDir = __DIR__ . '/../../uploads/patients/';
                            $uploadPath = $uploadDir . $photoFile;
                            
                            error_log('Checking photo at: ' . $uploadPath);
                            
                            if (file_exists($uploadPath)) {
                                $photoPath = '/MVC/uploads/patients/' . $photoFile;
                                error_log('Using patient record photo: ' . $photoPath);
                            } else {
                                error_log('Photo file not found at: ' . $uploadPath);
                            }
                        } 
                        // Check session for recently uploaded photo
                        elseif (!empty($_SESSION['patient_photo'])) {
                            $photoFile = $_SESSION['patient_photo'];
                            $uploadDir = __DIR__ . '/../../uploads/patients/';
                            $uploadPath = $uploadDir . $photoFile;
                            
                            error_log('Checking session photo at: ' . $uploadPath);
                            
                            if (file_exists($uploadPath)) {
                                $photoPath = '/MVC/uploads/patients/' . $photoFile;
                                error_log('Using session photo: ' . $photoPath);
                            } else {
                                error_log('Session photo file not found at: ' . $uploadPath);
                            }
                        }
                        
                        error_log('Final photo path: ' . $photoPath);
                        ?>
                        <img src="<?php echo $photoPath; ?>" 
                             alt="<?php echo htmlspecialchars($patient['FN'] . ' ' . $patient['LN']); ?>" 
                             class="profile-photo">
                        <button class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" 
                                style="width: 32px; height: 32px; padding: 0;" 
                                title="Change Photo"
                                data-bs-toggle="modal" 
                                data-bs-target="#changePhotoModal">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    
                    <div>
                        <h1 class="mb-1"><?php echo htmlspecialchars($patient['FN'] . ' ' . $patient['LN']); ?></h1>
                        <p class="mb-0">Patient ID: <?php echo 'P' . str_pad($patient['id'], 5, '0', STR_PAD_LEFT); ?></p>
                        <div class="mt-2">
                            <button id="printProfile" class="btn btn-light btn-sm" onclick="window.print()" title="Print Profile">
                                <i class="fas fa-print me-1"></i> Print Profile
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Clinic Logo -->
                <img src="images/logo.png" alt="Clinic Logo" class="clinic-logo d-none d-md-block">
            </div>
        </div>
    </div>
    
    <!-- Change Photo Modal -->
    <div class="modal fade" id="changePhotoModal" tabindex="-1" aria-labelledby="changePhotoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePhotoModalLabel">Change Profile Photo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../update_profile_photo.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="patient_id" value="<?php echo $patient['id']; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="profilePhoto" class="form-label">Choose a new photo</label>
                            <input class="form-control" type="file" id="profilePhoto" name="profilePhoto" accept="image/*" required>
                            <div class="form-text">Maximum file size: 5MB. Allowed formats: JPG, PNG, GIF</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload Photo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo htmlspecialchars($_SESSION['success']);
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <div class="row">
            <!-- Personal Information -->
            <div class="col-md-6">
                <div class="card profile-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-2"><span class="info-label">First Name:</span> <?php echo htmlspecialchars($patient['FN']); ?></p>
                                <p class="mb-2"><span class="info-label">Last Name:</span> <?php echo htmlspecialchars($patient['LN']); ?></p>
                                <p class="mb-2"><span class="info-label">Email:</span> <?php echo htmlspecialchars($patient['email']); ?></p>
                                <p class="mb-2"><span class="info-label">Phone:</span> <?php echo htmlspecialchars($patient['phone']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><span class="info-label">Age:</span> <?php echo htmlspecialchars($patient['age']); ?></p>
                                <p class="mb-2"><span class="info-label">Gender:</span> <?php echo ucfirst(htmlspecialchars($patient['gender'])); ?></p>
                                <p class="mb-2"><span class="info-label">Marital Status:</span> <?php echo ucfirst(htmlspecialchars($patient['marital'])); ?></p>
                                <p class="mb-2"><span class="info-label">ID Number:</span> <?php echo htmlspecialchars($patient['idnumber'] ?? 'N/A'); ?></p>
                                <?php if (!empty($patient['NN'])): ?>
                                <p class="mb-2"><span class="info-label">National Number:</span> <?php echo htmlspecialchars($patient['NN']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <p class="mb-2"><span class="info-label">Address:</span> <?php echo htmlspecialchars($patient['address']); ?></p>
                            <p class="mb-0"><span class="info-label">Occupation:</span> <?php echo htmlspecialchars($patient['job']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical Information -->
            <div class="col-md-6">
                <div class="card profile-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-heartbeat me-2"></i>Medical Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Medical History</h6>
                            <p class="text-muted">No medical history recorded yet.</p>
                        </div>
                        <div class="mb-3">
                            <h6>Allergies</h6>
                            <p class="text-muted">No known allergies.</p>
                        </div>
                        <div>
                            <h6>Current Medications</h6>
                            <p class="text-muted">No current medications.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Preview selected image before upload
            const profilePhotoInput = document.getElementById('profilePhoto');
            const profilePhoto = document.querySelector('.profile-photo');

            if (profilePhotoInput && profilePhoto) {
                profilePhotoInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        if (file.size > 5 * 1024 * 1024) { // 5MB limit
                            alert('File size must be less than 5MB');
                            this.value = '';
                            return;
                        }
                        
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            profilePhoto.src = e.target.result;
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
</body>
</html>
