import sys
from PyPDF2 import PdfReader, PdfWriter

def rotate_pdf(input_path, output_path, angle):
    reader = PdfReader(input_path)
    writer = PdfWriter()

    for page in reader.pages:
        page.rotate(int(angle))
        writer.add_page(page)

    with open(output_path, "wb") as f:
        writer.write(f)

if __name__ == "__main__":
    if len(sys.argv) != 4:
        print("Usage: python rotate_pdf.py input.pdf output.pdf angle")
        sys.exit(1)

    rotate_pdf(sys.argv[1], sys.argv[2], sys.argv[3])