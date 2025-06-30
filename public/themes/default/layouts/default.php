/**
* Main Layout
* File: public/themes/default/layouts/default.php
*/
?>
<!DOCTYPE html>
<html lang="<?= config('App')->defaultLocale ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= $this->renderSection('meta') ?>

    <title><?= $title ?? setting('site_name') ?></title>

    <!-- Theme CSS -->
    <?= theme_styles() ?>

    <!-- Custom CSS -->
    <?= $this->renderSection('styles') ?>
</head>
<body class="<?= body_classes() ?>">
<!-- Header -->
<header class="site-header">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="<?= site_url() ?>">
                <?php if ($logo = setting('site_logo')): ?>
                    <img src="<?= media_url($logo) ?>" alt="<?= setting('site_name') ?>" height="40">
                <?php else: ?>
                    <?= setting('site_name') ?>
                <?php endif; ?>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <?= render_menu('main', [
                    'class' => 'navbar-nav ms-auto',
                    'item_class' => 'nav-item',
                    'link_class' => 'nav-link'
                ]) ?>

                <?php if (auth()->loggedIn()): ?>
                    <div class="navbar-nav ms-3">
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <?= auth()->user()->username ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (has_permission('admin.access')): ?>
                                    <li><a class="dropdown-item" href="<?= site_url('admin') ?>">Admin Panel</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?= site_url('profile') ?>">Profile</a></li>
                                <li><a class="dropdown-item" href="<?= site_url('logout') ?>">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="navbar-nav ms-3">
                        <a class="nav-link" href="<?= site_url('login') ?>">Login</a>
                        <a class="nav-link btn btn-primary text-white ms-2" href="<?= site_url('register') ?>">Sign Up</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>

<!-- Flash Messages -->
<?php if (flash_message()): ?>
    <div class="container mt-3">
        <?= flash_message() ?>
    </div>
<?php endif; ?>

<!-- Main Content -->
<main class="site-content py-5">
    <div class="container">
        <?php if (!empty($breadcrumbs)): ?>
            <?= breadcrumbs($breadcrumbs) ?>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>
    </div>
</main>

<!-- Footer -->
<footer class="site-footer bg-dark text-light py-5">
    <div class="container">
        <div class="row">
            <?= dynamic_widgets('footer') ?>
        </div>

        <hr class="my-4">

        <div class="row">
            <div class="col-md-6">
                <p>&copy; <?= date('Y') ?> <?= setting('site_name') ?>. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-end">
                <?= render_menu('footer', [
                    'class' => 'list-inline',
                    'item_class' => 'list-inline-item'
                ]) ?>
            </div>
        </div>
    </div>
</footer>

<!-- Theme Scripts -->
<?= theme_scripts() ?>

<!-- Custom Scripts -->
<?= $this->renderSection('scripts') ?>
</body>
</html>