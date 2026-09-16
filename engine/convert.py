#!/usr/bin/env python3
import sys
import os
import argparse
import json

# Tambahkan direktori engine ke sys.path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from modules.word_to_pdf import convert_word_to_pdf
from modules.pdf_to_docx import convert_pdf_to_docx
from modules.pdf_merge_split import merge_pdfs, split_pdf
from modules.pdf_compress import compress_pdf
from modules.pdf_images import pdf_to_images, images_to_pdf
from modules.pdf_excel_pptx import pdf_to_excel, pdf_to_pptx

def main():
    parser = argparse.ArgumentParser(description="Mesin Konversi Dokumen PDF Multi-Format")
    parser.add_argument("--action", required=True, help="Tindakan: word_to_pdf, pdf_to_word, merge_pdf, split_pdf, compress_pdf, pdf_to_jpg, jpg_to_pdf, pdf_to_excel, pdf_to_pptx")
    parser.add_argument("--input", required=True, help="File input (dapat berupa satu path atau beberapa dipisahkan koma)")
    parser.add_argument("--output", required=True, help="Path file output tujuan")
    parser.add_argument("--pages", default=None, help="Rentang halaman untuk split (misal: 1-3,5)")
    parser.add_argument("--quality", default="medium", help="Kualitas kompresi (low, medium, high)")

    args = parser.parse_args()

    # Pastikan direktori output sudah ada
    out_dir = os.path.dirname(os.path.abspath(args.output))
    os.makedirs(out_dir, exist_ok=True)

    action = args.action.lower()
    input_files = [f.strip() for f in args.input.split(",") if f.strip()]

    result = {"success": False, "error": f"Aksi '{action}' tidak dikenal."}

    try:
        if action == "word_to_pdf":
            result = convert_word_to_pdf(input_files[0], args.output)
        elif action == "pdf_to_word":
            result = convert_pdf_to_docx(input_files[0], args.output)
        elif action == "merge_pdf":
            result = merge_pdfs(input_files, args.output)
        elif action == "split_pdf":
            result = split_pdf(input_files[0], args.output, args.pages)
        elif action == "compress_pdf":
            result = compress_pdf(input_files[0], args.output, args.quality)
        elif action == "pdf_to_jpg":
            result = pdf_to_images(input_files[0], args.output, img_format="jpg")
        elif action == "jpg_to_pdf":
            result = images_to_pdf(input_files, args.output)
        elif action == "pdf_to_excel":
            result = pdf_to_excel(input_files[0], args.output)
        elif action == "pdf_to_pptx":
            result = pdf_to_pptx(input_files[0], args.output)
    except Exception as e:
        result = {"success": False, "error": f"Kesalahan internal pada engine: {str(e)}"}

    # Cetak hasil dalam format JSON tunggal ke stdout
    print(json.dumps(result, ensure_ascii=False))
    if not result.get("success"):
        sys.exit(1)

if __name__ == "__main__":
    main()
