<?php
/**
 * Media API Controller
 * File: app/Controllers/Api/V1/MediaController.php
 */
namespace App\Controllers\Api\V1;

class MediaController extends BaseApiController
{
    protected $mediaModel;

    public function __construct()
    {
        $this->mediaModel = model('App\Models\CMS\MediaModel');
    }

    public function index()
    {
        $filters = [
            'type' => $this->request->getGet('type'),
            'folder' => $this->request->getGet('folder')
        ];

        // Remove empty filters
        $filters = array_filter($filters);

        return $this->paginatedResponse($this->mediaModel, $filters);
    }

    public function show($id)
    {
        $media = $this->mediaModel->find($id);

        if (!$media) {
            return $this->failNotFound('Media not found');
        }

        // Check permission if not public
        if (!$media->is_public && $media->user_id !== auth()->id()) {
            $this->checkPermission('media.manage');
        }

        return $this->respond([
            'success' => true,
            'data' => $media
        ]);
    }

    public function upload()
    {
        $rules = [
            'file' => 'uploaded[file]|max_size[file,10240]'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $file = $this->request->getFile('file');

        try {
            $options = [
                'folder' => $this->request->getPost('folder') ?? date('Y/m'),
                'is_public' => $this->request->getPost('is_public') ?? true,
                'alt_text' => $this->request->getPost('alt_text'),
                'description' => $this->request->getPost('description')
            ];

            $media = $this->mediaModel->upload($file, $options);

            if (!$media) {
                return $this->fail('Failed to upload file');
            }

            // Log API usage
            $this->logApiUsage(['action' => 'upload', 'media_id' => $media->id]);

            return $this->respondCreated([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => $media
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    public function delete($id)
    {
        $media = $this->mediaModel->find($id);

        if (!$media) {
            return $this->failNotFound('Media not found');
        }

        // Check permission
        if ($media->user_id !== auth()->id()) {
            $this->checkPermission('media.manage');
        }

        if ($this->mediaModel->deleteMedia($id)) {
            return $this->respondDeleted([
                'success' => true,
                'message' => 'Media deleted successfully'
            ]);
        }

        return $this->fail('Failed to delete media');
    }
}
