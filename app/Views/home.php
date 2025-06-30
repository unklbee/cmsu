<!DOCTYPE html>
<html lang="<?= config('App')->defaultLocale ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <meta name="description" content="<?= esc($description) ?>">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
        }

        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }

        .card {
            transition: transform 0.3s;
            height: 100%;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        footer {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 50px 0 20px;
            margin-top: 80px;
        }
    </style>
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= site_url() ?>">
            <?= cms_setting('site_name') ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url() ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('about') ?>">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('contact') ?>">Contact</a>
                </li>
                <?php if (auth()->loggedIn()): ?>
                    <?php if (has_permission('admin.access')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('admin') ?>">Admin Panel</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('logout') ?>">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('login') ?>">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-4"><?= esc($title) ?></h1>
        <p class="lead mb-5"><?= esc($description) ?></p>
        <a href="<?= site_url('about') ?>" class="btn btn-light btn-lg me-3">Learn More</a>
        <a href="<?= site_url('contact') ?>" class="btn btn-outline-light btn-lg">Get Started</a>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Features</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center p-5">
                        <i class="fas fa-rocket fa-3x text-primary mb-3"></i>
                        <h4>Fast & Secure</h4>
                        <p>Built with performance and security in mind using CodeIgniter 4 framework.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center p-5">
                        <i class="fas fa-puzzle-piece fa-3x text-primary mb-3"></i>
                        <h4>Modular System</h4>
                        <p>Extend functionality with our powerful module system.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center p-5">
                        <i class="fas fa-mobile-alt fa-3x text-primary mb-3"></i>
                        <h4>Responsive Design</h4>
                        <p>Beautiful on all devices with mobile-first approach.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Posts (if blog module is active) -->
<?php if (isset($recent_posts) && !empty($recent_posts)): ?>
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Recent Posts</h2>
            <div class="row g-4">
                <?php foreach ($recent_posts as $post): ?>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="<?= site_url('blog/post/' . $post->slug) ?>" class="text-decoration-none">
                                        <?= esc($post->title) ?>
                                    </a>
                                </h5>
                                <p class="card-text"><?= character_limiter($post->excerpt, 150) ?></p>
                                <small class="text-muted">
                                    <i class="far fa-calendar"></i> <?= $post->published_at ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5><?= cms_setting('site_name') ?></h5>
                <p><?= cms_setting('site_description') ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <p>&copy; <?= date('Y') ?> <?= cms_setting('site_name') ?>. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>