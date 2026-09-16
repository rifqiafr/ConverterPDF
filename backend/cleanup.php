<?php
/**
 * Garbage Collector Otomatis untuk File Unggahan & Hasil Konversi
 * Menjamin privasi pengguna dengan menghapus file yang berumur lebih dari batas waktu (1-2 jam)
 */

require_once __DIR__ . '/config.php';

function runFileCleanup($maxAgeSeconds = FILE_EXPIRATION_TIME) {
    $dirs = [UPLOAD_DIR, RESULT_DIR];
    $deletedCount = 0;
    $now = time();

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) continue;

        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === '.gitkeep') {
                continue;
            }

            $filePath = $dir . $file;
            if (is_file($filePath)) {
                $fileMtime = filemtime($filePath);
                if (($now - $fileMtime) > $maxAgeSeconds) {
                    @unlink($filePath);
                    $deletedCount++;
                }
            } elseif (is_dir($filePath)) {
                // Jika berupa folder sub-job
                $folderMtime = filemtime($filePath);
                if (($now - $folderMtime) > $maxAgeSeconds) {
                    deleteDirectoryRecursively($filePath);
                    $deletedCount++;
                }
            }
        }
    }

    return $deletedCount;
}

function deleteDirectoryRecursively($dir) {
    if (!is_dir($dir)) return;
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            deleteDirectoryRecursively($path);
        } else {
            @unlink($path);
        }
    }
    @rmdir($dir);
}
