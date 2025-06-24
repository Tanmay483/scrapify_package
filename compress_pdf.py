import sys
import subprocess

input_file = sys.argv[1]
output_file = sys.argv[2]

gs_command = [
    "gs",
    "-sDEVICE=pdfwrite",
    "-dCompatibilityLevel=1.4",
    "-dPDFSETTINGS=/screen",  # options: /screen, /ebook, /printer, /prepress
    "-dNOPAUSE",
    "-dQUIET",
    "-dBATCH",
    f"-sOutputFile={output_file}",
    input_file,
]

subprocess.run(gs_command, check=True)
