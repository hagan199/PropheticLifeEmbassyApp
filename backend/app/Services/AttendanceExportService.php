<?php

namespace App\Services;

use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceExportService
{
    /**
     * Export attendance to CSV
     *
     * @param array $filters
     * @return array
     */
    public function exportToCSV(array $filters = []): array
    {
        $query = Attendance::with(['member', 'submittedBy'])
            ->orderBy('service_date', 'desc');

        // Apply filters
        if (isset($filters['start_date'])) {
            $query->where('service_date', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->where('service_date', '<=', $filters['end_date']);
        }
        if (isset($filters['service_type'])) {
            $query->where('service_type', $filters['service_type']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $records = $query->get();

        $filename = 'attendance_' . Carbon::now()->format('Y-m-d_His') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);

        // Ensure directory exists
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $handle = fopen($filepath, 'w');
        if ($handle === false) {
            throw new \Exception('Could not create export file');
        }

        // Write header
        fputcsv($handle, [
            'ID',
            'Member Name',
            'Service Type',
            'Service Date',
            'Present',
            'Status',
            'Submitted By',
            'Submitted At',
            'Approved By',
            'Approved At',
            'Notes',
        ]);

        // Write data
        foreach ($records as $record) {
            fputcsv($handle, [
                $record->id,
                $record->member?->name ?? 'Unknown',
                $record->service_type,
                $record->service_date->format('Y-m-d'),
                $record->count > 0 ? 'Yes' : 'No',
                ucfirst($record->status),
                $record->submittedBy?->name ?? 'Unknown',
                $record->submitted_at?->format('Y-m-d H:i:s'),
                $record->approvedBy?->name ?? 'N/A',
                $record->approved_at?->format('Y-m-d H:i:s') ?? 'N/A',
                $record->notes ?? '',
            ]);
        }

        fclose($handle);

        return [
            'filename' => $filename,
            'filepath' => $filepath,
            'url' => url('exports/' . $filename),
            'count' => $records->count(),
        ];
    }
}
