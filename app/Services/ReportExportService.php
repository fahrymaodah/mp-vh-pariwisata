<?php

declare(strict_types=1);

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    /**
     * Export data as CSV.
     */
    public static function exportCsv(array $headers, array $rows, string $filename): StreamedResponse
    {
        return Response::streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Export data as PDF using DomPDF.
     */
    public static function exportPdf(string $view, array $data, string $filename, string $orientation = 'portrait'): \Illuminate\Http\Response
    {
        $pdf = Pdf::loadView($view, $data)->setPaper('a4', $orientation);

        return $pdf->download($filename . '.pdf');
    }
}
