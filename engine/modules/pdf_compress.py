import os
import pymupdf as fitz
from pypdf import PdfReader, PdfWriter

def compress_pdf(input_pdf: str, output_pdf: str, quality_level: str = "medium") -> dict:
    """
    Mengurangi ukuran file PDF melalui kompresi stream, reduksi gambar,
    dan pembersihan objek yang tidak digunakan tanpa mengorbankan keterbacaan dokumen.
    """
    if not os.path.exists(input_pdf):
        return {"success": False, "error": f"File PDF tidak ditemukan: {input_pdf}"}

    original_size = os.path.getsize(input_pdf)

    try:
        # Gunakan PyMuPDF (fitz) untuk kompresi tingkat tinggi yang efisien
        doc = fitz.open(input_pdf)

        # Deflate content & garbage collect unreferenced objects
        # garbage=4 (remove all unused streams and objects), deflate=True (compress streams)
        doc.save(
            output_pdf,
            garbage=4,
            deflate=True,
            clean=True,
            deflate_images=True,
            deflate_fonts=True
        )
        doc.close()

        compressed_size = os.path.getsize(output_pdf)

        # Jika ukuran tidak berkurang (misal PDF sudah sangat padat atau fitz tidak mengompres gambar internal),
        # coba metode pypdf stream compression
        if compressed_size >= original_size:
            reader = PdfReader(input_pdf)
            writer = PdfWriter()

            for page in reader.pages:
                page.compress_content_streams()
                writer.add_page(page)

            with open(output_pdf, "wb") as f_out:
                writer.write(f_out)

            compressed_size = os.path.getsize(output_pdf)

        saved_percent = round(((original_size - compressed_size) / original_size) * 100, 1) if original_size > 0 else 0
        if saved_percent < 0:
            saved_percent = 0

        return {
            "success": True,
            "output": output_pdf,
            "original_size": original_size,
            "compressed_size": compressed_size,
            "saved_percent": saved_percent,
            "message": f"Ukuran berkurang sebesar {saved_percent}% (dari {round(original_size/1024, 1)} KB menjadi {round(compressed_size/1024, 1)} KB)."
        }
    except Exception as e:
        return {"success": False, "error": f"Gagal mengompres PDF: {str(e)}"}
