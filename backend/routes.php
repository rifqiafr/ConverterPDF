<?php
/**
 * Backend API Router
 * Menangani request upload, konversi, pembatalan, dan unduhan file
 */

ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
ob_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/cleanup.php';
require_once __DIR__ . '/services/ConverterService.php';

// Jalankan pembersihan sampah sesekali saat ada request
if (rand(1, 20) === 1) {
    runFileCleanup();
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

function jsonResponse(array $data, int $statusCode = 200) {
    if (ob_get_level() > 0) {
        ob_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// 1. Upload File
if ($action === 'upload') {
    // Cari data file dari $_FILES dengan berbagai kemungkinan format nama
    $filesData = null;
    if (!empty($_FILES['files'])) {
        $filesData = $_FILES['files'];
    } elseif (!empty($_FILES['files[]'])) {
        $filesData = $_FILES['files[]'];
    } elseif (!empty($_FILES['file'])) {
        $filesData = $_FILES['file'];
    } elseif (!empty($_FILES)) {
        $first = reset($_FILES);
        if (!empty($first['name'])) {
            $filesData = $first;
        }
    }

    if (empty($filesData) || empty($filesData['name'])) {
        $contentLength = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
        if ($contentLength > 0) {
            $postMax = ini_get('post_max_size');
            $uploadMax = ini_get('upload_max_filesize');
            jsonResponse([
                'success' => false,
                'error' => "Ukuran file melebihi batas server PHP (upload_max: {$uploadMax}, post_max: {$postMax})."
            ], 400);
        }
        jsonResponse(['success' => false, 'error' => 'Tidak ada file yang diterima server. Silakan pilih file kembali.'], 400);
    }

    $toolId = $_POST['tool_id'] ?? '';
    $config = require __DIR__ . '/config.php';
    $tools = $config['tools'];

    if (!isset($tools[$toolId])) {
        jsonResponse(['success' => false, 'error' => 'Jenis alat konversi tidak valid.'], 400);
    }

    $toolConfig = $tools[$toolId];
    $allowedExts = $toolConfig['accept'];

    $sessionToken = bin2hex(random_bytes(12));
    $uploadedFiles = [];

    // Normalisasi struktur $_FILES jika multiple atau single
    $fileCount = is_array($filesData['name']) ? count($filesData['name']) : 1;

    for ($i = 0; $i < $fileCount; $i++) {
        $origName = is_array($filesData['name']) ? $filesData['name'][$i] : $filesData['name'];
        $tmpName  = is_array($filesData['tmp_name']) ? $filesData['tmp_name'][$i] : $filesData['tmp_name'];
        $fileSize = is_array($filesData['size']) ? $filesData['size'][$i] : $filesData['size'];
        $fileError = is_array($filesData['error']) ? $filesData['error'][$i] : $filesData['error'];

        if ($fileError !== UPLOAD_ERR_OK) {
            switch ($fileError) {
                case UPLOAD_ERR_INI_SIZE:
                    $errText = "Ukuran file '{$origName}' melebihi batas upload server (" . ini_get('upload_max_filesize') . ").";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $errText = "Ukuran file '{$origName}' melebihi batas form.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errText = "File '{$origName}' hanya terunggah sebagian. Silakan coba kembali.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errText = "Tidak ada file yang berhasil dikirim.";
                    break;
                default:
                    $errText = "Gagal mengunggah file {$origName} (Kode: {$fileError}).";
            }
            jsonResponse(['success' => false, 'error' => $errText], 400);
        }

        if ($fileSize > MAX_FILE_SIZE) {
            jsonResponse(['success' => false, 'error' => "Ukuran file {$origName} melebihi batas 50MB."], 400);
        }

        $ext = '.' . strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts)) {
            $allowedList = implode(', ', $allowedExts);
            jsonResponse(['success' => false, 'error' => "Format file {$origName} ({$ext}) tidak didukung. Harap unggah file berformat {$allowedList}."], 400);
        }

        // Simpan file dengan nama aman
        $safeOrigName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $origName);
        $storedFilename = $sessionToken . '_' . time() . '_' . $i . '_' . $safeOrigName;
        $destination = UPLOAD_DIR . $storedFilename;

        if (move_uploaded_file($tmpName, $destination)) {
            $uploadedFiles[] = [
                'original_name' => $origName,
                'stored_name' => $storedFilename,
                'path' => $destination,
                'size' => $fileSize
            ];
        } else {
            jsonResponse(['success' => false, 'error' => "Gagal menyimpan file {$origName} ke server."], 500);
        }
    }

    jsonResponse([
        'success' => true,
        'token' => $sessionToken,
        'files' => $uploadedFiles,
        'message' => count($uploadedFiles) . ' file berhasil diunggah.'
    ]);
}

