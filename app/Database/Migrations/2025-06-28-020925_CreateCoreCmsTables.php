<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreCmsTables extends Migration
{
    public function up()
    {
        // Settings Table - Konfigurasi global CMS
        $this->forge->addField([
            'id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'key' => ['type' => 'varchar', 'constraint' => 100, 'unique' => true],
            'value' => ['type' => 'text', 'null' => true],
            'type' => ['type' => 'enum', 'constraint' => ['string', 'number', 'json', 'boolean'], 'default' => 'string'],
            'group' => ['type' => 'varchar', 'constraint' => 50, 'default' => 'general'],
            'description' => ['type' => 'text', 'null' => true],
            'is_public' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
            'deleted_at' => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('group');
        $this->forge->createTable('cms_settings');

        // Modules Table - Sistem modular
        $this->forge->addField([
            'id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'varchar', 'constraint' => 100, 'unique' => true],
            'display_name' => ['type' => 'varchar', 'constraint' => 200],
            'description' => ['type' => 'text', 'null' => true],
            'version' => ['type' => 'varchar', 'constraint' => 20, 'default' => '1.0.0'],
            'author' => ['type' => 'varchar', 'constraint' => 100, 'null' => true],
            'status' => ['type' => 'enum', 'constraint' => ['active', 'inactive', 'error'], 'default' => 'inactive'],
            'config' => ['type' => 'json', 'null' => true],
            'permissions' => ['type' => 'json', 'null' => true],
            'routes' => ['type' => 'json', 'null' => true],
            'order' => ['type' => 'int', 'constraint' => 11, 'default' => 0],
            'installed_at' => ['type' => 'datetime', 'null' => true],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
            'deleted_at' => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('cms_modules');

        // Menus Table - Sistem menu dinamis
        $this->forge->addField([
            'id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'parent_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'menu_group' => ['type' => 'varchar', 'constraint' => 50, 'default' => 'main'],
            'title' => ['type' => 'varchar', 'constraint' => 200],
            'url' => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'route_name' => ['type' => 'varchar', 'constraint' => 100, 'null' => true],
            'icon' => ['type' => 'varchar', 'constraint' => 50, 'null' => true],
            'target' => ['type' => 'enum', 'constraint' => ['_self', '_blank'], 'default' => '_self'],
            'permission' => ['type' => 'varchar', 'constraint' => 100, 'null' => true],
            'order' => ['type' => 'int', 'constraint' => 11, 'default' => 0],
            'is_active' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
            'metadata' => ['type' => 'json', 'null' => true],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
            'deleted_at'=> ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('parent_id');
        $this->forge->addKey('menu_group');
        $this->forge->addForeignKey('parent_id', 'cms_menus', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('cms_menus');

        // Media Table - Manajemen file/media
        $this->forge->addField([
            'id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'filename' => ['type' => 'varchar', 'constraint' => 255],
            'original_name' => ['type' => 'varchar', 'constraint' => 255],
            'mime_type' => ['type' => 'varchar', 'constraint' => 100],
            'size' => ['type' => 'bigint', 'constraint' => 20],
            'path' => ['type' => 'varchar', 'constraint' => 500],
            'url' => ['type' => 'varchar', 'constraint' => 500],
            'type' => ['type' => 'enum', 'constraint' => ['image', 'video', 'audio', 'document', 'other'], 'default' => 'other'],
            'alt_text' => ['type' => 'varchar', 'constraint' => 255, 'null' => true],
            'description' => ['type' => 'text', 'null' => true],
            'metadata' => ['type' => 'json', 'null' => true],
            'folder' => ['type' => 'varchar', 'constraint' => 255, 'default' => '/'],
            'is_public' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
            'deleted_at' => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('type');
        $this->forge->addKey('folder');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('cms_media');

        // Activity Logs Table - Audit trail
        $this->forge->addField([
            'id' => ['type' => 'bigint', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'type' => ['type' => 'varchar', 'constraint' => 50],
            'model' => ['type' => 'varchar', 'constraint' => 100, 'null' => true],
            'model_id' => ['type' => 'int', 'constraint' => 11, 'null' => true],
            'action' => ['type' => 'varchar', 'constraint' => 50],
            'description' => ['type' => 'text', 'null' => true],
            'data' => ['type' => 'json', 'null' => true],
            'ip_address' => ['type' => 'varchar', 'constraint' => 45, 'null' => true],
            'user_agent' => ['type' => 'text', 'null' => true],
            'created_at' => ['type' => 'datetime'],
            'deleted_at' => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id');
        $this->forge->addKey(['model', 'model_id']);
        $this->forge->addKey('type');
        $this->forge->addKey('created_at');
        $this->forge->createTable('cms_activity_logs');

        // Notifications Table - Sistem notifikasi
        $this->forge->addField([
            'id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'type' => ['type' => 'varchar', 'constraint' => 50],
            'title' => ['type' => 'varchar', 'constraint' => 255],
            'message' => ['type' => 'text'],
            'data' => ['type' => 'json', 'null' => true],
            'read_at' => ['type' => 'datetime', 'null' => true],
            'action_url' => ['type' => 'varchar', 'constraint' => 500, 'null' => true],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
            'deleted_at' => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('type');
        $this->forge->addKey('read_at');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('cms_notifications');

        // API Keys Table - API Management
        $this->forge->addField([
            'id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'varchar', 'constraint' => 100],
            'key' => ['type' => 'varchar', 'constraint' => 64, 'unique' => true],
            'secret' => ['type' => 'varchar', 'constraint' => 128],
            'permissions' => ['type' => 'json', 'null' => true],
            'rate_limit' => ['type' => 'int', 'constraint' => 11, 'default' => 60],
            'expires_at' => ['type' => 'datetime', 'null' => true],
            'last_used_at' => ['type' => 'datetime', 'null' => true],
            'is_active' => ['type' => 'tinyint', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'datetime', 'null' => true],
            'updated_at' => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('cms_api_keys');
    }

    public function down()
    {
        $this->forge->dropTable('cms_api_keys', true);
        $this->forge->dropTable('cms_notifications', true);
        $this->forge->dropTable('cms_activity_logs', true);
        $this->forge->dropTable('cms_media', true);
        $this->forge->dropTable('cms_menus', true);
        $this->forge->dropTable('cms_modules', true);
        $this->forge->dropTable('cms_settings', true);
    }
}