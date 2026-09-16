import os
import zipfile
from pypdf import PdfReader, PdfWriter

def merge_pdfs(input_files: list, output_pdf: str) -> dict:
    """Menggabungkan beberapa file PDF berurutan menjadi satu dokumen."""
    try:
        writer = PdfWriter()
        total_pages = 0

        for file_path in input_files:
            if not os.path.exists(file_path):
                return {"success": False, "error": f"File PDF tidak ditemukan: {file_path}"}
            reader = PdfReader(file_path)
            for page in reader.pages:
                writer.add_page(page)
                total_pages += 1

        with open(output_pdf, "wb") as f_out:
            writer.write(f_out)

        return {
            "success": True,
            "output": output_pdf,
            "pages": total_pages,
            "size": os.path.getsize(output_pdf),
            "message": f"Berhasil menggabungkan {len(input_files)} file ({total_pages} halaman) ke dalam satu PDF."
        }
    except Exception as e:
        return {"success": False, "error": f"Gagal menggabungkan PDF: {str(e)}"}

def split_pdf(input_pdf: str, output_zip_or_pdf: str, page_ranges: str = None) -> dict:
    """
    Memisahkan satu dokumen PDF menjadi beberapa file atau halaman yang dipilih.
    Jika menghasilkan banyak file, dikemas dalam format .zip.
    """
    try:
        if not os.path.exists(input_pdf):
            return {"success": False, "error": f"File tidak ditemukan: {input_pdf}"}

        reader = PdfReader(input_pdf)
        total_pages = len(reader.pages)

        if total_pages == 0:
            return {"success": False, "error": "Dokumen PDF kosong."}

        # Parsing rentang halaman jika ada (misal: "1-3,5" atau default semua halaman)
        target_pages = []
        if page_ranges and page_ranges.strip():
            parts = page_ranges.split(",")
            for part in parts:
                part = part.strip()
                if "-" in part:
                    start_str, end_str = part.split("-")
                    start = max(1, int(start_str))
                    end = min(total_pages, int(end_str))
                    target_pages.extend(range(start, end + 1))
                else:
                    p = int(part)
                    if 1 <= p <= total_pages:
                        target_pages.append(p)
            target_pages = sorted(list(set(target_pages)))
        else:
            target_pages = list(range(1, total_pages + 1))

        # Simpan halaman-halaman ke file terpisah
        base_name = os.path.splitext(os.path.basename(input_pdf))[0]
        temp_dir = os.path.join(os.path.dirname(output_zip_or_pdf), f"split_{os.path.splitext(os.path.basename(output_zip_or_pdf))[0]}")
        os.makedirs(temp_dir, exist_ok=True)

        created_files = []
        for p_num in target_pages:
            writer = PdfWriter()
            writer.add_page(reader.pages[p_num - 1])
            single_filename = f"{base_name}_hal_{p_num}.pdf"
            single_path = os.path.join(temp_dir, single_filename)
            with open(single_path, "wb") as f_out:
                writer.write(f_out)
            created_files.append(single_path)

        # Jika hanya 1 file dan ekstensi output adalah .pdf
        if len(created_files) == 1 and output_zip_or_pdf.endswith('.pdf'):
            import shutil
            shutil.copy2(created_files[0], output_zip_or_pdf)
        else:
            # Kemas ke ZIP jika banyak file atau format .zip
            if not output_zip_or_pdf.endswith('.zip'):
                output_zip_or_pdf = os.path.splitext(output_zip_or_pdf)[0] + '.zip'

            with zipfile.ZipFile(output_zip_or_pdf, 'w', zipfile.ZIP_DEFLATED) as zipf:
                for file_p in created_files:
                    zipf.write(file_p, arcname=os.path.basename(file_p))

        # Bersihkan temp files
        for f in created_files:
            try: os.remove(f)
            except: pass
        try: os.rmdir(temp_dir)
        except: pass

        return {
            "success": True,
            "output": output_zip_or_pdf,
            "pages_split": len(target_pages),
            "size": os.path.getsize(output_zip_or_pdf),
            "message": f"Berhasil memisahkan {len(target_pages)} halaman PDF."
        }
    except Exception as e:
        return {"success": False, "error": f"Gagal memisahkan PDF: {str(e)}"}
