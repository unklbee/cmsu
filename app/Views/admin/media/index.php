<?php
/**
 * Media Manager Index View
 * File: app/Views/admin/media/index.php
 */
?>
<?= $this->extend('admin/layout') ?>

<?= $this->section('styles') ?>
    <style>
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .media-item {
            position: relative;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s;
        }

        .media-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .media-item.selected {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.25);
        }

        .media-preview {
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            overflow: hidden;
        }

        .media-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

        .media-info {
            padding: 10px;
            background: white;
        }

        .media-name {
            font-size: 14px;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .media-meta {
            font-size: 12px;
            color: #6c757d;
        }

        .media-checkbox {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1;
        }

        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s;
        }

        .upload-area:hover {
            border-color: #007bff;
            background: #e9ecef;
        }

        .upload-area.dragover {
            border-color: #007bff;
            background: #e7f3ff;
        }

        .media-filters {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .media-sidebar {
            position: sticky;
            top: 80px;
        }
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Media Library</h2>
        <div>
            <button class="btn btn-danger me-2" id="deleteSelected" style="display:none;">
                <i class="fas fa-trash me-2"></i> Delete Selected
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fas fa-upload me-2"></i> Upload Files
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-9">
            <!-- Filters -->
            <div class="media-filters">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select class="form-select" id="typeFilter">
                            <option value="">All Types</option>
                            <option value="image" <?= $current_type === 'image' ? 'selected' : '' ?>>Images</option>
                            <option value="video" <?= $current_type === 'video' ? 'selected' : '' ?>>Videos</option>
                            <option value="audio" <?= $current_type === 'audio' ? 'selected' : '' ?>>Audio</option>
                            <option value="document" <?= $current_type === 'document' ? 'selected' : '' ?>>Documents</option>
                            <option value="other" <?= $current_type === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="folderFilter">
                            <option value="">All Folders</option>
                            <?php foreach ($folders as $folder): ?>
                                <option value="<?= $folder ?>" <?= $current_folder === $folder ? 'selected' : '' ?>>
                                    <?= $folder ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search files...">
                    </div>
                    <div class="col-md-2">
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-secondary active" id="gridView">
                                <i class="fas fa-th"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="listView">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Media Grid -->
            <div class="media-grid" id="mediaGrid">
                <?php if (empty($media)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No media files found</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($media as $item): ?>
                        <div class="media-item" data-id="<?= $item->id ?>" data-info='<?= json_encode($item) ?>'>
                            <input type="checkbox" class="form-check-input media-checkbox" value="<?= $item->id ?>">
                            <div class="media-preview">
                                <?php if ($item->type === 'image'): ?>
                                    <img src="<?= $item->getThumbnail('medium') ?>" alt="<?= esc($item->alt_text) ?>">
                                <?php else: ?>
                                    <i class="fas fa-<?= $item->getTypeIcon() ?> fa-3x text-muted"></i>
                                <?php endif; ?>
                            </div>
                            <div class="media-info">
                                <p class="media-name" title="<?= esc($item->original_name) ?>">
                                    <?= esc($item->original_name) ?>
                                </p>
                                <div class="media-meta">
                                    <?= $item->getSizeFormatted() ?> • <?= date('M d, Y', strtotime($item->created_at)) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-3">
            <!-- Media Details Sidebar -->
            <div class="card border-0 shadow-sm media-sidebar" id="mediaDetails" style="display:none;">
                <div class="card-body">
                    <h5 class="card-title">File Details</h5>
                    <div id="detailsContent"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Files</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="upload-area" id="uploadArea">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <h5>Drag files here or click to browse</h5>
                        <p class="text-muted">Maximum file size: <?= cms_setting('upload_max_size', 10) ?>MB</p>
                        <input type="file" id="fileInput" multiple style="display:none;">
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Upload to folder:</label>
                        <input type="text" class="form-control" id="uploadFolder"
                               value="<?= date('Y/m') ?>" placeholder="e.g., 2024/01">
                    </div>

                    <div id="uploadProgress" class="mt-3" style="display:none;">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" style="width: 0%"></div>
                        </div>
                        <div id="uploadStatus" class="mt-2"></div>
                    </div>

                    <div id="uploadedFiles" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="startUpload" style="display:none;">
                        <i class="fas fa-upload me-2"></i> Start Upload
                    </button>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>
        let selectedFiles = [];
        let selectedMedia = [];

        // Upload functionality
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const uploadProgress = document.getElementById('uploadProgress');
        const startUploadBtn = document.getElementById('startUpload');

        uploadArea.addEventListener('click', () => fileInput.click());

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            selectedFiles = Array.from(files);
            displaySelectedFiles();
            startUploadBtn.style.display = 'inline-block';
        }

        function displaySelectedFiles() {
            const uploadedFiles = document.getElementById('uploadedFiles');
            uploadedFiles.innerHTML = '<h6>Selected Files:</h6>';

            selectedFiles.forEach((file, index) => {
                const fileDiv = document.createElement('div');
                fileDiv.className = 'border rounded p-2 mb-2 d-flex justify-content-between align-items-center';
                fileDiv.innerHTML = `
            <div>
                <i class="fas fa-file me-2"></i>
                ${file.name} (${formatFileSize(file.size)})
            </div>
            <button class="btn btn-sm btn-danger" onclick="removeFile(${index})">
                <i class="fas fa-times"></i>
            </button>
        `;
                uploadedFiles.appendChild(fileDiv);
            });
        }

        function removeFile(index) {
            selectedFiles.splice(index, 1);
            displaySelectedFiles();
            if (selectedFiles.length === 0) {
                startUploadBtn.style.display = 'none';
            }
        }

        function formatFileSize(bytes) {
            const units = ['B', 'KB', 'MB', 'GB'];
            let size = bytes;
            let unitIndex = 0;

            while (size >= 1024 && unitIndex < units.length - 1) {
                size /= 1024;
                unitIndex++;
            }

            return `${size.toFixed(2)} ${units[unitIndex]}`;
        }

        // Start upload
        startUploadBtn.addEventListener('click', async () => {
            if (selectedFiles.length === 0) return;

            uploadProgress.style.display = 'block';
            startUploadBtn.disabled = true;

            const folder = document.getElementById('uploadFolder').value;
            const formData = new FormData();

            selectedFiles.forEach(file => {
                formData.append('files[]', file);
            });
            formData.append('folder', folder);

            try {
                const response = await fetch('<?= site_url('admin/media/upload') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message || 'Upload failed');
                }
            } catch (error) {
                alert('Upload error: ' + error.message);
            } finally {
                uploadProgress.style.display = 'none';
                startUploadBtn.disabled = false;
            }
        });

        // Media selection
        document.querySelectorAll('.media-item').forEach(item => {
            item.addEventListener('click', function(e) {
                if (e.target.type === 'checkbox') return;

                const checkbox = this.querySelector('.media-checkbox');
                checkbox.checked = !checkbox.checked;

                this.classList.toggle('selected', checkbox.checked);
                updateSelection();

                // Show details for last selected item
                if (checkbox.checked) {
                    showMediaDetails(JSON.parse(this.dataset.info));
                }
            });
        });

        document.querySelectorAll('.media-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                this.closest('.media-item').classList.toggle('selected', this.checked);
                updateSelection();
            });
        });

        function updateSelection() {
            selectedMedia = Array.from(document.querySelectorAll('.media-checkbox:checked'))
                .map(cb => cb.value);

            document.getElementById('deleteSelected').style.display =
                selectedMedia.length > 0 ? 'inline-block' : 'none';
        }

        function showMediaDetails(media) {
            const details = document.getElementById('mediaDetails');
            const content = document.getElementById('detailsContent');

            let html = `
        <div class="mb-3">
            ${media.type === 'image'
                ? `<img src="${media.url}" class="img-fluid rounded mb-3">`
                : `<i class="fas fa-${media.type === 'video' ? 'video' : media.type === 'audio' ? 'music' : media.type === 'document' ? 'file-alt' : 'file'} fa-3x text-muted"></i>`
            }
        </div>
        <dl>
            <dt>File Name</dt>
            <dd>${media.original_name}</dd>

            <dt>File Type</dt>
            <dd>${media.mime_type}</dd>

            <dt>File Size</dt>
            <dd>${formatFileSize(media.size)}</dd>

            <dt>Uploaded</dt>
            <dd>${new Date(media.created_at).toLocaleDateString()}</dd>

            <dt>URL</dt>
            <dd><input type="text" class="form-control form-control-sm" value="${media.url}" readonly></dd>
        </dl>

        <div class="d-grid gap-2">
            <a href="${media.url}" class="btn btn-sm btn-primary" target="_blank">
                <i class="fas fa-external-link-alt me-2"></i> View File
            </a>
            <button class="btn btn-sm btn-danger" onclick="deleteMedia(${media.id})">
                <i class="fas fa-trash me-2"></i> Delete
            </button>
        </div>
    `;

            content.innerHTML = html;
            details.style.display = 'block';
        }

        // Delete selected
        document.getElementById('deleteSelected').addEventListener('click', () => {
            if (!confirm(`Delete ${selectedMedia.length} selected files?`)) return;

            selectedMedia.forEach(id => deleteMedia(id));
        });

        async function deleteMedia(id) {
            if (!confirm('Are you sure you want to delete this file?')) return;

            try {
                const response = await fetch(`<?= site_url('admin/media') ?>/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message || 'Delete failed');
                }
            } catch (error) {
                alert('Delete error: ' + error.message);
            }
        }

        // Filters
        document.getElementById('typeFilter').addEventListener('change', function() {
            updateFilters();
        });

        document.getElementById('folderFilter').addEventListener('change', function() {
            updateFilters();
        });

        function updateFilters() {
            const type = document.getElementById('typeFilter').value;
            const folder = document.getElementById('folderFilter').value;

            let url = '<?= site_url('admin/media') ?>';
            const params = new URLSearchParams();

            if (type) params.append('type', type);
            if (folder) params.append('folder', folder);

            if (params.toString()) {
                url += '?' + params.toString();
            }

            window.location.href = url;
        }

        // Search
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.toLowerCase();

            searchTimeout = setTimeout(() => {
                document.querySelectorAll('.media-item').forEach(item => {
                    const name = item.querySelector('.media-name').textContent.toLowerCase();
                    item.style.display = name.includes(query) ? '' : 'none';
                });
            }, 300);
        });

        // View toggle
        document.getElementById('gridView').addEventListener('click', function() {
            document.getElementById('mediaGrid').className = 'media-grid';
            this.classList.add('active');
            document.getElementById('listView').classList.remove('active');
        });

        document.getElementById('listView').addEventListener('click', function() {
            document.getElementById('mediaGrid').className = 'list-group';
            this.classList.add('active');
            document.getElementById('gridView').classList.remove('active');
        });
    </script>
<?= $this->endSection() ?>