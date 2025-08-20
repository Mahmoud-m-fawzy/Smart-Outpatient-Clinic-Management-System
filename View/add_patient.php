<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../Controller/StaffController.php';

$controller = new StaffController();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->createPatient($_POST);
    
    if ($result['success']) {
        $success = 'Patient created successfully!';
        // Clear form or redirect
        // header('Location: staff_dashboard.php');
        // exit;
    } else {
        $error = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة مريض جديد</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 2rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .form-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        .required::after {
            content: ' *';
            color: red;
        }
        .input-group-text {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            border-top-left-radius: 0.25rem !important;
            border-bottom-left-radius: 0.25rem !important;
        }
        .form-control, .form-select {
            text-align: right;
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
                        <h2><i class="fas fa-user-plus me-2"></i>إضافة مريض جديد</h2>
                        <a href="staff_dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right me-1"></i> العودة للوحة التحكم
                        </a>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger text-right"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success text-right">
                            <?php echo htmlspecialchars($success); ?>
                            <script>
                                // Redirect to patient dashboard after 2 seconds
                                setTimeout(function() {
                                    window.location.href = 'patient_dashboard.php';
                                }, 2000);
                            </script>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="patientForm" class="text-right">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">الاسم الأول <span class="text-danger">*</span></label>
                                <input type="text" class="form-control text-right mb-1" id="first_name" name="first_name" required placeholder="أدخل الاسم الأول">
                                <button type="button" class="btn btn-outline-secondary w-100 btn-microphone" onclick="startDictation('first_name', this)">
                                    <i class="fas fa-microphone"></i> تحدث الآن
                                </button>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">الاسم الأخير <span class="text-danger">*</span></label>
                                <input type="text" class="form-control text-right mb-1" id="last_name" name="last_name" required placeholder="أدخل الاسم الأخير">
                                <button type="button" class="btn btn-outline-secondary w-100 btn-microphone" onclick="startDictation('last_name', this)">
                                    <i class="fas fa-microphone"></i> تحدث الآن
                                </button>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">رقم الهاتف <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control text-left mb-1" id="phone" name="phone" required dir="ltr" placeholder="05XXXXXXXX">
                                <button type="button" class="btn btn-outline-secondary w-100 btn-microphone" onclick="startDictation('phone', this)">
                                    <i class="fas fa-microphone"></i> تحدث الآن
                                </button>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="age" class="form-label">العمر <span class="text-danger">*</span></label>
                                <input type="text" class="form-control text-right mb-1" id="age" name="age" required min="1" max="120" placeholder="أدخل العمر">
                                <button type="button" class="btn btn-outline-secondary w-100 btn-microphone" onclick="startDictation('age', this)">
                                    <i class="fas fa-microphone"></i> تحدث الآن
                                </button>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label">الجنس <span class="text-danger">*</span></label>
                                <select class="form-select text-right mb-1" id="gender" name="gender" required>
                                    <option value="">اختر الجنس</option>
                                    <option value="ذكر">ذكر</option>
                                    <option value="أنثى">أنثى</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="marital_status" class="form-label">الحالة الاجتماعية</label>
                                <select class="form-select text-right mb-1" id="marital_status" name="marital_status">
                                    <option value="">اختر الحالة الاجتماعية</option>
                                    <option value="أعزب">أعزب</option>
                                    <option value="متزوج">متزوج</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">العنوان</label>
                            <textarea class="form-control text-right mb-1" id="address" name="address" rows="2" placeholder="أدخل العنوان بالتفصيل"></textarea>
                            <button type="button" class="btn btn-outline-secondary w-100 btn-microphone" onclick="startDictation('address', this)">
                                <i class="fas fa-microphone"></i> تحدث الآن
                            </button>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="id_number" class="form-label">رقم id </label>
                                <input type="text" class="form-control text-left mb-1" id="id_number" name="id_number" dir="ltr" placeholder="10 أرقام">
                                <button type="button" class="btn btn-outline-secondary w-100 btn-microphone" onclick="startDictation('id_number', this)">
                                    <i class="fas fa-microphone"></i> تحدث الآن
                                </button>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="national_id" class="form-label">رقم قومي</label>
                                <input type="text" class="form-control text-left mb-1" id="national_id" name="national_id" dir="ltr" placeholder="10 أرقام">
                                <button type="button" class="btn btn-outline-secondary w-100 btn-microphone" onclick="startDictation('national_id', this)">
                                    <i class="fas fa-microphone"></i> تحدث الآن
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> حفظ البيانات
                            </button>
                            <button type="reset" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-undo me-1"></i> إعادة تعيين
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
    .btn-microphone {
        transition: all 0.3s ease;
    }
    .btn-microphone.listening {
        background-color: #dc3545 !important;
        border-color: #dc3545 !important;
        color: white !important;
    }
    .btn-microphone i {
        margin-left: 5px;
    }
    </style>
    <script>
    function startDictation(fieldId, button) {
        if ('webkitSpeechRecognition' in window) {
            const recognition = new webkitSpeechRecognition();
            recognition.lang = 'ar-EG';
            
            // Change button style to show it's listening
            button.classList.add('listening');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-microphone"></i> جاري الاستماع...';
            
            recognition.onresult = function(event) {
                // Get the transcript and remove any periods
                let transcript = event.results[0][0].transcript;
                transcript = transcript.replace(/\./g, ''); // Remove all periods
                
                // For age field, keep only numbers
                if (fieldId === 'age') {
                    transcript = transcript.replace(/\D/g, '');
                }
                
                document.getElementById(fieldId).value = transcript;
                // Reset button after getting result
                button.classList.remove('listening');
                button.innerHTML = originalText;
            };

            recognition.onerror = function(event) {
                // Only show error if it's not a 'no-speech' error (user cancelled)
                if (event.error !== 'no-speech') {
                    alert('خطأ في المايكروفون: ' + event.error);
                }
                // Reset button on error
                button.classList.remove('listening');
                button.innerHTML = originalText;
            };
            
            recognition.onend = function() {
                // Reset button when recognition ends
                button.classList.remove('listening');
                button.innerHTML = originalText;
            };
            
            recognition.start();
        } else {
            alert("متصفحك لا يدعم تحويل الصوت لنص. يرجى استخدام متصفح مثل Chrome أو Edge.");
        }
    }
    </script>
</body>
</html>
