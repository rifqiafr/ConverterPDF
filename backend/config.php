<?php
/**
 * Konfigurasi Utama Aplikasi PDF Converter
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
    define('UPLOAD_DIR', BASE_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR);
    define('RESULT_DIR', BASE_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'results' . DIRECTORY_SEPARATOR);
    define('ENGINE_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'engine' . DIRECTORY_SEPARATOR . 'convert.py');
    define('MAX_FILE_SIZE', 50 * 1024 * 1024);
    define('FILE_EXPIRATION_TIME', 7200);
    define('PYTHON_EXECUTABLE', 'python');
}

// Daftar Alat yang Didukung
return [
    'tools' => [
        'word_to_pdf' => [
            'id' => 'word_to_pdf',
            'title' => 'Word ke PDF',
            'description' => 'Konversi dokumen .doc dan .docx ke PDF dengan tata letak asli tetap terjaga.',
            'category' => 'conversion',
            'badge' => 'Populer',
            'icon' => 'word-to-pdf',
            'accept' => ['.docx', '.doc'],
            'output_ext' => 'pdf',
            'multiple' => false,
        ],
        'pdf_to_word' => [
            'id' => 'pdf_to_word',
            'title' => 'PDF ke Word',
            'description' => 'Ubah dokumen PDF menjadi file Word (.docx) yang dapat diedit beserta teks & tabel.',
            'category' => 'conversion',
            'badge' => 'Populer',
            'icon' => 'pdf-to-word',
            'accept' => ['.pdf'],
            'output_ext' => 'docx',
            'multiple' => false,
        ],
        'merge_pdf' => [
            'id' => 'merge_pdf',
            'title' => 'Gabungkan PDF',
            'description' => 'Satukan beberapa file PDF terpisah menjadi satu dokumen yang rapi dan berurutan.',
            'category' => 'management',
            'badge' => 'Paling Dicari',
            'icon' => 'merge-pdf',
            'accept' => ['.pdf'],
            'output_ext' => 'pdf',
            'multiple' => true,
        ],
        'split_pdf' => [
            'id' => 'split_pdf',
            'title' => 'Pisahkan PDF',
            'description' => 'Ekstrak atau pisahkan halaman tertentu dari file PDF menjadi dokumen tersendiri.',
            'category' => 'management',
            'badge' => null,
            'icon' => 'split-pdf',
            'accept' => ['.pdf'],
            'output_ext' => 'zip',
            'multiple' => false,
        ],
        'compress_pdf' => [
            'id' => 'compress_pdf',
            'title' => 'Kompres PDF',
            'description' => 'Perkecil ukuran dokumen PDF untuk email atau web tanpa menurunkan keterbacaan.',
            'category' => 'management',
            'badge' => 'Efisien',
            'icon' => 'compress-pdf',
            'accept' => ['.pdf'],
            'output_ext' => 'pdf',
            'multiple' => false,
        ],
        'pdf_to_jpg' => [
            'id' => 'pdf_to_jpg',
            'title' => 'PDF ke Gambar (JPG/PNG)',
            'description' => 'Ekstrak setiap halaman PDF menjadi file gambar berkualitas tinggi.',
            'category' => 'expansion',
            'badge' => null,
            'icon' => 'pdf-to-image',
            'accept' => ['.pdf'],
            'output_ext' => 'zip',
            'multiple' => false,
        ],
        'jpg_to_pdf' => [
            'id' => 'jpg_to_pdf',
            'title' => 'Gambar ke PDF',
            'description' => 'Ubah gambar JPG, PNG, atau WebP menjadi satu dokumen PDF siap cetak.',
            'category' => 'expansion',
            'badge' => null,
            'icon' => 'image-to-pdf',
            'accept' => ['.jpg', '.jpeg', '.png', '.webp'],
            'output_ext' => 'pdf',
            'multiple' => true,
        ],
        'pdf_to_excel' => [
            'id' => 'pdf_to_excel',
            'title' => 'PDF ke Excel',
            'description' => 'Ekstrak data tabel dari PDF ke spreadsheet Excel (.xlsx) dengan rapi.',
            'category' => 'expansion',
            'badge' => 'Pro',
            'icon' => 'pdf-to-excel',
            'accept' => ['.pdf'],
            'output_ext' => 'xlsx',
            'multiple' => false,
        ],
        'pdf_to_pptx' => [
            'id' => 'pdf_to_pptx',
            'title' => 'PDF ke PowerPoint',
            'description' => 'Ubah slide presentasi PDF menjadi file PowerPoint (.pptx) yang dapat diedit.',
            'category' => 'expansion',
            'badge' => 'Pro',
            'icon' => 'pdf-to-powerpoint',
            'accept' => ['.pdf'],
            'output_ext' => 'pptx',
            'multiple' => false,
        ],
    ]
];
