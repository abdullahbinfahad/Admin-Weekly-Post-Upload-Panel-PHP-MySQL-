<?php
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/Logger.php';

// 1. Database backup
$backupDir = __DIR__ . '/../backups/daily/';
if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);

$backupFile = $backupDir . 'backup_' . date('Ymd') . '.sql';
exec("mysqldump --user=".DB_USER." --password=".DB_PASS." --host=".DB_HOST." ".DB_NAME." > $backupFile");

// 2. Cleanup old backups (keep 7 days)
$files = glob($backupDir . '*.sql');
$keepDays = 7;
foreach ($files as $file) {
    if (filemtime($file) < time() - 86400 * $keepDays) {
        unlink($file);
    }
}

// 3. Log cleanup
$logger = new Logger();
$logger->log("CRON", "Daily maintenance completed");

// 4. Email notification (optional)
mail('admin@example.com', 'Daily Backup Complete', 'Backup was successfully created');