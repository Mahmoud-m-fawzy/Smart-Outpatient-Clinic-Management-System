document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Toggle mobile menu
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            document.body.classList.toggle('sidebar-active');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 992) {
            if (sidebar && !sidebar.contains(e.target) && menuToggle && !menuToggle.contains(e.target)) {
                sidebar.classList.remove('active');
                document.body.classList.remove('sidebar-active');
            }
        }
    });

    // Toggle notification dropdown
    const notificationBell = document.querySelector('.notification-bell');
    const notificationDropdown = document.querySelector('.notification-dropdown');
    
    if (notificationBell && notificationDropdown) {
        notificationBell.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
        });
    }

    // Toggle user dropdown
    const userProfile = document.querySelector('.user-profile');
    const userDropdown = document.querySelector('.user-dropdown');
    
    if (userProfile && userDropdown) {
        userProfile.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        if (notificationDropdown) notificationDropdown.classList.remove('show');
        if (userDropdown) userDropdown.classList.remove('show');
    });

    // Mark notifications as read
    const notificationItems = document.querySelectorAll('.notification-item');
    notificationItems.forEach(item => {
        item.addEventListener('click', function() {
            this.classList.remove('unread');
            // Here you would typically make an API call to mark the notification as read
        });
    });

    // Initialize charts if any
    initializeCharts();
    
    // Handle responsive behavior
    handleResize();
    
    // Add resize event listener
    window.addEventListener('resize', handleResize);
    
    // Initialize date pickers if any
    initializeDatePickers();
    
    // Initialize any modals
    initializeModals();
    const clearNotifications = document.querySelector('.notification-clear');
    if (clearNotifications) {
        clearNotifications.addEventListener('click', function(e) {
            e.stopPropagation();
            const notifications = document.querySelectorAll('.notification-item');
            notifications.forEach(notification => {
                notification.remove();
            });
            // Here you would typically make an API call to clear all notifications
            updateNotificationBadge(0);
        });
    }

    // Set active menu item based on current page
    const currentPath = window.location.pathname.split('/').pop() || 'dashboard';
    const menuItems = document.querySelectorAll('.sidebar-menu a');
    
    menuItems.forEach(item => {
        const href = item.getAttribute('href').split('/').pop() || 'dashboard';
        if (currentPath.includes(href) || 
            (currentPath === '' && href === 'dashboard')) {
            item.classList.add('active');
        }
    });

    // Handle window resize
    function handleResize() {
        const sidebar = document.querySelector('.sidebar');
        if (window.innerWidth > 992) {
            if (sidebar) sidebar.classList.remove('active');
            document.body.classList.remove('sidebar-active');
        }
    }

    // Initialize charts
    function initializeCharts() {
        const weightChartEl = document.getElementById('weightChart');
        if (weightChartEl) {
            new Chart(weightChartEl, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Weight (kg)',
                        data: [72, 71, 70, 69, 70, 69],
                        borderColor: '#4f46e5',
                        tension: 0.3,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                }
            });
        }
    }


    // Initialize date pickers
    function initializeDatePickers() {
        const datePickers = document.querySelectorAll('.date-picker');
        if (datePickers.length > 0) {
            datePickers.forEach(picker => {
                // Initialize flatpickr or any other date picker library
                // Example: flatpickr(picker, { dateFormat: 'Y-m-d' });
            });
        }
    }


    // Initialize modals
    function initializeModals() {
        const modals = document.querySelectorAll('.modal');
        if (modals.length > 0) {
            modals.forEach(modal => {
                // Initialize Bootstrap modals if needed
            });
        }
    }


    // Update notification badge
    function updateNotificationBadge(count) {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }


    // Mark all notifications as read
    function markAllAsRead() {
        const unreadNotifications = document.querySelectorAll('.notification-item.unread');
        unreadNotifications.forEach(notification => {
            notification.classList.remove('unread');
        });
        updateNotificationBadge(0);
        // Here you would typically make an API call to mark all notifications as read
    }


    // Logout function
    function logout() {
        // Here you would typically make an API call to log the user out
        window.location.href = '/MVC/View/login.php';
    }
});
