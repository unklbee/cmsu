<?php

namespace App\Controllers\Admin;

use App\Controllers\CMS\BaseAdminController;

class Media extends BaseAdminController
{
    protected $mediaModel;

    public function __construct()
    {
        $this->mediaModel = model('App\Models\CMS\MediaModel');
    }

    public function index()
    {
        $this->checkPermission('media.upload');
        $this->setTitle('Media Library');

        $type = $this->request->getGet('type');
        $folder = $this->request->getGet('folder');
        $search = $this->request->getGet('search');

        // Build filters
        $filters = [];
        if ($type) {
            $filters['type'] = $type;
        }
        if ($folder) {
            $filters['folder'] = $folder;
        }

        // Get media files
        if ($search) {
            $media = $this->mediaModel->searchMedia($search, $filters);
        } else {
            $query = $this->mediaModel;
            foreach ($filters as $key => $value) {
                $query = $query->where($key, $value);
            }
            $media = $query->orderBy('created_at', 'DESC')->findAll();
        }

        $data = [
            'media' => $media,
            'folders' => $this->getFolders(),
            'current_type' => $type,
            'current_folder' => $folder
        ];

        return $this->render('media/index', $data);
    }

    public function upload()
    {
        $this->checkPermission('media.upload');

        if (!$this->request->isAJAX()) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        $rules = [
            'files' => 'uploaded[files]|max_size[files,10240]'
        ];

        if (!$this->validate($rules)) {
            return $this->error('Validation failed', $this->validator->getErrors());
        }

        $files = $this->request->getFileMultiple('files');
        $folder = $this->request->getPost('folder') ?? date('Y/m');

        $uploaded = [];
        $errors = [];

        foreach ($files as $file) {
            try {
                $media = $this->mediaModel->upload($file, [
                    'folder' => $folder,
                    'is_public' => true
                ]);

                if ($media) {
                    $uploaded[] = $media;
                }
            } catch (\Exception $e) {
                $errors[] = $file->getName() . ': ' . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            return $this->error('Some files failed to upload', $errors);
        }

        return $this->success($uploaded, 'Files uploaded successfully');
    }

    public function delete($id)
    {
        $this->checkPermission('media.delete');

        $media = $this->mediaModel->find($id);

        if (!$media) {
            return $this->error('Media not found');
        }

        // Check ownership
        if ($media->user_id !== auth()->id() && !has_permission('media.manage')) {
            return $this->error('Access denied');
        }

        if ($this->mediaModel->deleteMedia($id)) {
            return $this->success(null, 'Media deleted successfully');
        }

        return $this->error('Failed to delete media');
    }

    private function getFolders(): array
    {
        $db = \Config\Database::connect();
        $folders = $db->table('cms_media')
            ->select('folder')
            ->distinct()
            ->orderBy('folder', 'ASC')
            ->get()
            ->getResultArray();

        return array_column($folders, 'folder');
    }
}