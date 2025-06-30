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
                       class="list-group-item list-group-item-action <?= current_url() == site_url('admin/settings/general') ? 'active' : '' ?>">
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
                    <a href="<?= site_url('admin/settings/api') ?>"
                       class="list-group-item list-group-item-action">
                        <i class="fas fa-key me-2"></i> API
                    </a>
                    <a href="<?= site_url('admin/settings/maintenance') ?>"
                       class="list-group-item list-group-item-action">
                        <i class="fas fa-tools me-2"></i> Maintenance
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Settings Groups -->
            <div class="row g-4">
                <?php foreach ($settings as $group => $items): ?>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white">
                                <h5 class="mb-0 text-capitalize">
                                    <i class="fas fa-folder me-2"></i> <?= str_replace('_', ' ', $group) ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <tbody>
                                        <?php foreach ($items as $key => $value): ?>
                                            <tr>
                                                <td class="text-muted"><?= str_replace('_', ' ', $key) ?></td>
                                                <td class="text-end">
                                                    <?php if (is_bool($value)): ?>
                                                        <span class="badge bg-<?= $value ? 'success' : 'secondary' ?>">
                                                            <?= $value ? 'Yes' : 'No' ?>
                                                        </span>
                                                    <?php elseif (is_array($value)): ?>
                                                        <code><?= json_encode($value) ?></code>
                                                    <?php else: ?>
                                                        <?= character_limiter(esc($value), 50) ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="<?= site_url('admin/settings/' . $group) ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>