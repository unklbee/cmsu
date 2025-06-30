<?php

namespace App\Models\CMS;

use App\Entities\CMS\MediaEntity;
use CodeIgniter\Files\File;
use CodeIgniter\HTTP\Files\UploadedFile;

class MediaModel extends BaseModel
{
    protected $table = 'cms_media';
    protected $primaryKey = 'id';
    protected $returnType = MediaEntity::class;
    protected $allowedFields = [
        'user_id', 'filename', 'original_name', 'mime_type',
        'size', 'path', 'url', 'type', 'alt_text',
        'description', 'metadata', 'folder', 'is_public'
    ];

    protected $validationRules = [
        'filename' => 'required|string',
        'original_name' => 'required|string',
        'mime_type' => 'required|string',
        'size' => 'required|numeric',
        'path' => 'required|string',
        'type' => 'required|in_list[image,video,audio,document,other]'
    ];

    protected array $casts = [
        'metadata' => 'json',
        'is_public' => 'boolean',
        'size' => 'integer'
    ];

    private $uploadPath = WRITEPATH . 'uploads/';
    private $publicPath = FCPATH . 'uploads/';
    private $allowedMimes = [
        'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
        'video' => ['video/mp4', 'video/mpeg', 'video/quicktime', 'video/webm'],
        'audio' => ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp4'],
        'document' => [
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain', 'text/csv'
        ]
    ];

    /**
     * Upload file
     */
    public function upload(UploadedFile $file, array $options = []): ?MediaEntity
    {
        if (!$file->isValid()) {
            throw new \Exception($file->getErrorString());
        }

        // Validate file type
        $mimeType = $file->getMimeType();
        $fileType = $this->detectFileType($mimeType);

        // Generate unique filename
        $newName = $this->generateFileName($file);
        $folder = $options['folder'] ?? date('Y/m');
        $targetPath = $this->getUploadPath($folder, $options['is_public'] ?? true);

        // Create directory if not exists
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
        }

        // Move file
        $file->move($targetPath, $newName);

        // Process image if needed
        if ($fileType === 'image' && ($options['process'] ?? true)) {
            $this->processImage($targetPath . $newName, $options);
        }

        // Generate thumbnails
        $thumbnails = [];
        if ($fileType === 'image' && ($options['thumbnails'] ?? true)) {
            $thumbnails = $this->generateThumbnails($targetPath . $newName, $folder);
        }

        // Save to database
        $data = [
            'user_id' => user_id() ?? 0,
            'filename' => $newName,
            'original_name' => $file->getClientName(),
            'mime_type' => $mimeType,
            'size' => $file->getSize(),
            'path' => $folder . '/' . $newName,
            'url' => $this->getFileUrl($folder . '/' . $newName, $options['is_public'] ?? true),
            'type' => $fileType,
            'alt_text' => $options['alt_text'] ?? null,
            'description' => $options['description'] ?? null,
            'metadata' => array_merge([
                'extension' => $file->getExtension(),
                'thumbnails' => $thumbnails
            ], $options['metadata'] ?? []),
            'folder' => $folder,
            'is_public' => $options['is_public'] ?? true
        ];

        $mediaId = $this->insert($data);

