<?php

namespace App\Modules\Blog;

class Hooks
{
    /**
     * Add blog widgets to dashboard
     */
    public function admin_dashboard($params)
    {
        $postModel = model('App\Modules\Blog\Models\PostModel');

        return [
            'widgets' => [
                [
                    'title' => 'Recent Posts',
                    'content' => view('Modules/Blog/widgets/recent_posts', [
                        'posts' => $postModel->getPublished(5)
                    ])
                ],
                [
                    'title' => 'Blog Statistics',
                    'content' => view('Modules/Blog/widgets/statistics', [
                        'stats' => $this->getStatistics()
                    ])
                ]
            ]
        ];
    }

    /**
     * Add blog menu items
     */
    public function build_admin_menu($params)
    {
        return [
            [
                'title' => 'Posts',
                'url' => '/admin/blog',
                'icon' => 'fa-file-alt',
                'permission' => 'blog.view',
                'children' => [
                    [
                        'title' => 'All Posts',
                        'url' => '/admin/blog',
                        'permission' => 'blog.view'
                    ],
                    [
                        'title' => 'Add New',
                        'url' => '/admin/blog/create',
                        'permission' => 'blog.create'
                    ],
                    [
                        'title' => 'Categories',
                        'url' => '/admin/blog/categories',
                        'permission' => 'blog.view'
                    ],
                    [
                        'title' => 'Tags',
                        'url' => '/admin/blog/tags',
                        'permission' => 'blog.view'
                    ]
                ]
            ],
            [
                'title' => 'Comments',
                'url' => '/admin/blog/comments',
                'icon' => 'fa-comments',
                'permission' => 'blog.manage_comments'
            ]
        ];
    }

    /**
     * Add sitemap entries
     */
    public function generate_sitemap($params)
    {
        $postModel = model('App\Modules\Blog\Models\PostModel');
        $posts = $postModel->getPublished(1000);

        $entries = [];
        foreach ($posts as $post) {
            $entries[] = [
                'loc' => site_url('blog/post/' . $post->slug),
                'lastmod' => $post->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.8'
            ];
        }

        return $entries;
    }

    /**
     * Get statistics
     */
    private function getStatistics()
    {
        $postModel = model('App\Modules\Blog\Models\PostModel');
        $commentModel = model('App\Modules\Blog\Models\CommentModel');

        return [
            'total_posts' => $postModel->countAll(),
            'published_posts' => $postModel->where('status', 'published')->countAllResults(),
            'draft_posts' => $postModel->where('status', 'draft')->countAllResults(),
            'total_comments' => $commentModel->countAll(),
            'pending_comments' => $commentModel->where('status', 'pending')->countAllResults()
        ];
    }
}