---
title: Converter PDF
emoji: 📑
colorFrom: indigo
colorTo: cyan
sdk: docker
app_port: 7860
pinned: false
---

# PDF Converter - Platform Konversi & Manajemen Dokumen Modern

Aplikasi utilitas dokumen berbasis web yang memungkinkan konversi Word ke PDF, PDF ke Word, penggabungan, pemisahan, kompresi, serta manajemen berbagai format dokumen secara cepat, aman, dan mempertahankan tata letak asli dokumen.

---

## 🚀 Fitur Utama

### 1. Modul Konversi
- **Word ke PDF (`.docx`, `.doc` ➔ `.pdf`)**: Menggunakan mesin asli Microsoft Word COM Automation dengan *fallback* cerdas Python untuk mempertahankan 100% tata letak, margin, gambar, dan tabel.
- **PDF ke Word (`.pdf` ➔ `.docx`)**: Ekstraksi teks, font, tabel, dan tata letak secara akurat menjadi file Word yang dapat diedit.
- **PDF ke Gambar (JPG/PNG)**: Ekstraksi halaman dokumen ke resolusi tinggi (150 DPI) dalam arsip ZIP.
- **Gambar ke PDF**: Menggabungkan file gambar (JPG, PNG, WebP) menjadi satu dokumen PDF utuh.
- **PDF ke Excel (`.xlsx`)**: Ekstraksi struktur tabel PDF ke spreadsheet Excel secara rapi.
- **PDF ke PowerPoint (`.pptx`)**: Konversi slide PDF ke presentasi PowerPoint.

### 2. Modul Manajemen PDF
- **Gabungkan PDF (Merge)**: Menyatukan beberapa dokumen PDF secara berurutan.
- **Pisahkan PDF (Split)**: Memisahkan halaman PDF berdasarkan rentang yang dipilih (misal: `1-3, 5`).
- **Kompres PDF**: Memperkecil ukuran dokumen tanpa mengorbankan keterbacaan (efisiensi hingga ~47%).

### 3. Keamanan & Antarmuka
- **Privasi Terjamin**: File temporer dienkripsi dengan ID sesi unik dan dihapus otomatis dari server dalam 1–2 jam (*Garbage Collector*).
- **Drag-and-Drop Cerdas**: Area peletakan file intuitif dengan dukungan multi-file dan filter format.
- **Real-Time Progress & Cancel**: Menampilkan progres animasi dan tombol pembatalan proses.
- **Desain Modern & Responsif**: Tampilan *glassmorphism* modern dengan tipografi *Plus Jakarta Sans*, optimal untuk desktop dan seluler.

---

## 🛠️ Persyaratan Sistem

- **PHP**: Versi 8.0 ke atas (diuji pada PHP 8.5)
- **Python**: Versi 3.10 ke atas (diuji pada Python 3.11)
- **Web Server**: Apache (Laragon / XAMPP) atau PHP Built-in Server

### Dependensi Python
```bash
pip install pypdf pdf2docx python-docx reportlab openpyxl python-pptx pymupdf pywin32 Pillow
```

---

## ⚡ Cara Menjalankan

### Opsi 1: Menggunakan Laragon Langsung
1. Letakkan folder ini di `c:\laragon\www\Konversi`.
2. Buka Laragon dan klik **Start All**.
3. Akses melalui peramban web di:
   - `http://localhost/Konversi` atau `http://konversi.test`

### Opsi 2: Menggunakan PHP Built-in Server
```bash
php -d upload_max_filesize=100M -d post_max_size=100M -d memory_limit=256M -S localhost:8000
```
Buka peramban di `http://localhost:8000`.

---

## 📁 Struktur Direktori
```
Konversi/
├── assets/
│   ├── css/style.css            # Desain UI modern, responsive, & glassmorphism
│   ├── js/app.js                # Logika frontend, drag-and-drop, & AJAX
│   └── icons/                   # Favicon & ikon SVG
├── backend/
│   ├── config.php               # Konfigurasi aplikasi & daftar alat
│   ├── cleanup.php              # Skrip penghapusan otomatis berkas
│   ├── routes.php               # Router API (upload, convert, download, cancel)
│   └── services/ConverterService.php # Penghubung PHP ke Python worker
├── engine/                      # Mesin konversi dokumen Python
│   ├── convert.py               # CLI dispatcher utama
│   └── modules/                 # Modul konversi per jenis format
├── storage/
│   ├── uploads/                 # Berkas sementara pengguna
│   └── results/                 # Berkas hasil konversi siap unduh
├── tests/                       # Automated test suite
├── index.php                    # Halaman dashboard & converter modal
├── .htaccess                    # Proteksi direktori storage
└── .user.ini                    # Konfigurasi upload limit PHP
```

---

## 📄 Lisensi
Hak Cipta &copy; 2026. Dibuat untuk kebutuhan konversi dokumen yang cepat, presisi, dan aman.
