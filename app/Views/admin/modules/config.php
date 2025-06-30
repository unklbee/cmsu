<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Configure <?= esc($module->display_name) ?></h5>
                </div>
                <div class="card-body">
                    <?php
                    $config = $module->getConfig();
                    if (empty($config)):
                        ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> This module has no configurable options.
                        </div>
                    <?php else: ?>
                        <form method="post" action="<?= site_url('admin/modules/config/' . $module->id) ?>">
                            <?= csrf_field() ?>

                            <?php foreach ($config as $key => $value): ?>
                                <div class="mb-3">
                                    <label class="form-label"><?= esc(ucwords(str_replace('_', ' ', $key))) ?></label>

                                    <?php if (is_bool($value)): ?>
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="config[<?= $key ?>]" value="0">
                                            <input type="checkbox"
                                                   class="form-check-input"
                                                   name="config[<?= $key ?>]"
                                                   value="1"
                                                <?= $value ? 'checked' : '' ?>>
                                        </div>
                                    <?php elseif (is_numeric($value)): ?>
                                        <input type="number"
                                               class="form-control"
                                               name="config[<?= $key ?>]"
                                               value="<?= esc($value) ?>">
                                    <?php elseif (is_array($value)): ?>
                                        <textarea class="form-control"
                                                  name="config[<?= $key ?>]"
                                                  rows="3"><?= esc(json_encode($value, JSON_PRETTY_PRINT)) ?></textarea>
                                        <small class="text-muted">JSON format</small>
                                    <?php else: ?>
                                        <input type="text"
                                               class="form-control"
                                               name="config[<?= $key ?>]"
                                               value="<?= esc($value) ?>">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Configuration
                                </button>
                                <a href="<?= site_url('admin/modules') ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Modules
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Module Information</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Name:</dt>
                        <dd class="col-sm-8"><?= esc($module->name) ?></dd>

                        <dt class="col-sm-4">Version:</dt>
                        <dd class="col-sm-8"><?= esc($module->version) ?></dd>

                        <dt class="col-sm-4">Author:</dt>
                        <dd class="col-sm-8"><?= esc($module->author ?? 'Unknown') ?></dd>

                        <dt class="col-sm-4">Status:</dt>
                        <dd class="col-sm-8">
                            <?php if ($module->status === 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </dd>

                        <dt class="col-sm-4">Installed:</dt>
                        <dd class="col-sm-8"><?= date('Y-m-d', strtotime($module->installed_at)) ?></dd>
                    </dl>

                    <?php if ($module->description): ?>
                        <hr>
                        <p class="mb-0 text-muted"><?= esc($module->description) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($module->permissions)): ?>
                        <hr>
                        <h6>Required Permissions:</h6>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($module->permissions as $permission): ?>
                                <li><code><?= esc($permission) ?></code></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>