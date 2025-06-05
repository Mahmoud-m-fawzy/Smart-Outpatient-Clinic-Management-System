<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    $_SESSION['error'] = 'You must be logged in to update your profile photo';
    header('Location: /MVC/login.php');
    exit();
}

// Check if file was uploaded
if (!isset($_FILES['profilePhoto']) || $_FILES['profilePhoto']['error'] !== UPLOAD_ERR_OK) {
    $error_message = 'No file was uploaded';
    if (isset($_FILES['profilePhoto']['error'])) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file is too large (server limit)',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file is too large',
            UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was selected',
            UPLOAD_ERR_NO_TMP_DIR => 'Server configuration error',
            UPLOAD_ERR_CANT_WRITE => 'Failed to save file',
            UPLOAD_ERR_EXTENSION => 'File upload was stopped',
        ];
        $error_message = $error_messages[$_FILES['profilePhoto']['error']] ?? 'Upload error';
    }
    
    $_SESSION['error'] = 'Upload failed: ' . $error_message;
    header('Location: /MVC/patient_profile.php');
    exit();
}

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
$file_type = mime_content_type($_FILES['profilePhoto']['tmp_name']);
if (!in_array($file_type, $allowed_types)) {
    $_SESSION['error'] = 'Only JPG, PNG, and GIF files are allowed';
    header('Location: /MVC/patient_profile.php');
    exit();
}

// Validate file size (5MB max)
$max_size = 5 * 1024 * 1024; // 5MB
if ($_FILES['profilePhoto']['size'] > $max_size) {
    $_SESSION['error'] = 'File size must be less than 5MB';
    header('Location: /MVC/patient_profile.php');
    exit();
}

try {
        // Define uploads directory
    $base_dir = __DIR__;
    $upload_dir = $base_dir . '/uploads/patients/';
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            throw new Exception('Failed to create upload directory at: ' . $upload_dir);
        }
    }
    
    // Check if directory is writable
    if (!is_writable($upload_dir)) {
        throw new Exception('Upload directory is not writable: ' . $upload_dir);
    }

    // Generate unique filename
    $file_extension = strtolower(pathinfo($_FILES['profilePhoto']['name'], PATHINFO_EXTENSION));
    $new_filename = 'patient_' . $_SESSION['patient_id'] . '_' . time() . '.' . $file_extension;
    $target_file = $upload_dir . $new_filename;
    
    // Log the file upload attempt
    error_log('Attempting to upload file to: ' . $target_file);

    // Move uploaded file
    if (move_uploaded_file($_FILES['profilePhoto']['tmp_name'], $target_file)) {
        // Update database with new photo filename
        require_once $base_dir . '/Model/Database.php';
        require_once $base_dir . '/Model/Patient.php';
        
        $patient = new Patient();
        $result = $patient->updateProfilePhoto($_SESSION['patient_id'], $new_filename);
        
        if ($result) {
            // Update the session with the new photo
            $updatedPatient = $patient->getPatientById($_SESSION['patient_id']);
            if ($updatedPatient) {
                $_SESSION['patient_photo'] = $updatedPatient['photo'];
                $_SESSION['success'] = 'Profile photo updated successfully';
            } else {
                throw new Exception('Failed to refresh profile data');
            }
        } else {
            // If database update fails, delete the uploaded file
            if (file_exists($target_file)) {
                unlink($target_file);
            }
            throw new Exception('Failed to update profile in database');
        }
    } else {
        throw new Exception('Failed to save the uploaded file');
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Error updating profile: ' . $e->getMessage();
}

// Redirect back to profile page
header('Location: View/patient_profile.php');
exit();
