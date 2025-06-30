<?php
/**
 * Admin Layout - Complete Version
 * File: app/Views/admin/layout.php
 */
?>
<!DOCTYPE html>
<html lang="<?= config('App')->defaultLocale ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin' ?> - <?= cms_setting('site_name') ?></title>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 250px;
            --topbar-height: 60px;
        }

        body {
            background: #f5f6fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: #2c3e50;
            color: #ecf0f1;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            background: #34495e;
            text-align: center;
            border-bottom: 1px solid #243342;
        }

        .sidebar-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .sidebar-header small {
            opacity: 0.8;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-link:hover {
            background: #34495e;
            color: #fff;
            border-left-color: #3498db;
        }

        .sidebar-link.active {
            background: #3498db;
            color: #fff;
            border-left-color: #2980b9;
        }

        .sidebar-link i {
            width: 20px;
            text-align: center;
        }

        /* Main content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            background: #fff;
            height: var(--topbar-height);
            box-shadow: 0 2px 4px rgba(0,0,0,.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .content {
            flex: 1;
            padding: 20px;
        }

        /* Cards */
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
            transition: all 0.3s;
        }

        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
        }

        /* Buttons */
        .btn {
            border-radius: 5px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-link {
            color: #495057;
            text-decoration: none;
        }

        .btn-link:hover {
            color: #007bff;
        }

        /* Dropdown */
        .dropdown-menu {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
            border-radius: 8px;
        }

        .dropdown-item {
            padding: 10px 20px;
            transition: all 0.3s;
        }

        .dropdown-item:hover {
            background: #f8f9fa;
            padding-left: 25px;
        }

        /* Notifications */
        .notifications-dropdown {
            width: 350px;
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-item {
            display: flex;
            align-items: start;
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .dropdown-item.unread {
            background: #f0f8ff;
        }

        /* Tables */
        .table {
            background: #fff;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
            color: #6c757d;
        }

        /* Forms */
        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #495057;
        }

        .form-control,
        .form-select {
            border-radius: 5px;
            border: 1px solid #ced4da;
            padding: 10px 15px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(var(--sidebar-width) * -1);
            }

            .sidebar.show {
                margin-left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .topbar {
                padding: 0 15px;
            }

            .content {
                padding: 15px;
            }
        }

        /* Utilities */
        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Loading spinner */
        .spinner-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
    </style>

    <?= $this->renderSection('styles') ?>
</head>
<body>
<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h4 class="mb-0"><?= cms_setting('site_name') ?></h4>
        <small>Admin Panel</small>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="<?= site_url('admin') ?>" class="sidebar-link <?= current_url() === site_url('admin') ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
        </li>

        <!-- Content Management -->
        <?php if (has_permission('content.create') || has_permission('content.edit')): ?>
            <li class="sidebar-section">
                <small class="text-muted px-3">CONTENT</small>
            </li>
        <?php endif; ?>

        <?php if (has_permission('media.upload')): ?>
            <li>
                <a href="<?= site_url('admin/media') ?>" class="sidebar-link <?= str_contains(current_url(), 'admin/media') ? 'active' : '' ?>">
                    <i class="fas fa-images me-2"></i> Media
                </a>
            </li>
        <?php endif; ?>

        <!-- System -->
        <?php if (has_permission('admin.users') || has_permission('admin.settings')): ?>
            <li class="sidebar-section mt-3">
                <small class="text-muted px-3">SYSTEM</small>
            </li>
        <?php endif; ?>

        <?php if (has_permission('admin.users')): ?>
            <li>
                <a href="<?= site_url('admin/users') ?>" class="sidebar-link <?= str_contains(current_url(), 'admin/users') ? 'active' : '' ?>">
                    <i class="fas fa-users me-2"></i> Users
                </a>
            </li>
        <?php endif; ?>

        <?php if (has_permission('admin.settings')): ?>
            <li>
                <a href="<?= site_url('admin/settings') ?>" class="sidebar-link <?= str_contains(current_url(), 'admin/settings') ? 'active' : '' ?>">
                    <i class="fas fa-cog me-2"></i> Settings
                </a>
            </li>
        <?php endif; ?>

        <?php if (has_permission('admin.modules')): ?>
            <li>
                <a href="<?= site_url('admin/modules') ?>" class="sidebar-link <?= str_contains(current_url(), 'admin/modules') ? 'active' : '' ?>">
                    <i class="fas fa-puzzle-piece me-2"></i> Modules
                </a>
            </li>
        <?php endif; ?>

        <!-- Quick Links -->
        <li class="sidebar-section mt-3">
            <small class="text-muted px-3">QUICK LINKS</small>
        </li>
        <li>
            <a href="<?= site_url() ?>" class="sidebar-link" target="_blank">
                <i class="fas fa-external-link-alt me-2"></i> View Site
            </a>
        </li>
    </ul>
</nav>

<!-- Main Content -->
<div class="main-content">
    <!-- Topbar -->
    <div class="topbar">
        <button class="btn btn-link d-md-none" onclick="document.getElementById('sidebar').classList.toggle('show')">
            <i class="fas fa-bars"></i>
        </button>

        <div class="topbar-right">
            <!-- Notifications -->
            <div class="dropdown">
                <button class="btn btn-link position-relative" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    <?php $unreadCount = model('App\Models\CMS\NotificationModel')->getUnreadCount(auth()->id()); ?>
                    <?php if ($unreadCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
                            </span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-end notifications-dropdown">
                    <h6 class="dropdown-header d-flex justify-content-between align-items-center">
                        <span>Notifications</span>
                        <?php if ($unreadCount > 0): ?>
                            <a href="#" class="text-primary small" onclick="markAllAsRead()">Mark all as read</a>
                        <?php endif; ?>
                    </h6>
                    <?php $notifications = model('App\Models\CMS\NotificationModel')->getUnread(auth()->id(), 5); ?>
                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No new notifications</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $notif): ?>
                            <a class="dropdown-item notification-item <?= !$notif->isRead() ? 'unread' : '' ?>"
                               href="<?= $notif->action_url ?? '#' ?>"
                               data-id="<?= $notif->id ?>">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i class="<?= $notif->getIcon() ?> fa-lg"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1"><?= esc($notif->title) ?></h6>
                                        <p class="mb-1 small text-muted"><?= esc($notif->message) ?></p>
                                        <small class="text-muted"><?= $notif->getTimeAgo() ?></small>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-center small" href="<?= site_url('admin/notifications') ?>">
                        View All Notifications
                    </a>
                </div>
            </div>

            <!-- User Menu -->
            <div class="dropdown">
                <button class="btn btn-link d-flex align-items-center" data-bs-toggle="dropdown">
                    <img src="<?= get_avatar(auth()->user(), 32) ?>" class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                    <span class="d-none d-md-inline"><?= esc(auth()->user()->username) ?></span>
                    <i class="fas fa-chevron-down ms-2 small"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <h6 class="dropdown-header"><?= esc(auth()->user()->email) ?></h6>
                    <a class="dropdown-item" href="<?= site_url('admin/profile') ?>">
                        <i class="fas fa-user me-2"></i> My Profile
                    </a>
                    <a class="dropdown-item" href="<?= site_url('admin/settings') ?>">
                        <i class="fas fa-cog me-2"></i> Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="<?= site_url('logout') ?>">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Content -->
    <div class="content">
        <!-- Breadcrumbs -->
        <?php if (!empty($breadcrumbs) && count($breadcrumbs) > 1): ?>
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <?php foreach ($breadcrumbs as $crumb): ?>
                        <?php if (isset($crumb['url']) && $crumb['url']): ?>
                            <li class="breadcrumb-item"><a href="<?= $crumb['url'] ?>"><?= esc($crumb['title']) ?></a></li>
                        <?php else: ?>
                            <li class="breadcrumb-item active"><?= esc($crumb['title']) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0"><?= $title ?? 'Admin Panel' ?></h1>
            <?= $this->renderSection('page_actions') ?>
        </div>

        <!-- Flash Messages -->
        <?= view('partials/flash_messages') ?>

        <!-- Main Content Area -->
        <?= $this->renderSection('content') ?>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-top py-3 px-4 text-center text-muted small">
        <div>
            &copy; <?= date('Y') ?> <?= cms_setting('site_name') ?> -
            CMS v<?= $cms_version ?? '1.0.0' ?> |
            Page rendered in {elapsed_time} seconds |
            Memory: {memory_usage}
        </div>
    </footer>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Global admin functions

    // Mark notification as read
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function() {
            const notifId = this.dataset.id;
            if (notifId && this.classList.contains('unread')) {
                fetch(`<?= site_url('admin/notifications') ?>/${notifId}/read`, {
                    method: 'PUT',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                    }
                });
                this.classList.remove('unread');
            }
        });
    });

    // Mark all notifications as read
    function markAllAsRead() {
        fetch('<?= site_url('admin/notifications/mark-all-read') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
            }
        }).then(() => {
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
            });
            document.querySelector('.badge.bg-danger')?.remove();
        });
    }

    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert-dismissible').forEach(alert => {
            if (!alert.classList.contains('alert-danger')) {
                new bootstrap.Alert(alert).close();
            }
        });
    }, 5000);

    // Add loading state to forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            }
        });
    });

    // Tooltip initialization
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>

<?= $this->renderSection('scripts') ?>
</body>
</html>