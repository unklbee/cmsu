<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
    <div class="row">
        <div class="col-md-3">
            <!-- Settings Menu -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Settings</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?= site_url('admin/settings/general') ?>"
                       class="list-group-item list-group-item-action active">
                        <i class="fas fa-cog me-2"></i> General
                    </a>
                    <a href="<?= site_url('admin/settings/email') ?>"
                       class="list-group-item list-group-item-action">
                        <i class="fas fa-envelope me-2"></i> Email
                    </a>
                    <a href="<?= site_url('admin/settings/media') ?>"
                       class="list-group-item list-group-item-action">
                        <i class="fas fa-images me-2"></i> Media
                    </a>
                    <a href="<?= site_url('admin/settings/seo') ?>"
                       class="list-group-item list-group-item-action">
                        <i class="fas fa-search me-2"></i> SEO
                    </a>
                    <a href="<?= site_url('admin/settings/social') ?>"
                       class="list-group-item list-group-item-action">
                        <i class="fas fa-share-alt me-2"></i> Social Media
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">General Settings</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= current_url() ?>">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label">Site Name</label>
                            <input type="text" name="site_name" class="form-control"
                                   value="<?= esc($settings['site_name'] ?? '') ?>" required>
                            <small class="text-muted">The name of your website</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Site Description</label>
                            <textarea name="site_description" class="form-control" rows="3"><?= esc($settings['site_description'] ?? '') ?></textarea>
                            <small class="text-muted">Brief description of your website</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Site Email</label>
                            <input type="email" name="site_email" class="form-control"
                                   value="<?= esc($settings['site_email'] ?? '') ?>" required>
                            <small class="text-muted">Primary contact email address</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Site Keywords (SEO)</label>
                            <input type="text" name="site_keywords" class="form-control"
                                   value="<?= esc($settings['site_keywords'] ?? '') ?>"
                                   placeholder="keyword1, keyword2, keyword3">
                            <small class="text-muted">Comma-separated keywords for SEO</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date Format</label>
                                    <select name="date_format" class="form-select">
                                        <option value="Y-m-d" <?= ($settings['date_format'] ?? 'Y-m-d') === 'Y-m-d' ? 'selected' : '' ?>>
                                            <?= date('Y-m-d') ?> (Y-m-d)
                                        </option>
                                        <option value="d/m/Y" <?= ($settings['date_format'] ?? '') === 'd/m/Y' ? 'selected' : '' ?>>
                                            <?= date('d/m/Y') ?> (d/m/Y)
                                        </option>
                                        <option value="m/d/Y" <?= ($settings['date_format'] ?? '') === 'm/d/Y' ? 'selected' : '' ?>>
                                            <?= date('m/d/Y') ?> (m/d/Y)
                                        </option>
                                        <option value="F j, Y" <?= ($settings['date_format'] ?? '') === 'F j, Y' ? 'selected' : '' ?>>
                                            <?= date('F j, Y') ?> (F j, Y)
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Time Format</label>
                                    <select name="time_format" class="form-select">
                                        <option value="H:i:s" <?= ($settings['time_format'] ?? 'H:i:s') === 'H:i:s' ? 'selected' : '' ?>>
                                            <?= date('H:i:s') ?> (24-hour)
                                        </option>
                                        <option value="g:i A" <?= ($settings['time_format'] ?? '') === 'g:i A' ? 'selected' : '' ?>>
                                            <?= date('g:i A') ?> (12-hour)
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Timezone</label>
                            <select name="timezone" class="form-select">
                                <?php foreach (timezone_identifiers_list() as $tz): ?>
                                    <option value="<?= $tz ?>" <?= ($settings['timezone'] ?? 'UTC') === $tz ? 'selected' : '' ?>>
                                        <?= $tz ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Choose your local timezone</small>
                        </div>

                        <hr>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>