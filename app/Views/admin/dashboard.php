<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="row g-4 mb-4">
    <!-- Statistics Cards -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Users</h6>
                        <h3 class="mb-0"><?= number_format($stats['total_users']) ?></h3>
                    </div>
                    <i class="fas fa-users fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Active Users</h6>
                        <h3 class="mb-0"><?= number_format($stats['active_users']) ?></h3>
                    </div>
                    <i class="fas fa-user-check fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Media</h6>
                        <h3 class="mb-0"><?= number_format($stats['total_media']) ?></h3>
                    </div>
                    <i class="fas fa-images fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Active Modules</h6>
                        <h3 class="mb-0"><?= number_format($stats['total_modules']) ?></h3>
                    </div>
                    <i class="fas fa-puzzle-piece fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Activities -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Recent Activities</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recent_activities)): ?>
                    <p class="text-muted text-center py-3">No activities found</p>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">
                                        <i class="<?= $activity->getActionIcon() ?> me-1"></i>
                                        <?= esc($activity->getFormattedDescription()) ?>
                                    </h6>
                                    <small class="text-muted">
                                        <?= $activity->created_at ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- System Info -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">System Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td>PHP Version</td>
                        <td class="text-end"><?= $system_info['php_version'] ?></td>
                    </tr>
                    <tr>
                        <td>CodeIgniter</td>
                        <td class="text-end"><?= $system_info['ci_version'] ?></td>
                    </tr>
                    <tr>
                        <td>CMS Version</td>
                        <td class="text-end"><?= $system_info['cms_version'] ?></td>
                    </tr>
                    <tr>
                        <td>Environment</td>
                        <td class="text-end">
                            <span class="badge bg-<?= $system_info['environment'] === 'production' ? 'success' : 'warning' ?>">
                                <?= $system_info['environment'] ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>Database</td>
<!--                        <td class="text-end">--><?php //= $system_info['database'] ?><!--</td>-->
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }

    .timeline-item:before {
        content: '';
        position: absolute;
        left: -21px;
        top: 10px;
        height: calc(100% - 10px);
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item:last-child:before {
        display: none;
    }

    .timeline-marker {
        position: absolute;
        left: -25px;
        top: 5px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #007bff;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #e9ecef;
    }
</style>
<?= $this->endSection() ?>
