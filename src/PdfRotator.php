<?php


namespace Scrapify\Pdftools;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Exception;

class PdfRotator
{
    public function rotate(UploadedFile $file, int $angle): array
    {
        if (!in_array($angle, [90, 180, 270])) {
            throw new Exception("Invalid rotation angle.");
        }

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $inputPath = storage_path("app/public/{$originalName}.pdf");
        $rotatedFilename = $originalName . '_Rotated.pdf';
        $outputPath = storage_path("app/public/{$rotatedFilename}");

        // Save uploaded file
        $file->move(dirname($inputPath), basename($inputPath));

        // Copy to public folder for visibility
        $pdfDir = public_path('storage/pdfs');
        File::ensureDirectoryExists($pdfDir);
        File::copy($inputPath, $pdfDir . '/' . basename($inputPath));

        // Run Python script
        $scriptPath = __DIR__ . '/../rotate_pdf.py';
        $process = new Process(['python', $scriptPath, $inputPath, $outputPath, $angle]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception("Python Error: " . $process->getErrorOutput());
        }

        if (!File::exists($outputPath)) {
            throw new Exception("Rotated file not found.");
        }

        $fileContent = File::get($outputPath);

        return [
            'filename' => $rotatedFilename,
            'file' => base64_encode($fileContent),
            'url' => asset('storage/pdfs/' . basename($inputPath)),
        ];
    }
}
