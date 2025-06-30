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
                       class="list-group-item list-group-item-action">
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
                       class="list-group-item list-group-item-action active">
                        <i class="fas fa-share-alt me-2"></i> Social Media
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Social Media Settings</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= current_url() ?>">
                        <?= csrf_field() ?>

                        <h6 class="mb-3">Social Media Profiles</h6>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fab fa-facebook text-primary me-2"></i> Facebook Page URL
                            </label>
                            <input type="url" name="social_facebook" class="form-control"
                                   value="<?= esc($settings['social_facebook'] ?? '') ?>"
                                   placeholder="https://facebook.com/yourpage">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fab fa-twitter text-info me-2"></i> Twitter/X Profile URL
                            </label>
                            <input type="url" name="social_twitter" class="form-control"
                                   value="<?= esc($settings['social_twitter'] ?? '') ?>"
                                   placeholder="https://twitter.com/yourprofile">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fab fa-instagram text-danger me-2"></i> Instagram Profile URL
                            </label>
                            <input type="url" name="social_instagram" class="form-control"
                                   value="<?= esc($settings['social_instagram'] ?? '') ?>"
                                   placeholder="https://instagram.com/yourprofile">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fab fa-linkedin text-primary me-2"></i> LinkedIn Profile/Page URL
                            </label>
                            <input type="url" name="social_linkedin" class="form-control"
                                   value="<?= esc($settings['social_linkedin'] ?? '') ?>"
                                   placeholder="https://linkedin.com/in/yourprofile">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fab fa-youtube text-danger me-2"></i> YouTube Channel URL
                            </label>
                            <input type="url" name="social_youtube" class="form-control"
                                   value="<?= esc($settings['social_youtube'] ?? '') ?>"
                                   placeholder="https://youtube.com/c/yourchannel">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fab fa-tiktok me-2"></i> TikTok Profile URL
                            </label>
                            <input type="url" name="social_tiktok" class="form-control"
                                   value="<?= esc($settings['social_tiktok'] ?? '') ?>"
                                   placeholder="https://tiktok.com/@yourprofile">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fab fa-whatsapp text-success me-2"></i> WhatsApp Number
                            </label>
                            <input type="text" name="social_whatsapp" class="form-control"
                                   value="<?= esc($settings['social_whatsapp'] ?? '') ?>"
                                   placeholder="+62812345678">
                            <small class="text-muted">Include country code (e.g., +62 for Indonesia)</small>
                        </div>

                        <hr>

                        <h6 class="mb-3">Social Sharing Settings</h6>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="social_sharing_enabled" class="form-check-input"
                                       id="sharing_enabled" value="1"
                                    <?= ($settings['social_sharing_enabled'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="sharing_enabled">
                                    Enable social sharing buttons on posts
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Share Buttons to Display</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" name="share_facebook" class="form-check-input"
                                               id="share_fb" value="1"
                                            <?= ($settings['share_facebook'] ?? true) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="share_fb">
                                            Facebook
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="share_twitter" class="form-check-input"
                                               id="share_tw" value="1"
                                            <?= ($settings['share_twitter'] ?? true) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="share_tw">
                                            Twitter/X
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="share_linkedin" class="form-check-input"
                                               id="share_li" value="1"
                                            <?= ($settings['share_linkedin'] ?? true) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="share_li">
                                            LinkedIn
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" name="share_whatsapp" class="form-check-input"
                                               id="share_wa" value="1"
                                            <?= ($settings['share_whatsapp'] ?? true) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="share_wa">
                                            WhatsApp
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="share_telegram" class="form-check-input"
                                               id="share_tg" value="1"
                                            <?= ($settings['share_telegram'] ?? false) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="share_tg">
                                            Telegram
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="share_email" class="form-check-input"
                                               id="share_email" value="1"
                                            <?= ($settings['share_email'] ?? true) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="share_email">
                                            Email
                                        </label>
                                    </div>
                                </div>
                            </div>
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