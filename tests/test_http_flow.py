import requests

BASE_URL = 'http://localhost:8000/backend/routes.php'

def test_full_flow():
    # 1. Upload sample.docx
    files = {
        'files[]': ('test_doc.docx', open('tests/test_files/sample.docx', 'rb'), 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
    }
    data = {
        'action': 'upload',
        'tool_id': 'word_to_pdf'
    }
    r = requests.post(f"{BASE_URL}?action=upload", data=data, files=files)
    print("Upload Status:", r.status_code)
    print("Upload Response:", r.text)
    assert r.status_code == 200, "Upload failed"
    upload_res = r.json()
    assert upload_res.get('success') is True, "Upload not successful"
    token = upload_res['token']
    stored_name = upload_res['files'][0]['stored_name']

    # 2. Convert Word to PDF
    convert_data = {
        'action': 'convert',
        'tool_id': 'word_to_pdf',
        'token': token,
        'file_names[]': [stored_name]
    }
    r2 = requests.post(f"{BASE_URL}?action=convert", data=convert_data)
    print("Convert Status:", r2.status_code)
    print("Convert Response:", r2.text)
    assert r2.status_code == 200, "Convert failed"
    conv_res = r2.json()
    assert conv_res.get('success') is True, "Conversion not successful"

    # 3. Test Download
    download_url = f"{BASE_URL}?action=download&file={conv_res['file_name']}&token={token}&name={conv_res['download_name']}"
    r3 = requests.get(download_url)
    print("Download Status:", r3.status_code)
    print("Downloaded Bytes:", len(r3.content))
    assert r3.status_code == 200, "Download failed"
    assert len(r3.content) > 0, "Downloaded file empty"

    print("\n[SUKSES] Seluruh siklus HTTP (Upload -> Convert -> Download) berjalan sempurna!")

if __name__ == '__main__':
    test_full_flow()
