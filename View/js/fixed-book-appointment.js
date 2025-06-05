// Store booking data
let currentBooking = {
    doctorId: null,
    doctorName: null,
    dayOfWeek: null,
    timeSlot: null
};

// Get the modal elements
let modal, closeBtn, cancelBtn, confirmBtn;

document.addEventListener('DOMContentLoaded', function() {
    modal = document.getElementById('bookingModal');
    closeBtn = document.querySelector('.close');
    cancelBtn = document.getElementById('cancelBooking');
    confirmBtn = document.getElementById('confirmBooking');
    
    if (!modal) console.error('Modal element not found!');
    if (!closeBtn) console.error('Close button not found!');
    if (!cancelBtn) console.error('Cancel button not found!');
    if (!confirmBtn) console.error('Confirm button not found!');
});

// Debug: Log when the script loads
console.log('Script loaded, setting up click handlers');

// Set up event delegation for available slots
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded');
    
    // Debug: Log all available slots
    const slots = document.querySelectorAll('.available-slot');
    console.log('Found', slots.length, 'available slots');
    slots.forEach(slot => {
        console.log('Slot found:', slot);
    });

    // Set up the click handler for the schedule table
    const scheduleTable = document.querySelector('.schedule-table');
    if (!scheduleTable) {
        console.error('Schedule table not found!');
        return;
    }
    
    console.log('Adding click handler to schedule table');
    scheduleTable.addEventListener('click', function(e) {
        // Find the closest available slot (handles clicks on the slot or its children)
        const slot = e.target.closest('.available-slot');
        
        if (!slot) {
            console.log('Click was not on an available slot');
            return;
        }
        
        console.log('Available slot clicked:', slot);
        
        // Get all data attributes
        const doctorId = slot.getAttribute('data-doctor-id');
        const doctorName = slot.getAttribute('data-doctor-name');
        const dayOfWeek = slot.getAttribute('data-day');
        const timeSlot = slot.getAttribute('data-time');
        
        // For debugging
        console.log('Slot data:', {
            doctorId,
            doctorName,
            dayOfWeek,
            timeSlot
        });
        
        // Get the time from the row's first cell if needed
        const row = slot.closest('tr');
        const displayTimeSlot = row ? row.cells[0].textContent.trim() : 'Unknown time';
        
        // Set current booking data
        currentBooking = {
            doctorId: doctorId,
            doctorName: doctorName,
            dayOfWeek: dayOfWeek,
            timeSlot: timeSlot,
            displayTimeSlot: displayTimeSlot
        };
        
        console.log('Current booking data:', currentBooking);
        
        // Update modal with booking details
        document.getElementById('modalDoctorName').textContent = currentBooking.doctorName;
        
        // Get the next occurrence of this day of the week
        const appointmentDate = getNextDayOfWeek(currentBooking.dayOfWeek);
        document.getElementById('modalAppointmentDate').textContent = appointmentDate.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        document.getElementById('modalTimeSlot').textContent = displayTimeSlot;
        
        // Show modal
        if (modal) {
            console.log('Showing modal for booking');
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Add a class to the modal for additional styling if needed
            modal.classList.add('active');
            
            // Log the current state for debugging
            console.log('Modal display style:', window.getComputedStyle(modal).display);
        } else {
            console.error('Modal element not found when trying to show it');
        }
    });
});

// Helper function to get the next occurrence of a specific day of the week
function getNextDayOfWeek(dayName) {
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const today = new Date();
    const dayIndex = days.indexOf(dayName);
    
    if (dayIndex === -1) return today; // Invalid day name
    
    const todayIndex = today.getDay();
    let daysUntilNext = dayIndex - todayIndex;
    
    const nextDate = new Date(today);
    nextDate.setDate(today.getDate() + daysUntilNext);
    return nextDate;
}

// Close modal when clicking on X or Cancel
if (closeBtn) {
    closeBtn.addEventListener('click', function() {
        console.log('Close button clicked');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    });
}

if (cancelBtn) {
    cancelBtn.addEventListener('click', function() {
        console.log('Cancel button clicked');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    });
}

// Close modal when clicking outside of it
window.addEventListener('click', function(event) {
    if (event.target === modal) {
        console.log('Clicked outside modal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
});

// Handle booking confirmation
confirmBtn.addEventListener('click', function() {
    // Disable button to prevent double submission
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    // Format the time properly for the database
    // First convert display time (like "9:00 AM") to 24-hour format for the database
    let timeForDb = currentBooking.timeSlot; // This is the raw time value from data-time
    
    console.log('Sending appointment data:', {
        doctor_id: currentBooking.doctorId,
        day_of_week: currentBooking.dayOfWeek,
        appointment_time: timeForDb
    });
    
    // Send AJAX request to book the appointment
    $.ajax({
        url: '../Controller/BookingController.php?action=book',
        type: 'POST',
        dataType: 'json',
        data: {
            doctor_id: currentBooking.doctorId,
            day_of_week: currentBooking.dayOfWeek,
            appointment_time: timeForDb
        },
        success: function(response) {
            if (response.success) {
                // Show success message
                alert('Appointment booked successfully!');
                // Refresh the page to update the schedule
                window.location.reload();
            } else {
                alert('Error: ' + (response.error || 'Failed to book appointment'));
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = 'Confirm Booking';
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error, xhr.responseText);
            alert('Network error. Please try again.');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = 'Confirm Booking';
        }
    });
});