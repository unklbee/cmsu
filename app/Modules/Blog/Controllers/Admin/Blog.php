<?php

namespace App\Modules\Blog\Controllers\Admin;

use App\Controllers\CMS\BaseAdminController;
use App\Controllers\CMS\Traits\CrudTrait;

class Blog extends BaseAdminController
{
    use CrudTrait;

    protected $model;
    protected $validation = [
        'title' => 'required|string|max_length[255]',
        'content' => 'required|string',
        'category_id' => 'required|numeric',
        'status' => 'required|in_list[draft,published,scheduled]'
    ];

    public function __construct()
    {
        $this->model = model('App\Modules\Blog\Models\PostModel');
    }

    /**
     * List posts
     */
    public function index()
    {
        $this->checkPermission('blog.view');

        $filter = $this->request->getGet('filter');
        $search = $this->request->getGet('search');

        $query = $this->model;

        if ($filter) {
            $query = $query->where('status', $filter);
        }

        if ($search) {
            $query = $query->like('title', $search);
        }

        $posts = $query->orderBy('created_at', 'DESC')
            ->paginate(20);

        return $this->render('modules/blog/admin/index', [
            'title' => 'Blog Posts',
            'posts' => $posts,
            'pager' => $this->model->pager
        ]);
    }

    /**
     * Create post form
     */
    public function create()
    {
        $this->checkPermission('blog.create');

        return $this->render('modules/blog/admin/create', [
            'title' => 'Create Post',
            'categories' => $this->getCategoriesDropdown(),
            'tags' => model('App\Modules\Blog\Models\TagModel')->findAll()
        ]);
    }

    /**
     * Store post
     */
    public function store()
    {
        $this->checkPermission('blog.create');

        if (!$this->validate($this->validation)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();

        // Handle featured image upload
        $file = $this->request->getFile('featured_image');
        if ($file && $file->isValid()) {
            $mediaModel = model('App\Models\CMS\MediaModel');
            $media = $mediaModel->upload($file, ['folder' => 'blog']);
            if ($media) {
                $data['featured_image'] = $media->id;
            }
        }

        // Handle tags
        $tags = $this->request->getPost('tags') ?? [];

        // Save post
        $postId = $this->model->insert($data);

        if ($postId) {
            // Save tags
            $this->saveTags($postId, $tags);

            // Clear cache
            clear_cms_cache('blog');

            return redirect()->to('/admin/blog')->with('success', 'Post created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create post');
    }

    /**
     * Edit post form
     */
    public function edit($id)
    {
        $this->checkPermission('blog.edit');

        $post = $this->model->find($id);

        if (!$post) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->render('modules/blog/admin/edit', [
            'title' => 'Edit Post',
            'post' => $post,
            'categories' => $this->getCategoriesDropdown(),
            'tags' => model('App\Modules\Blog\Models\TagModel')->findAll(),
            'postTags' => $post->getTags()
        ]);
    }

    /**
     * Update post
     */
    public function update($id)
    {
        $this->checkPermission('blog.edit');

        if (!$this->validate($this->validation)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();

        // Handle featured image upload
        $file = $this->request->getFile('featured_image');
        if ($file && $file->isValid()) {
            $mediaModel = model('App\Models\CMS\MediaModel');
            $media = $mediaModel->upload($file, ['folder' => 'blog']);
            if ($media) {
                $data['featured_image'] = $media->id;
            }
        }

        // Handle tags
        $tags = $this->request->getPost('tags') ?? [];

        // Update post
        if ($this->model->update($id, $data)) {
            // Update tags
            $this->saveTags($id, $tags);

            // Clear cache
            clear_cms_cache('blog');
            clear_cms_cache('post_' . $id);

            return redirect()->to('/admin/blog')->with('success', 'Post updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update post');
    }

    /**
     * Helper methods
     */
    private function getCategoriesDropdown()
    {
        $categories = model('App\Modules\Blog\Models\CategoryModel')->findAll();
        $options = [];

        foreach ($categories as $category) {
            $options[$category->id] = $category->name;
        }

        return $options;
    }

    private function saveTags($postId, array $tagNames)
    {
        $tagModel = model('App\Modules\Blog\Models\TagModel');
        $db = \Config\Database::connect();

        // Delete existing tags
        $db->table('blog_post_tags')->where('post_id', $postId)->delete();

        // Save new tags
        foreach ($tagNames as $tagName) {
            $tag = $tagModel->firstOrCreate(['name' => $tagName]);

            $db->table('blog_post_tags')->insert([
                'post_id' => $postId,
                'tag_id' => $tag->id
            ]);
        }
    }
}