        return $mediaId ? $this->find($mediaId) : null;
    }

    /**
     * Upload multiple files
     */
    public function uploadMultiple(array $files, array $options = []): array
    {
        $uploaded = [];

        foreach ($files as $file) {
            try {
                $uploaded[] = $this->upload($file, $options);
            } catch (\Exception $e) {
                log_message('error', 'Failed to upload file: ' . $e->getMessage());
            }
        }

        return $uploaded;
    }

    /**
     * Upload from URL
     */
    public function uploadFromUrl(string $url, array $options = []): ?MediaEntity
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'download');

        // Download file
        $client = \Config\Services::curlrequest();
        $response = $client->get($url, ['sink' => $tempFile]);

        if ($response->getStatusCode() !== 200) {
            unlink($tempFile);
            throw new \Exception('Failed to download file');
        }

        // Get file info
        $mimeType = $response->getHeaderLine('Content-Type') ?: 'application/octet-stream';
        $filename = basename(parse_url($url, PHP_URL_PATH)) ?: 'download';
        $size = filesize($tempFile);

        // Create UploadedFile instance
        $file = new \CodeIgniter\HTTP\Files\UploadedFile($tempFile, $filename, $mimeType, $size, null, true);

        // Upload
        $result = $this->upload($file, $options);

        // Clean up
        unlink($tempFile);

        return $result;
    }

    /**
     * Delete media
     */
    public function deleteMedia(int $id): bool
    {
        $media = $this->find($id);

        if (!$media) {
            return false;
        }

        // Delete physical files
        $basePath = $media->is_public ? $this->publicPath : $this->uploadPath;
        $filePath = $basePath . $media->path;

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete thumbnails
        if ($media->type === 'image' && isset($media->metadata['thumbnails'])) {
            foreach ($media->metadata['thumbnails'] as $size => $thumbnail) {
                $thumbPath = $basePath . $media->folder . '/' . $thumbnail;
                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }
            }
        }

        return $this->delete($id);
    }

    /**
     * Get media by folder
     */
    public function getByFolder(string $folder, array $options = []): array
    {
        $query = $this->where('folder', $folder);

        if (isset($options['type'])) {
            $query->where('type', $options['type']);
        }

        if (isset($options['is_public'])) {
            $query->where('is_public', $options['is_public']);
        }

        return $query->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Search media
     */
    public function searchMedia(string $keyword, array $filters = []): array
    {
        $query = $this->groupStart()
            ->like('original_name', $keyword)
            ->orLike('alt_text', $keyword)
            ->orLike('description', $keyword)
            ->groupEnd();

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at >=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at <=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Process image (resize, optimize)
     */
    private function processImage(string $path, array $options): void
    {
        $image = \Config\Services::image();

        // Resize if needed
        if (isset($options['max_width']) || isset($options['max_height'])) {
            $image->withFile($path)
                ->resize(
                    $options['max_width'] ?? 0,
                    $options['max_height'] ?? 0,
                    true
                )
                ->save($path);
        }

        // Optimize
        if ($options['optimize'] ?? true) {
            $image->withFile($path)
                ->save($path, 85); // 85% quality
        }
    }

    /**
     * Generate thumbnails
     */
    private function generateThumbnails(string $path, string $folder): array
    {
        $thumbnails = [];
        $sizes = [
            'small' => [150, 150],
            'medium' => [300, 300],
            'large' => [600, 600]
        ];

        $image = \Config\Services::image();
        $info = pathinfo($path);

        foreach ($sizes as $size => $dimensions) {
            $thumbName = $info['filename'] . '_' . $size . '.' . $info['extension'];
            $thumbPath = dirname($path) . '/' . $thumbName;

            $image->withFile($path)
                ->fit($dimensions[0], $dimensions[1], 'center')
                ->save($thumbPath);

            $thumbnails[$size] = $thumbName;
        }

        return $thumbnails;
    }

    /**
     * Helper methods
     */
    private function detectFileType(string $mimeType): string
    {
        foreach ($this->allowedMimes as $type => $mimes) {
            if (in_array($mimeType, $mimes)) {
                return $type;
            }
        }

        return 'other';
    }

    private function generateFileName(UploadedFile $file): string
    {
        $extension = $file->getExtension();
        return uniqid() . '_' . time() . '.' . $extension;
    }

    private function getUploadPath(string $folder, bool $isPublic): string
    {
        $basePath = $isPublic ? $this->publicPath : $this->uploadPath;
        return $basePath . $folder . '/';
    }

    private function getFileUrl(string $path, bool $isPublic): string
    {
        if ($isPublic) {
            return base_url('uploads/' . $path);
        }

        return site_url('media/file/' . base64_encode($path));
    }
}