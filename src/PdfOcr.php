<?php


namespace Scrapify\Pdftools;

use Smalot\PdfParser\Parser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Exception;

class PdfOcr
{
    public function extractText(UploadedFile $pdf): array
    {
        $maxSizeKB = 25 * 1024;
        if ($pdf->getSize() / 1024 > $maxSizeKB) {
            throw new Exception("PDF exceeds maximum size of 25MB.");
        }

        $originalName = $pdf->getClientOriginalName();
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $uploadedFilename = $baseName . '.pdf';
        $ocrFilename = $baseName . '-ocr.txt';

        // Save uploaded PDF to public path
        $pdfDir = public_path('storage/pdfs');
        if (!File::exists($pdfDir)) {
            File::makeDirectory($pdfDir, 0755, true);
        }

        $uploadedPath = $pdfDir . '/' . $uploadedFilename;
        $pdf->move($pdfDir, $uploadedFilename);

        // Run OCR
        $parser = new Parser();
        $document = $parser->parseFile($uploadedPath);
        $text = $document->getText();

        // Save OCR result
        $ocrDir = public_path('storage/ocr');
        if (!File::exists($ocrDir)) {
            File::makeDirectory($ocrDir, 0755, true);
        }

        $ocrPath = $ocrDir . '/' . $ocrFilename;
        file_put_contents($ocrPath, $text);

        return [
            'filename' => $uploadedFilename,
            'file'     => base64_encode(file_get_contents($uploadedPath)),
            'url'      => asset('storage/pdfs/' . $uploadedFilename),
            'ocr_text' => $text,
            'ocr_file' => $ocrFilename,
        ];
    }
}
