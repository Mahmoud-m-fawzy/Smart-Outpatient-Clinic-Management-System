document.addEventListener('DOMContentLoaded', function() {
    const notificationDropdown = document.querySelector('.notification-dropdown');
    const notificationLink = document.querySelector('.notification-link');
    const markAllRead = document.querySelector('.mark-all-read');
    const notificationItems = document.querySelectorAll('.notification-item');
    const notificationBadge = document.querySelector('.notification-badge');

    // Toggle notification panel on click
    notificationLink.addEventListener('click', function(e) {
        e.preventDefault();
        notificationDropdown.classList.toggle('active');
    });

    // Mark all as read
    if (markAllRead) {
        markAllRead.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove unread styles
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
            });
            
            // Hide the badge
            if (notificationBadge) {
                notificationBadge.style.display = 'none';
            }
            
            // Here you would typically make an AJAX call to mark all as read on the server
            // fetch('/mark-all-notifications-read', { method: 'POST' });
        });
    }


    // Close when clicking outside
    document.addEventListener('click', function(e) {
        if (!notificationDropdown.contains(e.target)) {
            notificationDropdown.classList.remove('active');
        }
    });
});
