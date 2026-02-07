<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PdfExportService
{
    /**
     * Generate financial report PDF
     *
     * @param array $reportData
     * @return array
     */
    public function generateFinancialReportPdf(array $reportData): array
    {
        $pdf = Pdf::loadView('reports.financial', [
            'report' => $reportData,
            'generated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'generated_by' => auth()->user()?->name ?? 'System',
        ]);

        $filename = 'financial_report_' . $reportData['month'] . '.pdf';
        $filepath = storage_path('app/exports/' . $filename);

        // Ensure directory exists
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $pdf->save($filepath);

        return [
            'filename' => $filename,
            'filepath' => $filepath,
            'url' => url('exports/' . $filename),
            'size' => filesize($filepath),
        ];
    }

    /**
     * Generate attendance report PDF
     *
     * @param array $reportData
     * @return array
     */
    public function generateAttendanceReportPdf(array $reportData): array
    {
        $pdf = Pdf::loadView('reports.attendance', [
            'report' => $reportData,
            'generated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'generated_by' => auth()->user()?->name ?? 'System',
        ]);

        $filename = 'attendance_report_' . Carbon::now()->format('Y-m-d') . '.pdf';
        $filepath = storage_path('app/exports/' . $filename);

        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $pdf->save($filepath);

        return [
            'filename' => $filename,
            'filepath' => $filepath,
            'url' => url('exports/' . $filename),
            'size' => filesize($filepath),
        ];
    }
}
