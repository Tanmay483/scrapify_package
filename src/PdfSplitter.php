<?php

namespace Scrapify\Pdftools;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Exception;

class PdfSplitter
{
    public function split(UploadedFile $uploadedFile): array
    {
        $originalFileName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $slugName = Str::slug($originalFileName) . '-' . time();
        $pdfFilename = $slugName . '.pdf';

        $publicPdfDir = public_path('storage/split');
        if (!File::exists($publicPdfDir)) {
            File::makeDirectory($publicPdfDir, 0755, true);
        }

        $publicPdfPath = $publicPdfDir . '/' . $pdfFilename;
        $uploadedFile->move($publicPdfDir, $pdfFilename);

        $outputDirectory = storage_path('app/public/split_output');
        if (!file_exists($outputDirectory)) {
            mkdir($outputDirectory, 0777, true);
        }

        $pythonScript = base_path('packages/scrapify/pdftools/split_pdf.py');
        $process = new Process([
            'python',
            $pythonScript,
            $publicPdfPath,
            $outputDirectory,
            $slugName,
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception($process->getErrorOutput());
        }

        $splitFiles = glob($outputDirectory . '/' . $slugName . '_*.pdf');
        $zipFileName = $slugName . '_split_files.zip';
        $zipPath = $outputDirectory . '/' . $zipFileName;

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            foreach ($splitFiles as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        }

        foreach ($splitFiles as $file) {
            unlink($file);
        }

        $fileContent = File::get($zipPath);
        unlink($zipPath);

        return [
            'filename' => $zipFileName,
            'zip_file' => base64_encode($fileContent),
        ];
    }
}
