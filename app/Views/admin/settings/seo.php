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
                       class="list-group-item list-group-item-action active">
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
                    <h5 class="mb-0">SEO Settings</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= current_url() ?>">
                        <?= csrf_field() ?>

                        <h6 class="mb-3">Meta Tags</h6>

                        <div class="mb-3">
                            <label class="form-label">Default Meta Title</label>
                            <input type="text" name="seo_meta_title" class="form-control"
                                   value="<?= esc($settings['seo_meta_title'] ?? cms_setting('site_name')) ?>"
                                   maxlength="60">
                            <small class="text-muted">Default title for pages without specific title (max 60 chars)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Default Meta Description</label>
                            <textarea name="seo_meta_description" class="form-control" rows="3"
                                      maxlength="160"><?= esc($settings['seo_meta_description'] ?? cms_setting('site_description')) ?></textarea>
                            <small class="text-muted">Default description for pages (max 160 chars)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Default Meta Keywords</label>
                            <input type="text" name="seo_meta_keywords" class="form-control"
                                   value="<?= esc($settings['seo_meta_keywords'] ?? cms_setting('site_keywords')) ?>"
                                   placeholder="keyword1, keyword2, keyword3">
                            <small class="text-muted">Comma-separated keywords</small>
                        </div>

                        <hr>

                        <h6 class="mb-3">Open Graph Settings</h6>

                        <div class="mb-3">
                            <label class="form-label">Default OG Image</label>
                            <input type="text" name="seo_og_image" class="form-control"
                                   value="<?= esc($settings['seo_og_image'] ?? '') ?>"
                                   placeholder="https://example.com/og-image.jpg">
                            <small class="text-muted">Default image for social media sharing (1200x630 recommended)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Site Type</label>
                            <select name="seo_og_type" class="form-select">
                                <option value="website" <?= ($settings['seo_og_type'] ?? 'website') === 'website' ? 'selected' : '' ?>>
                                    Website
                                </option>
                                <option value="blog" <?= ($settings['seo_og_type'] ?? '') === 'blog' ? 'selected' : '' ?>>
                                    Blog
                                </option>
                                <option value="business" <?= ($settings['seo_og_type'] ?? '') === 'business' ? 'selected' : '' ?>>
                                    Business
                                </option>
                            </select>
                        </div>

                        <hr>

                        <h6 class="mb-3">Search Engine Settings</h6>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="seo_index_site" class="form-check-input"
                                       id="index_site" value="1"
                                    <?= ($settings['seo_index_site'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="index_site">
                                    Allow search engines to index this site
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="seo_follow_links" class="form-check-input"
                                       id="follow_links" value="1"
                                    <?= ($settings['seo_follow_links'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="follow_links">
                                    Allow search engines to follow links
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Google Analytics ID</label>
                            <input type="text" name="seo_google_analytics" class="form-control"
                                   value="<?= esc($settings['seo_google_analytics'] ?? '') ?>"
                                   placeholder="UA-XXXXX-Y or G-XXXXXXX">
                            <small class="text-muted">Your Google Analytics tracking ID</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Google Search Console Verification</label>
                            <input type="text" name="seo_google_verification" class="form-control"
                                   value="<?= esc($settings['seo_google_verification'] ?? '') ?>"
                                   placeholder="Verification code">
                            <small class="text-muted">Google Search Console verification meta tag content</small>
                        </div>

                        <hr>

                        <h6 class="mb-3">Sitemap Settings</h6>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="seo_enable_sitemap" class="form-check-input"
                                       id="enable_sitemap" value="1"
                                    <?= ($settings['seo_enable_sitemap'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="enable_sitemap">
                                    Enable XML sitemap
                                </label>
                            </div>
                            <small class="text-muted">Sitemap URL: <?= site_url('sitemap.xml') ?></small>
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