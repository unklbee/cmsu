<?php
/**
 * Users Index View
 * File: app/Views/admin/users/index.php
 */
?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Users Management</h2>
        <a href="<?= site_url('admin/users/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Add New User
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Groups</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th width="150">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">No users found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user->id ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= get_avatar($user, 32) ?>" class="rounded-circle me-2" width="32" height="32">
                                        <?= esc($user->username) ?>
                                    </div>
                                </td>
                                <td><?= esc($user->email) ?></td>
                                <td>
                                    <?php
                                    $db = \Config\Database::connect();
                                    $userGroups = $db->table('auth_groups_users')
                                        ->where('user_id', $user->id)
                                        ->get()
                                        ->getResultArray();
                                    ?>
                                    <?php foreach ($userGroups as $group): ?>
                                        <span class="badge bg-primary"><?= $group['group'] ?></span>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <?php if ($user->active): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $user->created_at ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= site_url('admin/users/edit/' . $user->id) ?>"
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user->id !== auth()->id()): ?>
                                            <button type="button" class="btn btn-outline-danger"
                                                    onclick="deleteUser(<?= $user->id ?>)" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>
        function deleteUser(id) {
            if (confirm('Are you sure you want to delete this user?')) {
                fetch(`<?= site_url('admin/users') ?>/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message || 'Failed to delete user');
                        }
                    });
            }
        }
    </script>
<?= $this->endSection() ?>