import os
import zipfile
import pymupdf as fitz
from PIL import Image

def pdf_to_images(input_pdf: str, output_zip: str, img_format: str = "png", dpi: int = 150) -> dict:
    """Mengonversi seluruh halaman PDF menjadi kumpulan gambar (PNG/JPG) dalam arsip ZIP."""
    if not os.path.exists(input_pdf):
        return {"success": False, "error": f"File tidak ditemukan: {input_pdf}"}

    try:
        doc = fitz.open(input_pdf)
        if len(doc) == 0:
            return {"success": False, "error": "Dokumen PDF tidak memiliki halaman."}

        base_name = os.path.splitext(os.path.basename(input_pdf))[0]
        temp_dir = os.path.join(os.path.dirname(output_zip), f"img_{os.path.splitext(os.path.basename(output_zip))[0]}")
        os.makedirs(temp_dir, exist_ok=True)

        zoom = dpi / 72.0  # 72 is default PDF resolution
        mat = fitz.Matrix(zoom, zoom)

        image_files = []
        for i, page in enumerate(doc):
            pix = page.get_pixmap(matrix=mat, alpha=False)
            ext = img_format.lower().replace('.', '')
            img_filename = f"{base_name}_hal_{i+1}.{ext}"
            img_path = os.path.join(temp_dir, img_filename)
            pix.save(img_path)
            image_files.append(img_path)

        doc.close()

        if not output_zip.endswith('.zip'):
            output_zip = os.path.splitext(output_zip)[0] + '.zip'

        with zipfile.ZipFile(output_zip, 'w', zipfile.ZIP_DEFLATED) as zipf:
            for img_p in image_files:
                zipf.write(img_p, arcname=os.path.basename(img_p))

        # Bersihkan file temporer
        for img_p in image_files:
            try: os.remove(img_p)
            except: pass
        try: os.rmdir(temp_dir)
        except: pass

        return {
            "success": True,
            "output": output_zip,
            "images_count": len(image_files),
            "size": os.path.getsize(output_zip),
            "message": f"Berhasil mengekstrak {len(image_files)} halaman ke dalam format gambar."
        }
    except Exception as e:
        return {"success": False, "error": f"Gagal mengekstrak gambar dari PDF: {str(e)}"}

def images_to_pdf(input_images: list, output_pdf: str) -> dict:
    """Menggabungkan satu atau beberapa file gambar (JPG, PNG, WebP) menjadi satu dokumen PDF."""
    try:
        pil_images = []
        for img_path in input_images:
            if not os.path.exists(img_path):
                return {"success": False, "error": f"File gambar tidak ditemukan: {img_path}"}

            img = Image.open(img_path)
            # Konversi RGBA ke RGB jika ada transparansi agar kompatibel dengan format PDF
            if img.mode in ("RGBA", "P"):
                img = img.convert("RGB")
            pil_images.append(img)

        if not pil_images:
            return {"success": False, "error": "Tidak ada gambar yang valid untuk dikonversi."}

        # Simpan gambar pertama dan append sisanya
        first_img = pil_images[0]
        rest_images = pil_images[1:] if len(pil_images) > 1 else []

        first_img.save(
            output_pdf,
            "PDF",
            resolution=100.0,
            save_all=True,
            append_images=rest_images
        )

        return {
            "success": True,
            "output": output_pdf,
            "pages": len(pil_images),
            "size": os.path.getsize(output_pdf),
            "message": f"Berhasil mengonversi {len(pil_images)} gambar menjadi dokumen PDF."
        }
    except Exception as e:
        return {"success": False, "error": f"Gagal mengonversi gambar ke PDF: {str(e)}"}
