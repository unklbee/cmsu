// Admin Panel App
class AdminApp {
    constructor() {
        this.initSidebar();
        this.initDataTables();
        this.initCharts();
        this.initForms();
        this.initNotifications();
    }

    initSidebar() {
        // Toggle sidebar
        const toggleBtn = document.querySelector('.sidebar-toggle');
        const sidebar = document.querySelector('.sidebar');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                document.body.classList.toggle('sidebar-collapsed');
            });
        }

        // Active menu item
        const currentPath = window.location.pathname;
        document.querySelectorAll('.sidebar-link').forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('active');
                // Expand parent if in submenu
                const parent = link.closest('.has-dropdown');
                if (parent) {
                    parent.classList.add('open');
                }
            }
        });
    }

    initDataTables() {
        // Initialize DataTables if present
        if (typeof $.fn.DataTable !== 'undefined') {
            $('.data-table').DataTable({
                responsive: true,
                pageLength: 25,
                language: {
                    search: '_INPUT_',
                    searchPlaceholder: 'Search...'
                }
            });
        }
    }

    initCharts() {
        // Initialize charts if Chart.js is loaded
        if (typeof Chart !== 'undefined') {
            // Dashboard chart example
            const ctx = document.getElementById('dashboardChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Visitors',
                            data: [12, 19, 3, 5, 2, 3],
                            borderColor: 'rgb(75, 192, 192)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        }
    }

    initForms() {
        // Form validation
        const forms = document.querySelectorAll('.needs-validation');
        forms.forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });

        // File input preview
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const preview = document.querySelector('#' + this.id + '-preview');
                if (preview && this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });
    }

    initNotifications() {
        // Mark notification as read
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const notificationId = this.dataset.id;
                if (notificationId) {
                    fetch(`/api/v1/notifications/${notificationId}/read`, {
                        method: 'PUT',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                }
            });
        });

        // Real-time notifications (if using WebSocket/SSE)
        if (typeof EventSource !== 'undefined') {
            const source = new EventSource('/api/v1/notifications/stream');
            source.addEventListener('notification', event => {
                const data = JSON.parse(event.data);
                this.showNotification(data);
            });
        }
    }

    showNotification(data) {
        // Show browser notification if permitted
        if (Notification.permission === 'granted') {
            new Notification(data.title, {
                body: data.message,
                icon: '/favicon.ico'
            });
        }

        // Update notification badge
        const badge = document.querySelector('.topbar .badge');
        if (badge) {
            const count = parseInt(badge.textContent) + 1;
            badge.textContent = count;
            badge.style.display = 'inline-block';
        }
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    new AdminApp();
});