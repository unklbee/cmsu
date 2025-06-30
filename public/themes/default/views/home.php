<?php
/**
 * Homepage Template
 * File: public/themes/default/views/home.php
 */
?>
<?= $this->extend(theme()->getThemePath('layouts/default')) ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-lg-8">
        <!-- Hero Section -->
        <section class="hero mb-5">
            <div class="p-5 bg-light rounded">
                <h1 class="display-4"><?= setting('site_name') ?></h1>
                <p class="lead"><?= setting('site_description') ?></p>
                <a class="btn btn-primary btn-lg" href="<?= site_url('about') ?>">Learn More</a>
            </div>
        </section>

        <!-- Recent Posts -->
        <?php if (module_enabled('blog')): ?>
            <section class="recent-posts">
                <h2 class="mb-4">Recent Posts</h2>
                <div class="row">
                    <?php foreach ($recent_posts as $post): ?>
                        <div class="col-md-6 mb-4">
                            <article class="card h-100">
                                <?php if ($post->featured_image): ?>
                                    <img src="<?= media_url($post->featured_image, 'medium') ?>"
                                         class="card-img-top"
                                         alt="<?= esc($post->title) ?>">
                                <?php endif; ?>

                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="<?= site_url('blog/post/' . $post->slug) ?>">
                                            <?= esc($post->title) ?>
                                        </a>
                                    </h5>
                                    <p class="card-text"><?= character_limiter($post->excerpt, 150) ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted"><?= time_ago($post->published_at) ?></small>
                                        <a href="<?= site_url('blog/post/' . $post->slug) ?>" class="btn btn-sm btn-outline-primary">
                                            Read More
                                        </a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <!-- Sidebar -->
        <aside class="sidebar">
            <?= dynamic_widgets('sidebar') ?>
        </aside>
    </div>
</div>
<?= $this->endSection() ?>
