<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use ZipArchive;


/**
 * Database Backup Command
 * File: app/Commands/CMSBackup.php
 */
class CMSBackup extends BaseCommand
{
    protected $group = 'CMS';
    protected $name = 'cms:backup';
    protected $description = 'Backup CMS database and files';
    protected $usage = 'cms:backup [type]';
    protected $arguments = [
        'type' => 'Backup type: all, database, files'
    ];
    protected $options = [
        '--compress' => 'Compress backup file',
        '--exclude' => 'Directories to exclude (comma separated)'
    ];

    public function run(array $params)
    {
        $type = $params[0] ?? 'all';
        $compress = CLI::getOption('compress') ?? true;
        $exclude = CLI::getOption('exclude');
        $excludeDirs = $exclude ? explode(',', $exclude) : ['vendor', 'node_modules', '.git'];

        $backupDir = WRITEPATH . 'backups/';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        switch ($type) {
            case 'all':
                $this->backupDatabase($backupDir, $compress);
                $this->backupFiles($backupDir, $compress, $excludeDirs);
                break;

            case 'database':
                $this->backupDatabase($backupDir, $compress);
                break;

            case 'files':
                $this->backupFiles($backupDir, $compress, $excludeDirs);
                break;

            default:
                CLI::error("Unknown type: {$type}");
        }
    }


    private function backupDatabase(string $backupDir, bool $compress): void
    {
        CLI::write('Backing up database...', 'yellow');

        $db = \Config\Database::connect();
        $util = \Config\Database::utils();

        $filename = 'db_backup_' . date('Y-m-d_His') . '.sql';
        $filepath = $backupDir . $filename;

        $backup = $util->backup([
            'format' => 'sql',
            'filename' => $filepath
        ]);

        if ($compress) {
            $zip = new ZipArchive();
            $zipFile = $filepath . '.zip';

            if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
                $zip->addFile($filepath, $filename);
                $zip->close();
                unlink($filepath);

                CLI::write("Database backup created: {$zipFile}", 'green');
            }
        } else {
            CLI::write("Database backup created: {$filepath}", 'green');
        }
    }

    private function backupFiles(string $backupDir, bool $compress, array $exclude): void
    {
        CLI::write('Backing up files...', 'yellow');

        $filename = 'files_backup_' . date('Y-m-d_His');

        if ($compress) {
            $zipFile = $backupDir . $filename . '.zip';
            $zip = new ZipArchive();

            if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
                $this->addDirectoryToZip($zip, FCPATH, '', $exclude);
                $zip->close();

                CLI::write("Files backup created: {$zipFile}", 'green');
            }
        } else {
            $targetDir = $backupDir . $filename;
            mkdir($targetDir, 0755, true);

            $this->copyDirectory(FCPATH, $targetDir, $exclude);
            CLI::write("Files backup created: {$targetDir}", 'green');
        }
    }

    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $base = '', array $exclude = []): void
    {
        $files = scandir($dir);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || in_array($file, $exclude)) {
                continue;
            }

            $path = $dir . '/' . $file;
            $localPath = $base ? $base . '/' . $file : $file;

            if (is_dir($path)) {
                $zip->addEmptyDir($localPath);
                $this->addDirectoryToZip($zip, $path, $localPath, $exclude);
            } else {
                $zip->addFile($path, $localPath);
            }
        }
    }

    private function copyDirectory(string $src, string $dst, array $exclude = []): void
    {
        $dir = opendir($src);
        @mkdir($dst);

        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..' || in_array($file, $exclude)) {
                continue;
            }

            if (is_dir($src . '/' . $file)) {
                $this->copyDirectory($src . '/' . $file, $dst . '/' . $file, $exclude);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }

        closedir($dir);
    }
}