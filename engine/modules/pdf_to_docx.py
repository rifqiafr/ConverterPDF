import os
from pdf2docx import Converter

def convert_pdf_to_docx(input_pdf: str, output_docx: str) -> dict:
    """
    Mengonversi file PDF ke Word (.docx) dengan mempertahankan teks, tabel, font, dan layout.
    """
    if not os.path.exists(input_pdf):
        return {"success": False, "error": f"File tidak ditemukan: {input_pdf}"}

    try:
        cv = Converter(input_pdf)
        # convert(output_file, start=0, end=None)
        cv.convert(output_docx, multi_processing=True, cpu_count=2)
        cv.close()

        if os.path.exists(output_docx):
            return {
                "success": True,
                "output": output_docx,
                "size": os.path.getsize(output_docx),
                "message": "Konversi PDF ke Word berhasil!"
            }
        else:
            return {"success": False, "error": "File DOCX hasil konversi tidak tercipta."}
    except Exception as e:
        return {"success": False, "error": f"Gagal mengonversi PDF ke Word: {str(e)}"}