// 2. Eksekusi Konversi
if ($action === 'convert') {
    $toolId = $_POST['tool_id'] ?? '';
    $token = $_POST['token'] ?? '';
    $fileNames = $_POST['file_names'] ?? [];
    $pages = $_POST['pages'] ?? '';
    $quality = $_POST['quality'] ?? 'medium';

    if (empty($toolId) || empty($token) || empty($fileNames)) {
        jsonResponse(['success' => false, 'error' => 'Data permintaan konversi tidak lengkap.'], 400);
    }

    if (!is_array($fileNames)) {
        $fileNames = [$fileNames];
    }

    $config = require __DIR__ . '/config.php';
    if (!isset($config['tools'][$toolId])) {
        jsonResponse(['success' => false, 'error' => 'Alat konversi tidak ditemukan.'], 400);
    }
    $toolConfig = $config['tools'][$toolId];

    // Validasi file tersimpan dan token
    $inputPaths = [];
    $firstOriginalName = 'dokumen';

    foreach ($fileNames as $fName) {
        $baseFname = basename($fName);
        // Pastikan nama file diawali dengan token sesi pengguna
        if (strpos($baseFname, $token) !== 0) {
            jsonResponse(['success' => false, 'error' => 'Akses file tidak diizinkan atau sesi kadaluarsa.'], 403);
        }

        $fullPath = UPLOAD_DIR . $baseFname;
        if (!file_exists($fullPath)) {
            jsonResponse(['success' => false, 'error' => "File tidak ditemukan di server: {$baseFname}"], 404);
        }
        $inputPaths[] = $fullPath;

        // Ambil nama asli dari pola stored filename
        $parts = explode('_', $baseFname, 4);
        if (isset($parts[3])) {
            $firstOriginalName = pathinfo($parts[3], PATHINFO_FILENAME);
        }
    }

    // Nama file output hasil
    $outputExt = $toolConfig['output_ext'];
    $cleanOriginal = preg_replace('/[^a-zA-Z0-9_-]/', '_', $firstOriginalName);
    $resultFilename = 'konversi_' . $cleanOriginal . '_' . $token . '.' . $outputExt;

    $startTime = microtime(true);

    $conversionResult = ConverterService::process($toolId, $inputPaths, $resultFilename, [
        'pages' => $pages,
        'quality' => $quality
    ]);

    $elapsedSeconds = round(microtime(true) - $startTime, 2);

    if ($conversionResult['success']) {
        jsonResponse([
            'success' => true,
            'file_name' => $resultFilename,
            'download_name' => $cleanOriginal . '_konversi.' . $outputExt,
            'size' => $conversionResult['size'],
            'message' => $conversionResult['message'],
            'elapsed_time' => $elapsedSeconds,
            'token' => $token,
            'details' => $conversionResult['details'] ?? []
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'error' => $conversionResult['error'] ?? 'Terjadi kegagalan saat konversi.',
            'elapsed_time' => $elapsedSeconds
        ], 500);
    }
}

// 3. Unduh File Hasil
if ($action === 'download') {
    $fileName = $_GET['file'] ?? '';
    $token = $_GET['token'] ?? '';
    $downloadName = $_GET['name'] ?? '';

    if (empty($fileName) || empty($token)) {
        http_response_code(400);
        die('Parameter tidak lengkap.');
    }

    $safeFileName = basename($fileName);
    // Verifikasi kepemilikan token sesi
    if (strpos($safeFileName, $token) === false) {
        http_response_code(403);
        die('Akses ditolak.');
    }

    $filePath = RESULT_DIR . $safeFileName;
    if (!file_exists($filePath)) {
        http_response_code(404);
        die('File hasil konversi tidak ditemukan atau telah kedaluwarsa.');
    }

    $clientFilename = !empty($downloadName) ? preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $downloadName) : $safeFileName;

    // Set header download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $clientFilename . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));

    // Kirim file ke client
    ob_clean();
    flush();
    readfile($filePath);
    exit;
}

// 4. Batalkan / Hapus File Sesi
if ($action === 'cancel') {
    $token = $_POST['token'] ?? '';
    $fileNames = $_POST['file_names'] ?? [];

    if (!empty($token)) {
        // Hapus semua file dengan token terkait di folder upload
        $files = scandir(UPLOAD_DIR);
        foreach ($files as $file) {
            if (strpos($file, $token) === 0) {
                @unlink(UPLOAD_DIR . $file);
            }
        }
    }

    jsonResponse(['success' => true, 'message' => 'File berhasil dibatalkan dan dihapus.']);
}

// 5. Cek Kesehatan
if ($action === 'health') {
    jsonResponse([
        'success' => true,
        'status' => 'OK',
        'timestamp' => date('c'),
        'php_version' => PHP_VERSION,
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size')
    ]);
}

jsonResponse(['success' => false, 'error' => 'Aksi API tidak valid.'], 404);
