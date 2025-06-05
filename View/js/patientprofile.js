document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Add click event to medical history items
    const medicalHistoryItems = document.querySelectorAll('.medical-history-item');
    medicalHistoryItems.forEach(item => {
        item.addEventListener('click', function() {
            this.classList.toggle('expanded');
        });
    });

    // Add print functionality
    const printButton = document.getElementById('printProfile');
    if (printButton) {
        printButton.addEventListener('click', function() {
            window.print();
        });
    }

    // Function to calculate age from date of birth
    function calculateAge(dob) {
        const today = new Date();
        const birthDate = new Date(dob);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        return age;
    }

    // Add edit functionality for patient information
    const editButtons = document.querySelectorAll('.edit-info');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const field = this.getAttribute('data-field');
            const valueElement = document.querySelector(`[data-field="${field}"]`);
            const currentValue = valueElement.textContent.trim();
            
            if (field === 'age') {
                // Create number input for Age
                const input = document.createElement('input');
                input.type = 'number';
                input.className = 'form-control';
                input.min = 0;
                input.value = (currentValue !== 'Not specified') ? currentValue : '';
                valueElement.innerHTML = '';
                valueElement.appendChild(input);
                input.focus();

                input.addEventListener('blur', function() {
                    const newValue = this.value.trim();
                    if (newValue && !isNaN(newValue)) {
                        valueElement.textContent = newValue;
                    } else {
                        valueElement.textContent = 'Not specified';
                    }
                });

                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        this.blur();
                    }
                });
            } else {
                // Handle other fields as before
                const input = document.createElement('input');
                input.type = 'text';
                input.value = currentValue;
                input.className = 'form-control';
                
                valueElement.innerHTML = '';
                valueElement.appendChild(input);
                input.focus();

                input.addEventListener('blur', function() {
                    const newValue = this.value.trim();
                    if (newValue !== currentValue) {
                        valueElement.textContent = newValue;
                    } else {
                        valueElement.textContent = currentValue;
                    }
                });

                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        this.blur();
                    }
                });
            }
        });
    });

    // Add appointment reminder functionality
    const appointmentItems = document.querySelectorAll('.appointment-item');
    appointmentItems.forEach(item => {
        const reminderButton = item.querySelector('.set-reminder');
        if (reminderButton) {
            reminderButton.addEventListener('click', function(e) {
                e.stopPropagation();
                const appointmentDate = this.getAttribute('data-date');
                const doctorName = this.getAttribute('data-doctor');
                
                // Here you would typically integrate with a calendar API
                alert(`Reminder set for appointment with ${doctorName} on ${appointmentDate}`);
            });
        }
    });

    // Add medication tracking functionality
    const medicationItems = document.querySelectorAll('.medication-item');
    medicationItems.forEach(item => {
        const trackButton = item.querySelector('.track-medication');
        if (trackButton) {
            trackButton.addEventListener('click', function() {
                const medicationName = this.getAttribute('data-medication');
                const dosage = this.getAttribute('data-dosage');
                
                // Here you would typically update the medication tracking system
                alert(`Tracking started for ${medicationName} (${dosage})`);
            });
        }
    });

    // Add responsive sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    }
}); 