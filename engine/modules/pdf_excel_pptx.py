import os
import pymupdf as fitz
from openpyxl import Workbook
from pptx import Presentation
from pptx.util import Inches

def pdf_to_excel(input_pdf: str, output_xlsx: str) -> dict:
    """Mengekstrak tabel dan teks dari dokumen PDF ke dalam spreadsheet Excel (.xlsx)."""
    if not os.path.exists(input_pdf):
        return {"success": False, "error": f"File tidak ditemukan: {input_pdf}"}

    try:
        doc = fitz.open(input_pdf)
        wb = Workbook()
        # Buat sheet default
        ws = wb.active
        ws.title = "Halaman 1"

        total_rows_extracted = 0

        for page_num, page in enumerate(doc):
            if page_num > 0:
                ws = wb.create_sheet(title=f"Halaman {page_num + 1}")

            # PyMuPDF memiliki fitur deteksi tabel canggih (page.find_tables())
            tabs = page.find_tables()
            if tabs.tables:
                for tab in tabs:
                    table_matrix = tab.extract()
                    for r_idx, row in enumerate(table_matrix):
                        cleaned_row = [cell if cell is not None else "" for cell in row]
                        ws.append(cleaned_row)
                        total_rows_extracted += 1
                    ws.append([])  # Baris kosong antar tabel
            else:
                # Jika tidak ada pola tabel terstruktur, ekstrak baris teks
                text_lines = page.get_text("text").splitlines()
                for line in text_lines:
                    if line.strip():
                        # Coba split berdasarkan spasi ganda atau tab jika ada format kolom
                        parts = [p.strip() for p in line.split("  ") if p.strip()]
                        ws.append(parts if len(parts) > 1 else [line.strip()])
                        total_rows_extracted += 1

        doc.close()
        wb.save(output_xlsx)

        return {
            "success": True,
            "output": output_xlsx,
            "rows": total_rows_extracted,
            "size": os.path.getsize(output_xlsx),
            "message": f"Berhasil mengekstrak data dari PDF ke spreadsheet Excel."
        }
    except Exception as e:
        return {"success": False, "error": f"Gagal mengonversi PDF ke Excel: {str(e)}"}

def pdf_to_pptx(input_pdf: str, output_pptx: str) -> dict:
    """Mengonversi slide atau halaman dokumen PDF ke presentasi PowerPoint (.pptx)."""
    if not os.path.exists(input_pdf):
        return {"success": False, "error": f"File tidak ditemukan: {input_pdf}"}

    try:
        doc = fitz.open(input_pdf)
        prs = Presentation()
        # Gunakan blank slide layout (layout index 6)
        blank_slide_layout = prs.slide_layouts[6]

        temp_dir = os.path.join(os.path.dirname(output_pptx), f"pptx_temp_{os.getpid()}")
        os.makedirs(temp_dir, exist_ok=True)

        temp_images = []

        for i, page in enumerate(doc):
            slide = prs.slides.add_slide(blank_slide_layout)
            
            # Render halaman PDF ke gambar beresolusi tinggi
            pix = page.get_pixmap(dpi=150)
            img_file = os.path.join(temp_dir, f"slide_{i+1}.png")
            pix.save(img_file)
            temp_images.append(img_file)

            # Tambahkan gambar ke slide dengan penyesuaian ukuran
            slide_width = prs.slide_width
            slide_height = prs.slide_height
            slide.shapes.add_picture(img_file, 0, 0, width=slide_width, height=slide_height)

        doc.close()
        prs.save(output_pptx)

        # Bersihkan file temporer
        for img_f in temp_images:
            try: os.remove(img_f)
            except: pass
        try: os.rmdir(temp_dir)
        except: pass

        return {
            "success": True,
            "output": output_pptx,
            "slides": len(temp_images),
            "size": os.path.getsize(output_pptx),
            "message": f"Berhasil mengonversi PDF menjadi presentasi PowerPoint ({len(temp_images)} slide)."
        }
    except Exception as e:
        return {"success": False, "error": f"Gagal mengonversi PDF ke PowerPoint: {str(e)}"}
