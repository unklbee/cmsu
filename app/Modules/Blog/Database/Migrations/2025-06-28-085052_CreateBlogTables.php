<?php

namespace App\Modules\Blog\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBlogTables extends Migration
{
    public function up()
    {
        // Posts table
        $this->forge->addField([
            'id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'category_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'title' => ['type' => 'varchar', 'constraint' => 255],
            'slug' => ['type' => 'varchar', 'constraint' => 255, 'unique' => true],
            'excerpt' => ['type' => 'text', 'null' => true],
            'content' => ['type' => 'text'],
            'featured_image' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'status' => ['type' => 'enum', 'constraint' => ['draft', 'published', 'scheduled'], 'default' => 'draft'],
            'published_at' => ['type' => 'datetime', 'null' => true],
            'views' => ['type' => 'int', 'constraint' => 11, 'default' => 0],
            'allow_comments' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
            'meta_title' => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'meta_description' => ['type' => 'text', 'null' => true],
            'meta_keywords' => ['type' => 'text', 'null' => true],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
            'deleted_at' => ['type' => 'datetime', 'null' => true]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('category_id');
        $this->forge->addKey('status');
        $this->forge->addKey('published_at');
        $this->forge->createTable('blog_posts');

        // Categories table
        $this->forge->addField([
            'id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'parent_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'name' => ['type' => 'varchar', 'constraint' => 100],
            'slug' => ['type' => 'varchar', 'constraint' => 100, 'unique' => true],
            'description' => ['type' => 'text', 'null' => true],
            'image' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'posts_count' => ['type' => 'int', 'constraint' => 11, 'default' => 0],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('parent_id');
        $this->forge->createTable('blog_categories');

        // Tags table
        $this->forge->addField([
            'id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'varchar', 'constraint' => 50],
            'slug' => ['type' => 'varchar', 'constraint' => 50, 'unique' => true],
            'posts_count' => ['type' => 'int', 'constraint' => 11, 'default' => 0],
            'created_at' => ['type' => 'datetime', 'null' => true]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('blog_tags');

        // Post tags pivot table
        $this->forge->addField([
            'post_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'tag_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true]
        ]);
        $this->forge->addKey(['post_id', 'tag_id']);
        $this->forge->createTable('blog_post_tags');

        // Comments table
        $this->forge->addField([
            'id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'post_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'parent_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'user_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'author_name' => ['type' => 'varchar', 'constraint' => 100],
            'author_email' => ['type' => 'varchar', 'constraint' => 100],
            'author_url' => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'content' => ['type' => 'text'],
            'status' => ['type' => 'enum', 'constraint' => ['pending', 'approved', 'spam'], 'default' => 'pending'],
            'ip_address' => ['type' => 'varchar', 'constraint' => 45],
            'user_agent' => ['type' => 'text'],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('post_id');
        $this->forge->addKey('parent_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('status');
        $this->forge->createTable('blog_comments');
    }

    public function down()
    {
        $this->forge->dropTable('blog_comments', true);
        $this->forge->dropTable('blog_post_tags', true);
        $this->forge->dropTable('blog_tags', true);
        $this->forge->dropTable('blog_categories', true);
        $this->forge->dropTable('blog_posts', true);
    }
}
