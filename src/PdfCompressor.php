<?php


namespace Scrapify\Pdftools;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Exception;

class PdfCompressor
{
    public function compress(UploadedFile $file): array
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $originalPath = storage_path("app/public/{$originalName}.pdf");
        $compressedPath = storage_path("app/public/{$originalName}_compressed.pdf");

        // Save the uploaded file
        $file->move(dirname($originalPath), basename($originalPath));

        // Run compression script
        $script = __DIR__ . '/../compress_pdf.py';
        $process = new Process(['python', $script, $originalPath, $compressedPath]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception('Compression failed: ' . $process->getErrorOutput());
        }

        if (!File::exists($compressedPath)) {
            throw new Exception("Compressed file not found.");
        }

        $compressedContent = File::get($compressedPath);

        // Copy to public folder
        $publicPath = public_path('storage/pdfs/' . $originalName . '_compressed.pdf');
        File::ensureDirectoryExists(dirname($publicPath));
        File::copy($compressedPath, $publicPath);

        // Return result
        return [
            'filename' => $originalName . '_compressed.pdf',
            'file' => base64_encode($compressedContent),
            'url' => asset('storage/pdfs/' . $originalName . '_compressed.pdf'),
        ];
    }
}
