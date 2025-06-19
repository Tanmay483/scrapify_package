import sys
from PyPDF2 import PdfReader, PdfWriter

def merge_pdfs(output_path, input_files):
    writer = PdfWriter()

    for pdf_file in input_files:
        reader = PdfReader(pdf_file)
        for page in range(len(reader.pages)):
            writer.add_page(reader.pages[page])

    with open(output_path, 'wb') as output_pdf:
        writer.write(output_pdf)

if __name__ == '__main__':
    output_file = sys.argv[1]  # First argument: output file path
    input_files = sys.argv[2:]  # Remaining arguments: input file paths

    # Call the merge_pdfs function
    merge_pdfs(output_file, input_files)
