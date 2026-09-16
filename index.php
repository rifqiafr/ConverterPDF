<?php
/**
 * Aplikasi PDF Converter - Beranda & Antarmuka Utama
 */

$config = require __DIR__ . '/backend/config.php';
$tools = $config['tools'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Converter - Konversi & Manajemen Dokumen Cepat, Aman, & Akurat</title>
    <meta name="description" content="Platform utilitas dokumen terlengkap: Word ke PDF, PDF ke Word, Gabungkan PDF, Pisahkan PDF, Kompresi, dan Konversi Gambar tanpa merusak tata letak.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Favicon & Touch Icons -->
    <link rel="icon" type="image/svg+xml" href="assets/icons/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/favicon.png">
    <link rel="shortcut icon" href="favicon.ico">
    <link rel="apple-touch-icon" href="assets/icons/favicon.png">

    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        // Injeksi konfigurasi alat ke Javascript
        window.APP_TOOLS = <?= json_encode($tools, JSON_UNESCAPED_UNICODE) ?>;
    </script>
</head>
<body>

    <div class="ambient-glow"></div>

    <div class="container">
        <!-- Navbar -->
        <header class="navbar">
            <a href="index.php" class="logo">
                <div class="logo-icon">
                    <svg width="22" height="22" fill="none" stroke="#ffffff" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <span>PDF<span style="color: #38bdf8;">Converter</span></span>
            </a>
            <div class="nav-badges">
                <div class="nav-tag">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Privasi & Enkripsi Aman
                </div>
                <div class="nav-tag">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Hapus Otomatis 2 Jam
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-pill">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Pemrosesan Dokumen Kilat & Presisi Tinggi
            </div>
            <h1>Solusi Lengkap <span>Konversi & Manajemen Dokumen</span></h1>
            <p>Konversi Word ke PDF, PDF ke Word, gabungkan, pisahkan, dan kompresi file Anda dengan tata letak dokumen yang tetap utuh dan terlindungi.</p>
        </section>

        <!-- Controls: Filters & Search -->
        <div class="controls-bar">
            <div class="filter-tabs">
                <button class="tab-btn active" data-category="all">Semua Alat</button>
                <button class="tab-btn" data-category="conversion">Konversi Utama</button>
                <button class="tab-btn" data-category="management">Manajemen PDF</button>
                <button class="tab-btn" data-category="expansion">Ekspansi Format</button>
            </div>

            <div class="search-box">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" id="searchInput" placeholder="Cari alat (mis: Word, Gabung, Kompres)...">
            </div>
        </div>

        <!-- Tool Cards Grid -->
        <main class="tools-grid" id="toolsGrid">
            <?php foreach ($tools as $id => $tool): ?>
                <div class="tool-card" data-id="<?= htmlspecialchars($id) ?>" data-category="<?= htmlspecialchars($tool['category']) ?>" onclick="openTool('<?= htmlspecialchars($id) ?>')">
                    <?php if (!empty($tool['badge'])): ?>
                        <span class="card-badge badge-<?= strtolower(str_replace(' ', '-', $tool['badge'])) ?>">
                            <?= htmlspecialchars($tool['badge']) ?>
                        </span>
                    <?php endif; ?>

                    <div class="card-icon icon-<?= htmlspecialchars($tool['icon']) ?>">
                        <?php if (strpos($id, 'word') !== false): ?>
                            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        <?php elseif (strpos($id, 'merge') !== false): ?>
                            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                            </svg>
                        <?php elseif (strpos($id, 'split') !== false): ?>
                            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"></path>
                            </svg>
                        <?php elseif (strpos($id, 'compress') !== false): ?>
                            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                            </svg>
                        <?php elseif (strpos($id, 'excel') !== false): ?>
                            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        <?php elseif (strpos($id, 'pptx') !== false): ?>
                            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                            </svg>
                        <?php else: ?>
                            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        <?php endif; ?>
                    </div>

                    <h3><?= htmlspecialchars($tool['title']) ?></h3>
                    <p><?= htmlspecialchars($tool['description']) ?></p>

                    <div class="card-footer">
                        <span>Pilih File Sekarang</span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
            <?php endforeach; ?>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; <?= date('Y') ?> PDF Converter • Konversi Cepat & Aman. File dihapus secara otomatis dalam 2 jam untuk privasi Anda.</p>
        </footer>
    </div>

    <!-- Modal Dialog Converter Interaktif -->
    <div class="modal-overlay" id="converterModal">
        <div class="modal-container">
            <!-- Header Modal -->
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <div class="card-icon" id="modalToolIcon">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 id="modalToolTitle" style="font-size: 1.25rem; font-weight: 700;">Konversi Dokumen</h2>
                        <p id="modalToolDesc" style="font-size: 0.85rem; color: var(--text-muted);">Pilih file untuk memulai proses konversi.</p>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" id="modalCloseBtn" aria-label="Tutup Modal">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- FASE 1: UNGGAH FILE -->
            <div class="phase active" id="phaseUpload">
                <div class="dropzone" id="dropzone">
                    <div class="dropzone-icon">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                    <h4>Tarik & Lepaskan File Di Sini</h4>
                    <p>Atau klik tombol di bawah untuk memilih dari komputer Anda</p>
                    <div class="btn-file-select">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Pilih File
                    </div>
                </div>
                <input type="file" id="fileInput" class="file-input-hidden">

                <!-- Daftar File yang Dipilih -->
                <div class="selected-files-list" id="selectedFilesList"></div>

                <!-- Kontainer Opsi Kustom Alat (Split range, Compress quality) -->
                <div id="toolOptionsContainer"></div>

                <!-- Tombol Aksi -->
                <div class="modal-actions">
                    <button type="button" class="btn-primary" id="btnStartConvert" disabled>
                        <span>Mulai Konversi</span>
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- FASE 2: PEMROSESAN & PROGRES -->
            <div class="phase" id="phaseProcessing">
                <div class="processing-box">
                    <div class="spinner-wrap">
                        <div class="spinner-ring"></div>
                        <svg class="spinner-icon" width="34" height="34" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                    <div class="processing-title" id="processingTitle">Sedang Mengonversi...</div>
                    <div class="processing-subtitle" id="processingSubtitle">Menyiapkan dokumen dan mengekstrak struktur...</div>

                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" id="progressBarFill"></div>
                    </div>
                    <div class="progress-percentage" id="progressPercentage">0%</div>

                    <button type="button" class="btn-secondary" id="btnCancelConvert">
                        Batal
                    </button>
                </div>
            </div>

            <!-- FASE 3: HASIL & UNDUH -->
            <div class="phase" id="phaseResult">
                <div class="result-box">
                    <div class="success-badge">
                        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 6px;">Konversi Berhasil!</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Dokumen Anda telah selesai diproses dan siap diunduh.</p>

                    <!-- Kartu Info Hasil -->
                    <div class="result-card">
                        <div class="result-file-header">
                            <div class="result-file-name" id="resultFileName">dokumen_hasil.pdf</div>
                            <span class="badge-savings" id="resultSavingsBadge" style="display: none;">Hemat 0%</span>
                        </div>
                        <div class="result-file-meta">
                            <div>Ukuran: <strong id="resultFileSize" style="color: #fff;">0 KB</strong></div>
                            <div>Waktu: <span class="badge-metric" id="resultTimeElapsed">0.0s</span></div>
                        </div>
                    </div>

                    <!-- Tombol Unduh Utama -->
                    <a href="#" class="btn-download-big" id="btnDownloadResult" download>
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Unduh File Sekarang
                    </a>

                    <!-- Aksi Sekunder -->
                    <div class="result-secondary-actions">
                        <button type="button" class="btn-secondary" id="btnCopyLink">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align: -2px; margin-right: 4px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Salin Tautan Unduh
                        </button>
                        <button type="button" class="btn-secondary" id="btnConvertAnother">
                            Konversi Dokumen Lain
                        </button>
                    </div>

                    <!-- Prompt Aksi Cerdas Lanjutan -->
                    <div id="nextActionContainer"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="appToast">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span id="toastMessage">Pemberitahuan</span>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
