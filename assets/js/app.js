/**
 * Aplikasi PDF Converter - Interaktivitas & Manajemen Konversi Dokumen
 */

document.addEventListener('DOMContentLoaded', () => {
    // State Aplikasi
    const state = {
        currentTool: null,
        selectedFiles: [],
        currentPhase: 'upload', // 'upload', 'processing', 'result'
        sessionToken: null,
        uploadedServerFiles: [],
        activeXhr: null,
        resultData: null
    };

    // Elemen DOM
    const modalOverlay = document.getElementById('converterModal');
    const modalCloseBtn = document.getElementById('modalCloseBtn');
    const modalToolTitle = document.getElementById('modalToolTitle');
    const modalToolDesc = document.getElementById('modalToolDesc');
    const modalToolIcon = document.getElementById('modalToolIcon');

    const phaseUpload = document.getElementById('phaseUpload');
    const phaseProcessing = document.getElementById('phaseProcessing');
    const phaseResult = document.getElementById('phaseResult');

    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const selectedFilesList = document.getElementById('selectedFilesList');
    const toolOptionsContainer = document.getElementById('toolOptionsContainer');
    const btnStartConvert = document.getElementById('btnStartConvert');

    const processingTitle = document.getElementById('processingTitle');
    const processingSubtitle = document.getElementById('processingSubtitle');
    const progressBarFill = document.getElementById('progressBarFill');
    const progressPercentage = document.getElementById('progressPercentage');
    const btnCancelConvert = document.getElementById('btnCancelConvert');

    const resultFileName = document.getElementById('resultFileName');
    const resultFileSize = document.getElementById('resultFileSize');
    const resultTimeElapsed = document.getElementById('resultTimeElapsed');
    const resultSavingsBadge = document.getElementById('resultSavingsBadge');
    const btnDownloadResult = document.getElementById('btnDownloadResult');
    const btnCopyLink = document.getElementById('btnCopyLink');
    const btnConvertAnother = document.getElementById('btnConvertAnother');
    const nextActionContainer = document.getElementById('nextActionContainer');

    const searchInput = document.getElementById('searchInput');
    const tabButtons = document.querySelectorAll('.tab-btn');
    const toolCards = document.querySelectorAll('.tool-card');

    // 1. Filter dan Pencarian Alat
    function filterTools() {
        const query = searchInput.value.toLowerCase().trim();
        const activeCategory = document.querySelector('.tab-btn.active').dataset.category;

        toolCards.forEach(card => {
            const toolName = card.querySelector('h3').textContent.toLowerCase();
            const toolDesc = card.querySelector('p').textContent.toLowerCase();
            const toolCategory = card.dataset.category;

            const matchesCategory = (activeCategory === 'all' || toolCategory === activeCategory);
            const matchesQuery = (toolName.includes(query) || toolDesc.includes(query));

            card.style.display = (matchesCategory && matchesQuery) ? 'flex' : 'none';
        });
    }

    searchInput.addEventListener('input', filterTools);
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            tabButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            filterTools();
        });
    });

    // 2. Membuka Modal Alat Konversi
    window.openTool = function(toolId) {
        const toolConfig = window.APP_TOOLS && window.APP_TOOLS[toolId];
        if (!toolConfig) return;

        state.currentTool = toolConfig;
        state.selectedFiles = [];
        state.sessionToken = null;
        state.uploadedServerFiles = [];
        state.resultData = null;

        modalToolTitle.textContent = toolConfig.title;
        modalToolDesc.textContent = toolConfig.description;
        modalToolIcon.className = `card-icon icon-${toolConfig.icon}`;

        // Reset file input accept & multiple
        fileInput.accept = toolConfig.accept.join(',');
        fileInput.multiple = Boolean(toolConfig.multiple);

        // Kustomisasi opsi tambahan berdasarkan alat
        renderToolOptions(toolId);
        renderSelectedFiles();
        switchPhase('upload');

        modalOverlay.classList.add('active');
    };

    function closeModal() {
        if (state.currentPhase === 'processing') {
            if (!confirm('Proses konversi sedang berjalan. Yakin ingin membatalkan?')) {
                return;
            }
            cancelCurrentProcess();
        }
        modalOverlay.classList.remove('active');
        fileInput.value = '';
    }

    modalCloseBtn.addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) closeModal();
    });

    // 3. Render Opsi Alat Tambahan
    function renderToolOptions(toolId) {
        toolOptionsContainer.innerHTML = '';
        if (toolId === 'split_pdf') {
            toolOptionsContainer.innerHTML = `
                <div class="tool-options">
                    <label for="splitRangeInput">Rentang Halaman yang Dipisahkan (Opsional)</label>
                    <input type="text" id="splitRangeInput" placeholder="Contoh: 1-3, 5 (Kosongkan untuk memisahkan semua halaman)">
                </div>
            `;
        } else if (toolId === 'compress_pdf') {
            toolOptionsContainer.innerHTML = `
                <div class="tool-options">
                    <label for="compressQualitySelect">Tingkat Kompresi Dokumen</label>
                    <select id="compressQualitySelect">
                        <option value="medium" selected>Rekomendasi (Kualitas Baik & Ukuran Berkurang)</option>
                        <option value="high">Maksimal (Ukuran Paling Kecil untuk Email)</option>
                        <option value="low">Ringan (Prioritas Ketajaman Visual)</option>
                    </select>
                </div>
            `;
        }
    }

    // 4. Drag and Drop & File Picker Handlers
    dropzone.addEventListener('click', () => fileInput.click());

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('dragover');
        });
    });

    ['dragleave'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('dragover');
        });
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove('dragover');
        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            const files = Array.from(e.dataTransfer.files);
            handleFilesSelected(files);
        }
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target.files && e.target.files.length > 0) {
            const files = Array.from(e.target.files);
            handleFilesSelected(files);
        }
    });

    function handleFilesSelected(newFiles) {
        if (!state.currentTool || newFiles.length === 0) return;

        const allowedExts = state.currentTool.accept.map(ext => ext.toLowerCase());

        for (const file of newFiles) {
            const fileExt = '.' + file.name.split('.').pop().toLowerCase();
            if (!allowedExts.includes(fileExt)) {
                showToast(`Format file "${file.name}" tidak didukung. Harap pilih ${allowedExts.join(', ')}`, 'error');
                continue;
            }
            if (file.size > 50 * 1024 * 1024) {
                showToast(`Ukuran file "${file.name}" melebihi batas 50MB.`, 'error');
                continue;
            }

            if (state.currentTool.multiple) {
                // Jangan duplikasi file yang sama persis
                const exists = state.selectedFiles.some(f => f.name === file.name && f.size === file.size);
                if (!exists) {
                    state.selectedFiles.push(file);
                }
            } else {
                // Single file: ganti file yang ada
                state.selectedFiles = [file];
                break;
            }
        }

        renderSelectedFiles();
    }

    function renderSelectedFiles() {
        selectedFilesList.innerHTML = '';
        if (state.selectedFiles.length === 0) {
            btnStartConvert.disabled = true;
            return;
        }

        btnStartConvert.disabled = false;
        state.selectedFiles.forEach((file, index) => {
            const item = document.createElement('div');
            item.className = 'file-item';
            item.innerHTML = `
                <div class="file-info">
                    <svg class="file-info-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <div>
                        <div class="file-name">${escapeHtml(file.name)}</div>
                        <div class="file-size">${formatBytes(file.size)}</div>
                    </div>
                </div>
                <button type="button" class="btn-remove-file" title="Hapus File" data-index="${index}">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;
            item.querySelector('.btn-remove-file').addEventListener('click', () => {
                state.selectedFiles.splice(index, 1);
                renderSelectedFiles();
            });
            selectedFilesList.appendChild(item);
        });
    }

    // 5. Alur Tahapan Antarmuka (Phase Switching)
    function switchPhase(phase) {
        state.currentPhase = phase;
        phaseUpload.classList.remove('active');
        phaseProcessing.classList.remove('active');
        phaseResult.classList.remove('active');

        if (phase === 'upload') {
            phaseUpload.classList.add('active');
        } else if (phase === 'processing') {
            phaseProcessing.classList.add('active');
        } else if (phase === 'result') {
            phaseResult.classList.add('active');
        }
    }

    // 6. Eksekusi Upload dan Konversi
    btnStartConvert.addEventListener('click', startProcess);

    function startProcess() {
        if (state.selectedFiles.length === 0 || !state.currentTool) return;

        switchPhase('processing');
        updateProgress(5, 'Mengunggah dokumen ke server...');

        const formData = new FormData();
        formData.append('action', 'upload');
        formData.append('tool_id', state.currentTool.id);

        state.selectedFiles.forEach(file => {
            formData.append('files[]', file);
        });

        const xhr = new XMLHttpRequest();
        state.activeXhr = xhr;

        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 45);
                updateProgress(percent, `Mengunggah file... (${percent * 2}%)`);
            }
        };

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                const resp = safeJsonParse(xhr.responseText);
                if (resp && resp.success) {
                    state.sessionToken = resp.token;
                    state.uploadedServerFiles = resp.files;
                    triggerConversion();
                } else {
                    handleProcessError((resp && resp.error) ? resp.error : 'Gagal mengunggah file.');
                }
            } else {
                handleProcessError(`Kesalahan jaringan/server (HTTP ${xhr.status})`);
            }
        };

        xhr.onerror = function() {
            handleProcessError('Koneksi ke server terputus.');
        };

        xhr.open('POST', 'backend/routes.php?action=upload', true);
        xhr.send(formData);
    }

    function triggerConversion() {
        updateProgress(55, 'Memulai proses konversi dokumen...');

        const formData = new FormData();
        formData.append('action', 'convert');
        formData.append('tool_id', state.currentTool.id);
        formData.append('token', state.sessionToken);

        state.uploadedServerFiles.forEach(file => {
            formData.append('file_names[]', file.stored_name);
        });

        // Opsi tambahan
        const rangeInput = document.getElementById('splitRangeInput');
        if (rangeInput && rangeInput.value.trim()) {
            formData.append('pages', rangeInput.value.trim());
        }

        const qualitySelect = document.getElementById('compressQualitySelect');
        if (qualitySelect) {
            formData.append('quality', qualitySelect.value);
        }

        const xhr = new XMLHttpRequest();
        state.activeXhr = xhr;

        // Simulasi progres konversi halus
        let simProgress = 60;
        const progressInterval = setInterval(() => {
            if (simProgress < 92) {
                simProgress += 4;
                updateProgress(simProgress, 'Menata tata letak & mengekstrak konten...');
            }
        }, 400);

        xhr.onload = function() {
            clearInterval(progressInterval);
            if (xhr.status >= 200 && xhr.status < 300) {
                const resp = safeJsonParse(xhr.responseText);
                if (resp && resp.success) {
                    updateProgress(100, 'Selesai!');
                    setTimeout(() => showResult(resp), 400);
                } else {
                    handleProcessError((resp && resp.error) ? resp.error : 'Konversi dokumen gagal.');
                }
            } else {
                const resp = safeJsonParse(xhr.responseText);
                handleProcessError((resp && resp.error) ? resp.error : `Gagal melakukan konversi (HTTP ${xhr.status})`);
            }
        };

        xhr.onerror = function() {
            clearInterval(progressInterval);
            handleProcessError('Koneksi terputus saat memproses dokumen.');
        };

        xhr.open('POST', 'backend/routes.php?action=convert', true);
        xhr.send(formData);
    }

    function updateProgress(percent, message) {
        progressBarFill.style.width = `${percent}%`;
        progressPercentage.textContent = `${percent}%`;
        processingSubtitle.textContent = message;
    }

    function handleProcessError(errorMessage) {
        showToast(errorMessage, 'error');
        switchPhase('upload');
    }

    // 7. Pembatalan Proses
    btnCancelConvert.addEventListener('click', cancelCurrentProcess);

    function cancelCurrentProcess() {
        if (state.activeXhr) {
            state.activeXhr.abort();
            state.activeXhr = null;
        }

        if (state.sessionToken) {
            const formData = new FormData();
            formData.append('action', 'cancel');
            formData.append('token', state.sessionToken);
            navigator.sendBeacon('backend/routes.php?action=cancel', formData);
        }

        showToast('Proses berhasil dibatalkan.', 'info');
        switchPhase('upload');
    }

    // 8. Menampilkan Hasil & Aksi Lanjutan
    function showResult(result) {
        state.resultData = result;
        switchPhase('result');

        resultFileName.textContent = result.download_name;
        resultFileSize.textContent = formatBytes(result.size);
        resultTimeElapsed.textContent = `${result.elapsed_time}s`;

        // Tampilkan badge penghematan kompresi jika ada
        if (result.details && result.details.saved_percent !== undefined && result.details.saved_percent > 0) {
            resultSavingsBadge.style.display = 'inline-block';
            resultSavingsBadge.textContent = `Hemat ${result.details.saved_percent}%`;
        } else {
            resultSavingsBadge.style.display = 'none';
        }

        // URL Download
        const downloadUrl = `backend/routes.php?action=download&file=${encodeURIComponent(result.file_name)}&token=${encodeURIComponent(result.token)}&name=${encodeURIComponent(result.download_name)}`;
        btnDownloadResult.href = downloadUrl;

        // Siapkan Prompt Aksi Lanjutan Cerdas
        setupNextActionPrompt(result);
    }

    function setupNextActionPrompt(result) {
        nextActionContainer.innerHTML = '';

        // Jika hasil berupa file PDF dan bukan dari alat kompres, tawarkan kompresi langsung
        if (state.currentTool.output_ext === 'pdf' && state.currentTool.id !== 'compress_pdf') {
            const promptDiv = document.createElement('div');
            promptDiv.className = 'next-action-prompt';
            promptDiv.innerHTML = `
                <div class="next-action-text">
                    <strong>💡 Langkah Selanjutnya:</strong> File PDF Anda sudah siap! Ingin mengompres ukurannya sekarang agar lebih ringan dikirim?
                </div>
                <button type="button" class="btn-quick-next" id="btnQuickCompress">
                    Kompres Sekarang
                </button>
            `;
            promptDiv.querySelector('#btnQuickCompress').addEventListener('click', () => {
                closeModal();
                window.openTool('compress_pdf');
            });
            nextActionContainer.appendChild(promptDiv);
        }
    }

    // Salin Tautan Unduh Sementara
    btnCopyLink.addEventListener('click', () => {
        if (!state.resultData) return;
        const fullDownloadUrl = window.location.origin + window.location.pathname.replace('index.php', '') + btnDownloadResult.getAttribute('href');
        navigator.clipboard.writeText(fullDownloadUrl).then(() => {
            showToast('Tautan unduh sementara berhasil disalin! (Berlaku 2 jam)', 'success');
        }).catch(() => {
            showToast('Gagal menyalin tautan.', 'error');
        });
    });

    btnConvertAnother.addEventListener('click', () => {
        state.selectedFiles = [];
        state.sessionToken = null;
        state.resultData = null;
        renderSelectedFiles();
        switchPhase('upload');
    });

    // Helper Toast
    function showToast(message, type = 'info') {
        const toast = document.getElementById('appToast');
        const toastMsg = document.getElementById('toastMessage');
        toast.className = `toast toast-${type} show`;
        toastMsg.textContent = message;

        setTimeout(() => {
            toast.classList.remove('show');
        }, 4000);
    }

    // Helper Ukuran File
    function formatBytes(bytes, decimals = 1) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    function safeJsonParse(text) {
        if (!text || typeof text !== 'string') return null;
        try {
            return JSON.parse(text);
        } catch (e) {
            const start = text.indexOf('{');
            const end = text.lastIndexOf('}');
            if (start !== -1 && end !== -1 && end > start) {
                try {
                    return JSON.parse(text.substring(start, end + 1));
                } catch (err) {}
            }
            console.error('Respon mentah server gagal di-parse:', text);
            return null;
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
