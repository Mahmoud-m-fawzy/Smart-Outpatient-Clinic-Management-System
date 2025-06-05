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
    <link rel="stylesheet" href="css/manager_staff.css">
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

</header>

<body>
    <div class="container">
        <h1 class="text-center my-4">Staff Management</h1>

        <!-- Side Button for Staff Management -->
        <div class="side-button">
            <a href="manage_staff.php" class="manage-staff-btn">
                <i class="fas fa-users-cog"></i>
                Manage Staff
            </a>
        </div>

        <!-- Staff Management Section -->
        <div class="staff-management-section" id="staffManagementSection">
            <h3>Manage Staff Members</h3>
            <div class="staff-filters">
                <select id="staffTypeFilter" onchange="filterStaff()">
                    <option value="all">All Staff</option>
                    <option value="doctor">Doctors</option>
                    <option value="ta">Teaching Assistants</option>
                    <option value="reception">Reception Staff</option>
                </select>
                <select id="statusFilter" onchange="filterStaff()">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <input type="text" id="searchStaff" placeholder="Search staff..." onkeyup="filterStaff()">
            </div>
            <div class="staff-list" id="staffList">
                <!-- Staff list will be populated dynamically -->
            </div>
        </div>

        <!-- Form Toggle Buttons -->
        <div class="form-toggle">
            <button type="button" class="toggle-btn active" onclick="toggleForm('doctor')">
                <i class="fas fa-user-md"></i>
                Add Doctor/TA
            </button>
            <button type="button" class="toggle-btn" onclick="toggleForm('reception')">
                <i class="fas fa-user-tie"></i>
                Add Reception Staff
            </button>
        </div>

        <div class="staff-form">
            <!-- Doctor/TA Form Section -->
            <div class="form-section active" id="doctorFormSection">
                <h3>Add New <span class="underline">Doctor</span></h3>
                <form id="doctorForm" action="add_staff.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="staff_type" value="doctor">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="staffId" class="form-label"> ID</label>
                            <div class="input-group">
                                <span class="input-group-text">DR</span>
                                <input type="text" class="form-control" id="staffId" name="staffId" required
                                    pattern="[0-9]{4}" maxlength="4" placeholder="0000"
                                    title="Please enter a 4-digit ID number">
                            </div>
                            <small class="form-text text-muted">Enter a 4-digit ID number</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="doctor">Doctor</option>
                                <option value="ta">Teaching Assistant</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="password-field">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender</label>
                            <div class="gender-options">
                                <div class="gender-option">
                                    <input type="radio" id="male" name="gender" value="male" required>
                                    <label for="male">Male</label>
                                </div>
                                <div class="gender-option">
                                    <input type="radio" id="female" name="gender" value="female">
                                    <label for="female">Female</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="specialization" class="form-label">Specialization</label>
                            <input type="text" class="form-control" id="specialization" name="specialization">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Photo</label>
                        <div class="photo-upload">
                            <label for="photo" class="upload-btn">
                                <i class="fas fa-upload"></i>
                                Choose Photo
                            </label>
                            <input type="file" id="photo" name="photo" accept="image/*" onchange="previewPhoto(this)">
                            <div class="photo-preview" id="photoPreview">
                                <img src="" alt="Preview">
                            </div>
                        </div>
                    </div>
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">Add Doctor/TA</button>
                        <button type="button" class="email-credentials-btn" onclick="showEmailModal('doctor')">
                            <i class="fas fa-envelope"></i>
                            Send Credentials via Email
                        </button>
                    </div>
                </form>
            </div>

            <!-- Reception Staff Form Section -->
            <div class="form-section" id="receptionFormSection">
                <h3>Add New <span class="underline">Reception Staff</span></h3>
                <form id="receptionForm" action="add_staff.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="staff_type" value="reception">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="recStaffId" class="form-label">Staff ID</label>
                            <div class="input-group">
                                <span class="input-group-text">RC</span>
                                <input type="text" class="form-control" id="recStaffId" name="staffId" required
                                    pattern="[0-9]{4}" maxlength="4" placeholder="0000"
                                    title="Please enter a 4-digit ID number">
                            </div>
                            <small class="form-text text-muted">Enter a 4-digit ID number</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="recFirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="recFirstName" name="firstName" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="recLastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="recLastName" name="lastName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="recEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="recEmail" name="email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="recPhone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="recPhone" name="phone" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="recPassword" class="form-label">Password</label>
                            <div class="password-field">
                                <input type="password" class="form-control" id="recPassword" name="password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('recPassword')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender</label>
                            <div class="gender-options">
                                <div class="gender-option">
                                    <input type="radio" id="recMale" name="gender" value="male" required>
                                    <label for="recMale">Male</label>
                                </div>
                                <div class="gender-option">
                                    <input type="radio" id="recFemale" name="gender" value="female">
                                    <label for="recFemale">Female</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="shift" class="form-label">Preferred Shift</label>
                            <select class="form-select" id="shift" name="shift" required>
                                <option value="morning">Morning (8 AM - 4 PM)</option>
                                <option value="afternoon">Afternoon (4 PM - 12 AM)</option>
                                <option value="night">Night (12 AM - 8 AM)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Photo</label>
                        <div class="photo-upload">
                            <label for="recPhoto" class="upload-btn">
                                <i class="fas fa-upload"></i>
                                Choose Photo
                            </label>
                            <input type="file" id="recPhoto" name="photo" accept="image/*"
                                onchange="previewPhoto(this)">
                            <div class="photo-preview" id="recPhotoPreview">
                                <img src="" alt="Preview">
                            </div>
                        </div>
                    </div>
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">Add Reception Staff</button>
                        <button type="button" class="email-credentials-btn" onclick="showEmailModal('reception')">
                            <i class="fas fa-envelope"></i>
                            Send Credentials via Email
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Email Credentials Modal -->
    <div class="modal-overlay" id="emailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Send Credentials</h3>
                <button type="button" class="modal-close" onclick="closeEmailModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to send the login credentials to the staff member's email?</p>
                <p>The email will contain their username and password for accessing the system.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn cancel" onclick="closeEmailModal()">Cancel</button>
                <button type="button" class="modal-btn send" onclick="sendCredentials()">
                    <i class="fas fa-paper-plane"></i>
                    Send Email
                </button>
            </div>
        </div>
    </div>

    <script>
        function toggleForm(formType) {
            // Update toggle buttons
            const buttons = document.querySelectorAll('.toggle-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.currentTarget.classList.add('active');

            // Show/hide forms
            const doctorSection = document.getElementById('doctorFormSection');
            const receptionSection = document.getElementById('receptionFormSection');

            if (formType === 'doctor') {
                doctorSection.classList.add('active');
                receptionSection.classList.remove('active');
            } else {
                doctorSection.classList.remove('active');
                receptionSection.classList.add('active');
            }
        }

        // Add new function to update ID prefix
        function updateIdPrefix() {
            const roleSelect = document.getElementById('role');
            const idPrefix = document.querySelector('.input-group-text');
            const staffIdInput = document.getElementById('staffId');
            const heading = document.querySelector('#doctorFormSection h3');

            if (roleSelect.value === 'ta') {
                idPrefix.textContent = 'TA';
                heading.innerHTML = 'Add New <span class="underline">TA</span>';
                // Clear the input when changing prefix
                staffIdInput.value = '';
            } else {
                idPrefix.textContent = 'DR';
                heading.innerHTML = 'Add New <span class="underline">Doctor</span>';
                // Clear the input when changing prefix
                staffIdInput.value = '';
            }
        }

        // Add event listener to role select
        document.getElementById('role').addEventListener('change', updateIdPrefix);
        // Call updateIdPrefix on page load to set initial state
        document.addEventListener('DOMContentLoaded', updateIdPrefix);

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function previewPhoto(input) {
            const preview = input.parentElement.querySelector('.photo-preview');
            const previewImg = preview.querySelector('img');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    preview.classList.add('show');
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        let currentFormType = 'doctor';

        function showEmailModal(formType) {
            currentFormType = formType;
            const modal = document.getElementById('emailModal');
            modal.classList.add('active');
        }

        function closeEmailModal() {
            const modal = document.getElementById('emailModal');
            modal.classList.remove('active');
        }

        function sendCredentials() {
            const form = document.getElementById(currentFormType + 'Form');
            const formData = new FormData(form);
            formData.append('send_email', '1');

            // Disable the send button
            const sendBtn = document.querySelector('.modal-btn.send');
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

            // Send the request
            fetch('add_staff.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Credentials have been sent successfully!');
                        closeEmailModal();
                    } else {
                        alert('Error sending credentials: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error sending credentials. Please try again.');
                    console.error('Error:', error);
                })
                .finally(() => {
                    // Re-enable the send button
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Email';
                });
        }

        // Close modal when clicking outside
        document.getElementById('emailModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeEmailModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeEmailModal();
            }
        });

        function toggleStaffManagement() {
            const section = document.getElementById('staffManagementSection');
            section.classList.toggle('active');
            if (section.classList.contains('active')) {
                loadStaffList();
            }
        }

        function loadStaffList() {
            // Fetch staff list from the server
            fetch('get_staff_list.php')
                .then(response => response.json())
                .then(data => {
                    displayStaffList(data);
                })
                .catch(error => {
                    console.error('Error loading staff list:', error);
                });
        }

        function displayStaffList(staff) {
            const staffList = document.getElementById('staffList');
            staffList.innerHTML = '';

            staff.forEach(member => {
                const card = document.createElement('div');
                card.className = 'staff-card';
                card.innerHTML = `
                    <div class="staff-info">
                        <h4>${member.name}</h4>
                        <p>ID: ${member.id}</p>
                        <p>Role: ${member.role}</p>
                        <p>Status: ${member.status}</p>
                    </div>
                    <div class="staff-actions">
                        <button class="deactivate-btn" onclick="toggleStaffStatus('${member.id}')">
                            ${member.status === 'active' ? 'Deactivate' : 'Activate'}
                        </button>
                        <button class="remove-btn" onclick="removeStaff('${member.id}')">Remove</button>
                    </div>
                `;
                staffList.appendChild(card);
            });
        }

        function filterStaff() {
            const typeFilter = document.getElementById('staffTypeFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const searchTerm = document.getElementById('searchStaff').value.toLowerCase();

            // Fetch filtered staff list
            fetch(`get_staff_list.php?type=${typeFilter}&status=${statusFilter}&search=${searchTerm}`)
                .then(response => response.json())
                .then(data => {
                    displayStaffList(data);
                })
                .catch(error => {
                    console.error('Error filtering staff:', error);
                });
        }

        function toggleStaffStatus(staffId) {
            if (confirm('Are you sure you want to change this staff member\'s status?')) {
                fetch('toggle_staff_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ staffId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadStaffList();
                        } else {
                            alert('Error changing staff status: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error changing staff status. Please try again.');
                    });
            }
        }

        function removeStaff(staffId) {
            if (confirm('Are you sure you want to remove this staff member? This action cannot be undone.')) {
                fetch('remove_staff.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ staffId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadStaffList();
                        } else {
                            alert('Error removing staff: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error removing staff. Please try again.');
                    });
            }
        }
    </script>

    <footer class="main-footer">
        <div class="footer-main">
            <div class="footer-col about">
                <img src="images/logo.png" alt="Andalusia Hospital" class="footer-logo">
                <p class="footer-goal">
                    Committed to your recovery and well-being — combining expert care with the latest in physical
                    therapy
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
                    <li><i class="fas fa-map-marker-alt"></i> 26 July Mehwar Road intersection with Wahat Road, 6th
                        October
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

    <style>
        /* Add these styles to your existing CSS */
        .side-button {
            position: fixed;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1000;
        }

        .manage-staff-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .manage-staff-btn:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .staff-management-section {
            display: none;
            margin-top: 30px;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .staff-management-section.active {
            display: block;
        }

        .staff-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .staff-filters select,
        .staff-filters input {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .staff-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .staff-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .staff-card .staff-info {
            margin-bottom: 15px;
        }

        .staff-card .staff-actions {
            display: flex;
            gap: 10px;
        }

        .staff-card .staff-actions button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .staff-card .deactivate-btn {
            background-color: #ffc107;
            color: #000;
        }

        .staff-card .remove-btn {
            background-color: #dc3545;
            color: white;
        }
    </style>
</body>

</html>