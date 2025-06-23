<?php

namespace Scrapify\Pdftools;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory as ExcelIOFactory;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Exception;

class OfficeToPdfConverter
{
    public function convert(UploadedFile $file): array
    {
        $extension = $file->getClientOriginalExtension();
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $convertedFilename = $originalName . '_converted.pdf';

        // Save file to public/uploads
        $uploadDir = public_path('storage/uploads');
        if (!File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        $uploadedFilename = $file->getClientOriginalName();
        $uploadedPath = $uploadDir . '/' . $uploadedFilename;
        $file->move($uploadDir, $uploadedFilename);

        // Convert content
        $htmlContent = '';
        if ($extension === 'docx') {
            $phpWord = WordIOFactory::load($uploadedPath);
            $htmlWriter = WordIOFactory::createWriter($phpWord, 'HTML');
            ob_start();
            $htmlWriter->save('php://output');
            $htmlContent = ob_get_clean();
        } elseif ($extension === 'xlsx') {
            $spreadsheet = ExcelIOFactory::load($uploadedPath);
            $worksheet = $spreadsheet->getActiveSheet()->toArray();
            $htmlContent = "<h2>{$originalName}</h2><table border='1' cellpadding='5'>";
            foreach ($worksheet as $row) {
                $htmlContent .= '<tr>';
                foreach ($row as $cell) {
                    $htmlContent .= '<td>' . htmlspecialchars($cell) . '</td>';
                }
                $htmlContent .= '</tr>';
            }
            $htmlContent .= '</table>';
        } else {
            throw new Exception("Unsupported file type.");
        }

        // Convert HTML to PDF
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlContent);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();

        // Save PDF
        $pdfDir = public_path('storage/pdfs');
        if (!File::exists($pdfDir)) {
            File::makeDirectory($pdfDir, 0755, true);
        }
        $pdfPath = $pdfDir . '/' . $convertedFilename;
        file_put_contents($pdfPath, $pdfContent);

        return [
            'filename' => $convertedFilename,
            'file' => base64_encode($pdfContent),
            'url' => asset('storage/pdfs/' . $convertedFilename),
            'source_url' => asset('storage/uploads/' . $uploadedFilename),
        ];
    }
}
