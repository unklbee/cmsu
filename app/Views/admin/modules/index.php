<?= $this->extend('admin/layout') ?>

<?= $this->section('page_actions') ?>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scanModulesModal">
        <i class="fas fa-search"></i> Scan for Modules
    </button>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="row">
        <div class="col-12">
            <!-- Installed Modules -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Installed Modules</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($modules)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No modules installed yet.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Version</th>
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($modules as $module): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($module->display_name) ?></strong><br>
                                            <small class="text-muted"><?= esc($module->description ?? 'No description') ?></small>
                                        </td>
                                        <td><?= esc($module->version) ?></td>
                                        <td><?= esc($module->author ?? 'Unknown') ?></td>
                                        <td>
                                            <?php if ($module->status === 'active'): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php elseif ($module->status === 'inactive'): ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Error</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <form method="post" action="<?= site_url('admin/modules/toggle/' . $module->id) ?>" style="display: inline;">
                                                    <?= csrf_field() ?>
                                                    <?php if ($module->status === 'active'): ?>
                                                        <button type="submit" class="btn btn-warning"
                                                                onclick="return confirm('Disable this module?')">
                                                            <i class="fas fa-power-off"></i> Disable
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="fas fa-power-off"></i> Enable
                                                        </button>
                                                    <?php endif; ?>
                                                </form>

                                                <a href="<?= site_url('admin/modules/config/' . $module->id) ?>"
                                                   class="btn btn-info">
                                                    <i class="fas fa-cog"></i> Configure
                                                </a>

                                                <a href="<?= site_url('admin/modules/uninstall/' . $module->id) ?>"
                                                   class="btn btn-danger"
                                                   onclick="return confirm('Uninstall this module? This action cannot be undone.')">
                                                    <i class="fas fa-trash"></i> Uninstall
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scan Modules Modal -->
    <div class="modal fade" id="scanModulesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Available Modules</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (empty($available_modules)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No new modules found in the Modules directory.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Version</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($available_modules as $name => $config): ?>
                                    <?php
                                    $isInstalled = false;
                                    foreach ($modules as $module) {
                                        if ($module->name === $name) {
                                            $isInstalled = true;
                                            break;
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($config['display_name'] ?? $name) ?></strong><br>
                                            <small class="text-muted"><?= esc($config['description'] ?? 'No description') ?></small>
                                        </td>
                                        <td><?= esc($config['version'] ?? '1.0.0') ?></td>
                                        <td>
                                            <?php if ($isInstalled): ?>
                                                <span class="badge bg-success">Installed</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Not Installed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!$isInstalled): ?>
                                                <a href="<?= site_url('admin/modules/install/' . $name) ?>"
                                                   class="btn btn-sm btn-primary"
                                                   onclick="return confirm('Install this module?')">
                                                    <i class="fas fa-download"></i> Install
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-secondary" disabled>
                                                    <i class="fas fa-check"></i> Installed
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .table td {
            vertical-align: middle;
        }
    </style>

<?= $this->endSection() ?>