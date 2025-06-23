<?php

namespace scrapify\PdfTools;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Exception;

class PdfMerger
{
    public function merge(array $uploadedFiles): array
    {
        $filePaths = [];

        $uploadDir = public_path('storage/uploads');
        if (!File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        foreach ($uploadedFiles as $file) {
            if ($file instanceof UploadedFile) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $slugName = Str::slug($originalName) . '-' . time() . '.pdf';
                $publicPath = $uploadDir . '/' . $slugName;
                $file->move($uploadDir, $slugName);
                $filePaths[] = $publicPath;
            }
        }

        $mergedDir = public_path('storage/merged');
        if (!File::exists($mergedDir)) {
            File::makeDirectory($mergedDir, 0755, true);
        }

        $outputFilename = 'merged-' . time() . '.pdf';
        $outputPath = $mergedDir . '/' . $outputFilename;

        $pythonScript = __DIR__ . '/../merge_pdf.py';
        $process = new Process(array_merge(['python', $pythonScript, $outputPath], $filePaths));
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception('Python Error: ' . $process->getErrorOutput());
        }

        return [
            'filename' => $outputFilename,
            'path' => $outputPath,
            'base64' => base64_encode(File::get($outputPath)),
        ];
    }
}
