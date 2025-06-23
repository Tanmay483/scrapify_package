import sys
from PyPDF2 import PdfReader, PdfWriter

def split_pdf(input_pdf_path, output_directory, base_name):
    reader = PdfReader(input_pdf_path)
    total_pages = len(reader.pages)

    split_files = []
    
    # Split each page into a separate PDF
    for page_num in range(total_pages):
        writer = PdfWriter()
        writer.add_page(reader.pages[page_num])

        output_filename = f"{base_name}_{page_num + 1}.pdf"
        output_path = f"{output_directory}/{output_filename}"
        
        with open(output_path, "wb") as output_file:
            writer.write(output_file)
        
        split_files.append(output_path)
    
    return split_files

if __name__ == "__main__":
    input_pdf_path = sys.argv[1]
    output_directory = sys.argv[2]
    base_name = sys.argv[3]

    split_pdf(input_pdf_path, output_directory, base_name)
