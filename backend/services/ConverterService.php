<?php
/**
 * ConverterService
 * Menghubungkan Backend PHP dengan Mesin Konversi Python
 */

require_once dirname(__DIR__) . '/config.php';

class ConverterService {

    public static function process(string $action, array $inputFiles, string $outputFilename, array $options = []): array {
        // Validasi ketersediaan file input
        foreach ($inputFiles as $file) {
            if (!file_exists($file)) {
                return [
                    'success' => false,
                    'error' => 'File input tidak ditemukan di server: ' . basename($file)
                ];
            }
        }

        // Jalur file output
        $outputPath = RESULT_DIR . $outputFilename;
        $inputArg = implode(',', array_map('escapeshellarg', $inputFiles));
        $outputArg = escapeshellarg($outputPath);
        $actionArg = escapeshellarg($action);

        $cmd = PYTHON_EXECUTABLE . ' ' . escapeshellarg(ENGINE_PATH) . " --action {$actionArg} --input {$inputArg} --output {$outputArg}";

        if (!empty($options['pages'])) {
            $cmd .= ' --pages ' . escapeshellarg($options['pages']);
        }
        if (!empty($options['quality'])) {
            $cmd .= ' --quality ' . escapeshellarg($options['quality']);
        }

        // Eksekusi proses dengan proc_open untuk menangani stdout & stderr secara aman
        $descriptorspec = [
            0 => ["pipe", "r"], // stdin
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"]  // stderr
        ];

        $process = proc_open($cmd, $descriptorspec, $pipes);

        if (!is_resource($process)) {
            return [
                'success' => false,
                'error' => 'Gagal memulai proses converter di server.'
            ];
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        // Cari baris JSON dari stdout (jika ada pesan log lain)
        $outputJson = null;
        $lines = explode("\n", trim($stdout));
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            $trimmed = trim($lines[$i]);
            if (!empty($trimmed) && $trimmed[0] === '{' && substr($trimmed, -1) === '}') {
                $outputJson = json_decode($trimmed, true);
                if ($outputJson !== null) {
                    break;
                }
            }
        }

        if ($outputJson && isset($outputJson['success'])) {
            if ($outputJson['success']) {
                return [
                    'success' => true,
                    'file' => $outputFilename,
                    'size' => file_exists($outputPath) ? filesize($outputPath) : ($outputJson['size'] ?? 0),
                    'message' => $outputJson['message'] ?? 'Konversi berhasil diselesaikan.',
                    'details' => $outputJson
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $outputJson['error'] ?? 'Terjadi kesalahan saat pemrosesan dokumen.'
                ];
            }
        }

        return [
            'success' => false,
            'error' => 'Engine gagal memproses file. ' . (!empty($stderr) ? trim($stderr) : trim($stdout))
        ];
    }
}
