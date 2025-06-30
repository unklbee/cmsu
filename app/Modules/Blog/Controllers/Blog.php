<?php

namespace App\Modules\Blog\Controllers;

use App\Controllers\BaseController;

class Blog extends BaseController
{
    protected $postModel;
    protected $categoryModel;
    protected $tagModel;
    protected $commentModel;

    public function __construct()
    {
        $this->postModel = model('App\Modules\Blog\Models\PostModel');
        $this->categoryModel = model('App\Modules\Blog\Models\CategoryModel');
        $this->tagModel = model('App\Modules\Blog\Models\TagModel');
        $this->commentModel = model('App\Modules\Blog\Models\CommentModel');
    }

    /**
     * Blog index page
     */
    public function index()
    {
        $page = $this->request->getGet('page') ?? 1;
        $perPage = setting('blog_posts_per_page', 10);

        $posts = cms_cache('blog_posts_page_' . $page, function() use ($perPage) {
            return $this->postModel->getPublished($perPage, ($page - 1) * $perPage);
        }, 300, ['blog']);

        $data = [
            'title' => 'Blog',
            'posts' => $posts,
            'categories' => $this->categoryModel->findAll(),
            'tags' => $this->tagModel->getPopular(20),
            'pagination' => $this->postModel->pager
        ];

        return theme()->render('blog/index', $data);
    }

    /**
     * Show single post
     */
    public function show($slug)
    {
        $post = cms_cache('blog_post_' . $slug, function() use ($slug) {
            return $this->postModel->getBySlug($slug);
        }, 3600, ['blog', 'post_' . $slug]);

        if (!$post) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Increment views
        $this->postModel->incrementViews($post->id);

        // Get related data
        $related = $this->postModel->getRelated($post->id, $post->category_id);
        $comments = $this->commentModel->getByPost($post->id);

        $data = [
            'title' => $post->title,
            'post' => $post,
            'category' => $post->getCategory(),
            'tags' => $post->getTags(),
            'author' => $post->getAuthor(),
            'related' => $related,
            'comments' => $comments,
            'meta' => [
                'title' => $post->meta_title ?: $post->title,
                'description' => $post->meta_description ?: $post->excerpt,
                'keywords' => $post->meta_keywords,
                'image' => $post->getFeaturedImageUrl(),
                'type' => 'article'
            ]
        ];

        return theme()->render('blog/show', $data);
    }

    /**
     * Category posts
     */
    public function category($slug)
    {
        $category = $this->categoryModel->getBySlug($slug);

        if (!$category) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $posts = $this->postModel->getByCategory($category->id);

        $data = [
            'title' => $category->name,
            'category' => $category,
            'posts' => $posts,
            'categories' => $this->categoryModel->findAll()
        ];

        return theme()->render('blog/category', $data);
    }

    /**
     * Tag posts
     */
    public function tag($slug)
    {
        $tag = $this->tagModel->getBySlug($slug);

        if (!$tag) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $posts = $this->postModel->getByTag($tag->id);

        $data = [
            'title' => 'Posts tagged: ' . $tag->name,
            'tag' => $tag,
            'posts' => $posts,
            'tags' => $this->tagModel->getPopular(20)
        ];

        return theme()->render('blog/tag', $data);
    }

    /**
     * Search posts
     */
    public function search()
    {
        $keyword = $this->request->getGet('q');

        if (!$keyword) {
            return redirect()->to('/blog');
        }

        $posts = $this->postModel->search($keyword);

        $data = [
            'title' => 'Search results for: ' . $keyword,
            'keyword' => $keyword,
            'posts' => $posts,
            'categories' => $this->categoryModel->findAll()
        ];

        return theme()->render('blog/search', $data);
    }

    /**
     * Submit comment (AJAX)
     */
    public function comment()
    {
        if (!$this->request->isAJAX()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'post_id' => 'required|numeric',
            'author_name' => 'required|string|max_length[100]',
            'author_email' => 'required|valid_email',
            'content' => 'required|string|min_length[10]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = $this->request->getPost();
        $data['ip_address'] = $this->request->getIPAddress();
        $data['user_agent'] = $this->request->getUserAgent()->getAgentString();
        $data['status'] = setting('blog_moderation_required', true) ? 'pending' : 'approved';

        if (auth()->loggedIn()) {
            $data['user_id'] = auth()->id();
        }

        $commentId = $this->commentModel->insert($data);

        if ($commentId) {
            // Clear cache
            clear_cms_cache('post_' . $data['post_id']);

            // Notify admin
            notify_user(1, 'comment', 'New Comment', 'New comment on blog post');

            return $this->response->setJSON([
                'success' => true,
                'message' => $data['status'] === 'pending'
                    ? 'Comment submitted for moderation'
                    : 'Comment published successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to submit comment'
        ]);
    }
}