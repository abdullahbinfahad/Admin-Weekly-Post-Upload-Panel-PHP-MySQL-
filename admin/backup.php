<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/security.php';

$auth = new Auth();
$auth->requireLogin();

$type = $_GET['type'] ?? 'db';
$backupDir = __DIR__ . '/../backups/';

// Create backup directory if not exists
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$filename = 'backup_' . date('Ymd_His') . '_' . $type . '.zip';
$backupPath = $backupDir . $filename;

try {
    // Create ZIP archive
    $zip = new ZipArchive();
    if ($zip->open($backupPath, ZipArchive::CREATE) !== TRUE) {
        throw new Exception("Cannot open <$backupPath>\n");
    }
    
    // Database backup
    if ($type === 'db' || $type === 'full') {
        $dbFile = $backupDir . 'database.sql';
        exec("mysqldump --user=".DB_USER." --password=".DB_PASS." --host=".DB_HOST." ".DB_NAME." > $dbFile");
        $zip->addFile($dbFile, 'database.sql');
    }
    
    // File backup
    if ($type === 'files' || $type === 'full') {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(__DIR__ . '/../uploads'),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen(__DIR__ . '/../') + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
    
    $zip->close();
    
    // Force download
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($backupPath) . '"');
    header('Content-Length: ' . filesize($backupPath));
    readfile($backupPath);
    exit;
    
} catch (Exception $e) {
    die("Backup failed: " . $e->getMessage());
}