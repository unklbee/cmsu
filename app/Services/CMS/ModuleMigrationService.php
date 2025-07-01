<?php

namespace App\Services\CMS;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use Config\Migrations;

class ModuleMigrationService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Run migrations for a specific module
     */
    public function migrateModule(string $moduleName): bool
    {
        $modulePath = APPPATH . 'Modules/' . ucfirst($moduleName) . '/Database/Migrations/';

        if (!is_dir($modulePath)) {
            return false;
        }

        // Get all migration files in the module
        $migrationFiles = glob($modulePath . '*.php');

        if (empty($migrationFiles)) {
            return true;
        }

        // Sort migration files by name
        sort($migrationFiles);

        foreach ($migrationFiles as $migrationFile) {
            $this->runMigrationFile($migrationFile, $moduleName);
        }

        return true;
    }

    /**
     * Run a single migration file
     */
    protected function runMigrationFile(string $filePath, string $moduleName): bool
    {
        $fileName = basename($filePath, '.php');
        $className = $this->getClassNameFromFile($fileName);
        $namespace = "App\\Modules\\" . ucfirst($moduleName) . "\\Database\\Migrations";

        // Check if migration already ran
        if ($this->isMigrationAlreadyRun($fileName, $namespace)) {
            return true;
        }

        // Check if tables already exist (for safety)
        if ($this->areTablesAlreadyExist($moduleName)) {
            $this->recordMigration($fileName, $namespace);
            return true;
        }

        try {
            // Include the migration file
            require_once $filePath;

            $fullClassName = $namespace . '\\' . $className;

            if (!class_exists($fullClassName)) {
                return false;
            }

            $migration = new $fullClassName();

            if (method_exists($migration, 'up')) {
                $migration->up();

                // Record the migration
                $this->recordMigration($fileName, $namespace);

                return true;
            }

        } catch (\Exception $e) {
            return false;
        }

        return false;
    }

    /**
     * Get class name from migration file name
     */
    protected function getClassNameFromFile(string $fileName): string
    {
        // Remove timestamp prefix (e.g., "2025-06-28-085052_CreateBlogTables" -> "CreateBlogTables")
        $parts = explode('_', $fileName);
        array_shift($parts); // Remove timestamp

        return implode('_', $parts);
    }

    /**
     * Check if migration already ran
     */
    protected function isMigrationAlreadyRun(string $fileName, string $namespace): bool
    {
        $result = $this->db->table('migrations')
            ->where('version', $fileName)
            ->where('namespace', $namespace)
            ->get()
            ->getRow();

        return $result !== null;
    }

    /**
     * Record migration in database
     */
    protected function recordMigration(string $fileName, string $namespace): void
    {
        $this->db->table('migrations')->insert([
            'version' => $fileName,
            'class' => $this->getClassNameFromFile($fileName),
            'group' => 'default',
            'namespace' => $namespace,
            'time' => time(),
            'batch' => $this->getNextBatch()
        ]);
    }

    /**
     * Get next batch number
     */
    protected function getNextBatch(): int
    {
        $result = $this->db->table('migrations')
            ->selectMax('batch')
            ->get()
            ->getRow();

        return ($result->batch ?? 0) + 1;
    }

    /**
     * Check if tables already exist for a module
     */
    protected function areTablesAlreadyExist(string $moduleName): bool
    {
        $tables = [];

        switch (strtolower($moduleName)) {
            case 'blog':
                $tables = ['blog_categories', 'blog_posts', 'blog_tags', 'blog_post_tags', 'blog_comments'];
                break;
            default:
                return false;
        }

        foreach ($tables as $table) {
            if (!$this->db->tableExists($table)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Rollback migrations for a specific module
     */
    public function rollbackModule(string $moduleName): bool
    {
        $modulePath = APPPATH . 'Modules/' . ucfirst($moduleName) . '/Database/Migrations/';

        if (!is_dir($modulePath)) {
            return false;
        }

        // Get migrations for this module
        $migrations = $this->db->table('migrations')
            ->where('namespace', "App\\Modules\\" . ucfirst($moduleName) . "\\Database\\Migrations")
            ->orderBy('batch', 'DESC')
            ->orderBy('version', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($migrations as $migration) {
            $this->rollbackMigration($migration, $moduleName);
        }

        return true;
    }

    /**
     * Rollback a single migration
     */
    protected function rollbackMigration(array $migration, string $moduleName): bool
    {
        $filePath = APPPATH . 'Modules/' . ucfirst($moduleName) . '/Database/Migrations/' . $migration['version'] . '.php';

        if (!file_exists($filePath)) {
            return false;
        }

        try {
            require_once $filePath;

            $className = $migration['class'];
            $namespace = $migration['namespace'];
            $fullClassName = $namespace . '\\' . $className;

            if (class_exists($fullClassName)) {
                $migrationInstance = new $fullClassName();

                if (method_exists($migrationInstance, 'down')) {
                    $migrationInstance->down();

                    // Remove migration record
                    $this->db->table('migrations')
                        ->where('version', $migration['version'])
                        ->where('namespace', $migration['namespace'])
                        ->delete();

                    return true;
                }
            }
        } catch (\Exception $e) {
            log_message('error', "Rollback failed for {$migration['version']}: " . $e->getMessage());
        }

        return false;
    }
}