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
                       class="list-group-item list-group-item-action active">
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
                    <h5 class="mb-0">Media Settings</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= current_url() ?>">
                        <?= csrf_field() ?>

                        <h6 class="mb-3">Upload Settings</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Maximum Upload Size (MB)</label>
                                    <input type="number" name="upload_max_size" class="form-control"
                                           value="<?= esc($settings['upload_max_size'] ?? '10') ?>"
                                           min="1" max="100">
                                    <small class="text-muted">Maximum file size allowed for uploads</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Allowed File Types</label>
                                    <input type="text" name="allowed_file_types" class="form-control"
                                           value="<?= esc(is_array($settings['allowed_file_types'] ?? null) ? implode(', ', $settings['allowed_file_types']) : 'jpg, jpeg, png, gif, pdf, doc, docx') ?>"
                                           placeholder="jpg, jpeg, png, gif, pdf">
                                    <small class="text-muted">Comma-separated list of allowed extensions</small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-3">Image Settings</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Maximum Image Width (px)</label>
                                    <input type="number" name="image_max_width" class="form-control"
                                           value="<?= esc($settings['image_max_width'] ?? '2000') ?>"
                                           min="100" max="5000">
                                    <small class="text-muted">Images will be resized if wider</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Maximum Image Height (px)</label>
                                    <input type="number" name="image_max_height" class="form-control"
                                           value="<?= esc($settings['image_max_height'] ?? '2000') ?>"
                                           min="100" max="5000">
                                    <small class="text-muted">Images will be resized if taller</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Image Quality (%)</label>
                            <input type="number" name="image_quality" class="form-control"
                                   value="<?= esc($settings['image_quality'] ?? '85') ?>"
                                   min="10" max="100">
                            <small class="text-muted">JPEG compression quality (10-100)</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="auto_optimize_images" class="form-check-input"
                                       id="auto_optimize" value="1"
                                    <?= ($settings['auto_optimize_images'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="auto_optimize">
                                    Automatically optimize images on upload
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="generate_thumbnails" class="form-check-input"
                                       id="generate_thumbs" value="1"
                                    <?= ($settings['generate_thumbnails'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="generate_thumbs">
                                    Generate thumbnails for images
                                </label>
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-3">Storage Settings</h6>

                        <div class="mb-3">
                            <label class="form-label">Storage Path</label>
                            <input type="text" name="media_storage_path" class="form-control"
                                   value="<?= esc($settings['media_storage_path'] ?? 'uploads/') ?>" readonly>
                            <small class="text-muted">Base path for media storage</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="organize_by_date" class="form-check-input"
                                       id="organize_date" value="1"
                                    <?= ($settings['organize_by_date'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="organize_date">
                                    Organize uploads in year/month folders
                                </label>
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