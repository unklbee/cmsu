<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
    <div class="row">
        <div class="col-md-3">
            <!-- Settings Menu (same as above) -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Settings</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?= site_url('admin/settings/general') ?>"
                       class="list-group-item list-group-item-action">
                        <i class="fas fa-cog me-2"></i> General
                    </a>
                    <a href="<?= site_url('admin/settings/email') ?>"
                       class="list-group-item list-group-item-action active">
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
                    <h5 class="mb-0">Email Settings</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= current_url() ?>">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label">Email Protocol</label>
                            <select name="email_protocol" class="form-select">
                                <option value="mail" <?= ($settings['email_protocol'] ?? 'mail') === 'mail' ? 'selected' : '' ?>>
                                    PHP Mail
                                </option>
                                <option value="smtp" <?= ($settings['email_protocol'] ?? '') === 'smtp' ? 'selected' : '' ?>>
                                    SMTP
                                </option>
                                <option value="sendmail" <?= ($settings['email_protocol'] ?? '') === 'sendmail' ? 'selected' : '' ?>>
                                    Sendmail
                                </option>
                            </select>
                        </div>

                        <div id="smtp-settings" style="display: <?= ($settings['email_protocol'] ?? 'mail') === 'smtp' ? 'block' : 'none' ?>">
                            <h6 class="mb-3">SMTP Settings</h6>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">SMTP Host</label>
                                        <input type="text" name="smtp_host" class="form-control"
                                               value="<?= esc($settings['smtp_host'] ?? '') ?>"
                                               placeholder="smtp.gmail.com">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">SMTP Port</label>
                                        <input type="number" name="smtp_port" class="form-control"
                                               value="<?= esc($settings['smtp_port'] ?? '587') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">SMTP Username</label>
                                <input type="text" name="smtp_user" class="form-control"
                                       value="<?= esc($settings['smtp_user'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">SMTP Password</label>
                                <input type="password" name="smtp_pass" class="form-control"
                                       placeholder="Enter to change">
                                <small class="text-muted">Leave blank to keep current password</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">SMTP Encryption</label>
                                <select name="smtp_crypto" class="form-select">
                                    <option value="" <?= ($settings['smtp_crypto'] ?? '') === '' ? 'selected' : '' ?>>
                                        None
                                    </option>
                                    <option value="tls" <?= ($settings['smtp_crypto'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>
                                        TLS
                                    </option>
                                    <option value="ssl" <?= ($settings['smtp_crypto'] ?? '') === 'ssl' ? 'selected' : '' ?>>
                                        SSL
                                    </option>
                                </select>
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-3">Email Templates</h6>

                        <div class="mb-3">
                            <label class="form-label">From Email</label>
                            <input type="email" name="email_from" class="form-control"
                                   value="<?= esc($settings['email_from'] ?? cms_setting('site_email')) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">From Name</label>
                            <input type="text" name="email_from_name" class="form-control"
                                   value="<?= esc($settings['email_from_name'] ?? cms_setting('site_name')) ?>">
                        </div>

                        <hr>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Save Settings
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="testEmail()">
                                <i class="fas fa-paper-plane me-2"></i> Send Test Email
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>
        // Show/hide SMTP settings based on protocol
        document.querySelector('[name="email_protocol"]').addEventListener('change', function() {
            document.getElementById('smtp-settings').style.display =
                this.value === 'smtp' ? 'block' : 'none';
        });

        // Test email function
        function testEmail() {
            // Add AJAX call to send test email
            alert('Test email functionality to be implemented');
        }
    </script>
<?= $this->endSection() ?>