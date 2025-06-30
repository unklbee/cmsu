<?php

// Bootstrap File
// File: app/Modules/Blog/Bootstrap.php
namespace App\Modules\Blog;

use CodeIgniter\Events\Events;
use Config\Services;

class Bootstrap
{
    public function init()
    {
        // Register event listeners
        Events::on('pre_system', function() {
            // Module initialization code
        });

        // Add filters
        $filters = Services::filters();
        $filters->addFilter('blog_view_counter', 'before', 'blog/post/*');

        // Register helpers
        helper('blog');
    }
}