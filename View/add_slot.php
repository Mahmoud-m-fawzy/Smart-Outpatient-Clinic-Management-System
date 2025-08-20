<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../Controller/StaffController.php';
require_once __DIR__ . '/../Model/Doctor.php';

$controller = new StaffController();
$doctorModel = new Doctor();
$error = '';
$success = '';

// Get list of doctors
$doctors = $doctorModel->getAllDoctors();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->addAvailableSlot($_POST);
    
    if ($result['success']) {
        $success = 'Time slot added successfully!';
        // Clear form or redirect
        $_POST = [];
    } else {
        $error = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Available Time Slot - Clinic Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
        }
        .form-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .btn-submit {
            background-color: #0d6efd;
            border: none;
            padding: 10px 25px;
            font-weight: 500;
        }
        .btn-submit:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="form-container">
                    <div class="form-header d-flex justify-content-between align-items-center">
                        <h2><i class="fas fa-calendar-plus me-2"></i>Add Available Time Slot</h2>
                        <a href="staff_dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                        </a>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="slotForm">
                        <div class="mb-3">
                            <label for="doctor_id" class="form-label">Doctor <span class="text-danger">*</span></label>
                            <select class="form-select" id="doctor_id" name="doctor_id" required>
                                <option value="">Select Doctor</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?php echo $doctor['id']; ?>">
                                        <?php echo htmlspecialchars($doctor['FN'] . ' ' . $doctor['LN'] . ' - ' . $doctor['specialty']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="day_of_week" class="form-label">Day of Week <span class="text-danger">*</span></label>
                                <select class="form-select" id="day_of_week" name="appointment_date" required>
                                    <option value="">Select Day</option>
                                    <option value="Sunday">Sunday</option>
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Saturday">Saturday</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="end_time" class="form-label">End Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                            <select class="form-select" id="location" name="location" required>
                                <option value="">Select Location</option>
                                <option value="Clinic A">Clinic A</option>
                                <option value="Clinic B">Clinic B</option>
                                <option value="Clinic C">Clinic C</option>
                                <option value="Clinic D">Clinic D</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fee" class="form-label">Fee <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="fee" name="fee" min="0" step="0.01" value="0.00" required>
                                    <span class="input-group-text">.00</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="notes" class="form-label">Notes (Optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Add any additional notes here"></textarea>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Time Slot
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.getElementById('slotForm').addEventListener('submit', function(e) {
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            
            if (startTime >= endTime) {
                e.preventDefault();
                alert('End time must be after start time!');
                return false;
            }
            
            return true;
        });
        
        // Set minimum time to current time if today's date is selected
        document.getElementById('appointment_date').addEventListener('change', function() {
            const selectedDate = this.value;
            const today = new Date().toISOString().split('T')[0];
            const now = new Date();
            const currentHour = now.getHours().toString().padStart(2, '0');
            const currentMinute = now.getMinutes().toString().padStart(2, '0');
            
            if (selectedDate === today) {
                document.getElementById('start_time').min = `${currentHour}:${currentMinute}`;
            } else {
                document.getElementById('start_time').removeAttribute('min');
            }
        });
    </script>
</body>
</html>
