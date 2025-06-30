<?php
/**
 * Admin Theme Layout
 * File: public/themes/default/admin/layout.php
 */
?>
<!DOCTYPE html>
<html lang="<?= config('App')->defaultLocale ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Admin Panel</title>

    <!-- Admin CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/admin/css/style.css') ?>" rel="stylesheet">

    <?= $this->renderSection('styles') ?>
</head>
<body>
<div class="wrapper">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h3><?= setting('site_name') ?></h3>
            <small>Admin Panel</small>
        </div>

        <?= render_menu('admin', [
            'class' => 'sidebar-menu',
            'item_class' => 'sidebar-item',
            'link_class' => 'sidebar-link',
            'dropdown_class' => 'has-dropdown'
        ]) ?>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <header class="topbar">
            <button class="btn btn-link sidebar-toggle">
                <i class="fas fa-bars"></i>
            </button>

            <div class="topbar-right">
                <!-- Notifications -->
                <div class="dropdown">
                    <button class="btn btn-link" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <?php if ($unreadNotifications > 0): ?>
                            <span class="badge bg-danger"><?= $unreadNotifications ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notifications-dropdown">
                        <h6 class="dropdown-header">Notifications</h6>
                        <?php if (empty($notifications)): ?>
                            <div class="dropdown-item text-center py-3">
                                <small class="text-muted">No new notifications</small>
                            </div>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <a class="dropdown-item <?= !$notification->isRead() ? 'unread' : '' ?>"
                                   href="<?= $notification->action_url ?? '#' ?>">
                                    <div class="notification-item">
                                        <i class="<?= $notification->getIcon() ?> me-2"></i>
                                        <div class="notification-content">
                                            <strong><?= esc($notification->title) ?></strong>
                                            <p class="mb-0 small"><?= esc($notification->message) ?></p>
                                            <small class="text-muted"><?= $notification->getTimeAgo() ?></small>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-center" href="<?= site_url('admin/notifications') ?>">
                                View All Notifications
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="dropdown">
                    <button class="btn btn-link" data-bs-toggle="dropdown">
                        <img src="<?= get_avatar($user) ?>" class="rounded-circle" width="32" height="32">
                        <span class="ms-2"><?= esc($user->username) ?></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="<?= site_url('admin/profile') ?>">
                            <i class="fas fa-user me-2"></i> Profile
                        </a>
                        <a class="dropdown-item" href="<?= site_url('admin/settings') ?>">
                            <i class="fas fa-cog me-2"></i> Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= site_url('logout') ?>">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="content">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title"><?= $title ?></h1>
                <?= breadcrumbs($breadcrumbs, ['class' => 'breadcrumb mb-0']) ?>
            </div>

            <!-- Flash Messages -->
            <?= flash_message() ?>

            <!-- Main Content -->
            <?= $this->renderSection('content') ?>
        </main>

        <!-- Footer -->
        <footer class="content-footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-6">
                        <p class="mb-0">
                            &copy; <?= date('Y') ?> <?= setting('site_name') ?>
                        </p>
                    </div>
                    <div class="col-6 text-end">
                        <p class="mb-0">
                            Version <?= $cms_version ?>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/admin/js/app.js') ?>"></script>

<?= $this->renderSection('scripts') ?>
</body>
</html>