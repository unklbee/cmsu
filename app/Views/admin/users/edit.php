<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Edit User: <?= esc($user->username) ?></h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('admin/users/update/' . $user->id) ?>">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control"
                                   value="<?= old('username', $user->username) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= old('email', $user->email) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control"
                                   placeholder="Leave blank to keep current password">
                            <small class="text-muted">Only fill this if you want to change the password</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Groups</label>
                            <div>
                                <?php foreach ($groups as $key => $name): ?>
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" name="groups[]" value="<?= $key ?>"
                                               class="form-check-input" id="group_<?= $key ?>"
                                            <?= in_array($key, $userGroups) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="group_<?= $key ?>">
                                            <?= $name ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="active" class="form-select">
                                <option value="1" <?= $user->active ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= !$user->active ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Update User
                            </button>
                            <a href="<?= site_url('admin/users') ?>" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">User Information</h5>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>User ID</dt>
                        <dd><?= $user->id ?></dd>

                        <dt>Created</dt>
                        <dd><?= $user->created_at ?></dd>

                        <dt>Last Updated</dt>
                        <dd><?= $user->updated_at ?></dd>

                        <dt>Last Active</dt>
                        <dd><?= $user->last_active ?? 'Never' ?></dd>
                    </dl>

                    <?php if ($user->id === auth()->id()): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            This is your account
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>