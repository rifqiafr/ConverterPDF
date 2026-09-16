import os
import sys

def convert_docx_via_com(input_path: str, output_path: str) -> bool:
    """Mengonversi dokumen Word ke PDF menggunakan Microsoft Word asli di Windows."""
    word = None
    doc = None
    try:
        import win32com.client
        import pythoncom
        pythoncom.CoInitialize()
        
        # Buka instance Microsoft Word
        word = win32com.client.DispatchEx("Word.Application")
        word.Visible = False
        word.DisplayAlerts = 0  # wdAlertsNone
        
        abs_in = os.path.abspath(input_path)
        abs_out = os.path.abspath(output_path)
        
        doc = word.Documents.Open(
            abs_in,
            ReadOnly=True,
            ConfirmConversions=False,
            AddToRecentFiles=False
        )
        # 17 = wdFormatPDF (format PDF resmi Microsoft Word)
        doc.SaveAs(abs_out, FileFormat=17)
        return os.path.exists(abs_out)
    except Exception as e:
        sys.stderr.write(f"Word COM notice: {str(e)}\n")
        return False
    finally:
        try:
            if doc is not None:
                doc.Close(SaveChanges=0)
        except Exception:
            pass
        try:
            if word is not None:
                word.Quit()
        except Exception:
            pass
        try:
            import pythoncom
            pythoncom.CoUninitialize()
        except Exception:
            pass

def convert_docx_via_reportlab(input_path: str, output_path: str) -> bool:
    """
    Fallback converter murni Python menggunakan python-docx dan ReportLab.
    Mempertahankan judul, paragraf, daftar, tebal/miring, dan tabel dokumen.
    """
    try:
        import docx
        from reportlab.lib.pagesizes import letter, A4
        from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle
        from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
        from reportlab.lib import colors
        from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_RIGHT, TA_JUSTIFY

        doc_in = docx.Document(input_path)
        pdf_doc = SimpleDocTemplate(
            output_path,
            pagesize=A4,
            rightMargin=40,
            leftMargin=40,
            topMargin=40,
            bottomMargin=40
        )

        styles = getSampleStyleSheet()
        normal_style = styles['Normal']
        normal_style.fontSize = 10
        normal_style.leading = 14

        story = []

        for p in doc_in.paragraphs:
            text = p.text.strip()
            if not text:
                story.append(Spacer(1, 8))
                continue

            # Menentukan style berdasarkan jenis heading
            p_style = ParagraphStyle(
                'CustomStyle',
                parent=normal_style,
                fontSize=14 if p.style.name.startswith('Heading 1') else (12 if p.style.name.startswith('Heading') else 10),
                leading=18 if p.style.name.startswith('Heading') else 14,
                fontName='Helvetica-Bold' if p.style.name.startswith('Heading') else 'Helvetica',
            )

            # Format formatting inline
            runs_html = []
            for run in p.runs:
                r_text = run.text.replace('&', '&amp;').replace('<', '&lt;').replace('>', '&gt;')
                if run.bold and run.italic:
                    r_text = f"<b><i>{r_text}</i></b>"
                elif run.bold:
                    r_text = f"<b>{r_text}</b>"
                elif run.italic:
                    r_text = f"<i>{r_text}</i>"
                if run.underline:
                    r_text = f"<u>{r_text}</u>"
                runs_html.append(r_text)

            formatted_text = "".join(runs_html) if runs_html else text
            story.append(Paragraph(formatted_text, p_style))
            story.append(Spacer(1, 4))

        # Memproses tabel
        for t in doc_in.tables:
            table_data = []
            for row in t.rows:
                row_data = []
                for cell in row.cells:
                    cell_text = cell.text.strip().replace('&', '&amp;').replace('<', '&lt;').replace('>', '&gt;')
                    row_data.append(Paragraph(cell_text, normal_style))
                table_data.append(row_data)

            if table_data:
                table = Table(table_data, colWidths=None)
                table.setStyle(TableStyle([
                    ('BACKGROUND', (0,0), (-1,0), colors.HexColor('#f1f5f9')),
                    ('TEXTCOLOR', (0,0), (-1,0), colors.HexColor('#1e293b')),
                    ('ALIGN', (0,0), (-1,-1), 'LEFT'),
                    ('FONTNAME', (0,0), (-1,-1), 'Helvetica'),
                    ('FONTSIZE', (0,0), (-1,-1), 9),
                    ('BOTTOMPADDING', (0,0), (-1,-1), 6),
                    ('TOPPADDING', (0,0), (-1,-1), 6),
                    ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor('#cbd5e1')),
                ]))
                story.append(table)
                story.append(Spacer(1, 10))

        pdf_doc.build(story)
        return os.path.exists(output_path)
    except Exception as e:
        sys.stderr.write(f"ReportLab fallback error: {str(e)}\n")
        return False

def convert_word_to_pdf(input_docx: str, output_pdf: str) -> dict:
    if not os.path.exists(input_docx):
        return {"success": False, "error": f"File input tidak ditemukan: {input_docx}"}

    # 1. Coba lewat MS Word COM jika tersedia di Windows
    success = convert_docx_via_com(input_docx, output_pdf)
    
    # 2. Jika COM tidak berhasil, gunakan konverter ReportLab murni
    if not success:
        success = convert_docx_via_reportlab(input_docx, output_pdf)

    if success and os.path.exists(output_pdf):
        return {
            "success": True,
            "output": output_pdf,
            "size": os.path.getsize(output_pdf),
            "message": "Konversi Word ke PDF berhasil!"
        }
    else:
        return {"success": False, "error": "Gagal mengonversi file Word ke PDF."}
